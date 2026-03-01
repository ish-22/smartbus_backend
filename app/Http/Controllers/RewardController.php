<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;

class RewardController extends Controller
{
    public function getUserPoints(Request $request)
    {
        try {
            $userId = $request->user_id ?? $request->user()->id;
            $totalPoints = Reward::getUserTotalPoints($userId);
            
            return response()->json([
                'success' => true,
                'total_points' => $totalPoints
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRewardHistory(Request $request)
    {
        try {
            $user = $request->user();
            
            // Admin can see all rewards with user data
            if ($user->role === 'admin') {
                $rewards = Reward::with('user')
                               ->orderBy('created_at', 'desc')
                               ->get();
            } else {
                // Regular users see only their rewards
                $userId = $request->user_id ?? $user->id;
                $rewards = Reward::where('user_id', $userId)
                               ->orderBy('created_at', 'desc')
                               ->get();
            }
            
            return response()->json($rewards);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addPoints(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'points' => 'required|integer|min:1',
                'description' => 'nullable|string'
            ]);

            $reward = Reward::addPoints(
                $data['user_id'],
                $data['points'],
                'manual_admin',
                $data['description'] ?? 'Admin added points'
            );

            return response()->json([
                'success' => true,
                'message' => 'Points added successfully',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deductPoints(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'points' => 'required|integer|min:1',
                'description' => 'nullable|string'
            ]);

            $currentPoints = Reward::getUserTotalPoints($data['user_id']);
            if ($currentPoints < $data['points']) {
                return response()->json(['error' => 'Insufficient points'], 400);
            }

            $reward = Reward::deductPoints(
                $data['user_id'],
                $data['points'],
                'manual_admin',
                $data['description'] ?? 'Admin deducted points'
            );

            return response()->json([
                'success' => true,
                'message' => 'Points deducted successfully',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function autoAddPointsOnBookingComplete($userId, $bookingId)
    {
        // Check if reward already exists for this booking
        $existingReward = Reward::where('booking_id', $bookingId)
                               ->where('reason', 'booking')
                               ->first();
        
        if ($existingReward) {
            return $existingReward;
        }
        
        return Reward::addPoints(
            $userId,
            10,
            'booking',
            "Booking completed - ID: {$bookingId}",
            $bookingId
        );
    }

    public static function autoAddPointsOnFeedback($userId, $feedbackId)
    {
        return Reward::addPoints(
            $userId,
            5,
            'feedback',
            "Feedback submitted - ID: {$feedbackId}"
        );
    }
}