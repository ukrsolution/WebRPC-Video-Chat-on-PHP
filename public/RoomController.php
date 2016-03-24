<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class RoomController extends Controller {

  /**
   * Показать профиль данного пользователя.
   *
   * @param  int  $id
   * @return Response
   */
  public function showRoom($id)
  {
    return "Hi, you are in room ".$id. "now";//view('user.profile', ['user' => User::findOrFail($id)]);
  }

}