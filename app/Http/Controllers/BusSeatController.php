<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Booking;
use Illuminate\Http\Request;

class BusSeatController extends Controller
{
    public function getAvailableSeats(Request $request, $busId)
    {
        $bus = Bus::findOrFail($busId);
        $travelDate = $request->input('travel_date', now()->format('Y-m-d'));
        $tripNumber = $request->input('trip_number', 1);
        
        // Get booked seats for this bus on the travel date and specific trip
        $bookedSeats = Booking::where('bus_id', $busId)
            ->whereDate('travel_date', $travelDate)
            ->where('trip_number', $tripNumber)
            ->whereIn('status', ['confirmed', 'completed', 'boarded'])
            ->pluck('seat_number')
            ->toArray();
        
        $capacity = $bus->capacity ?? 40;
        
        return response()->json([
            'bus_id' => $busId,
            'trip_number' => $tripNumber,
            'capacity' => $capacity,
            'booked_seats' => $bookedSeats,
            'available_count' => $capacity - count($bookedSeats)
        ]);
    }
}
