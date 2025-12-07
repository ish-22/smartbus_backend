<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OwnerOfferPayment;

class OwnerPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = OwnerOfferPayment::with(['offer', 'passenger', 'booking']);

            if ($user->role === 'owner') {
                $query->where('owner_id', $user->id);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        try {
            $payment = OwnerOfferPayment::findOrFail($id);
            $payment->update(['status' => 'paid']);

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as paid',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStats(Request $request)
    {
        try {
            $user = $request->user();
            $query = OwnerOfferPayment::query();

            if ($user->role === 'owner') {
                $query->where('owner_id', $user->id);
            }

            $stats = [
                'total_pending' => (clone $query)->where('status', 'pending')->sum('discount_amount'),
                'total_paid' => (clone $query)->where('status', 'paid')->sum('discount_amount'),
                'pending_count' => (clone $query)->where('status', 'pending')->count(),
                'paid_count' => (clone $query)->where('status', 'paid')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}