<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Offer extends Model
{
    protected $fillable = [
        'title',
        'description',
        'discount_percentage',
        'required_points',
        'start_date',
        'end_date',
        'status'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function ownerPayments()
    {
        return $this->hasMany(OwnerOfferPayment::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               Carbon::now()->between($this->start_date, $this->end_date);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
    }
}