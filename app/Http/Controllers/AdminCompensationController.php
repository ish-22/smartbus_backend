<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminCompensation;
use App\Models\User;

class AdminCompensationController extends Controller
{
    public function index(Request $request)
    {
        $compensations = AdminCompensation::with(['booking.user', 'busOwner', 'offer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $compensations
        ]);
    }

    public function getStats(Request $request)
    {
        $stats = [
            'total_pending' => AdminCompensation::where('status', 'pending')->count(),
            'total_paid' => AdminCompensation::where('status', 'paid')->count(),
            'pending_amount' => AdminCompensation::where('status', 'pending')->sum('amount'),
            'paid_amount' => AdminCompensation::where('status', 'paid')->sum('amount'),
            'total_amount' => AdminCompensation::sum('amount')
        ];

        return response()->json($stats);
    }

    public function markAsPaid($id, Request $request)
    {
        try {
            $compensation = AdminCompensation::findOrFail($id);
            $compensation->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compensation marked as paid',
                'data' => $compensation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getOwnerCompensations(Request $request)
    {
        try {
            $ownerId = $request->user()->id;
            
            $compensations = AdminCompensation::with(['booking.user', 'offer'])
                ->where('bus_owner_id', $ownerId)
                ->orderBy('created_at', 'desc')
                ->get();

            $stats = [
                'pending_count' => AdminCompensation::where('bus_owner_id', $ownerId)->where('status', 'pending')->count(),
                'paid_count' => AdminCompensation::where('bus_owner_id', $ownerId)->where('status', 'paid')->count(),
                'pending_amount' => AdminCompensation::where('bus_owner_id', $ownerId)->where('status', 'pending')->sum('amount') ?? 0,
                'paid_amount' => AdminCompensation::where('bus_owner_id', $ownerId)->where('status', 'paid')->sum('amount') ?? 0
            ];

            return response()->json([
                'success' => true,
                'data' => $compensations,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'stats' => [
                    'pending_count' => 0,
                    'paid_count' => 0,
                    'pending_amount' => 0,
                    'paid_amount' => 0
                ]
            ], 200); // Return 200 with empty data instead of 500
        }
    }
}