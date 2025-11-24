<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Route;
use App\Models\Bus;
use App\Models\Stop;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Create sample users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@smartbus.com',
            'phone' => '+94771234567',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        $passenger = User::create([
            'name' => 'John Passenger',
            'email' => 'passenger@example.com',
            'phone' => '+94777654321',
            'password' => Hash::make('password123'),
            'role' => 'passenger'
        ]);

        $driver = User::create([
            'name' => 'Driver Smith',
            'email' => 'driver@example.com',
            'phone' => '+94779876543',
            'password' => Hash::make('driver123'),
            'role' => 'driver'
        ]);

        // Create sample routes
        $route1 = Route::create([
            'name' => 'Colombo - Kandy',
            'start_point' => 'Colombo',
            'end_point' => 'Kandy',
            'metadata' => ['distance' => '115km', 'duration' => '3h']
        ]);

        $route2 = Route::create([
            'name' => 'Colombo - Galle',
            'start_point' => 'Colombo',
            'end_point' => 'Galle',
            'metadata' => ['distance' => '119km', 'duration' => '2.5h']
        ]);

        $route3 = Route::create([
            'name' => 'Kandy - Nuwara Eliya',
            'start_point' => 'Kandy',
            'end_point' => 'Nuwara Eliya',
            'metadata' => ['distance' => '77km', 'duration' => '2h']
        ]);

        // Create sample buses
        Bus::create([
            'number' => 'EXP-1001',
            'type' => 'expressway',
            'route_id' => $route1->id,
            'capacity' => 40,
            'driver_id' => $driver->id
        ]);

        Bus::create([
            'number' => 'EXP-1002',
            'type' => 'expressway',
            'route_id' => $route2->id,
            'capacity' => 45,
            'driver_id' => null
        ]);

        Bus::create([
            'number' => 'NOR-2001',
            'type' => 'normal',
            'route_id' => $route3->id,
            'capacity' => 50,
            'driver_id' => null
        ]);

        Bus::create([
            'number' => 'EXP-1003',
            'type' => 'expressway',
            'route_id' => $route1->id,
            'capacity' => 42,
            'driver_id' => null
        ]);

        // Create sample stops
        Stop::create([
            'route_id' => $route1->id,
            'name' => 'Colombo Fort',
            'lat' => 6.9344,
            'lng' => 79.8428,
            'sequence' => 1
        ]);

        Stop::create([
            'route_id' => $route1->id,
            'name' => 'Kegalle',
            'lat' => 7.2513,
            'lng' => 80.3464,
            'sequence' => 2
        ]);

        Stop::create([
            'route_id' => $route1->id,
            'name' => 'Kandy Central',
            'lat' => 7.2906,
            'lng' => 80.6337,
            'sequence' => 3
        ]);

        Stop::create([
            'route_id' => $route2->id,
            'name' => 'Colombo Fort',
            'lat' => 6.9344,
            'lng' => 79.8428,
            'sequence' => 1
        ]);

        Stop::create([
            'route_id' => $route2->id,
            'name' => 'Kalutara',
            'lat' => 6.5854,
            'lng' => 79.9607,
            'sequence' => 2
        ]);

        Stop::create([
            'route_id' => $route2->id,
            'name' => 'Galle Fort',
            'lat' => 6.0329,
            'lng' => 80.2168,
            'sequence' => 3
        ]);
    }
}