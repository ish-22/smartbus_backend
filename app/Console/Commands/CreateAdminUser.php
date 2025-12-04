<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--email=admin@gmail.com} {--password=admin123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role' => 'admin'
            ]);
            $this->info("Admin user updated successfully!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
        } else {
            User::create([
                'name' => 'Administrator',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin'
            ]);
            $this->info("Admin user created successfully!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
        }

        return 0;
    }
}
