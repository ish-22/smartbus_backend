<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;

class PaymentController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,completed,failed',
            'gateway' => 'nullable|string|max:100',
            'meta' => 'nullable|array'
        ]);

        $payment = Payment::findOrFail($id);
        $payment->update($data);

        // Update booking status if payment completed
        if ($data['status'] === 'completed' && $payment->booking) {
            $payment->booking->update(['status' => 'confirmed']);
        }

        return response()->json($payment->load('booking'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'gateway' => 'nullable|string|max:100',
            'meta' => 'nullable|array'
        ]);

        $data['status'] = 'pending';
        $data['created_at'] = now();

        $payment = Payment::create($data);
        return response()->json($payment->load('booking'), 201);
    }

    public function show($id)
    {
        $payment = Payment::with('booking')->findOrFail($id);
        return response()->json($payment);
    }
}