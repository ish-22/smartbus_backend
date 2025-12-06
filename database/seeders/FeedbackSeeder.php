<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feedback;
use App\Models\User;

class FeedbackSeeder extends Seeder
{
    public function run()
    {
        // Get or create users
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartbus.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        $passenger = User::firstOrCreate(
            ['email' => 'passenger@smartbus.com'],
            [
                'name' => 'John Passenger',
                'password' => bcrypt('password'),
                'role' => 'passenger'
            ]
        );

        // Create sample feedback
        Feedback::create([
            'user_id' => $passenger->id,
            'subject' => 'Great Service!',
            'message' => 'The bus service was excellent today. Driver was very professional and the bus was clean.',
            'type' => 'praise',
            'rating' => 5,
            'status' => 'pending'
        ]);

        Feedback::create([
            'user_id' => $passenger->id,
            'subject' => 'Bus Delay Issue',
            'message' => 'The bus was 20 minutes late this morning. This caused me to be late for work.',
            'type' => 'complaint',
            'rating' => 2,
            'status' => 'pending'
        ]);

        Feedback::create([
            'user_id' => $passenger->id,
            'subject' => 'Suggestion for Mobile App',
            'message' => 'It would be great if the mobile app could show real-time bus locations.',
            'type' => 'suggestion',
            'status' => 'reviewed',
            'admin_response' => 'Thank you for your suggestion. We are working on implementing real-time tracking.',
            'responded_by' => $admin->id,
            'responded_at' => now()
        ]);
    }
}