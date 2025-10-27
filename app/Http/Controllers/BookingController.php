<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Bus;

class BookingController extends Controller
{
    // GET /api/bookings
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json([], 200);
        $bookings = Booking::where('user_id', $user->id)->get();
        return response()->json($bookings);
    }

    // POST /api/bookings
    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'required|integer|exists:buses,id',
            'route_id' => 'nullable|integer|exists:routes,id',
            'ticket_category' => 'nullable|string',
            'seat_number' => 'nullable|string',
            'payment_method' => 'nullable|string'
        ]);

        $bus = Bus::findOrFail($data['bus_id']);
        // Enforce only expressway bookings if required by frontend rule
        if ($bus->type !== 'expressway') {
            return response()->json(['message' => 'This bus is not bookable.'], 400);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'bus_id' => $data['bus_id'],
            'route_id' => $data['route_id'] ?? null,
            'ticket_category' => $data['ticket_category'] ?? null,
            'seat_number' => $data['seat_number'] ?? null,
            'status' => 'confirmed',
            'total_amount' => 0,
            'payment_method' => $data['payment_method'] ?? 'pay_on_bus'
        ]);

        return response()->json($booking, 201);
    }
}
