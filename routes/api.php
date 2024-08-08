<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\DriverController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\RideController;
 



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
Route::post('/driver/register', [DriverController::class,'registerDriver']);
Route::get('/driver/ask-email-verfication-code', [DriverController::class, "askEmailCodeDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/verify-email', [DriverController::class, "verifyEmailDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/change-password', [DriverController::class, "changePasswordDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/forgot-password', [DriverController::class, "forgetPasswordDriver"]);
Route::post('/driver/forgot-password-change', [DriverController::class, "forgetPasswordCheckCodeDriver"]);
Route::get('/driver', [DriverController::class,'getUserDriver'])->middleware('auth:sanctum,driver');
Route::post('/driver/edit', [DriverController::class,"editProfileDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/login', [DriverController::class, 'loginDriver']);
Route::get('/driver/logout', [DriverController::class, "logoutDriver"])->middleware('auth:sanctum,driver');

//WalletController
Route::post('/driver/add-wallet', [WalletController::class,'addWallet'])->middleware('auth:sanctum,driver');
Route::post('/driver/edit-wallet', [WalletController::class,'editWallet'])->middleware('auth:sanctum,driver');
Route::get('/driver/get-wallet', [WalletController::class,'getWallet'])->middleware('auth:sanctum,driver');
Route::post('/driver/delete-wallet', [WalletController::class,'deleteWallet'])->middleware('auth:sanctum,driver');

//RideController
Route::post('/ride/request-ride', [RideController::class,'sendRideRequest'])->middleware('auth:sanctum');