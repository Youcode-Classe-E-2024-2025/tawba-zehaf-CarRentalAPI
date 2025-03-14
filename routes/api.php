<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\RentalController; 
use App\Http\Controllers\PaymentController; 
use Illuminate\Support\Facades\Route;



Route::get('/cars/pagin/{param}', [CarController::class, 'getAll']);




Route::middleware('auth:sanctum')->group(function () {
   
    Route::apiResource('cars', CarController::class);

   
});

Route::get('filter', [CarController::class, 'filter']);

Route::middleware('auth:sanctum')->group(function () {
    // Rentals Resource Routes
    Route::apiResource('rentals', RentalController::class);



    
    Route::apiResource('payments', PaymentController::class);


   

    // Payments
    Route::get('/payments/user/{user_id}', [PaymentController::class, 'getUserPaymentsById']); // Get user-specific payments
    Route::get('/payments/rental/{rentalId}', [PaymentController::class, 'getPaymentByRentalId']); // Get payments by rental ID  
});
 Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payments/success', [PaymentController::class, 'cancel'])->name('payment.cancel');


Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);