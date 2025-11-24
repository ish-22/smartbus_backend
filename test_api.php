<?php

// Simple API test script
$baseUrl = 'http://127.0.0.1:8000/api';

function testEndpoint($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => json_decode($response, true)];
}

echo "Testing SmartBus API Endpoints\n";
echo "==============================\n\n";

// Test 1: Register user
echo "1. Testing user registration...\n";
$registerData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'role' => 'passenger'
];
$result = testEndpoint($baseUrl . '/auth/register', 'POST', $registerData);
echo "Status: " . $result['code'] . "\n";
if ($result['code'] == 201) {
    echo "✓ Registration successful\n";
    $token = $result['response']['token'];
} else {
    echo "✗ Registration failed\n";
    print_r($result['response']);
}
echo "\n";

// Test 2: Login
echo "2. Testing user login...\n";
$loginData = [
    'email' => 'test@example.com',
    'password' => 'password123'
];
$result = testEndpoint($baseUrl . '/auth/login', 'POST', $loginData);
echo "Status: " . $result['code'] . "\n";
if ($result['code'] == 200) {
    echo "✓ Login successful\n";
    $token = $result['response']['token'];
} else {
    echo "✗ Login failed\n";
    print_r($result['response']);
}
echo "\n";

// Test 3: Get buses
echo "3. Testing get buses...\n";
$result = testEndpoint($baseUrl . '/buses');
echo "Status: " . $result['code'] . "\n";
if ($result['code'] == 200) {
    echo "✓ Buses fetched successfully\n";
    echo "Found " . count($result['response']) . " buses\n";
} else {
    echo "✗ Failed to fetch buses\n";
}
echo "\n";

// Test 4: Get routes
echo "4. Testing get routes...\n";
$result = testEndpoint($baseUrl . '/routes');
echo "Status: " . $result['code'] . "\n";
if ($result['code'] == 200) {
    echo "✓ Routes fetched successfully\n";
    echo "Found " . count($result['response']) . " routes\n";
} else {
    echo "✗ Failed to fetch routes\n";
}
echo "\n";

// Test 5: Get user info (requires auth)
if (isset($token)) {
    echo "5. Testing get user info...\n";
    $result = testEndpoint($baseUrl . '/user', 'GET', null, $token);
    echo "Status: " . $result['code'] . "\n";
    if ($result['code'] == 200) {
        echo "✓ User info fetched successfully\n";
        echo "User: " . $result['response']['name'] . "\n";
    } else {
        echo "✗ Failed to fetch user info\n";
    }
    echo "\n";
}

echo "API testing completed!\n";
?>