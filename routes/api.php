<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('rooms/available', [RoomController::class, 'available']);
Route::apiResource('rooms', RoomController::class);
Route::apiResource('bookings', BookingController::class);
