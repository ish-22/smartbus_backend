<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFeedbackDaily extends Model
{
    protected $table = 'user_feedback_daily';
    
    protected $fillable = [
        'user_id',
        'feedback_date'
    ];

    protected $casts = [
        'feedback_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}