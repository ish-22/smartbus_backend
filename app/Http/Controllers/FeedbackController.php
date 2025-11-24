<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedback = Feedback::with(['user', 'bus'])->get();
        return response()->json($feedback);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'nullable|exists:buses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $data['user_id'] = $request->user()->id;
        $data['created_at'] = now();

        $feedback = Feedback::create($data);
        return response()->json($feedback->load(['user', 'bus']), 201);
    }
}