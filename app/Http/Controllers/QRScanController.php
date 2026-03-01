<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class QRScanController extends Controller
{
    public function validateTicket(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|string'
        ]);

        // Extract booking ID from ticket format (TKT-000001)
        $ticketId = $request->ticket_id;
        $bookingId = null;
        
        if (preg_match('/TKT-(\d+)/', $ticketId, $matches)) {
            $bookingId = (int) $matches[1];
        }

        $booking = Booking::with(['user', 'bus.route'])
            ->where('id', $bookingId)
            ->orWhere('booking_reference', $ticketId)
            ->first();

        if (!$booking) {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        $isValid = in_array($booking->status, ['confirmed', 'boarded']) && 
                   $booking->payment_status === 'paid';

        $route = $booking->route ?? $booking->bus->route;
        $routeInfo = $route ? ($route->start_point . ' - ' . $route->end_point) : 'N/A';

        return response()->json([
            'valid' => $isValid,
            'ticket_id' => 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
            'passenger' => $booking->user->name,
            'seat' => $booking->seat_number,
            'route' => $routeInfo,
            'booking_id' => $booking->id,
            'status' => $booking->status
        ]);
    }

    public function confirmBoarding(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer'
        ]);

        $booking = Booking::find($request->booking_id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Update status to completed (boarded)
        $booking->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Boarding confirmed',
            'booking' => $booking
        ]);
    }

    public function getRecentScans(Request $request)
    {
        $user = $request->user();
        
        // Get driver's current bus assignment
        $assignment = \App\Models\DriverAssignment::where('driver_id', $user->id)
            ->whereDate('assignment_date', today())
            ->whereNull('ended_at')
            ->first();
            
        if (!$assignment) {
            return response()->json([]);
        }

        $scans = Booking::with(['user'])
            ->where('bus_id', $assignment->bus_id)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                    'passenger' => $booking->user->name,
                    'seat' => $booking->seat_number,
                    'time' => $booking->updated_at->format('g:i A'),
                    'status' => 'Valid'
                ];
            });

        return response()->json($scans);
    }
}
