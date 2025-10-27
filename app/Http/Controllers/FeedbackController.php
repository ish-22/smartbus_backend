<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    // POST /api/feedback
    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'nullable|integer|exists:buses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $feedback = Feedback::create([
            'user_id' => $request->user()?->id ?? null,
            'bus_id' => $data['bus_id'] ?? null,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null
        ]);

        return response()->json($feedback, 201);
    }
}
