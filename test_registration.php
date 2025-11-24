<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test registration
echo "Testing user registration...\n";

try {
    // Create test user data
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'role' => 'passenger'
    ];

    // Create user using the model
    $user = App\Models\User::create([
        'name' => $userData['name'],
        'email' => $userData['email'],
        'password' => Illuminate\Support\Facades\Hash::make($userData['password']),
        'role' => $userData['role']
    ]);

    echo "✓ User created successfully!\n";
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";

    // Verify in database
    $count = App\Models\User::count();
    echo "\nTotal users in database: $count\n";

} catch (Exception $e) {
    echo "✗ Registration failed: " . $e->getMessage() . "\n";
}

?>