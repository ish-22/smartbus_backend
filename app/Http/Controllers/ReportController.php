<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bus;
use App\Models\Booking;
use App\Models\Incident;
use App\Models\Route;

class ReportController extends Controller
{
    /**
     * Get owner revenue report
     */
    public function revenueReport(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            
            // Daily revenue for last 30 days
            $dailyRevenue = Booking::whereIn('bus_id', $ownerBusIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(fare) as revenue'), DB::raw('COUNT(*) as trips'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->get();

            // Monthly breakdown
            $monthlyRevenue = Booking::whereIn('bus_id', $ownerBusIds)
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('SUM(fare) as revenue'),
                    DB::raw('COUNT(*) as trips'),
                    DB::raw('AVG(fare) as avg_fare')
                )
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();

            // Top performing buses by revenue
            $topBuses = Bus::where('owner_id', $user->id)
                ->with(['bookings' => function ($q) {
                    $q->select('bus_id', DB::raw('SUM(fare) as revenue'), DB::raw('COUNT(*) as trips'))
                        ->groupBy('bus_id');
                }])
                ->get()
                ->map(function ($bus) {
                    $totalRevenue = Booking::where('bus_id', $bus->id)->sum('fare');
                    $totalTrips = Booking::where('bus_id', $bus->id)->count();
                    return [
                        'id' => $bus->id,
                        'bus_number' => $bus->bus_number,
                        'revenue' => $totalRevenue,
                        'trips' => $totalTrips,
                        'status' => $bus->status
                    ];
                })
                ->sortByDesc('revenue')
                ->take(5)
                ->values();

            // Total summary
            $totalRevenue = Booking::whereIn('bus_id', $ownerBusIds)->sum('fare');
            $totalTrips = Booking::whereIn('bus_id', $ownerBusIds)->count();
            $totalCancelledTrips = Booking::whereIn('bus_id', $ownerBusIds)->where('status', 'cancelled')->count();

            return response()->json([
                'summary' => [
                    'total_revenue' => (float)$totalRevenue,
                    'total_trips' => $totalTrips,
                    'cancelled_trips' => $totalCancelledTrips,
                    'completion_rate' => $totalTrips > 0 ? round((($totalTrips - $totalCancelledTrips) / $totalTrips) * 100, 2) : 0
                ],
                'daily_revenue' => $dailyRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'top_buses' => $topBuses
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch revenue report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get owner bus performance report
     */
    public function busPerformanceReport(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $buses = Bus::where('owner_id', $user->id)->get();
            
            $busPerformance = $buses->map(function ($bus) {
                $totalBookings = Booking::where('bus_id', $bus->id)->count();
                $completedTrips = Booking::where('bus_id', $bus->id)->where('status', 'completed')->count();
                $cancelledTrips = Booking::where('bus_id', $bus->id)->where('status', 'cancelled')->count();
                $totalRevenue = Booking::where('bus_id', $bus->id)->sum('fare');

                return [
                    'id' => $bus->id,
                    'bus_number' => $bus->bus_number,
                    'model' => $bus->model,
                    'status' => $bus->status,
                    'total_bookings' => $totalBookings,
                    'completed_trips' => $completedTrips,
                    'cancelled_trips' => $cancelledTrips,
                    'completion_rate' => $totalBookings > 0 ? round(($completedTrips / $totalBookings) * 100, 2) : 0,
                    'revenue' => (float)$totalRevenue,
                    'avg_fare' => $totalBookings > 0 ? round($totalRevenue / $totalBookings, 2) : 0
                ];
            });

            return response()->json($busPerformance);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch bus performance report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get owner incident report
     */
    public function incidentReport(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            
            // Incidents by status
            $incidents = Incident::whereIn('bus_id', $ownerBusIds)
                ->select('*')
                ->orderBy('created_at', 'desc')
                ->get();

            $pending = $incidents->filter(fn($i) => in_array($i->status, ['reported', 'in_progress']))->count();
            $resolved = $incidents->filter(fn($i) => $i->status === 'resolved')->count();

            // Incidents by type
            $byType = $incidents->groupBy('type')->map(function ($group) {
                return [
                    'type' => $group->first()->type,
                    'count' => $group->count(),
                    'resolved' => $group->filter(fn($i) => $i->status === 'resolved')->count()
                ];
            })->values();

            // Recent incidents
            $recent = $incidents->take(10)->values();

            return response()->json([
                'summary' => [
                    'total_incidents' => $incidents->count(),
                    'pending' => $pending,
                    'resolved' => $resolved,
                    'resolution_rate' => $incidents->count() > 0 ? round(($resolved / $incidents->count()) * 100, 2) : 0
                ],
                'by_type' => $byType,
                'recent_incidents' => $recent
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch incident report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get owner booking statistics report
     */
    public function bookingStatsReport(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            
            $totalBookings = Booking::whereIn('bus_id', $ownerBusIds)->count();
            $confirmedBookings = Booking::whereIn('bus_id', $ownerBusIds)->where('status', 'confirmed')->count();
            $completedBookings = Booking::whereIn('bus_id', $ownerBusIds)->where('status', 'completed')->count();
            $cancelledBookings = Booking::whereIn('bus_id', $ownerBusIds)->where('status', 'cancelled')->count();

            // Payment method breakdown
            $paymentMethods = Booking::whereIn('bus_id', $ownerBusIds)
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(fare) as revenue'))
                ->groupBy('payment_method')
                ->get();

            // Payment status breakdown
            $paymentStatus = Booking::whereIn('bus_id', $ownerBusIds)
                ->select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(fare) as revenue'))
                ->groupBy('payment_status')
                ->get();

            return response()->json([
                'booking_status' => [
                    'total' => $totalBookings,
                    'confirmed' => $confirmedBookings,
                    'completed' => $completedBookings,
                    'cancelled' => $cancelledBookings
                ],
                'payment_methods' => $paymentMethods,
                'payment_status' => $paymentStatus
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch booking stats', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all available reports list
     */
    public function reportsIndex(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'available_reports' => [
                [
                    'id' => 'revenue',
                    'name' => 'Revenue Report',
                    'description' => 'Track daily, monthly and yearly revenue with top performing buses',
                    'endpoint' => '/reports/revenue',
                    'icon' => 'chart-bar'
                ],
                [
                    'id' => 'bus_performance',
                    'name' => 'Bus Performance Report',
                    'description' => 'View performance metrics for each of your buses',
                    'endpoint' => '/reports/bus-performance',
                    'icon' => 'chart-line'
                ],
                [
                    'id' => 'incidents',
                    'name' => 'Incident Report',
                    'description' => 'Monitor incidents and their resolution status',
                    'endpoint' => '/reports/incidents',
                    'icon' => 'exclamation-triangle'
                ],
                [
                    'id' => 'bookings',
                    'name' => 'Booking Statistics',
                    'description' => 'Analyze booking patterns and payment methods',
                    'endpoint' => '/reports/booking-stats',
                    'icon' => 'ticket'
                ]
            ]
        ]);
    }
}
