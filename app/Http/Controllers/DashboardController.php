<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Incident;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function adminStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $stats = [
                'total_users' => User::count(),
                'total_passengers' => User::where('role', 'passenger')->count(),
                'total_drivers' => User::where('role', 'driver')->count(),
                'total_owners' => User::where('role', 'owner')->count(),
                'total_admins' => User::where('role', 'admin')->count(),
                'active_routes' => Route::count(),
                'total_buses' => Bus::count(),
                'active_buses' => Bus::where('status', 'active')->count(),
                'today_trips' => Booking::whereDate('created_at', today())->count(),
                'today_bookings' => Booking::whereDate('created_at', today())->count(),
                'pending_incidents' => Incident::whereIn('status', ['reported', 'in_progress'])->count(),
                'resolved_incidents' => Incident::where('status', 'resolved')->count(),
                'total_incidents' => Incident::count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch statistics', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get owner dashboard statistics
     */
    public function ownerStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // For now, return general stats since owner-bus relationship may vary
            // This can be customized based on your actual schema
            $stats = [
                'total_buses' => Bus::count(), // Will be filtered by owner when schema is clear
                'active_buses' => Bus::where('status', 'active')->count(),
                'total_drivers' => User::where('role', 'driver')->count(),
                'total_routes' => Route::count(),
                'today_bookings' => Booking::whereDate('created_at', today())->count(),
                'total_bookings' => Booking::count(),
                'pending_incidents' => Incident::whereIn('status', ['reported', 'in_progress'])->count(),
                'resolved_incidents' => Incident::where('status', 'resolved')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch statistics', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get passenger dashboard statistics
     */
    public function passengerStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'passenger') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $stats = [
                'total_bookings' => Booking::where('user_id', $user->id)->count(),
                'upcoming_bookings' => Booking::where('user_id', $user->id)
                    ->where('status', 'confirmed')
                    ->whereDate('travel_date', '>=', today())
                    ->count(),
                'completed_trips' => Booking::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
                'cancelled_bookings' => Booking::where('user_id', $user->id)
                    ->where('status', 'cancelled')
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch statistics', 'error' => $e->getMessage()], 500);
        }
    }
}

