<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCompensation extends Model
{
    protected $fillable = [
        'booking_id',
        'bus_owner_id', 
        'offer_id',
        'type',
        'amount',
        'status',
        'description',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function busOwner()
    {
        return $this->belongsTo(User::class, 'bus_owner_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public static function createCompensation($bookingId, $busOwnerId, $type, $amount, $description, $offerId = null)
    {
        return self::create([
            'booking_id' => $bookingId,
            'bus_owner_id' => $busOwnerId,
            'offer_id' => $offerId,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'status' => 'pending'
        ]);
    }
}