<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'bus', 'route'])
            ->where('user_id', $request->user()->id)
            ->get();
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'route_id' => 'nullable|exists:routes,id',
            'seat_number' => 'nullable|string|max:50',
            'ticket_category' => 'nullable|string',
            'total_amount' => 'numeric|min:0',
            'payment_method' => 'string|max:50'
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $booking = Booking::create($data);
        return response()->json($booking->load(['user', 'bus', 'route']), 201);
    }

    public function cancel($id, Request $request)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);
        return response()->json($booking);
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