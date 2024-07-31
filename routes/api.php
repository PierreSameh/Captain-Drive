<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use Laravel\Socialite\Facades\Socialite;
 
Route::get('/auth/redirect', function () {
    return Socialite::driver('github')->redirect();
});
 
Route::get('/auth/callback', function () {
    $user = Socialite::driver('github')->user();
 
    // $user->token
});


//AuthController
Route::post('/user/register', [AuthController::class, 'register']);
Route::get('/auth/google', [SocialiteController::class,'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class,'handleGoogleCallback']);
Route::get('/auth/facebook', [SocialiteController::class,'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialiteController::class,'handleFacebookCallback']);

