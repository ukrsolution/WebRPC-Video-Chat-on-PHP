this.PeerConnection = window.RTCPeerConnection || window.webkitRTCPeerConnection;
this.IceCandidate = window.RTCIceCandidate || window.RTCIceCandidate;
this.SessionDescription = window.RTCSessionDescription || window.RTCSessionDescription;
navigator.getUserMedia = navigator.getUserMedia || navigator.mozGetUserMedia || navigator.webkitGetUserMedia;

function USRPCConnection()
{
	window.USRPCConnection = this;
  
	this.browser = {
		mozilla: /firefox/i.test(navigator.userAgent),
		chrome: /chrom(e|ium)/i.test(navigator.userAgent)
	};
	
	// Properties
	this.pc;
	this.chanel;
	this.creator;
	this.roomid;
	this.socket 			= new USSocket();
	this.candidates 		= [];
	this.createRoomTimeout 	= 5000;
	this.last_time 			= "";
	this.maxUploadSize 		= parseInt(systemoption.maxuploadsize);
	this.sdpDescription;			


	// Public Methods
	this.init 					= USRPCConnection_init;
	this.connect 				= USRPCConnection_connect;
	this.getmess				= USRPCConnection_getmess;
	
	// Private Methods
	this._createRoom 			= USRPCConnection_createRoom;
	this._closeTabConfirmation 	= USRPCConnection_closeTabConfirmation;
	this._sysmess 				= USRPCConnection_sysmess;
	this._sendFile 				= USRPCConnection_sendFile;
	this._handleFileUpload 		= USRPCConnection_handleFileUpload;
	this._error 				= USRPCConnection_error;
	this._createOffer 			= USRPCConnection_createOffer;
	this._chatmessage			= USRPCConnection_chatmessage;
	this._createAnswer			= USRPCConnection_createAnswer;
	this._gotLocalDescription	= USRPCConnection_gotLocalDescription;
	this._sendMail				= USRPCConnection_sendMail;
	this._ChangeUrl 			= USRPCConnection_ChangeUrl;
	this._afterDownloadFile 	= USRPCConnection_afterDownloadFile;
	
	
	// Callback Methods
	this.cb_gotStream			= USRPCConnection_cb_gotStream;
	this.cb_gotIceCandidate		= USRPCConnection_cb_gotIceCandidate;
	this.cb_gotRemoteStream		= USRPCConnection_cb_gotRemoteStream;
	this.cb_stateChange		= USRPCConnection_cb_stateChange;
	
	return this;
}

// Initializtion of USRPCConnection Class
function USRPCConnection_init(creator, roomid)
{	
	var RPC = this;
	
	RPC.creator = creator;
	RPC.roomid = roomid;
	
	RPC.socket.init(creator, roomid);
	
	// Confirmation on Tab/Room closing
	RPC._closeTabConfirmation();
	
	// Hide video element for mobile devices
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
		$('#localVideo').css('display', 'none');
	}
	
	// Is it creator ?
	if (RPC.creator)
	{	// Yes, it is creator
			
		// Display room in 5 seconds
		setTimeout(RPC._createRoom, RPC.createRoomTimeout);
	}
	else
	{	// No, it is opponent

		// Get offer & candidates from server
		RPC.socket.reciveConnectMessage();
	}
	
	navigator.getUserMedia( { audio: true, video: true },  RPC.cb_gotStream, RPC._error);
	
	console.log('Am I Creator ? - '+RPC.creator);
}
 
//Creator: Hide preloader and display room page 
function USRPCConnection_createRoom()
{
	var RPC = window.USRPCConnection;
	var spdAndCandidates = [RPC.sdpDescription, {type: 'candidate',candidates :RPC.candidates}];
	
	// Hide preloader & display room
	jQuery('.preloader').hide(); 
	jQuery('.main-container').hide(); 
	jQuery('.room-container').show(); 
	RPC._ChangeUrl("/", "r/"+RPC.roomid);

	// Sending candidates to server
	RPC.socket.sendConnectMessage(spdAndCandidates); 
	
	console.log("Room creation & Sending candidates ("+RPC.candidates.length+") (timeout "+RPC.createRoomTimeout+")");
	
	// Send email to opponent
	RPC._sendMail();

	// Getting offer & candidates from opponent
	RPC.socket.reciveConnectMessage();
}

  
// Confirmation on Tab/Room closing
function USRPCConnection_closeTabConfirmation() 
{
	jQuery(window).bind('beforeunload', function () {
		return 'If you live this connection will fall down and you will need to create new room.';
	});
}

