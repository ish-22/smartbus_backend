<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'offer_id',
        'booking_id',
        'discount_amount',
        'status',
        'used_at'
    ];

    protected $casts = [
        'used_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public static function hasUserRedeemedOffer($userId, $offerId)
    {
        return self::where('user_id', $userId)
                  ->where('offer_id', $offerId)
                  ->exists();
    }
}