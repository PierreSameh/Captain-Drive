<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\DriverController;
 



//AuthController
Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/forgot-password', [AuthController::class, "forgetPassword"]);
Route::post('/user/forgot-password-change', [AuthController::class, "forgetPasswordCheckCode"]);
Route::post('/user/login', [AuthController::class, 'login']);
Route::get('/user/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');
Route::get('/user/ask-email-verfication-code', [AuthController::class, "askEmailCode"])->middleware('auth:sanctum');
Route::post('/user/verify-email', [AuthController::class, "verifyEmail"])->middleware('auth:sanctum');
Route::post('/user/change-password', [AuthController::class, "changePassword"])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class,'getUser'])->middleware('auth:sanctum');
Route::post('/user/edit', [AuthController::class,"editProfile"])->middleware('auth:sanctum');


//Socialite
Route::get('/auth/google', [SocialiteController::class,'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class,'handleGoogleCallback']);
Route::get('/auth/facebook', [SocialiteController::class,'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class,'handleFacebookCallback']);

//AddressController
Route::post('/address/add-address', [AddressController::class,'addAddress'])->middleware('auth:sanctum');
Route::post('/address/{address}/update-address', [AddressController::class,'updateAddress'])->middleware('auth:sanctum');
Route::get('/address/all-addresses', [AddressController::class,'getAllAddresses'])->middleware('auth:sanctum');
Route::get('/address/user-addresses', [AddressController::class,'getUserAddresses'])->middleware('auth:sanctum');
Route::get('/address/{address}', [AddressController::class,'getAddress'])->middleware('auth:sanctum');
Route::post('/address/{address}/delete-address', [AddressController::class,'deleteAddress'])->middleware('auth:sanctum');

//DriverController
Route::post('/driver/register', [DriverController::class,'register']);
Route::get('/driver/ask-email-verfication-code', [DriverController::class, "askEmailCode"])->middleware('auth:sanctum,driver');
Route::post('/driver/verify-email', [DriverController::class, "verifyEmail"])->middleware('auth:sanctum,driver');
Route::post('/driver/change-password', [DriverController::class, "changePassword"])->middleware('auth:sanctum,driver');
Route::post('/driver/forgot-password', [DriverController::class, "forgetPassword"]);
Route::post('/driver/forgot-password-change', [DriverController::class, "forgetPasswordCheckCode"]);
Route::get('/driver', [DriverController::class,'getUser'])->middleware('auth:sanctum,driver');
Route::post('/driver/login', [DriverController::class, 'login']);
