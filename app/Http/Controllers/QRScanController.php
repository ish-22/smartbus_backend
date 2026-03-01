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

        $booking = Booking::with(['user', 'schedule.route'])
            ->where('booking_reference', $request->ticket_id)
            ->first();

        if (!$booking) {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        $isValid = $booking->status === 'confirmed' && 
                   $booking->payment_status === 'paid';

        return response()->json([
            'valid' => $isValid,
            'ticket_id' => $booking->booking_reference,
            'passenger' => $booking->user->name,
            'seat' => $booking->seat_number,
            'route' => $booking->schedule->route->origin . ' - ' . $booking->schedule->route->destination,
            'booking_id' => $booking->id
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

        $booking->update(['status' => 'boarded']);

        return response()->json([
            'message' => 'Boarding confirmed',
            'booking' => $booking
        ]);
    }

    public function getRecentScans(Request $request)
    {
        $scans = Booking::with(['user'])
            ->where('status', 'boarded')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->booking_reference,
                    'passenger' => $booking->user->name,
                    'time' => $booking->updated_at->format('g:i A'),
                    'status' => 'Valid'
                ];
            });

        return response()->json($scans);
    }
}
