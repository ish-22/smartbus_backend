<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Extended Sri Lankan Routes Seeder
 * This seeder can be expanded to include all 600+ routes
 * To add more routes, extend the getExtendedRoutes() method
 */
class ExtendedSriLankanRoutesSeeder extends Seeder
{
    /**
     * Seed extended Sri Lankan bus routes with route numbers
     * This can be expanded to include all routes
     */
    public function run(): void
    {
        $routes = $this->getExtendedRoutes();

        $inserted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($routes as $routeData) {
            // Check if route already exists
            $exists = DB::table('routes')
                ->where('route_number', $routeData['route_number'])
                ->where('name', $routeData['name'])
                ->first();

            if (!$exists) {
                try {
                    DB::table('routes')->insert([
                        'route_number' => $routeData['route_number'],
                        'name' => $routeData['name'],
                        'start_point' => $routeData['start_point'],
                        'end_point' => $routeData['end_point'],
                        'start_location' => $routeData['start_point'],
                        'end_location' => $routeData['end_point'],
                        'distance' => $routeData['distance'] ?? 0,
                        'fare' => $routeData['fare'] ?? 0,
                        'metadata' => json_encode([
                            'type' => $routeData['type'],
                            'created_by' => 'system',
                            'route_number' => $routeData['route_number']
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $this->command->warn("Failed to insert route {$routeData['route_number']}: {$routeData['name']} - {$e->getMessage()}");
                    $errors++;
                }
            } else {
                $skipped++;
            }
        }

        $this->command->info("Extended Sri Lankan routes seeding completed!");
        $this->command->info("Inserted: {$inserted} routes");
        $this->command->info("Skipped (already exist): {$skipped} routes");
        if ($errors > 0) {
            $this->command->warn("Errors: {$errors} routes");
        }
    }

    /**
     * Get extended list of Sri Lankan bus routes
     * 
     * INSTRUCTIONS TO ADD MORE ROUTES:
     * 1. Add route entries to this array following the format:
     *    ['route_number' => 'XXX', 'name' => 'Start - End', 'start_point' => 'Start', 'end_point' => 'End', 'type' => 'normal|expressway', 'distance' => XX]
     * 
     * 2. Route numbering conventions:
     *    - Expressway: E01, E02, etc. or H01, H02, etc.
     *    - Normal routes: 1-999 for intercity, 1000+ for city routes
     *    - Variations: Can use formats like '100A', '100-1', etc.
     * 
     * 3. To add all 600+ routes, you can:
     *    - Import from CSV file (create a CSV import seeder)
     *    - Use external API data
     *    - Expand this array with all routes
     */
    private function getExtendedRoutes(): array
    {
        return [
            // Add additional routes here following the comprehensive seeder pattern
            // Example format:
            // ['route_number' => '1200', 'name' => 'Route Name', 'start_point' => 'Start', 'end_point' => 'End', 'type' => 'normal', 'distance' => 50],
            
            // This seeder is designed to be extended. You can:
            // 1. Add routes manually here
            // 2. Import from CSV (create separate CSV import seeder)
            // 3. Connect to external API (add API integration)
        ];
    }
}

