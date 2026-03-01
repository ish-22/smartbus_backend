<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Reward;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\AdminCompensation;
use App\Http\Controllers\RewardController;
use App\Services\PaymentService;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Admin can see all bookings
        if ($user->role === 'admin') {
            $bookings = Booking::with(['user', 'bus', 'route'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Regular users see only their bookings
            $bookings = Booking::with(['user', 'bus', 'route'])
                ->where('user_id', $user->id)
                ->get();
        }
        
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'route_id' => 'nullable|exists:routes,id',
            'seat_number' => 'required|string|max:50',
            'fare' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card,debit_card,digital_wallet',
            'points_to_use' => 'nullable|integer|min:0',
            'offer_id' => 'nullable|exists:offers,id',
            // Card payment fields
            'card_number' => 'nullable|string',
            'card_expiry' => 'nullable|string',
            'card_cvv' => 'nullable|string',
            'card_holder_name' => 'nullable|string',
            // Wallet payment fields
            'wallet_type' => 'nullable|string'
        ]);

        try {
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'bus_id' => $data['bus_id'],
                'route_id' => $data['route_id'] ?? null,
                'seat_number' => $data['seat_number'],
                'fare' => $data['fare'],
                'travel_date' => now()->addDay(),
                'status' => 'confirmed',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending'
            ]);

            $paymentService = new PaymentService();
            $totalDiscount = 0;
            
            // Apply offer discount if provided
            if (!empty($data['offer_id'])) {
                $offer = Offer::findOrFail($data['offer_id']);
                
                // Validate offer
                if ($offer->status !== 'active' || now() > $offer->end_date) {
                    throw new \Exception('Selected offer is not valid or has expired');
                }
                
                // Check if user already redeemed this offer
                if (OfferRedemption::hasUserRedeemedOffer($request->user()->id, $offer->id)) {
                    throw new \Exception('You have already used this offer');
                }
                
                // Calculate offer discount
                $offerDiscount = ($booking->fare * $offer->discount_percentage) / 100;
                $totalDiscount += $offerDiscount;
                
                // Create offer redemption record
                OfferRedemption::create([
                    'user_id' => $request->user()->id,
                    'offer_id' => $offer->id,
                    'booking_id' => $booking->id,
                    'discount_amount' => $offerDiscount,
                    'status' => 'used',
                    'used_at' => now()
                ]);
            }
            
            // Apply points discount if requested
            if (!empty($data['points_to_use'])) {
                $pointsDiscount = $paymentService->applyPointsDiscount($booking, $data['points_to_use']);
                $totalDiscount += $pointsDiscount;
            }
            
            // Update booking with total discount
            $booking->update(['discount_amount' => $totalDiscount]);

            // Process payment
            $payment = $paymentService->processPayment($booking, $data);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['user', 'bus', 'route']),
                'payment' => $payment,
                'original_fare' => $booking->fare,
                'total_discount' => $totalDiscount,
                'final_amount' => $booking->fare - $totalDiscount,
                'points_used' => $booking->points_used,
                'offer_applied' => !empty($data['offer_id'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id, Request $request)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);
        return response()->json($booking);
    }

    public function complete($id, Request $request)
    {
        try {
            $booking = Booking::with('user')->findOrFail($id);
            
            if ($booking->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking already completed'
                ], 400);
            }

            $booking->update(['status' => 'completed']);
            
            $pointsAwarded = 0;
            $bonusPoints = 0;
            $totalPoints = 0;
            
            // Add reward points for completed booking
            if ($booking->user && $booking->user->role === 'passenger') {
                // Base points for completion
                $reward = RewardController::autoAddPointsOnBookingComplete($booking->user_id, $booking->id);
                if ($reward && $reward->wasRecentlyCreated) {
                    $pointsAwarded = 10;
                }
                
                // Bonus points based on payment method
                $busOwnerId = $booking->bus->user_id ?? 1;
                if ($booking->payment_method === 'digital_wallet') {
                    $bonusPoints = 5;
                    Reward::addPoints(
                        $booking->user_id,
                        $bonusPoints,
                        'payment_bonus',
                        'Digital wallet payment bonus',
                        $booking->id
                    );
                    // Admin compensates bus owner for bonus points (Rs. 5)
                    AdminCompensation::createCompensation(
                        $booking->id,
                        $busOwnerId,
                        'reward_bonus',
                        5,
                        "Admin compensation for digital wallet bonus on booking #{$booking->id}"
                    );
                } elseif (in_array($booking->payment_method, ['credit_card', 'debit_card'])) {
                    $bonusPoints = 3;
                    Reward::addPoints(
                        $booking->user_id,
                        $bonusPoints,
                        'payment_bonus',
                        'Card payment bonus',
                        $booking->id
                    );
                    // Admin compensates bus owner for bonus points (Rs. 3)
                    AdminCompensation::createCompensation(
                        $booking->id,
                        $busOwnerId,
                        'reward_bonus',
                        3,
                        "Admin compensation for card payment bonus on booking #{$booking->id}"
                    );
                }
                
                $totalPoints = Reward::getUserTotalPoints($booking->user_id);
            }
            
            $totalPointsEarned = $pointsAwarded + $bonusPoints;
            $message = $totalPointsEarned > 0 ? 
                "Booking completed! You earned {$totalPointsEarned} points ({$pointsAwarded} base + {$bonusPoints} bonus)" : 
                'Booking completed';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $booking,
                'points_awarded' => $pointsAwarded,
                'bonus_points' => $bonusPoints,
                'total_points_earned' => $totalPointsEarned,
                'total_points' => $totalPoints
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $booking = Booking::with(['user', 'bus', 'route'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        return response()->json($booking);
    }
}