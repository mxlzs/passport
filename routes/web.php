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

Route::post('/reg','TestController@reg');
Route::post('/login','TestController@login');
Route::get('/time','TestController@showTime');//获取数据
Route::get('/check','TestController@check');//get签名
Route::post('/check2','TestController@check2');//post签名
Route::post('/auth','TestController@auth');//鉴权
Route::get('/decrypt2','TestController@decrypt2');//对称加密
Route::any('/decrypt','TestController@decrypt');//非对称加密