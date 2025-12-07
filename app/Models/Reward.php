<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'description',
        'booking_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public static function getUserTotalPoints($userId)
    {
        return self::where('user_id', $userId)->sum('points');
    }

    public static function addPoints($userId, $points, $reason, $description = null, $bookingId = null)
    {
        return self::create([
            'user_id' => $userId,
            'points' => $points,
            'reason' => $reason,
            'description' => $description,
            'booking_id' => $bookingId
        ]);
    }

    public static function deductPoints($userId, $points, $reason, $description = null, $bookingId = null)
    {
        return self::create([
            'user_id' => $userId,
            'points' => -$points,
            'reason' => $reason,
            'description' => $description,
            'booking_id' => $bookingId
        ]);
    }
}