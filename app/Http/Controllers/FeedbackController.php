<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Reward;
use App\Models\UserFeedbackDaily;
use App\Http\Controllers\RewardController;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Feedback::query();
            
            if ($request->type) $query->where('type', $request->type);
            if ($request->status) $query->where('status', $request->status);
            
            $feedback = $query->orderBy('created_at', 'desc')->get();
            return response()->json(['data' => $feedback]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        
        // Check if user already submitted feedback today
        $dailyFeedback = UserFeedbackDaily::where('user_id', $user->id)
            ->where('feedback_date', $today)
            ->first();
            
        if ($dailyFeedback) {
            return response()->json([
                'success' => false,
                'message' => 'You can only submit one feedback per day. Please try again tomorrow.'
            ], 400);
        }

        try {
            // Validate input
            $data = $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|in:complaint,suggestion,praise,general',
                'rating' => 'nullable|integer|min:1|max:5',
                'bus_id' => 'nullable|integer',
                'route_id' => 'nullable|integer',
            ]);

            // Insert directly into database
            $feedbackData = [
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'type' => $data['type'],
                'rating' => $data['rating'] ?? null,
                'bus_id' => $data['bus_id'] ?? null,
                'route_id' => $data['route_id'] ?? null,
                'status' => 'pending',
                'admin_response' => null,
                'responded_by' => null,
                'responded_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $feedbackId = \DB::table('feedback')->insertGetId($feedbackData);
            
            // Record daily feedback submission
            UserFeedbackDaily::create([
                'user_id' => $user->id,
                'feedback_date' => $today
            ]);
            
            // Add reward points for feedback submission (only for passengers)
            $pointsEarned = 0;
            if ($user->role === 'passenger') {
                Reward::addPoints(
                    $user->id,
                    5,
                    'feedback',
                    'Daily feedback submission reward'
                );
                $pointsEarned = 5;
            }
            
            return response()->json([
                'success' => true,
                'message' => $pointsEarned > 0 ? 'Feedback submitted successfully! You earned 5 reward points.' : 'Feedback submitted successfully!',
                'id' => $feedbackId,
                'points_earned' => $pointsEarned,
                'data' => $feedbackData
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return response()->json(Feedback::with(['user:id,name,email', 'responder:id,name'])->findOrFail($id));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,rejected',
            'admin_response' => 'nullable|string',
        ]);

        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ]);

        return response()->json($feedback->load(['user:id,name', 'responder:id,name']));
    }

    public function myFeedback(Request $request)
    {
        try {
            $feedback = Feedback::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json(['data' => $feedback]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total' => Feedback::count(),
            'pending' => Feedback::where('status', 'pending')->count(),
            'resolved' => Feedback::where('status', 'resolved')->count(),
            'average_rating' => Feedback::whereNotNull('rating')->avg('rating') ?: 0,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        
        if ($feedback->user_id != $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}