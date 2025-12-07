<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'bus_id', 'route_id', 'seat_number', 'fare', 'status', 'travel_date',
        'payment_method', 'payment_status', 'transaction_id', 'payment_details',
        'discount_amount', 'points_used', 'payment_date'
    ];

    protected $casts = [
        'payment_details' => 'array',
        'payment_date' => 'datetime',
        'travel_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->fare - $this->discount_amount;
    }

    public function canUsePoints($points)
    {
        $maxPointsUsable = floor($this->fare * 0.5); // 1 point = Rs. 1, max 50% of fare
        return $points <= $maxPointsUsable && $points <= $this->fare;
    }
}
