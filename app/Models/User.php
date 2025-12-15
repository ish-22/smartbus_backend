<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'driver_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function drivenBuses()
    {
        return $this->hasMany(Bus::class, 'driver_id');
    }

    public function lostFoundItems()
    {
        return $this->hasMany(LostFound::class);
    }

    public function driverAssignments()
    {
        return $this->hasMany(DriverAssignment::class, 'driver_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(DriverAssignment::class, 'driver_id')
            ->whereDate('assigned_at', today())
            ->whereNull('ended_at')
            ->latest('assigned_at');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'driver_id');
    }
}
