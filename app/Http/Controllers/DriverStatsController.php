<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverAssignment;
use App\Models\Booking;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;

class DriverStatsController extends Controller
{
    public function getStats(Request $request)
    {
        $driverId = $request->user()->id;

        // Total trips (completed assignments)
        $totalTrips = DriverAssignment::where('driver_id', $driverId)
            ->whereNotNull('end_time')
            ->count();

        // Passengers served (from bookings where driver was assigned)
        $passengersServed = Booking::whereHas('schedule.bus.currentAssignment', function($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        })->where('status', 'boarded')->count();

        // Average rating from feedback
        $avgRating = Feedback::whereHas('booking.schedule.bus.currentAssignment', function($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        })->avg('rating') ?? 0;

        $totalReviews = Feedback::whereHas('booking.schedule.bus.currentAssignment', function($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        })->count();

        // On-time rate (mock calculation - you can implement actual logic)
        $onTimeRate = 94; // Placeholder

        return response()->json([
            'total_trips' => $totalTrips,
            'on_time_rate' => $onTimeRate,
            'passengers_served' => $passengersServed,
            'average_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews
        ]);
    }
}
