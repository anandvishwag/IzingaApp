<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::post('login', 'API\AuthController@login');
Route::post('send-otp', 'API\AuthController@sendOTP');
Route::post('verify-otp', 'API\AuthController@verifyOTP');
Route::post('resister', 'API\AuthController@resister');

Route::group(['middleware'=>['auth:api','user']], function () {
    Route::post('update-mobile-number', 'API\DashboardController@phoneNumber');
    Route::post('discovery-settings', 'API\DashboardController@decoverySettings');
    Route::post('upadate-account-status', 'API\DashboardController@pauseAccount');
    Route::get('/logout', 'API\AuthController@logout');
});