// System Message
function USRPCConnection_sysmess(text)
{
	var RPC = this;
	
	var myDate = new Date();
	//console.log(myDate);
	var time = "'"+myDate+"'";
	time = time.split(" ");
	$('#chatbox').append('<div class="chat-text-system chat-text"><span class="time">'+systemoption.systemsignature+' ['+time[4]+']:&nbsp;</span><span class="text">'+text+'</span></div>');
	var chatbox = $('#chatbox');
	var height = chatbox[0].scrollHeight;
	chatbox.scrollTop(height);
}

function USRPCConnection_error(message)
{
	console.log(message);
}

// Sending File
function USRPCConnection_sendFile()
{
	var RPC = this;
	
	var dropZone  = $('#chatWrapper');
	var choseElem = $('.glyphicon-open');
	var fileinput = $('#fileinput');
	dropZone.on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
	dropZone.on('dragover', function (e) 
	{
			e.stopPropagation();
			e.preventDefault();
	});
	dropZone.on('drop', function (e) 
	{
			e.preventDefault();
			var files = e.originalEvent.dataTransfer.files;
		
			//We need to send dropped files to Server
			RPC._handleFileUpload(files,dropZone);
	});
	fileinput.on('change',function (event) {
		RPC._handleFileUpload(event.target.files,choseElem);
	});
	choseElem.click(function () {fileinput.click();});

	$(document).on('dragenter', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('dragover', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('drop', function (e) 
	{
		e.stopPropagation();
		e.preventDefault();
	});
}

// handle File Upload
function USRPCConnection_handleFileUpload(files,dropZone) 
{
	var RPC = this;
	  
	if(dropZone[0].localName == 'a')
	{
		var errclass = 'glyphicon-open-error';
		var sacClass = 'glyphicon-open-sucess';
	}
	else
	{
		var errclass = 'dropZone-error';
		var sacClass = 'dropZone-sucess';
	}
	
	if (files.length > 1)
	{
		sysmess(systemoption.multifiletext);
		dropZone.addClass(errclass);
		setTimeout(function() {dropZone.removeClass(errclass);},2000);
	}
	else if (files[0].size > RPC.maxUploadSize)
	{
		sysmess(systemoption.largefiletext);
		dropZone.addClass(errclass);
		setTimeout(function() {dropZone.removeClass(errclass);},2000);
	}
	else 
	{
		var formData = new FormData();
		
		$.each(files, function (i, file){
			formData.append(i, file);
		});
		
		formData.append("roomid", RPC.roomid);
		formData.append("creator", creator);
		$.ajax({
			type: 'POST',
			url: '/laravel/public/upload',
			data: formData,
			dataType: "JSON",
			cache: false,
			processData: false,
			contentType: false,
			success: function (result)
			{
				RPC._sysmess('File sent');
				dropZone.addClass(sacClass);
				setTimeout(function() {dropZone.removeClass('success');},2000);
			}
		});
	}
}


function USRPCConnection_cb_gotStream(stream) 
{
	var RPC = window.USRPCConnection;

	document.getElementById("localVideo").src = URL.createObjectURL(stream);

	var configuration = {
		iceServers: [
			{urls: "stun:stun.l.google.com:19302"},

			{urls:"turn:23.251.135.55:3478?transport=udp",
			username:"1456553819:897661722",
			credential:"20FgAInAD/r/+cSv+JDrwri8tjA="},
			{urls:"turn:23.251.135.55:3478?transport=tcp",
			username:"1456553819:897661722",
			credential:"20FgAInAD/r/+cSv+JDrwri8tjA="},
			{urls:"turn:23.251.135.55:3479?transport=udp",
			username:"1456553819:897661722",
			credential:"20FgAInAD/r/+cSv+JDrwri8tjA="},
			{urls:"turn:23.251.135.55:3479?transport=tcp",
			username:"1456553819:897661722",
			credential:"20FgAInAD/r/+cSv+JDrwri8tjA="},
			{urls:"turn:numb.viagenie.ca",
			username:"postakk1@gmail.com",
			credential:"qwerty123"}]
	};

	var optionalRtpDataChannels = {
		optional: [{ DtlsSrtpKeyAgreement: true },{
			RtpDataChannels: true
		}]
	};

	RPC.pc = new PeerConnection(configuration);
	RPC.pc.addStream(stream);
	RPC.pc.onicecandidate = RPC.cb_gotIceCandidate;
	RPC.pc.oniceconnectionstatechange = RPC.cb_stateChange;

	//pc.ondatachannel = openAnswerChannel;
	RPC.pc.onaddstream = RPC.cb_gotRemoteStream;
	
	if (creator == true)
	{
		RPC._sysmess('Waiting for oponent...');
		RPC._createOffer(); 
	}
	else 
		RPC._sysmess('Connection...');

}   

function USRPCConnection_cb_stateChange(ev) 
{
  var RPC = window.USRPCConnection;
  if(ev.target.iceConnectionState == 'disconnected') 
  {
    console.log('connection disconnected');
    RPC.pc.close();
    document.getElementById("localVideo").src = "";
    RPC.socket.closeRoom();
  };
  
};

  // Step 2. createOffer
function USRPCConnection_createOffer() 
{	
	var RPC = window.USRPCConnection;
	
	var options = (RPC.browser.mozilla) ? {'OfferToReceiveAudio': true, 'OfferToReceiveVideo': true} : { 'mandatory': {'OfferToReceiveAudio': true, 'OfferToReceiveVideo': true} };
	RPC.pc.createOffer(
		RPC._gotLocalDescription, 
		RPC._error, 
		options
	);
}

function USRPCConnection_chatmessage() 
{	
	var RPC = this;
	
	$('#chatForm').submit(function (event) {
		
		var input = $('#chatinput');
		
		if (input.val() != "")
		{
			RPC.socket.sendChatMessage(input.val());
			input.val("");
		}
		event.preventDefault();
	});
}

// Step 3. createAnswer
function USRPCConnection_createAnswer() 
{	
	var RPC = this;
	
	var options = (RPC.browser.mozilla) ? {'OfferToReceiveAudio': true, 'OfferToReceiveVideo': true} : { 'mandatory': {'OfferToReceiveAudio': true, 'OfferToReceiveVideo': true} };
	RPC.pc.createAnswer(
		RPC._gotLocalDescription,
		RPC._error, 
		options
	);
}


function USRPCConnection_gotLocalDescription(description)
{
	var RPC = window.USRPCConnection;
	
	RPC.pc.setLocalDescription(description);
	//console.log(description);
	//RPC.socket.sendConnectMessage(description);
	RPC.sdpDescription = description;
}
  
function USRPCConnection_cb_gotIceCandidate(event)
{	
	var RPC = window.USRPCConnection;
	
	//console.log(event.candidate);

	// Works perfectly in FF but in Chrome with delay in a fe mins
	if (event.candidate == null) {
		console.log('Last null candidate has been found.');
	}
	
	if (event.candidate) {
		RPC.candidates.push({
			label: event.candidate.sdpMLineIndex,
			id: event.candidate.sdpMid,
			candidate: event.candidate.candidate
		});
	}
}

function USRPCConnection_cb_gotRemoteStream(event)
{
	document.getElementById("remoteVideo").src = URL.createObjectURL(event.stream);
}

function USRPCConnection_connect(messages)
{
	var RPC = window.USRPCConnection;

	for (var index in messages)
	{
		message = messages[index];

		if (RPC.creator == false && message.type === 'offer') 
		{
			RPC.pc.setRemoteDescription(new SessionDescription(message), function () {RPC._createAnswer(); setTimeout(function () {
				var spdAndCandidates = [RPC.sdpDescription, {type: 'candidate',candidates :RPC.candidates}];
				RPC.socket.sendConnectMessage(spdAndCandidates);
			}, 1000)}, RPC._error);
			RPC._sysmess('Connected');
			RPC._sendFile();
			RPC._chatmessage();
			RPC.socket.getChatMessage();
			$('#chatinput, #chatsend').attr('disabled', false);
			$('.glyphicon-open').removeClass('upload-disabled');
		} 
		else if (RPC.creator == true && message.type === 'answer') 
		{
			RPC.pc.setRemoteDescription(new SessionDescription(message));
			RPC._sysmess('Connected!');
			RPC._chatmessage();
			RPC._sendFile();
			RPC.socket.getChatMessage();
			$('#chatinput, #chatsend').attr('disabled', false);
			$('.glyphicon-open').removeClass('upload-disabled');
		} 
	}
	for (var index in messages)
	{
		message = messages[index];
		console.log(message);
		if (message.type === 'candidate') {
			for (var i = 0; i < message.candidates.length; i++)
			{
			var candidate = new IceCandidate({sdpMLineIndex: message.candidates[i].label, candidate: message.candidates[i].candidate, sdpMid: message.candidates[i].id});
			console.log(candidate);
			RPC.pc.addIceCandidate(candidate);
			}
		}
	}
}


function USRPCConnection_sendMail()
{
	var RPC = this;
	$.ajax({
		type: 'post',
		url: '/laravel/public/sendMail',
		data: {roomid: RPC.roomid, url:window.location.href},
		dataType: "JSON",
		error : RPC.error,
		success: function (result)
		{
			console.log('Email sent');
		}
	});
}

function USRPCConnection_ChangeUrl(page, url) 
{
	if (typeof (history.pushState) != "undefined") 
	{
		var obj = { Page: page, Url: url };
		history.pushState(obj, obj.Page, obj.Url);
	} 
	else 
	{
		alert("Browser does not support HTML5.");
	}
}

function USRPCConnection_getmess() 
{
	var RPC = window.USRPCConnection;
	
	$.ajax({
		url:'/laravel/public/getchatmessage',
		dataType: 'json',
		method: 'post',
		data: {
			creator:creator,
			roomid:RPC.roomid,
			lasttime:RPC.last_time
		},
		error: function (err) {
			setTimeout('window.USRPCConnection.getmess()',2000);
			RPC._error(err);
		},
		success: function (messages) {
			RPC.last_time = messages.last_mess_time;
			
			setTimeout('window.USRPCConnection.getmess()',2000);
			
			if (messages.messages.length > 0)
			{
			$.each(messages.messages, function (index) {
				var myDate = new Date();
				
				var time = "'"+myDate+"'";
				time = time.split(" ");
				$('#chatbox').append('<div class="chat-text-oponent chat-text"><span class="time">'+systemoption.oponentsignature+' ['+time[4]+']:&nbsp;</span><span class="text">'+messages.messages[index].message_text+'</span></div>');
				var chatbox = $('#chatbox');
				var patforfile = /<span data-type="file"><\/span>/;
				if (patforfile.test(messages.messages[index].message_text))
				{
				$('#fileModal .modal-body #fileLinkSpan').html(messages.messages[index].message_text);
				//$('#fileModal').modal();
				RPC._afterDownloadFile();
				}
				$('#soundMessage')[0].play();
				var height = chatbox[0].scrollHeight;
				chatbox.scrollTop(height);
			});
			}
		}
	});
}

function USRPCConnection_afterDownloadFile()
{
  $('#fileLinkSpan').click(function () {$('#fileModal').modal('hide');});
}

/*
function USRPCConnection_downloadFromModal()
{
  
  $('#fileLinkSpan').find('a')[0].click();

}*/
