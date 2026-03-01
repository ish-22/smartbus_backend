<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class DeleteOldBookings extends Command
{
    protected $signature = 'bookings:delete-old';
    protected $description = 'Delete bookings older than 2 months';

    public function handle()
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        
        $deleted = Booking::where('created_at', '<', $twoMonthsAgo)
            ->whereIn('status', ['completed', 'cancelled'])
            ->delete();
        
        $this->info("Deleted {$deleted} old bookings.");
        return 0;
    }
}
