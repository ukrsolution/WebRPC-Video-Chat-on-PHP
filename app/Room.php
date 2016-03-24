<?php

namespace App;

use DB;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{

	public static function getConfigValues()
	{
		$result = DB::select('SELECT * FROM `options` GROUP BY `type`, `id`');
		return $result;
	}

    public static function insertRoom($id, $emailOponent)
	{
		$result = DB::insert('insert into rooms (roomid, oponent_email) values (?, ?)', [$id, $emailOponent]);
		return $result;
	}

  public static function delRoom($roomid)
  {
      $result = DB::delete('delete from rooms where roomid = ?', [$roomid]);
      return $result;
  }
	    
	  
    public static function getRoom($id)
	{
	$results = DB::select('select count from rooms where roomid = ?', [$id]);
	return $results;
	}

    public static function updateRoomCount($id,$count)
	{
		return DB::table('rooms')
            ->where('roomid', $id)
            ->update(['count' => $count]);
	}
	public static function updateMessage($message, $roomid, $isOffer)
	{
		$field = ($isOffer) ? 'offer_message' : 'answer_message';
		$result = DB::update('update rooms set '.$field.' = ? where roomid = ?', [$message, $roomid]);
		if ($isOffer)
			$res = DB::update('update rooms set isReed = 1 where roomid = ?', [$roomid]);
		return $result;
	}

	public static function getMessage($roomid, $offer)
	{
		$isRead = ($offer) ? ' and isReed = 1' : ''; 
		$field = ($offer) ? 'offer_message' : 'answer_message' ;
		$result = DB::select('select '.$field.' from rooms where roomid = ?'.$isRead, [$roomid]);
		return $result;
	}

	public static function addChatMessage($data)
	{
		$result = DB::insert('insert into messages (room_id, message_text, is_creator) values (?,?,?)', [$data['roomid'],$data['text'],$data['creator']]);
		return $result;
	}

	public static function getChatMessage($data)
	{
		$result = DB::select('select message_text, time from messages where room_id = ? and is_creator != ? and time > ?', [$data['roomid'], $data['creator'], $data['lasttime']]);
		return $result;
	}
	public static function saveOptions($data)
	{
		$result = array();
		foreach ($data as $key => $value) {
			$result[] = DB::update('update `options` set value = ? where option_key = ?', [$value, $key]);
		}
		if (in_array(1, $result))
			return true;
	}
	public static function getEmail($roomid)
	{
		$result = DB::select('select oponent_email from rooms where roomid = ?', [$roomid]);
		return $result[0]->oponent_email;
	}

}
