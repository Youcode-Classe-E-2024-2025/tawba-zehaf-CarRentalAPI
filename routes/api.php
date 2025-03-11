<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\RentalController; 
use App\Http\Controllers\PaymentController; 
use Illuminate\Support\Facades\Route;

Route::get('/cars/pagin/{param}', [CarController::class, 'getAll']);


Route::get('/cars/{id}', [CarController::class, 'getById']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cars', [CarController::class, 'create']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'delete']);
});



// Route::middleware('auth:sanctum')->get('/rentals', [RentalsController::class, 'getUserRentals']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/rentals', [RentalController::class, 'create']);
    Route::get('/rentals', [RentalController::class, 'getUserRentals']);
    Route::put('/rentals/{id}', [RentalController::class, 'update']);
    Route::delete('/rentals/{id}', [RentalController::class, 'delete']);

});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payments', [PaymentController::class, 'getUserPaymentsById']);
    Route::get('/payments/rental/{rentalId}', [PaymentController::class, 'getPaymentByRentalId']);
    Route::post('/payments', [PaymentController::class, 'createOne']);
    Route::put('/payments/{id}', [PaymentController::class, 'updateOne']);
    Route::delete('/payments/{id}', [PaymentController::class, 'deleteOne']);


});


Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);