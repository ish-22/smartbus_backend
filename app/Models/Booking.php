<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $fillable = ['user_id','bus_id','route_id','seat_number','ticket_category','status','total_amount','payment_method'];
}
