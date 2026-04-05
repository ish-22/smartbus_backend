<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\DriverAssignment;
use App\Models\Reward;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\AdminCompensation;
use App\Http\Controllers\RewardController;
use App\Services\PaymentService;
use App\Services\PHPMailerService;

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
                ->orderBy('created_at', 'desc')
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
            'travel_date' => 'required|date',
            'trip_number' => 'nullable|integer|min:1',
            'payment_method' => 'required|in:cash,credit_card,debit_card,digital_wallet',
            'points_to_use' => 'nullable|integer|min:0',
            'offer_id' => 'nullable|exists:offers,id',
            'email' => 'required|email',
            // Card payment fields
            'card_number' => 'nullable|string',
            'card_expiry' => 'nullable|string',
            'card_cvv' => 'nullable|string',
            'card_holder_name' => 'nullable|string',
            // Wallet payment fields
            'wallet_type' => 'nullable|string'
        ]);

        try {
            $travelDate = $data['travel_date'];

            // Check if seat is already booked for this trip
            $existingBooking = Booking::where('bus_id', $data['bus_id'])
                ->where('seat_number', $data['seat_number'])
                ->whereDate('travel_date', $travelDate)
                ->where('trip_number', $data['trip_number'] ?? 1)
                ->whereIn('status', ['confirmed', 'completed'])
                ->first();

            if ($existingBooking) {
                throw new \Exception('This seat is already booked for this trip. Please select another seat.');
            }

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'bus_id' => $data['bus_id'],
                'route_id' => $data['route_id'] ?? null,
                'seat_number' => $data['seat_number'],
                'fare' => $data['fare'],
                'travel_date' => $travelDate,
                'trip_number' => $data['trip_number'] ?? 1,
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

            $recipientEmail = $data['email'] ?: ($request->user()->email ?? null);

            if ($recipientEmail) {
                $mailService = new PHPMailerService();
                $mailService->sendBookingConfirmation($booking, $recipientEmail);
            }

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

    /**
     * Get today's passengers for the currently assigned bus of the authenticated driver.
     * Response includes simple stats (total, boarded, pending) and a normalized passenger list.
     */
    public function driverPassengers(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find today's active assignment for this driver
        $assignment = DriverAssignment::where('driver_id', $user->id)
            ->whereDate('assignment_date', today())
            ->whereNull('ended_at')
            ->with('bus.route')
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'No active assignment found for today',
                'stats' => [
                    'total' => 0,
                    'boarded' => 0,
                    'pending' => 0,
                ],
                'passengers' => [],
            ]);
        }

        // Get today's bookings for this bus
        $bookings = Booking::with(['user', 'route'])
            ->where('bus_id', $assignment->bus_id)
            ->whereDate('travel_date', today())
            ->get();

        $total = $bookings->count();
        // Treat completed bookings as "Boarded", confirmed as "Pending"
        $boarded = $bookings->where('status', 'completed')->count();
        $pending = $bookings->where('status', 'confirmed')->count();

        $passengers = $bookings->map(function (Booking $booking) {
            $route = $booking->route;
            $from = $route?->start_point ?? $route?->start_location ?? $route?->name ?? 'Start';
            $to = $route?->end_point ?? $route?->end_location ?? $route?->name ?? 'End';

            $statusLabel = match ($booking->status) {
                'completed' => 'Boarded',
                'confirmed' => 'Pending',
                default => ucfirst($booking->status),
            };

            return [
                'id' => $booking->id,
                'name' => $booking->user->name ?? 'Passenger',
                'seat' => $booking->seat_number,
                'from' => $from,
                'to' => $to,
                'ticketId' => 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                'status' => $statusLabel,
            ];
        })->values();

        return response()->json([
            'assignment' => $assignment,
            'stats' => [
                'total' => $total,
                'boarded' => $boarded,
                'pending' => $pending,
            ],
            'passengers' => $passengers,
        ]);
    }
}
