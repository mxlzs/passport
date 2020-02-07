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

Route::get('/info', function () {
    phpinfo();
});

Route::any('/reg','TestController@reg');
Route::any('/login','TestController@login');
Route::any('/time','TestController@showTime');//获取数据
Route::any('/check','TestController@check');
Route::any('/auth','TestController@auth');
