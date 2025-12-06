<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LostFoundController;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Public routes (no auth required)
Route::get('/buses', [BusController::class, 'index']);
Route::get('/buses/{id}', [BusController::class, 'show']);
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{id}', [RouteController::class, 'show']);
Route::get('/stops', [StopController::class, 'index']);
Route::get('/stops/route/{routeId}', [StopController::class, 'byRoute']);
Route::get('/feedback', [FeedbackController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Profile routes - users can only access their own profile
    Route::get('/profile/{user_id}', [ProfileController::class, 'show']);
    Route::put('/profile/update/{user_id}', [ProfileController::class, 'update']);
    
    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    
    // Feedback
    Route::post('/feedback', [FeedbackController::class, 'store']);
    
    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::patch('/payments/{id}/status', [PaymentController::class, 'updateStatus']);
    
    // Lost & Found routes
    Route::get('/lost-found', [LostFoundController::class, 'index']);
    Route::post('/lost-found', [LostFoundController::class, 'store']);
    Route::get('/lost-found/my', [LostFoundController::class, 'myItems']);
    Route::get('/lost-found/stats', [LostFoundController::class, 'stats']);
    Route::get('/lost-found/{id}', [LostFoundController::class, 'show']);
    Route::put('/lost-found/{id}', [LostFoundController::class, 'update']);
    Route::delete('/lost-found/{id}', [LostFoundController::class, 'destroy']);
    Route::patch('/lost-found/{id}/status', [LostFoundController::class, 'updateStatus']);
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::post('/buses', [BusController::class, 'store']);
        Route::post('/routes', [RouteController::class, 'store']);
        Route::post('/stops', [StopController::class, 'store']);
    });
});
