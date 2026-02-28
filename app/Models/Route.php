<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = ['route_number', 'name', 'start_point', 'end_point', 'distance', 'fare', 'metadata'];
    
    protected $casts = [
        'metadata' => 'array'
    ];

    public function buses()
    {
        return $this->hasMany(Bus::class);
    }

    public function stops()
    {
        return $this->hasMany(Stop::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}