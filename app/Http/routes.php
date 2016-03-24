<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'RoomController@showMain');
Route::get('/admin', 'RoomController@showAdmin');
Route::get('/r/{id}', 'RoomController@showRoom');
Route::match(['get', 'post'], '/create', 'RoomController@createRoom');
Route::match(['get', 'post'], '/sendMail', 'RoomController@sendMail');
Route::match(['get','post'], '/addmessage', 'RoomController@addMessage');
Route::match(['get','post'], '/getmessage', 'RoomController@getMessage');
Route::match(['get','post'], '/addchatmessage', 'RoomController@addChatMessage');
Route::match(['get','post'], '/getchatmessage', 'RoomController@getChatMessage');
Route::match(['get','post'], '/upload', 'RoomController@fileUpload');
Route::match(['get','post'], '/saveoptions', 'RoomController@saveOptions');
Route::match(['get','post'], '/removeroom', 'RoomController@removeRoom');




/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
