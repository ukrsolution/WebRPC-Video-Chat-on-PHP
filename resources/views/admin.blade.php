<!DOCTYPE html>
<html>
    <head>
        <title>Video Calls</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
        <script src="bootstrap/dist/js/bootstrap-checkbox.min.js" defer></script>
        <script>$(document).ready(function () {
        	disable();
        	tinymce.init({ selector:'#email-text', menubar: false });
        	function disable()
        	{
        		if ($('#chatsendflag').prop('checked') == false)
        			$('input[name="chatbuttontext"]').prop('disabled',true).addClass('disabled');
        		else
        			$('input[name="chatbuttontext"]').prop('disabled',false).removeClass('disabled');
        	}
        	$('input[type="checkbox"]').checkboxpicker().change(function() {
        		disable();
        	});
        });
        </script>
        <style>
        body, html {
            background: #ecedf0;
            padding: 0px;
        }
        .panel-heading {
            background: #f6f6f6;
            border-bottom: 1px solid #DADADA;
            padding: 20px;
        }
        .page-header {
            background: #111;
            color: #f6f6f6;
            height: 50px;
            margin: 0px;
            padding: 0px;
        }
        .page-header h2 {
            margin: 0px;
            height: 50px;
            padding: 10px 20px;
        }
        .page-header div {
            height: 50px;
            vertical-align: middle;
            padding: 15px 20px;
        }
        .page-body {
            padding: 30px;
        }
        </style>
    </head>
    <body class="center">
        <section class="content-body" role="main">
        <header class="page-header">
            <h2 class="col-md-4">Video Chat</h2>
            <div class="right-wrapper pull-right"><span>hello, Admin</span></div>
        </header>
        <div class="container page-body">
            <?php 
            if (isset($_SESSION['message']) && !empty($_SESSION['message']))
                echo '<div class="alert alert-danger">'.$_SESSION['message'].'</div>';
            unset($_SESSION['message']);
            ?>
            <form  enctype="multipart/form-data" name="options" method="post" action="saveoptions">
            <!--section of main params -->
                <div class="row panel">
                    <div class="col-md-6 col-lg-12 col-xl-6">
                        <section class="row">
                            <header class="panel-heading"><h2 class="panel-title">Main page settings</h2></header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-none">
                                        <thead>
                                        <tr>
                                            <th class="col-md-1">#</th>
                                            <th class="col-md-6">option</th>
                                            <th class="col-md-5">value</th>
                                        </tr>    
                                        </thead>
                                        <tbody>
                                        <?php $index = 1; foreach ($values as $key => $value): 
                                            if ($value->type == 'main') : if($value->option_key == 'sitelogo'): ?>
                                            <tr>
                                                <td>{{$index}}</td>
                                                <td class="row">
                                                    <div class="col-md-4" style="padding-left:0px;">{{$value->option}}</div>
                                                    <div class="col-md-8"><img style="width:100%; max-width:300px;" src="{{$value->value}}" /></div>
                                                </td>
                                                <td><input name="file" class="col-md-12" type="file" value=""/></td>
                                            </tr>
                                            <?php else: ?>
                                            <tr>
                                                <td>{{$index}}</td>
                                                <td>{{$value->option}}</td>
                                                <?php if($value->option_key == 'sitelogoflag') : ?>
                                                <td>
                                                      <input name="{{$value->option_key}}" type="checkbox" <?php echo ($value->value == 1) ? 'checked' : '' ; ?> >
                                                </td>
                                                <?php else: ?>
                                                <td><input name="{{$value->option_key}}" class="col-md-12" type="text" value="{{$value->value}}"/></td>
                                            </tr>
                                            <?php $index++; endif; endif; endif; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <!--end section of main params -->
            <!--section of room params -->
                <div class="row panel">
                    <div class="col-md-6 col-lg-12 col-xl-6">
                        <section class="row">
                            <header class="panel-heading"><h2 class="panel-title">Room page settings</h2></header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-none">
                                        <thead>
                                        <tr>
                                            <th class="col-md-1">#</th>
                                            <th class="col-md-6">option</th>
                                            <th class="col-md-5">value</th>
                                        </tr>    
                                        </thead>
                                        <tbody>
                                        <?php $index = 1; foreach ($values as $key => $value): 
                                            if ($value->type == 'room') : ?>
                                            <tr>
                                                <td>{{$index}}</td>
                                                <td>{{$value->option}}</td>
                                                <?php if($value->option_key == 'chatsendflag') : ?>
                                                <td>
													  <input name="{{$value->option_key}}" id="chatsendflag" type="checkbox" <?php echo ($value->value == 1) ? 'checked' : '' ; ?> >
												</td>
												<?php  elseif ($value->option_key != 'chatsendflag'): ?>
                                                <td><input  name="{{$value->option_key}}" class="col-md-12" type="<?php echo ($value->option_key == 'maxuploadsize') ? 'number' : 'text' ?>"/ value="{{$value->value}}"/></td>
                                            </tr>
                                            <?php $index++; endif; endif; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <!--end section of room params -->
            <!--section of email params -->
                <div class="row panel">
                    <div class="col-md-6 col-lg-12 col-xl-6">
                        <section class="row">
                            <header class="panel-heading"><h2 class="panel-title">Email settings</h2></header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                        <?php $index = 1; foreach ($values as $key => $value): 
                                            if ($value->type == 'email') : ?>
                                            <div class="">
                                                <div class="col-md-4">{{$value->option}}</div>
                                                <?php if ($value->option_key == 'mailtext'): ?>
                                                <div class="col-md-8"><textarea name="{{$value->option_key}}" id="email-text" class="col-md-8" type="text">{{$value->value}}</textarea></div>
                                                <div class=" col-md-8 pull-right text-center">For insert into letter link to the room just put this {%room_url%} in the letter text.</div>
                                                <?php else: ?>
                                                <div class="col-md-8" style="margin-bottom: 20px;"><input name="{{$value->option_key}}" class="col-md-12" type="text"/ value="{{$value->value}}"/></div>
                                            </div>
                                    <?php $index++; endif; endif; endforeach; ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="text-center">
                <input type="submit" value="Save" class="btn  btn-primary"/>
                </div>
                <!--end section of email params -->
                </form>
            </div>
        </section>

    </body>
</html>
