<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    
    protected $fillable = [
        'user_id', 'bus_id', 'route_id', 'subject', 'message', 
        'type', 'rating', 'status', 'admin_response', 'responded_by', 'responded_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'responded_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function responder() { return $this->belongsTo(User::class, 'responded_by'); }
}
