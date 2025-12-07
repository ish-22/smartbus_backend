<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'gateway_response',
        'card_last_four',
        'processed_at'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'processed_at' => 'datetime'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}