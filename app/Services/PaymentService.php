<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Reward;
use App\Models\AdminCompensation;

class PaymentService
{
    public function processPayment(Booking $booking, array $paymentData)
    {
        $paymentMethod = $paymentData['payment_method'];
        
        switch ($paymentMethod) {
            case 'credit_card':
            case 'debit_card':
                return $this->processCardPayment($booking, $paymentData);
            case 'digital_wallet':
                return $this->processWalletPayment($booking, $paymentData);
            case 'cash':
                return $this->processCashPayment($booking);
            default:
                throw new \Exception('Invalid payment method');
        }
    }

    private function processCardPayment(Booking $booking, array $paymentData)
    {
        $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare - ($booking->discount_amount ?? 0),
            'payment_method' => $paymentData['payment_method'],
            'status' => 'completed',
            'transaction_id' => $transactionId
        ]);

        $booking->update([
            'payment_status' => 'paid',
            'transaction_id' => $transactionId
        ]);

        return $payment;
    }

    private function processWalletPayment(Booking $booking, array $paymentData)
    {
        $transactionId = 'WALLET_' . time() . '_' . rand(1000, 9999);
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->fare - ($booking->discount_amount ?? 0),
            'payment_method' => 'digital_wallet',
            'status' => 'completed',
            'transaction_id' => $transactionId
        ]);

        $booking->update([
            'payment_status' => 'paid',
            'transaction_id' => $transactionId
        ]);

        return $payment;
    }

    private function processCashPayment(Booking $booking)
    {
        $booking->update([
            'payment_status' => 'pending'
        ]);

        return null;
    }

    public function applyPointsDiscount(Booking $booking, int $pointsToUse)
    {
        if (!$booking->canUsePoints($pointsToUse)) {
            throw new \Exception('Cannot use that many points for this booking');
        }

        $userPoints = Reward::getUserTotalPoints($booking->user_id);
        if ($userPoints < $pointsToUse) {
            throw new \Exception('Insufficient points');
        }

        $discountAmount = $pointsToUse;
        
        $booking->update([
            'points_used' => $pointsToUse,
            'discount_amount' => $discountAmount
        ]);

        Reward::deductPoints(
            $booking->user_id,
            $pointsToUse,
            'booking_discount',
            "Points used for booking #{$booking->id}",
            $booking->id
        );

        // Create admin compensation for bus owner
        $busOwnerId = $booking->bus->user_id ?? 1; // Assuming bus has owner_id
        AdminCompensation::createCompensation(
            $booking->id,
            $busOwnerId,
            'points_discount',
            $discountAmount,
            "Admin compensation for {$pointsToUse} points discount on booking #{$booking->id}"
        );

        return $discountAmount;
    }
}