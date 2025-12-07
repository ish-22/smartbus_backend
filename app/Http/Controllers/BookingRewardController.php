<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Offer;
use App\Models\OfferRedemption;

class BookingRewardController extends Controller
{
    public function getBookingData(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get user's current points
            $userPoints = Reward::getUserTotalPoints($user->id);
            
            // Get active offers
            $offers = Offer::where('status', 'active')
                ->where('end_date', '>=', now())
                ->get();
            
            // Get user's redeemed offers
            $redeemedOfferIds = OfferRedemption::where('user_id', $user->id)
                ->pluck('offer_id')
                ->toArray();
            
            // Filter out already redeemed offers
            $availableOffers = $offers->filter(function($offer) use ($redeemedOfferIds) {
                return !in_array($offer->id, $redeemedOfferIds);
            })->values();
            
            return response()->json([
                'success' => true,
                'user_points' => $userPoints,
                'available_offers' => $availableOffers,
                'redeemed_offers' => $redeemedOfferIds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function calculateDiscount(Request $request)
    {
        try {
            $data = $request->validate([
                'fare' => 'required|numeric|min:0',
                'points_to_use' => 'nullable|integer|min:0',
                'offer_id' => 'nullable|exists:offers,id'
            ]);
            
            $user = $request->user();
            $fare = $data['fare'];
            $totalDiscount = 0;
            $breakdown = [];
            
            // Calculate points discount
            if (!empty($data['points_to_use'])) {
                $userPoints = Reward::getUserTotalPoints($user->id);
                $maxPointsUsable = floor($fare * 0.5); // Max 50% of fare
                $pointsToUse = min($data['points_to_use'], $userPoints, $maxPointsUsable);
                
                if ($pointsToUse > 0) {
                    $pointsDiscount = $pointsToUse; // 1 point = Rs. 1
                    $totalDiscount += $pointsDiscount;
                    $breakdown['points'] = [
                        'points_used' => $pointsToUse,
                        'discount_amount' => $pointsDiscount
                    ];
                }
            }
            
            // Calculate offer discount
            if (!empty($data['offer_id'])) {
                $offer = Offer::findOrFail($data['offer_id']);
                
                // Validate offer
                if ($offer->status === 'active' && now() <= $offer->end_date) {
                    // Check if user already redeemed this offer
                    if (!OfferRedemption::hasUserRedeemedOffer($user->id, $offer->id)) {
                        $offerDiscount = ($fare * $offer->discount_percentage) / 100;
                        $totalDiscount += $offerDiscount;
                        $breakdown['offer'] = [
                            'offer_title' => $offer->title,
                            'discount_percentage' => $offer->discount_percentage,
                            'discount_amount' => $offerDiscount
                        ];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'original_fare' => $fare,
                'total_discount' => $totalDiscount,
                'final_amount' => $fare - $totalDiscount,
                'discount_breakdown' => $breakdown
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}