<?php

// Test API registration endpoint
$apiUrl = 'http://127.0.0.1:8000/api/auth/register';

$userData = [
    'name' => 'Test User API',
    'email' => 'testapi@example.com',
    'password' => 'password123',
    'role' => 'passenger'
];

echo "Testing API registration endpoint...\n";
echo "URL: $apiUrl\n";
echo "Data: " . json_encode($userData) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "✗ cURL Error: $error\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: $response\n";
    
    if ($httpCode === 201) {
        echo "✓ Registration successful!\n";
    } else {
        echo "✗ Registration failed\n";
    }
}

?>