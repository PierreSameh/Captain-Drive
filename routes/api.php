<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;


//AuthController
Route::post('/user/register', [AuthController::class, 'register']);

