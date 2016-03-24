<?php namespace App\Http\Controllers;
use Request;
use App\Http\Controllers\Controller;
use App\Room;
use Session;
use IlluminateDatabaseEloquentModel;

class RoomController extends Controller {

	private $values = array();

	private function init($view = false)
	{
		$result = Room::getConfigValues();
		if ($view)
		{
			$values = array();
			foreach ($result as $key => $value) {
				if ($value->type == $view)
					$values[$value->option_key] = $value->value;
			}
		}
		else
			$values = $result;
		return $values;
	}

	public function showMain()
	{

		return view('creator_room')->with('values',$message = $this->init('room'))->with('main_values', $this->init('main'));
	}

	public function showAdmin()
	{
		$vals = $this->init();
		foreach ($vals as $key => $value) {
			if ($value->option_key == 'maxuploadsize')
			 	$value->value /= 1048576;
		}
		return view('admin')->with('values',$vals);
	}

	public function saveOptions()
	{

		$data = Request::all();
		$publicPath = public_path().'/';
		$uploadPath = 'admin_files';
		if (!is_dir($publicPath.$uploadPath))
			mkdir($publicPath.$uploadPath);
		if (Request::hasFile("file"))
		{
			$file = Request::file("file");
			if (!$file->isValid())
			{
				$message = 'file is not valid';
				$_SESSION['message'] = $message;
				return redirect('admin');
			}
			$fileType = explode('/', $file->getMimeType());
			if ($fileType[0] != 'image')
			{
				
				$message = 'File is not an image';
				//Session::put('message', $message); 
				$_SESSION['message'] = $message;
				return redirect('admin');
			}
			$fileName = 'logo.'.$fileType[1];
			$temparr = scandir($publicPath.$uploadPath);
				foreach ($temparr as $key => $value) {
						if ($value != '.' && $value != '..')
							unlink($publicPath.$uploadPath.'/'.$value);
					}
			unset($temparr);
			$file->move($publicPath.$uploadPath, $fileName);
			$data['sitelogo'] = $uploadPath.'/'.$fileName;
		}
		if (isset($data['maxuploadsize']))
		{
			$data['maxuploadsize'] *= 1048576;
		}
		$data['sitelogoflag'] = (isset($data['sitelogoflag']) && $data['sitelogoflag'] == 'on') ? 1 : 0;
		$data['chatsendflag'] = (isset($data['chatsendflag']) && $data['chatsendflag'] == 'on') ? 1 : 0;
		$message = Room::saveOptions($data);
		if ($message)
			return redirect('admin');
		else
			return redirect('admin');
	}
  /**
   * Показать профиль данного пользователя.
   *
   * @param  int  $id
   * @return Response
   */
  public function showRoom($id)
  {
	$isRoom = Room::where('roomid','=',$id)->count();
	if ($isRoom)
	{
		$count = Room::getRoom($id);
		$count = $count[0]->count;
		if ($count == 0)
		{ 
		  Room::updateRoomCount($id,$count+1);
    		  return $id;
		}
		else
		   return view('room')->with('creator', false)->with('values',$message = $this->init('room'))->with('main_values', $this->init('main'));
			
	}
	else
		return "This room does not exist";
  }

public function createRoom()
{
	srand((double) microtime() * 1000000);
	//$emailCreator = Request::input('emailCreator');
	$emailOponent = Request::input('emailOponent');
	$roomid = rand(100000000,999999999);
	if (Room::insertRoom($roomid, $emailOponent))
	{
			echo $this->showRoom($roomid);
	}
	exit();
}


public function removeRoom()
{
      $roomid = Request::input('roomid');
      $delres = Room::delRoom($roomid);
      $publicPath = public_path();
      $uploadPath = '/user_files/';
      $dir = $publicPath.$uploadPath.$roomid;
      if (is_dir($dir))
      {
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) {
	if (!is_dir($dir.$file))
	  unlink($dir.'/'.$file); 
	} 
	if (rmdir($dir) && $delres)
	  echo true;
      }
      exit();
}

	public function addMessage()
	{
		$data = Request::all();
		$data['isOffer'] = ($data['isOffer'] == 'true') ? true : false;
		$jsonMess = Room::getMessage($data['roomid'], $data['isOffer']);
		if ($data['isOffer'])
			$arrMess = (isset($jsonMess[0]->offer_message) && !empty($jsonMess[0]->offer_message)) ? json_decode($jsonMess[0]->offer_message,true) : array();
		else 
			$arrMess = (isset($jsonMess[0]->answer_message) && !empty($jsonMess[0]->answer_message)) ? json_decode($jsonMess[0]->answer_message,true) : array();
		$arrMess[] = $data['message'][0];
		$arrMess[] = $data['message'][1];
		$result = Room::updateMessage(json_encode($arrMess), $data['roomid'], $data['isOffer']);
		echo ($result);
		exit();
	}

	public function getMessage()
	 {

	 	$data = Request::all();
	 	$data['offer'] = ($data['offer'] == 'true') ? true : false;
	 	$jsonMess = Room::getMessage($data['roomid'], $data['offer']);
	 	if (count($jsonMess) == 0)
	 	{
	 		echo '';
	 		exit();
	 	}
	 	if ($data['offer'])
	 	echo $jsonMess[0]->offer_message;
	 	else 
	 	echo $jsonMess[0]->answer_message;
	 	//echo "yes";//$jsonMess = Room::getMessage($data['roomid']);
	 	exit();
	 }
	 //todo
	public function fileUpload()
	{
		$data = array();
		$data['roomid'] = Request::input('roomid');
		$data['creator'] = (Request::input('creator') == 'true') ? 1 : 0;
		$publicPath = public_path();
		$uploadPath = '/user_files';
		$roomPath = $uploadPath.'/'.$data['roomid'];
		if (Request::hasFile("0"))
		{
			$file = Request::file("0");
			if ($file->isValid())
			{
				if(!is_dir($publicPath.$roomPath))
					mkdir($publicPath.$roomPath);
				if(file_exists($publicPath.$roomPath.'/'.$file->getClientOriginalName()))
					unlink($publicPath.$roomPath.'/'.$file->getClientOriginalName());
				$file->move($publicPath.$roomPath,$file->getClientOriginalName());
				//$data['text'] = '<span data-type="file"></span>File: <a href="'.url($roomPath,$file->getClientOriginalName()).'" target="_blank">'.$file->getClientOriginalName().'</a>';
				$data['text'] = '<span data-type="file"></span>File: '.$file->getClientOriginalName().' <a class="btn btn-info" href="'.url($roomPath,$file->getClientOriginalName()).'" target="_blank">Download</a>';
				$result = Room::addChatMessage($data);
			}
		}
		echo ($result);
		exit();
	} 

	public function addChatMessage()
	{
		$data = Request::all();
		$data['creator'] = ($data['creator'] == 'true') ? 1 : 0;
		$result = Room::addChatMessage($data);
		echo ($result);
		exit();
	}

	public function getChatMessage()
	 {
	 	$data = Request::all();
	 	$data['creator'] = ($data['creator'] == 'true') ? 1 : 0;
	 	$data['lasttime'] = ($data['lasttime'] == "") ? date('Y-m-d H:i:s') : $data['lasttime'];
	 	$result = Room::getChatMessage($data);
	 	$messages = (!$result) ? '' : $result;
	 	$lasttime = (!$result) ? $data['lasttime'] : $result[count($result) - 1]->time;
	 	$message = array(
	 		'messages' => $result,
	 		'last_mess_time' => $lasttime
	 		);
	 	echo json_encode($message);
	 	exit();
	 }



	 public function sendMail()
	 {
	 	$data = Request::all();
	 	$data['email'] = Room::getEmail($data['roomid']);
	 	$message = $this->init('email');
	 	$message['mailtext'] = str_replace('{%room_url%}', $data['url'], $message['mailtext']);
	 	$headers  = "Content-type: text/html; charset=UTF-8 \r\n"; 
		$headers .= "From: ".Request::getHost()." <admin@".Request::getHost().">\r\n"; 
		$headers .= "Bcc: admin@".Request::getHost()."\r\n";
	 	//$message = 'Hello! '."\n".'You have invited to video chat, please follow this URL:'."\n".$data['url'];
	 	$mailstatus = mail($data['email'], $message['mailsubject'], $message['mailtext'], $headers);
	 	return json_encode($mailstatus);
	 }
}
