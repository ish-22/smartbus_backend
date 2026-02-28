<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CSV Route Importer Seeder
 * 
 * To use this seeder:
 * 1. Create a CSV file: storage/app/routes.csv
 * 2. Format: route_number,name,start_point,end_point,type,distance
 * 3. Example row: 100,Colombo Fort - Kandy,Colombo Fort,Kandy,normal,115
 * 4. Run: php artisan db:seed --class=CSVRouteImporterSeeder
 */
class CSVRouteImporterSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = storage_path('app/routes.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            $this->command->info("Please create a CSV file with the following format:");
            $this->command->info("route_number,name,start_point,end_point,type,distance");
            $this->command->info("Example: 100,Colombo Fort - Kandy,Colombo Fort,Kandy,normal,115");
            return;
        }

        $file = fopen($csvPath, 'r');
        
        // Skip header row
        fgetcsv($file);
        
        $inserted = 0;
        $skipped = 0;
        $errors = 0;
        $lineNumber = 1;

        while (($data = fgetcsv($file)) !== FALSE) {
            $lineNumber++;
            
            if (count($data) < 5) {
                $this->command->warn("Skipping line {$lineNumber}: Insufficient columns");
                $errors++;
                continue;
            }

            $routeData = [
                'route_number' => trim($data[0]),
                'name' => trim($data[1]),
                'start_point' => trim($data[2]),
                'end_point' => trim($data[3]),
                'type' => trim($data[4]) === 'expressway' ? 'expressway' : 'normal',
                'distance' => isset($data[5]) ? (float)trim($data[5]) : 0,
            ];

            // Validate required fields
            if (empty($routeData['route_number']) || empty($routeData['name'])) {
                $this->command->warn("Skipping line {$lineNumber}: Missing required fields");
                $errors++;
                continue;
            }

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
                        'distance' => $routeData['distance'],
                        'fare' => 0,
                        'metadata' => json_encode([
                            'type' => $routeData['type'],
                            'created_by' => 'csv_import'
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $this->command->warn("Failed to insert route at line {$lineNumber}: {$e->getMessage()}");
                    $errors++;
                }
            } else {
                $skipped++;
            }
        }

        fclose($file);

        $this->command->info("CSV Route Import completed!");
        $this->command->info("Inserted: {$inserted} routes");
        $this->command->info("Skipped (already exist): {$skipped} routes");
        if ($errors > 0) {
            $this->command->warn("Errors: {$errors} lines");
        }
    }
}

