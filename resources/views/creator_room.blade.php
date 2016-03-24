<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
  <script src="https://cdn.firebase.com/js/client/2.3.2/firebase.js"></script>
  <?php
  if (count($values) > 0) 
    echo '<script type="text/javascript">var systemoption = '.json_encode($values).'</script>';
  ?>
  <script type="text/javascript">
  var creator = true;
  </script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <script src="{{URL::asset('../resources/views/js/RPCConnection.js')}}"></script>
  <script src="{{URL::asset('../resources/views/js/Socket.js')}}"></script>
  <!--<script src="{{URL::asset('../resources/views/js/firebase.js')}}"></script>-->
  <script type="text/javascript">
  $(document).ready(function () {
    if (systemoption.chatsendflag == 1)
    {
      $('#chatsend').css('display','block');
      $('.chatInput-label').css('width','80%');
    }
  else
    {
      $('#chatsend').css('display','none');
      $('.chatInput-label').css('width','100%');
    }
  });
  </script>
  <title>Video Calls</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">
	<style>
	html,body {
	height:98%;
	color:#f5f5f5;
	font-family: tahoma, arial, verdana;
	overflow: hidden;
	}
	body {
	width: 100%;
	display: table;
	/*background-color: #ECECEC;*/
	}
	.main-container{
	text-align:center;
	display: table-cell;
	}
	.main-content{
		padding-top:220px;
		font-size:16px;
		font-weight:bold;
	}
	.preloader {
		width: 100%;
		height: 100%;
		position: fixed;
		display: block;
		background-color: #FFF;
		opacity: 0.5;
		display: none;
	}
	.preloader img {
		position: absolute;
		top: calc(50% - 240px);
		left: calc(50% - 40px);

	}
	</style>
</head>
<body>

<div class="preloader" style="display: none"><img src="loading.gif"/></div>
<div class="container main-container" style="display: block;">
    <div class="content main-content">
    <?php if ($main_values['sitelogoflag'] == 1) : ?> <div class="logo"><img src="{{$main_values['sitelogo']}}"/></div> <?php endif; ?>
	<form method="get" id="form" name="room" action="create">
		<div class="row">
			<!--<label><span>Enter your email</span><br/><input id="emailCreator" type="email" placeholder="my@mail.com" name="emailCreator"/></label><br/><br/>-->
			<label><span style="color: #000">{{$main_values['emaillabel']}}</span><br/><input class="form-control" id="emailOponent" type="email" placeholder="{{$main_values['emailplaceholder']}}" name="emailOponent"/></label><br/>
            <input class="btn btn-info button" type="submit" onclick="formValidate(event)" value="{{$main_values['sendemailbutton']}}" class=""/>
        </div>
	</form>
	<script type="text/javascript">
	var roomid = '';
	function formValidate(ev) {
	ev.preventDefault();
	//var emailCreator = $('#emailCreator');
	var emailOponent = $('#emailOponent');
	//if (emailCreator.val() == "")
		//alert("enter your email");
	if (emailOponent.val() == "")
	{
		alert("enter your oponent email");
		return;
	}
	else if (isValidEmailAddress(emailOponent.val()) == false)
	{
		alert("enter valid email adress");
		return;
	}
	else 
	{
		jQuery('.preloader').show();
		var data = {emailOponent : emailOponent.val()};
		$.ajax({
			method:"post",
			type:"JSON",
			url:"create",
			data: data,
			error: function(err){
				console.log(err);
			},
			success: function (sdata){
				roomid = sdata;
				var RPCConnection = new USRPCConnection();
				RPCConnection.init(creator, roomid);
			}
		});
	}
	}
	function isValidEmailAddress(emailAddress) {
	    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
	    return pattern.test(emailAddress);
	};
	</script>
    </div>
</div>

  <!-- Modal -->
  <div class="modal fade" id="fileModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">File Recieved</h4>
        </div>
        <div class="modal-body">
          <p><span id="fileLinkSpan"></span> has been sent to you. Do you want to download it ?</p>
        </div>
        <div class="modal-footer">
          <button type="button" onclick="downloadFromModal()" class="btn btn-info">Download</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>

<div style="display: none;" class="room-container">
<section class="content-body" role="main">
  <header class="page-header">
    <div class="container text-left">
       <?php if ($main_values['sitelogoflag'] == 1) : ?> <div class="logo"><img class="pull-left" style="height: 37px;margin: 7px 10px 0 5px;" src="{{$main_values['sitelogo']}}"/></div> <?php endif; ?>
      <h2><a href="{{URL::asset('')}}">Video Chat</a></h2>
    </div>
  </header>
</section>

<div class="container video-container" style="padding-top: 12px;">
  <div class="row">
    <div class="col-md-9">
      <video id="remoteVideo" autoplay></video>
    </div>

    <div class="col-md-3">
      <video id="localVideo" autoplay muted></video>
      <audio id="soundMessage" style="display: none"><source src="sounds-message.mp3" type="audio/mpeg"></audio>
      <div class="chatWrapper" id="chatWrapper">
        <div class="chatBox" id="chatbox">
          <form style="display:none;" action="nowhere" id="fileForm"><input name="file" type="file" id="fileinput"/></form>
        </div>
        <div class="chatForm-div">
          <form id="chatForm" method="post" action="nowhere">
            <div class="chatInput-label pull-left"  style="width:80%; margin:0;">
              <input class="form-control" style="width:100%; margin:0; " disabled="disabled" type="text" autocomplete="off" id="chatinput"/>
              <a class="glyphicon glyphicon-open upload-disabled"></a>
            </div>
            <input disabled="disabled" class="btn btn-default btn-md" type="submit" id="chatsend" style="width: 20%; margin:0;" value="{{$values['chatbuttontext']}}">
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
</div>
</body>
</html>