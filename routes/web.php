<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\DriverResource\Pages\RejectDriver;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/unauthorized', function () {
    return response()->json(
        [
            "status" => false,
            "message" => "unauthenticated",
            "errors" => ["Your are not authenticated"],
            "data" => [],
            "notes" => []
        ]
        , 401);
    });

    Route::get('/admin/drivers/{record}/reject', RejectDriver::class)->name('filament.resources.drivers.reject');
