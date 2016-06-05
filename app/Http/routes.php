<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', [
    'uses' => 'TwilioController@index'
]);


Route::get('phone-number', [
    'as' => 'phone-number',
    'uses' => 'TwilioController@phoneNumber'
]);

Route::post('incoming', [
    'as' => 'incoming',
    'uses' => 'TwilioController@incoming'
]);
