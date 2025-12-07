<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerOfferPayment extends Model
{
    protected $fillable = [
        'owner_id',
        'offer_id',
        'passenger_id',
        'booking_id',
        'discount_amount',
        'status'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}