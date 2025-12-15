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
use App\Http\Controllers\DriverAssignmentController;
use App\Http\Controllers\IncidentController;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Test route (no auth required)
Route::get('/test', function() {
    return response()->json([
        'status' => 'Laravel API is working',
        'timestamp' => now(),
        'database' => \DB::connection()->getPdo() ? 'Connected' : 'Not connected'
    ]);
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
    Route::patch('/bookings/{id}/complete', [BookingController::class, 'complete']);
    
    // Feedback routes
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/feedback/my', [FeedbackController::class, 'myFeedback']);
    Route::get('/feedback/{id}', [FeedbackController::class, 'show']);
    Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy']);
    
    // Debug route
    Route::post('/feedback/debug', function(Request $request) {
        return response()->json([
            'user' => $request->user(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
    });
    
    // Admin only routes (remove middleware for now)
    Route::get('/feedback/stats', [FeedbackController::class, 'stats']);
    Route::put('/feedback/{id}/status', [FeedbackController::class, 'updateStatus']);
    
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
    
    // Reward routes
    Route::get('/rewards/points', [\App\Http\Controllers\RewardController::class, 'getUserPoints']);
    Route::get('/rewards/history', [\App\Http\Controllers\RewardController::class, 'getRewardHistory']);
    Route::post('/rewards/add', [\App\Http\Controllers\RewardController::class, 'addPoints']);
    Route::post('/rewards/deduct', [\App\Http\Controllers\RewardController::class, 'deductPoints']);
    
    // Booking reward integration
    Route::get('/booking/reward-data', [\App\Http\Controllers\BookingRewardController::class, 'getBookingData']);
    Route::post('/booking/calculate-discount', [\App\Http\Controllers\BookingRewardController::class, 'calculateDiscount']);
    
    // Offer routes
    Route::get('/offers', [\App\Http\Controllers\OfferController::class, 'index']);
    Route::post('/offers', [\App\Http\Controllers\OfferController::class, 'store']);
    Route::put('/offers/{id}', [\App\Http\Controllers\OfferController::class, 'update']);
    Route::delete('/offers/{id}', [\App\Http\Controllers\OfferController::class, 'destroy']);
    Route::post('/offers/redeem', [\App\Http\Controllers\OfferController::class, 'redeemOffer']);
    Route::get('/offers/{id}/eligibility', [\App\Http\Controllers\OfferController::class, 'checkEligibility']);
    Route::get('/offers/redeemed', [\App\Http\Controllers\OfferController::class, 'getRedeemedOffers']);
    Route::get('/offers/redeemed', [\App\Http\Controllers\OfferController::class, 'getRedeemedOffers']);
    
    // Owner Payment routes
    Route::get('/owner-payments', [\App\Http\Controllers\OwnerPaymentController::class, 'index']);
    Route::patch('/owner-payments/{id}/paid', [\App\Http\Controllers\OwnerPaymentController::class, 'markAsPaid']);
    Route::get('/owner-payments/stats', [\App\Http\Controllers\OwnerPaymentController::class, 'getStats']);
    
    // Admin Compensation routes
    Route::get('/admin-compensations', [\App\Http\Controllers\AdminCompensationController::class, 'index']);
    Route::get('/admin-compensations/stats', [\App\Http\Controllers\AdminCompensationController::class, 'getStats']);
    Route::patch('/admin-compensations/{id}/paid', [\App\Http\Controllers\AdminCompensationController::class, 'markAsPaid']);
    Route::get('/owner-compensations', [\App\Http\Controllers\AdminCompensationController::class, 'getOwnerCompensations']);
    
    // Driver assignment routes
    Route::prefix('drivers')->group(function () {
        Route::post('/{driverId}/assign-bus', [DriverAssignmentController::class, 'assignBus']);
        Route::get('/{driverId}/current-assignment', [DriverAssignmentController::class, 'getCurrentAssignment']);
        Route::post('/{driverId}/assignments/{assignmentId}/end', [DriverAssignmentController::class, 'endAssignment']);
        Route::get('/{driverId}/assignments', [DriverAssignmentController::class, 'getAssignmentHistory']);
    });
    
    // Incident routes
    Route::prefix('incidents')->group(function () {
        Route::get('/', [IncidentController::class, 'index']); // Driver's incidents
        Route::post('/', [IncidentController::class, 'store']); // Report incident
        Route::get('/{id}', [IncidentController::class, 'show']); // Get specific incident
        Route::get('/admin/all', [IncidentController::class, 'getAll']); // Admin: all incidents
        Route::get('/admin/stats', [IncidentController::class, 'stats']); // Admin: statistics
        Route::get('/owner/all', [IncidentController::class, 'getOwnerIncidents']); // Owner: incidents for their buses
        Route::get('/passenger/all', [IncidentController::class, 'getPassengerIncidents']); // Passenger: public incidents
        Route::patch('/{id}/status', [IncidentController::class, 'updateStatus']); // Admin: update status
        Route::delete('/{id}', [IncidentController::class, 'destroy']); // Admin: delete
    });
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::post('/buses', [BusController::class, 'store']);
        Route::post('/routes', [RouteController::class, 'store']);
        Route::post('/stops', [StopController::class, 'store']);
        
        // Admin-only reward and offer management
        Route::post('/admin/offers', [\App\Http\Controllers\OfferController::class, 'store']);
        Route::put('/admin/offers/{id}', [\App\Http\Controllers\OfferController::class, 'update']);
        Route::delete('/admin/offers/{id}', [\App\Http\Controllers\OfferController::class, 'destroy']);
        Route::post('/admin/rewards/bulk-add', [\App\Http\Controllers\RewardController::class, 'bulkAddPoints']);
    });
});
