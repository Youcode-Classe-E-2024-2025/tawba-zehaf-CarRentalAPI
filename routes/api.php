<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RentalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('users', AuthController::class);

Route::apiResource('cars', CarController::class)->middleware('auth:sanctum');

Route::apiResource('rentals', RentalController::class)->middleware('auth:sanctum');

Route::apiResource('payments', PaymentController::class)->middleware('auth:sanctum');