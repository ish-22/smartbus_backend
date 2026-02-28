<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\OverpassApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Overpass API Routes Seeder
 * 
 * This seeder fetches REAL bus routes from OpenStreetMap using Overpass API
 * and seeds them into the database.
 * 
 * Usage: php artisan db:seed --class=OverpassRoutesSeeder
 */
class OverpassRoutesSeeder extends Seeder
{
    private OverpassApiService $overpassService;
    
    public function __construct()
    {
        $this->overpassService = new OverpassApiService();
    }
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching bus routes from OpenStreetMap Overpass API...');
        $this->command->info('This may take 30-60 seconds...');
        
        $routes = $this->overpassService->fetchBusRoutes();
        
        if (empty($routes)) {
            $this->command->warn('⚠️  No bus routes found in OpenStreetMap for Sri Lanka');
            $this->command->info('This could mean:');
            $this->command->info('1. OpenStreetMap does not have bus route data for Sri Lanka');
            $this->command->info('2. The routes are tagged differently');
            $this->command->info('3. Network connectivity issues');
            $this->command->info('');
            $this->command->info('Using fallback: RealSriLankanRoutesSeeder instead...');
            
            // Run the manual routes seeder as fallback
            $this->call(RealSriLankanRoutesSeeder::class);
            return;
        }
        
        $this->command->info("Found " . count($routes) . " routes from OpenStreetMap");
        
        $inserted = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($routes as $routeData) {
            // Check if route already exists
            $exists = DB::table('routes')
                ->where('route_number', $routeData['route_number'])
                ->where('name', $routeData['name'])
                ->first();
            
            if ($exists) {
                $skipped++;
                continue;
            }
            
            try {
                DB::table('routes')->insert([
                    'route_number' => $routeData['route_number'],
                    'name' => $routeData['name'],
                    'start_point' => $routeData['start_point'] ?? null,
                    'end_point' => $routeData['end_point'] ?? null,
                    'start_location' => $routeData['start_point'] ?? null,
                    'end_location' => $routeData['end_point'] ?? null,
                    'distance' => 0, // OSM doesn't always have distance
                    'fare' => 0,
                    'metadata' => json_encode([
                        'type' => $routeData['type'],
                        'source' => 'openstreetmap',
                        'coordinates' => $routeData['coordinates'] ?? null,
                        'stops' => $routeData['stops'] ?? [],
                        'tags' => $routeData['tags'] ?? [],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $this->command->warn("Failed to insert route {$routeData['route_number']}: {$e->getMessage()}");
                $errors++;
                Log::error('Failed to insert route from Overpass API', [
                    'route' => $routeData,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ Overpass API routes seeding completed!');
        $this->command->info("   Inserted: {$inserted} routes");
        $this->command->info("   Skipped (already exist): {$skipped} routes");
        
        if ($errors > 0) {
            $this->command->warn("   Errors: {$errors} routes");
        }
        
        if ($inserted === 0 && $skipped === 0) {
            $this->command->warn('');
            $this->command->warn('⚠️  No routes were inserted. Data may not be available in OpenStreetMap.');
            $this->command->info('Consider using RealSriLankanRoutesSeeder for manually curated routes.');
        }
    }
}

