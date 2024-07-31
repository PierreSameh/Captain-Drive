<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use Laravel\Socialite\Facades\Socialite;
 



//AuthController
Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/forgot-password', [AuthController::class, "forgetPassword"]);
Route::post('/user/forgot-password-change', [AuthController::class, "forgetPasswordCheckCode"]);

//Socialite
Route::get('/auth/google', [SocialiteController::class,'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class,'handleGoogleCallback']);
Route::get('/auth/facebook', [SocialiteController::class,'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class,'handleFacebookCallback']);

