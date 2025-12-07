<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Booking;
use App\Models\Reward;
use App\Http\Controllers\RewardController;

// Test the reward integration
echo "Testing Reward + Booking Integration\n";
echo "===================================\n\n";

// Find a passenger user
$passenger = User::where('role', 'passenger')->first();
if (!$passenger) {
    echo "No passenger found. Please create a passenger user first.\n";
    exit;
}

echo "Testing with passenger: {$passenger->name} (ID: {$passenger->id})\n";

// Get initial points
$initialPoints = Reward::getUserTotalPoints($passenger->id);
echo "Initial points: {$initialPoints}\n";

// Create a test booking
$booking = Booking::create([
    'user_id' => $passenger->id,
    'bus_id' => 1, // Assuming bus ID 1 exists
    'route_id' => 1, // Assuming route ID 1 exists
    'seat_number' => 'A01',
    'ticket_category' => 'regular',
    'status' => 'pending',
    'total_amount' => 250.00,
    'payment_method' => 'cash'
]);

echo "Created booking ID: {$booking->id}\n";

// Complete the booking and add reward points
$booking->update(['status' => 'completed']);
$reward = RewardController::autoAddPointsOnBookingComplete($passenger->id, $booking->id);

echo "Booking completed and reward added\n";

// Check final points
$finalPoints = Reward::getUserTotalPoints($passenger->id);
echo "Final points: {$finalPoints}\n";
echo "Points awarded: " . ($finalPoints - $initialPoints) . "\n";

// Test duplicate prevention
echo "\nTesting duplicate prevention...\n";
$duplicateReward = RewardController::autoAddPointsOnBookingComplete($passenger->id, $booking->id);
$afterDuplicatePoints = Reward::getUserTotalPoints($passenger->id);
echo "Points after duplicate attempt: {$afterDuplicatePoints}\n";
echo "Duplicate prevented: " . ($afterDuplicatePoints == $finalPoints ? 'YES' : 'NO') . "\n";

// Show reward history
echo "\nReward history for this booking:\n";
$rewards = Reward::where('booking_id', $booking->id)->get();
foreach ($rewards as $reward) {
    echo "- {$reward->points} points for '{$reward->reason}': {$reward->description}\n";
}

echo "\nTest completed successfully!\n";