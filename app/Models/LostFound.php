<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFound extends Model
{
    protected $table = 'lost_found';
    
    protected $fillable = [
        'item_name',
        'description',
        'found_location',
        'found_date',
        'status',
        'user_id',
        'bus_id',
    ];

    protected $casts = [
        'found_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
