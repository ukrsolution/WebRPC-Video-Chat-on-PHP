<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
  <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
  <script src="https://cdn.firebase.com/js/client/2.3.2/firebase.js"></script>
  <?php
  if (count($values) > 0) 
    echo '<script type="text/javascript">var systemoption = '.json_encode($values).'</script>';
  ?>
   <?php if ($creator) : ?>
    <script type="text/javascript">var creator = true;</script>
    <?php endif; if (!$creator) : ?>
    <script type="text/javascript">var creator = false;</script>
  <?php endif;?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="../bootstrap/js/bootstrap.min.js"></script>
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
  var pathname = window.location.pathname.split('/');
  var roomid = pathname[pathname.length - 1];
  
  var RPCConnection = new USRPCConnection();
  RPCConnection.init(creator, roomid);
  
  </script>
</head>
<body>
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





<section class="content-body" role="main">
  <header class="page-header">
    <div class="container text-left">
       <?php if ($main_values['sitelogoflag'] == 1) : ?> <div class="logo"><img class="pull-left" style="height: 37px;margin: 7px 10px 0 5px;" src="../{{$main_values['sitelogo']}}"/></div> <?php endif; ?>
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
      <audio id="soundMessage" style="display: none"><source src="../sounds-message.mp3" type="audio/mpeg"></audio>
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







  <!--<script src="{{URL::asset('../resources/views/js/ajaxsignaling.js')}}"></script>-->
</body>
</html>