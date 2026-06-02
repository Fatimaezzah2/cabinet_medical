<?php

use App\Http\Controllers\Api\AppointmentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/appointments/search', [AppointmentApiController::class, 'search']);
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::get('/appointments/search', [\App\Http\Controllers\Api\AppointmentApiController::class, 'search']);
});
