<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use App\Http\Controllers\User\AddressController;
 



//AuthController
Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/forgot-password', [AuthController::class, "forgetPassword"]);
Route::post('/user/forgot-password-change', [AuthController::class, "forgetPasswordCheckCode"]);
Route::post('/user/login', [AuthController::class, 'login']);
Route::get('/user/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');

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

