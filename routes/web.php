<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
Route::get('/sms', function () {
    return view('sms');
});
Route::post('/send-sms','API\AuthController@sendSms');

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    // return what you want
});
Route::get('/clear-view', function() {
    $exitCode = Artisan::call('view:clear');
    // return what you want
});
Route::get('/clear-config-cache', function() {
    $exitCode = Artisan::call('config:clear');
    // return what you want
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
