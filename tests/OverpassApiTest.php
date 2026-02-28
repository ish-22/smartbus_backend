<?php

/**
 * Test script for Overpass API
 * 
 * Run: php tests/OverpassApiTest.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Services\OverpassApiService;

echo "Testing Overpass API for Sri Lankan Bus Routes\n";
echo "==============================================\n\n";

$service = new OverpassApiService();

echo "Fetching routes from OpenStreetMap...\n";
echo "This may take 30-60 seconds...\n\n";

$routes = $service->fetchBusRoutes();

if (empty($routes)) {
    echo "⚠️  No bus routes found in OpenStreetMap for Sri Lanka\n";
    echo "\nThis could mean:\n";
    echo "1. OpenStreetMap does not have bus route data for Sri Lanka\n";
    echo "2. The routes are tagged differently\n";
    echo "3. Network connectivity issues\n";
    echo "\nConsider using RealSriLankanRoutesSeeder for manually curated routes.\n";
    exit(1);
}

echo "✅ Found " . count($routes) . " routes from OpenStreetMap\n\n";

echo "Sample routes:\n";
echo "--------------\n";
foreach (array_slice($routes, 0, 10) as $route) {
    echo sprintf(
        "[%s] %s\n   From: %s\n   To: %s\n   Type: %s\n\n",
        $route['route_number'],
        $route['name'],
        $route['start_point'] ?? 'N/A',
        $route['end_point'] ?? 'N/A',
        $route['type']
    );
}

if (count($routes) > 10) {
    echo "... and " . (count($routes) - 10) . " more routes\n";
}

