<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAssignment extends Model
{
    protected $fillable = [
        'driver_id',
        'bus_id',
        'driver_type',
        'assigned_at',
        'assignment_date',
        'ended_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'assignment_date' => 'date',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the driver (user) that owns this assignment
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the bus for this assignment
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    /**
     * Get the user (owner/admin) who assigned this driver
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get active assignments (not ended)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get today's assignments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('assigned_at', today());
    }

    /**
     * Scope to get assignments for a specific driver
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }
}

