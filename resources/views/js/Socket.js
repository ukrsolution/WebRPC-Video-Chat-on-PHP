function USSocket()
{	
	window.USSocket = this;
	
	this.counter = 0;
	this.roomid;
	this.creator;
	
	this.RPC = window.USRPCConnection;
	
	this.sendConnectMessage = USSocket_sendConnectMessage;
	this.reciveConnectMessage = USSocket_reciveConnectMessage;
	this.sendChatMessage = USSocket_sendChatMessage;
	this.getChatMessage = USSocket_getChatMessage;
	this.closeRoom = USSocket_closeRoom;
	this.init = USSocket_init;

	return this;
}

function USSocket_init(creator, roomid)
{
	this.creator = creator;
	this.roomid = roomid;
}

function USSocket_sendConnectMessage(message) 
{
	var SO = this;
	var smessage = [];
	if (message[0].type == 'offer' || message[0].type == 'answer')
		smessage.push({sdp: message[0].sdp, type: message[0].type});
	smessage.push(message[1]);
	var host = 0, srflx = 0, relay = 0;
	var hostPattern = /(typ host)/;
	var srflxPattern = /(typ srflx)/;
	var relayPattern = /(typ relay)/;
	for (var i = 0; i < message[1].candidates.length; i++)
	{
		if (hostPattern.test(message[1].candidates[i].candidate))
			host++;
		if (srflxPattern.test(message[1].candidates[i].candidate))
			srflx++;
		if (relayPattern.test(message[1].candidates[i].candidate))
			relay++;
	}
	if (relay == 0 && SO.creator)
		jQuery('#TurnModal').modal();
	console.log('Candidates host: '+host+'; srflx(stun): '+srflx+'; relay(turn): '+relay+';');
	var isOffer = (SO.creator) ? true : false;
	var data = {
		roomid : SO.roomid,
		message : smessage,
		dataType: 'JSON',
		isOffer : isOffer
	};

	console.log("Next Object will be send to server:");
	console.log(data);

	// Sending candidates to server
	$.ajax({
	url: '/laravel/public/addmessage',
	method: 'post',
	data: data,
	error: function(err)
	{
		console.log("sendConnectMessage: error:");	
		console.log(err);
	},
	success: function (success)
	{
		if (creator)
			console.log("sendConnectMessage: Offer and Candidates sent to server Sucessfuly (Creator)");
		else
			console.log("sendConnectMessage: Offer and Candidates sent to server Sucessfuly (Opponent)");
	}
	});
}

function USSocket_reciveConnectMessage()
{
	var SO = this;
	
	console.log("reciveConnectMessage - starting...");
      var offer = (!SO.creator) ? true : false;

      var interval = setInterval( function (){

        console.log("reciveConnectMessage...");
        
        var data = {};
        data.roomid = roomid;
        data.offer = offer;        


        $.ajax({
          url: '/laravel/public/getmessage',
          method: 'post',
          data: data,
          error: function (err)
          {
            console.log(err);
          },
          success: function (sdata)
          {
            if (sdata != "")
            {
              clearInterval(interval);
              console.log(jQuery.parseJSON(sdata));
              SO.RPC.connect(jQuery.parseJSON(sdata));
            }
          }
        });
      }, 5000);
}


function USSocket_closeRoom()
{
  var SO = window.USSocket;
  var data = {roomid : SO.roomid};
  $.ajax({
      url:'/laravel/public/removeroom',
      method: 'post',
      data: data,
      error: function (err) {
        console.debug(err);
      },
      success: function (success)
      {
	console.log('Room closet');
      }
  });
}

function USSocket_sendChatMessage(mtext)
{
	var SO = window.USSocket;
	
	 var data = {
      creator: SO.creator,
      text: mtext,
      roomid: SO.roomid
    };
    $.ajax({
      url:'/laravel/public/addchatmessage',
      method: 'post',
      data: data,
      error: function (err) {
        console.debug(err);
      },
      success: function (success)
      {
        var myDate = new Date();
        console.log(myDate);
        var time = "'"+myDate+"'";
        time = time.split(" ");
        $('#chatbox').append('<div class="chat-text-owner chat-text"><span class="time">'+systemoption.ownersignature+' ['+time[4]+']:&nbsp;</span><span class="text">'+mtext+'</span></div>');
        var chatbox = $('#chatbox');
        var height = chatbox[0].scrollHeight;
        chatbox.scrollTop(height);
      }
    });
}

function USSocket_getChatMessage()
{
	var SO = window.USSocket;
	
	SO.RPC.getmess();
}