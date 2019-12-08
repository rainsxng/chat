<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('chat','ChatController@index')->name('chat.index');

Route::get('/messages','ChatController@fetchMessages');

Route::post('/messages', 'ChatController@sendMessage');

Route::get('/redirect', 'SocialAuthGoogleController@redirect');

Route::get('/callback', 'SocialAuthGoogleController@callback');

Route::put('/ban', 'ChatController@banUser');

Route::put('/test', 'ChatController@banUser');

Route::put('mute', 'ChatController@muteUser');

