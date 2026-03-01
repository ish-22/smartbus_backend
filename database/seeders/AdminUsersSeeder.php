<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run()
    {
        $admins = [
            [
                'name' => 'John Admin',
                'email' => 'john@smartbus.lk',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'last_login' => now()->subHours(2),
                'permissions' => ['All Access'],
            ],
            [
                'name' => 'Sarah Manager',
                'email' => 'sarah@smartbus.lk',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'last_login' => now()->subHours(3),
                'permissions' => ['User Management', 'Reports'],
            ],
            [
                'name' => 'Mike Support',
                'email' => 'mike@smartbus.lk',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'inactive',
                'last_login' => now()->subDays(5),
                'permissions' => ['View Only', 'Customer Support'],
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }
}
