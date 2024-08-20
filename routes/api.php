<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SocialiteController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\Driver\DriverController;
use App\Http\Controllers\Driver\WalletController;
use App\Http\Controllers\User\RideController;
use App\Http\Controllers\Driver\OfferController;
 



//AuthController
Route::post('/user/register', [AuthController::class, 'register']);
Route::get('/user/forgot-password', [AuthController::class, "sendForgetPasswordEmail"]);
Route::post('/user/forgot-password-check-code', [AuthController::class, "forgetPasswordCheckCode"]);
Route::post('/user/forgot-password-set', [AuthController::class,'forgetPassword']);
Route::post('/user/login', [AuthController::class, 'login']);
Route::post('/user/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');
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
Route::post('/driver/forgot-password-check-code', [DriverController::class, "forgetPasswordCheckCodeDriver"]);
Route::get('/driver/forgot-password', [DriverController::class, "sendForgetPasswordEmailDriver"]);
Route::post('/driver/forgot-password-set', [DriverController::class,'forgetPasswordDriver']);
Route::get('/driver', [DriverController::class,'getUserDriver'])->middleware('auth:sanctum,driver');
Route::post('/driver/edit', [DriverController::class,"editProfileDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/login', [DriverController::class, 'loginDriver']);
Route::post('/driver/logout', [DriverController::class, "logoutDriver"])->middleware('auth:sanctum,driver');
Route::post('/driver/rejected', [DriverController::class, "deleteDriverAfterReject"])->middleware('auth:sanctum,driver');
Route::post('/driver/set-status', [DriverController::class,'setDriverStatus'])->middleware('auth:sanctum');
Route::post('/driver/set-location', [DriverController::class,'setDriverLocation'])->middleware('auth:sanctum');
//
Route::get('/admin/get-driver/{driver}', [DriverController::class,'getDriverForAdmin'])->middleware('auth:sanctum');
Route::get('/admin/all-unapproved-drivers', [DriverController::class,'getAllUnapprovedDrivers'])->middleware('auth:sanctum');
Route::post('/admin/approve-driver/{driver}', [DriverController::class,'approveDriver'])->middleware('auth:sanctum');
Route::post('/admin/reject-driver/{driver}', [DriverController::class,'rejectDriver'])->middleware('auth:sanctum');

//WalletController
Route::post('/driver/add-wallet', [WalletController::class,'addWallet'])->middleware('auth:sanctum,driver');
Route::post('/driver/edit-wallet', [WalletController::class,'editWallet'])->middleware('auth:sanctum,driver');
Route::get('/driver/get-wallet', [WalletController::class,'getWallet'])->middleware('auth:sanctum,driver');
Route::post('/driver/delete-wallet', [WalletController::class,'deleteWallet'])->middleware('auth:sanctum,driver');

//RideController
Route::post('/ride/request-ride', [RideController::class,'sendRideRequest'])->middleware('auth:sanctum');
Route::get('/ride/get-request', [RideController::class,'getForUserRideRequest'])->middleware('auth:sanctum');
Route::post('/ride/{ride}/cancel-request', [RideController::class,'cancelRideRequest'])->middleware('auth:sanctum');
Route::get('/ride/get-ride/user', [RideController::class, 'getRideUser'])->middleware('auth:sanctum');
#User
Route::get('/offer/user/get-all-offers', [RideController::class,'getAllOffersUser'])->middleware('auth:sanctum');
Route::get('/offer/user/get-offer/{offer}', [RideController::class,'getOfferUser'])->middleware('auth:sanctum');
Route::post('/offer/user/accept-offer/{offer}', [RideController::class,'acceptOfferUser'])->middleware('auth:sanctum');
Route::post('/offer/user/reject-offer/{offer}', [RideController::class,'rejectOfferUser'])->middleware('auth:sanctum');
Route::post('/ride/cancel-ride/user', [RideController::class, 'cancelRideByUser'])->middleware('auth:sanctum');

//OfferController
#Driver
Route::get('/offer/driver/show-near-requests', [OfferController::class, 'showNearRequests'])->middleware('auth:sanctum,driver');
Route::post('/offer/driver/make-offer/{request}', [OfferController::class,'makeOffer'])->middleware('auth:sanctum,driver');
Route::get('/offer/driver/get-offer', [OfferController::class,'getOfferDriver'])->middleware('auth:sanctum,driver');
Route::post('/offer/driver/cancel-offer/{offer}', [OfferController::class,'cancelOffer'])->middleware('auth:sanctum,driver');
Route::get('/ride/get-ride/driver', [OfferController::class, 'getRideDriver'])->middleware('auth:sanctum');
Route::post('/ride/cancel-ride/driver', [OfferController::class, 'cancelRideByDriver'])->middleware('auth:sanctum');
Route::post('/ride/set-arrived/driver', [OfferController::class, 'setArrived'])->middleware('auth:sanctum');

