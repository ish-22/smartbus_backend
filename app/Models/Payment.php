<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id', 'amount', 'status', 'gateway', 'meta'];
    
    protected $casts = [
        'meta' => 'array'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}