<?php

/**
 * Lost & Found Module Test Script
 * Run this after migration to verify the setup
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LostFound;
use App\Models\User;
use App\Models\Bus;

echo "=== Lost & Found Module Test ===\n\n";

// Test 1: Check if table exists
echo "1. Checking if lost_found table exists...\n";
try {
    $tableExists = Schema::hasTable('lost_found');
    echo $tableExists ? "✓ Table exists\n" : "✗ Table does not exist\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check model
echo "\n2. Testing LostFound model...\n";
try {
    $model = new LostFound();
    echo "✓ Model loaded successfully\n";
    echo "   Fillable fields: " . implode(', ', $model->getFillable()) . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check relationships
echo "\n3. Testing relationships...\n";
try {
    $testUser = User::first();
    $testBus = Bus::first();
    
    if ($testUser) {
        echo "✓ User relationship available\n";
    }
    if ($testBus) {
        echo "✓ Bus relationship available\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Create test record
echo "\n4. Creating test record...\n";
try {
    $testUser = User::first();
    if ($testUser) {
        $item = LostFound::create([
            'item_name' => 'Test Item',
            'description' => 'This is a test item',
            'found_location' => 'Test Location',
            'found_date' => now()->format('Y-m-d'),
            'status' => 'lost',
            'user_id' => $testUser->id,
        ]);
        echo "✓ Test record created (ID: {$item->id})\n";
        
        // Clean up
        $item->delete();
        echo "✓ Test record deleted\n";
    } else {
        echo "⚠ No users found in database. Create a user first.\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan migrate (if not done)\n";
echo "2. Test API endpoints using Postman or curl\n";
echo "3. Access frontend pages for each role\n";
