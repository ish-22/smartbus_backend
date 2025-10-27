<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    // POST /api/payments
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'nullable|integer|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'gateway' => 'nullable|string'
        ]);

        $payment = Payment::create([
            'booking_id' => $data['booking_id'] ?? null,
            'amount' => $data['amount'],
            'status' => 'pending',
            'gateway' => $data['gateway'] ?? null,
            'meta' => null
        ]);

        return response()->json($payment, 201);
    }

    // POST /api/payments/webhook
    public function webhook(Request $request)
    {
        // Implement gateway-specific logic and update payment status
        return response()->json(['ok'=>true]);
    }
}
