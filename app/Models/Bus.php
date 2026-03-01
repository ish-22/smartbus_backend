<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'bus_number',
        'number', // Alias for bus_number
        'model',
        'capacity',
        'status',
        'type',
        'route_id',
        'driver_id',
        'owner_id',
        'current_latitude',
        'current_longitude',
        'last_location_update',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function lostFoundItems()
    {
        return $this->hasMany(LostFound::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Accessor for number (alias of bus_number)
     */
    public function getNumberAttribute()
    {
        return $this->bus_number;
    }
}
