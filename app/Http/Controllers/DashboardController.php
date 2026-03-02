<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Incident;
use App\Models\DriverAssignment;

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
                'total_bookings' => Booking::count(),
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
            // Only show stats for owner's buses
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            
            // Get unique drivers assigned to owner's buses
            $assignedDriverIds = Bus::where('owner_id', $user->id)
                ->whereNotNull('driver_id')
                ->distinct()
                ->pluck('driver_id');
            
            // Get unique routes for owner's buses
            $ownerRouteIds = Bus::where('owner_id', $user->id)
                ->whereNotNull('route_id')
                ->distinct()
                ->pluck('route_id');
            
            $stats = [
                'total_buses' => Bus::where('owner_id', $user->id)->count(),
                'active_buses' => Bus::where('owner_id', $user->id)->where('status', 'active')->count(),
                'maintenance_buses' => Bus::where('owner_id', $user->id)->where('status', 'maintenance')->count(),
                'inactive_buses' => Bus::where('owner_id', $user->id)->where('status', 'inactive')->count(),
                'buses_with_drivers' => Bus::where('owner_id', $user->id)->whereNotNull('driver_id')->count(),
                'total_assignments' => DriverAssignment::whereIn('bus_id', $ownerBusIds)->count(),
                'active_assignments' => DriverAssignment::whereIn('bus_id', $ownerBusIds)
                    ->whereNull('ended_at')
                    ->count(),
                'total_drivers' => $assignedDriverIds->count(), // Unique drivers assigned to owner's buses
                'total_routes' => $ownerRouteIds->count(), // Unique routes for owner's buses
                'today_bookings' => Booking::whereIn('bus_id', $ownerBusIds)
                    ->whereDate('created_at', today())
                    ->count(),
                'total_bookings' => Booking::whereIn('bus_id', $ownerBusIds)->count(),
                'pending_incidents' => Incident::whereIn('bus_id', $ownerBusIds)
                    ->whereIn('status', ['reported', 'in_progress'])
                    ->count(),
                'resolved_incidents' => Incident::whereIn('bus_id', $ownerBusIds)
                    ->where('status', 'resolved')
                    ->count(),
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

    /**
     * Get owner analytics data including revenue
     */
    public function ownerAnalytics(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Get owner's buses
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            
            // Calculate monthly revenue (current month)
            $monthlyRevenue = Booking::whereIn('bus_id', $ownerBusIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('fare');
            
            // Calculate today's revenue
            $todayRevenue = Booking::whereIn('bus_id', $ownerBusIds)
                ->whereDate('created_at', today())
                ->sum('fare');
            
            // Get active buses count
            $activeBuses = Bus::where('owner_id', $user->id)
                ->where('status', 'active')
                ->count();
            
            // Get total trips (bookings)
            $totalTrips = Booking::whereIn('bus_id', $ownerBusIds)->count();
            
            // Get today's trips
            $todayTrips = Booking::whereIn('bus_id', $ownerBusIds)
                ->whereDate('created_at', today())
                ->count();
            
            // Get total bookings
            $totalBookings = Booking::whereIn('bus_id', $ownerBusIds)->count();
            
            $analytics = [
                'monthly_revenue' => (float)$monthlyRevenue,
                'active_buses' => $activeBuses,
                'total_trips' => $totalTrips,
                'today_revenue' => (float)$todayRevenue,
                'today_trips' => $todayTrips,
                'total_bookings' => $totalBookings,
            ];

            return response()->json($analytics);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch analytics', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Get security statistics and events
     */
    public function securityStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $totalUsers = User::count();
            $activeUsers = User::where('updated_at', '>=', now()->subDays(7))->count();
            $securityScore = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;
            
            $stats = [
                'security_score' => $securityScore,
                'threats_blocked' => User::whereNull('email_verified_at')->count(),
                'active_sessions' => User::where('updated_at', '>=', now()->subHours(24))->count(),
                'audit_logs' => DB::table('users')->count() + DB::table('bookings')->count() + DB::table('buses')->count(),
            ];

            $events = User::select('name', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($user) {
                    return [
                        'event' => "New {$user->role} user created: {$user->name}",
                        'time' => $user->created_at->diffForHumans(),
                        'severity' => $user->role === 'admin' ? 'high' : ($user->role === 'owner' ? 'medium' : 'low'),
                    ];
                });

            return response()->json([
                'stats' => $stats,
                'events' => $events,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch security statistics', 'error' => $e->getMessage()], 500);
        }
    }
}

