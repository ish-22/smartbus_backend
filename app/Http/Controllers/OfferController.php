<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\Reward;
use App\Models\OwnerOfferPayment;
use App\Models\OfferRedemption;
use App\Models\Booking;

class OfferController extends Controller
{
    public function index()
    {
        try {
            $offers = Offer::active()->get();
            return response()->json([
                'success' => true,
                'data' => $offers
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'required_points' => 'required|integer|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date'
            ]);

            $data['status'] = 'active';
            $offer = Offer::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Offer created successfully',
                'data' => $offer
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Offer creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create offer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $offer = Offer::findOrFail($id);
            
            $data = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'discount_percentage' => 'sometimes|numeric|min:0|max:100',
                'required_points' => 'sometimes|integer|min:1',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'status' => 'sometimes|in:active,expired'
            ]);

            $offer->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Offer updated successfully',
                'data' => $offer
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $offer = Offer::findOrFail($id);
            $offer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Offer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function redeemOffer(Request $request)
    {
        try {
            $data = $request->validate([
                'offer_id' => 'required|exists:offers,id',
                'booking_id' => 'nullable|exists:bookings,id'
            ]);

            $user = $request->user();
            $offer = Offer::findOrFail($data['offer_id']);

            // Check if user has already redeemed this offer
            if (OfferRedemption::hasUserRedeemedOffer($user->id, $offer->id)) {
                return response()->json(['error' => 'You have already redeemed this offer'], 400);
            }

            if (!$offer->isActive()) {
                return response()->json(['error' => 'Offer is not active'], 400);
            }

            $userPoints = Reward::getUserTotalPoints($user->id);
            if ($userPoints < $offer->required_points) {
                return response()->json(['error' => 'Insufficient points'], 400);
            }

            // Deduct points
            Reward::deductPoints(
                $user->id,
                $offer->required_points,
                'offer_redemption',
                "Redeemed offer: {$offer->title}"
            );

            // Create redemption record
            OfferRedemption::create([
                'user_id' => $user->id,
                'offer_id' => $offer->id,
                'booking_id' => $data['booking_id'] ?? null,
                'discount_amount' => $offer->discount_percentage,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer redeemed successfully! Use it on your next booking.',
                'discount_percentage' => $offer->discount_percentage
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkEligibility(Request $request, $offerId)
    {
        try {
            $user = $request->user();
            $offer = Offer::findOrFail($offerId);
            $userPoints = Reward::getUserTotalPoints($user->id);
            $alreadyRedeemed = OfferRedemption::hasUserRedeemedOffer($user->id, $offerId);

            return response()->json([
                'success' => true,
                'eligible' => $userPoints >= $offer->required_points && $offer->isActive() && !$alreadyRedeemed,
                'user_points' => $userPoints,
                'required_points' => $offer->required_points,
                'already_redeemed' => $alreadyRedeemed
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRedeemedOffers(Request $request)
    {
        try {
            $user = $request->user();
            $redeemedOffers = OfferRedemption::with('offer')
                ->where('user_id', $user->id)
                ->get()
                ->pluck('offer_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'redeemed_offer_ids' => $redeemedOffers
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}