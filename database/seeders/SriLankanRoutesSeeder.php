<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use Illuminate\Support\Facades\DB;

class SriLankanRoutesSeeder extends Seeder
{
    /**
     * Seed comprehensive Sri Lankan bus routes (Highway/Expressway and Normal routes)
     * Based on major bus routes operated in Sri Lanka
     */
    public function run(): void
    {
        $routes = [
            // ========== EXPRESSWAY/HIGHWAY ROUTES ==========
            ['name' => 'Colombo - Kandy (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Kandy', 'type' => 'expressway'],
            ['name' => 'Colombo - Galle (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Galle', 'type' => 'expressway'],
            ['name' => 'Colombo - Matara (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Matara', 'type' => 'expressway'],
            ['name' => 'Colombo - Kurunegala (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Kurunegala', 'type' => 'expressway'],
            ['name' => 'Colombo - Negombo (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Negombo', 'type' => 'expressway'],
            ['name' => 'Colombo - Pinnawala (Expressway)', 'start_point' => 'Colombo', 'end_point' => 'Pinnawala', 'type' => 'expressway'],
            ['name' => 'Kandy - Nuwara Eliya (Highway)', 'start_point' => 'Kandy', 'end_point' => 'Nuwara Eliya', 'type' => 'expressway'],
            ['name' => 'Colombo - Ratnapura (Highway)', 'start_point' => 'Colombo', 'end_point' => 'Ratnapura', 'type' => 'expressway'],
            ['name' => 'Colombo - Avissawella (Highway)', 'start_point' => 'Colombo', 'end_point' => 'Avissawella', 'type' => 'expressway'],
            
            // ========== COLOMBO TO NORTHERN PROVINCE ==========
            ['name' => 'Colombo - Jaffna', 'start_point' => 'Colombo Fort', 'end_point' => 'Jaffna', 'type' => 'normal'],
            ['name' => 'Colombo - Vavuniya', 'start_point' => 'Colombo Fort', 'end_point' => 'Vavuniya', 'type' => 'normal'],
            ['name' => 'Colombo - Anuradhapura', 'start_point' => 'Colombo Fort', 'end_point' => 'Anuradhapura', 'type' => 'normal'],
            ['name' => 'Colombo - Polonnaruwa', 'start_point' => 'Colombo Fort', 'end_point' => 'Polonnaruwa', 'type' => 'normal'],
            ['name' => 'Colombo - Kurunegala (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Kurunegala', 'type' => 'normal'],
            ['name' => 'Colombo - Puttalam', 'start_point' => 'Colombo Fort', 'end_point' => 'Puttalam', 'type' => 'normal'],
            ['name' => 'Colombo - Chilaw', 'start_point' => 'Colombo Fort', 'end_point' => 'Chilaw', 'type' => 'normal'],
            ['name' => 'Colombo - Negombo (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Negombo', 'type' => 'normal'],
            ['name' => 'Colombo - Katunayake', 'start_point' => 'Colombo Fort', 'end_point' => 'Katunayake', 'type' => 'normal'],
            ['name' => 'Colombo - Wennappuwa', 'start_point' => 'Colombo Fort', 'end_point' => 'Wennappuwa', 'type' => 'normal'],
            
            // ========== COLOMBO TO CENTRAL PROVINCE ==========
            ['name' => 'Colombo - Kandy (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Colombo - Peradeniya', 'start_point' => 'Colombo Fort', 'end_point' => 'Peradeniya', 'type' => 'normal'],
            ['name' => 'Colombo - Matale', 'start_point' => 'Colombo Fort', 'end_point' => 'Matale', 'type' => 'normal'],
            ['name' => 'Colombo - Dambulla', 'start_point' => 'Colombo Fort', 'end_point' => 'Dambulla', 'type' => 'normal'],
            ['name' => 'Colombo - Sigiriya', 'start_point' => 'Colombo Fort', 'end_point' => 'Sigiriya', 'type' => 'normal'],
            ['name' => 'Colombo - Kegalle', 'start_point' => 'Colombo Fort', 'end_point' => 'Kegalle', 'type' => 'normal'],
            ['name' => 'Colombo - Rambukkana', 'start_point' => 'Colombo Fort', 'end_point' => 'Rambukkana', 'type' => 'normal'],
            ['name' => 'Colombo - Mawanella', 'start_point' => 'Colombo Fort', 'end_point' => 'Mawanella', 'type' => 'normal'],
            
            // ========== COLOMBO TO UVA PROVINCE ==========
            ['name' => 'Colombo - Badulla', 'start_point' => 'Colombo Fort', 'end_point' => 'Badulla', 'type' => 'normal'],
            ['name' => 'Colombo - Bandarawela', 'start_point' => 'Colombo Fort', 'end_point' => 'Bandarawela', 'type' => 'normal'],
            ['name' => 'Colombo - Haputale', 'start_point' => 'Colombo Fort', 'end_point' => 'Haputale', 'type' => 'normal'],
            ['name' => 'Colombo - Monaragala', 'start_point' => 'Colombo Fort', 'end_point' => 'Monaragala', 'type' => 'normal'],
            ['name' => 'Colombo - Wellawaya', 'start_point' => 'Colombo Fort', 'end_point' => 'Wellawaya', 'type' => 'normal'],
            
            // ========== COLOMBO TO SABARAGAMUWA PROVINCE ==========
            ['name' => 'Colombo - Ratnapura (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Ratnapura', 'type' => 'normal'],
            ['name' => 'Colombo - Avissawella (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Avissawella', 'type' => 'normal'],
            ['name' => 'Colombo - Balangoda', 'start_point' => 'Colombo Fort', 'end_point' => 'Balangoda', 'type' => 'normal'],
            ['name' => 'Colombo - Pelmadulla', 'start_point' => 'Colombo Fort', 'end_point' => 'Pelmadulla', 'type' => 'normal'],
            ['name' => 'Colombo - Embilipitiya', 'start_point' => 'Colombo Fort', 'end_point' => 'Embilipitiya', 'type' => 'normal'],
            
            // ========== COLOMBO TO SOUTHERN PROVINCE ==========
            ['name' => 'Colombo - Galle (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Galle', 'type' => 'normal'],
            ['name' => 'Colombo - Matara (Normal)', 'start_point' => 'Colombo Fort', 'end_point' => 'Matara', 'type' => 'normal'],
            ['name' => 'Colombo - Hambantota', 'start_point' => 'Colombo Fort', 'end_point' => 'Hambantota', 'type' => 'normal'],
            ['name' => 'Colombo - Tangalle', 'start_point' => 'Colombo Fort', 'end_point' => 'Tangalle', 'type' => 'normal'],
            ['name' => 'Colombo - Kalutara', 'start_point' => 'Colombo Fort', 'end_point' => 'Kalutara', 'type' => 'normal'],
            ['name' => 'Colombo - Beruwala', 'start_point' => 'Colombo Fort', 'end_point' => 'Beruwala', 'type' => 'normal'],
            ['name' => 'Colombo - Aluthgama', 'start_point' => 'Colombo Fort', 'end_point' => 'Aluthgama', 'type' => 'normal'],
            ['name' => 'Colombo - Bentota', 'start_point' => 'Colombo Fort', 'end_point' => 'Bentota', 'type' => 'normal'],
            ['name' => 'Colombo - Hikkaduwa', 'start_point' => 'Colombo Fort', 'end_point' => 'Hikkaduwa', 'type' => 'normal'],
            ['name' => 'Colombo - Unawatuna', 'start_point' => 'Colombo Fort', 'end_point' => 'Unawatuna', 'type' => 'normal'],
            ['name' => 'Colombo - Weligama', 'start_point' => 'Colombo Fort', 'end_point' => 'Weligama', 'type' => 'normal'],
            ['name' => 'Colombo - Mirissa', 'start_point' => 'Colombo Fort', 'end_point' => 'Mirissa', 'type' => 'normal'],
            ['name' => 'Colombo - Panadura', 'start_point' => 'Colombo Fort', 'end_point' => 'Panadura', 'type' => 'normal'],
            ['name' => 'Colombo - Moratuwa', 'start_point' => 'Colombo Fort', 'end_point' => 'Moratuwa', 'type' => 'normal'],
            ['name' => 'Colombo - Mount Lavinia', 'start_point' => 'Colombo Fort', 'end_point' => 'Mount Lavinia', 'type' => 'normal'],
            
            // ========== COLOMBO TO EASTERN PROVINCE ==========
            ['name' => 'Colombo - Trincomalee', 'start_point' => 'Colombo Fort', 'end_point' => 'Trincomalee', 'type' => 'normal'],
            ['name' => 'Colombo - Batticaloa', 'start_point' => 'Colombo Fort', 'end_point' => 'Batticaloa', 'type' => 'normal'],
            ['name' => 'Colombo - Ampara', 'start_point' => 'Colombo Fort', 'end_point' => 'Ampara', 'type' => 'normal'],
            ['name' => 'Colombo - Kalmunai', 'start_point' => 'Colombo Fort', 'end_point' => 'Kalmunai', 'type' => 'normal'],
            ['name' => 'Colombo - Akkaraipattu', 'start_point' => 'Colombo Fort', 'end_point' => 'Akkaraipattu', 'type' => 'normal'],
            
            // ========== INTERCITY ROUTES - NORTH ==========
            ['name' => 'Kandy - Anuradhapura', 'start_point' => 'Kandy', 'end_point' => 'Anuradhapura', 'type' => 'normal'],
            ['name' => 'Kandy - Jaffna', 'start_point' => 'Kandy', 'end_point' => 'Jaffna', 'type' => 'normal'],
            ['name' => 'Kandy - Vavuniya', 'start_point' => 'Kandy', 'end_point' => 'Vavuniya', 'type' => 'normal'],
            ['name' => 'Kandy - Polonnaruwa', 'start_point' => 'Kandy', 'end_point' => 'Polonnaruwa', 'type' => 'normal'],
            ['name' => 'Kurunegala - Anuradhapura', 'start_point' => 'Kurunegala', 'end_point' => 'Anuradhapura', 'type' => 'normal'],
            ['name' => 'Kurunegala - Kandy', 'start_point' => 'Kurunegala', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Kurunegala - Jaffna', 'start_point' => 'Kurunegala', 'end_point' => 'Jaffna', 'type' => 'normal'],
            ['name' => 'Anuradhapura - Jaffna', 'start_point' => 'Anuradhapura', 'end_point' => 'Jaffna', 'type' => 'normal'],
            ['name' => 'Anuradhapura - Vavuniya', 'start_point' => 'Anuradhapura', 'end_point' => 'Vavuniya', 'type' => 'normal'],
            ['name' => 'Vavuniya - Jaffna', 'start_point' => 'Vavuniya', 'end_point' => 'Jaffna', 'type' => 'normal'],
            
            // ========== INTERCITY ROUTES - CENTRAL ==========
            ['name' => 'Kandy - Nuwara Eliya (Normal)', 'start_point' => 'Kandy', 'end_point' => 'Nuwara Eliya', 'type' => 'normal'],
            ['name' => 'Kandy - Badulla', 'start_point' => 'Kandy', 'end_point' => 'Badulla', 'type' => 'normal'],
            ['name' => 'Kandy - Bandarawela', 'start_point' => 'Kandy', 'end_point' => 'Bandarawela', 'type' => 'normal'],
            ['name' => 'Kandy - Matale', 'start_point' => 'Kandy', 'end_point' => 'Matale', 'type' => 'normal'],
            ['name' => 'Kandy - Dambulla', 'start_point' => 'Kandy', 'end_point' => 'Dambulla', 'type' => 'normal'],
            ['name' => 'Kandy - Sigiriya', 'start_point' => 'Kandy', 'end_point' => 'Sigiriya', 'type' => 'normal'],
            ['name' => 'Nuwara Eliya - Badulla', 'start_point' => 'Nuwara Eliya', 'end_point' => 'Badulla', 'type' => 'normal'],
            ['name' => 'Nuwara Eliya - Bandarawela', 'start_point' => 'Nuwara Eliya', 'end_point' => 'Bandarawela', 'type' => 'normal'],
            ['name' => 'Badulla - Bandarawela', 'start_point' => 'Badulla', 'end_point' => 'Bandarawela', 'type' => 'normal'],
            ['name' => 'Badulla - Monaragala', 'start_point' => 'Badulla', 'end_point' => 'Monaragala', 'type' => 'normal'],
            
            // ========== INTERCITY ROUTES - SOUTH ==========
            ['name' => 'Galle - Matara', 'start_point' => 'Galle', 'end_point' => 'Matara', 'type' => 'normal'],
            ['name' => 'Galle - Hambantota', 'start_point' => 'Galle', 'end_point' => 'Hambantota', 'type' => 'normal'],
            ['name' => 'Galle - Tangalle', 'start_point' => 'Galle', 'end_point' => 'Tangalle', 'type' => 'normal'],
            ['name' => 'Galle - Kalutara', 'start_point' => 'Galle', 'end_point' => 'Kalutara', 'type' => 'normal'],
            ['name' => 'Matara - Hambantota', 'start_point' => 'Matara', 'end_point' => 'Hambantota', 'type' => 'normal'],
            ['name' => 'Matara - Tangalle', 'start_point' => 'Matara', 'end_point' => 'Tangalle', 'type' => 'normal'],
            ['name' => 'Hambantota - Monaragala', 'start_point' => 'Hambantota', 'end_point' => 'Monaragala', 'type' => 'normal'],
            ['name' => 'Ratnapura - Badulla', 'start_point' => 'Ratnapura', 'end_point' => 'Badulla', 'type' => 'normal'],
            ['name' => 'Ratnapura - Embilipitiya', 'start_point' => 'Ratnapura', 'end_point' => 'Embilipitiya', 'type' => 'normal'],
            ['name' => 'Ratnapura - Balangoda', 'start_point' => 'Ratnapura', 'end_point' => 'Balangoda', 'type' => 'normal'],
            
            // ========== INTERCITY ROUTES - EAST ==========
            ['name' => 'Batticaloa - Trincomalee', 'start_point' => 'Batticaloa', 'end_point' => 'Trincomalee', 'type' => 'normal'],
            ['name' => 'Batticaloa - Ampara', 'start_point' => 'Batticaloa', 'end_point' => 'Ampara', 'type' => 'normal'],
            ['name' => 'Batticaloa - Kalmunai', 'start_point' => 'Batticaloa', 'end_point' => 'Kalmunai', 'type' => 'normal'],
            ['name' => 'Trincomalee - Polonnaruwa', 'start_point' => 'Trincomalee', 'end_point' => 'Polonnaruwa', 'type' => 'normal'],
            ['name' => 'Ampara - Monaragala', 'start_point' => 'Ampara', 'end_point' => 'Monaragala', 'type' => 'normal'],
            
            // ========== COLOMBO CITY ROUTES ==========
            ['name' => 'Colombo Fort - Pettah', 'start_point' => 'Colombo Fort', 'end_point' => 'Pettah', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Dehiwala', 'start_point' => 'Colombo Fort', 'end_point' => 'Dehiwala', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Bambalapitiya', 'start_point' => 'Colombo Fort', 'end_point' => 'Bambalapitiya', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Wellawatta', 'start_point' => 'Colombo Fort', 'end_point' => 'Wellawatta', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Borella', 'start_point' => 'Colombo Fort', 'end_point' => 'Borella', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Nugegoda', 'start_point' => 'Colombo Fort', 'end_point' => 'Nugegoda', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Maharagama', 'start_point' => 'Colombo Fort', 'end_point' => 'Maharagama', 'type' => 'normal'],
            ['name' => 'Colombo Fort - Kottawa', 'start_point' => 'Colombo Fort', 'end_point' => 'Kottawa', 'type' => 'normal'],
            ['name' => 'Pettah - Mount Lavinia', 'start_point' => 'Pettah', 'end_point' => 'Mount Lavinia', 'type' => 'normal'],
            ['name' => 'Pettah - Dehiwala', 'start_point' => 'Pettah', 'end_point' => 'Dehiwala', 'type' => 'normal'],
            ['name' => 'Borella - Fort', 'start_point' => 'Borella', 'end_point' => 'Colombo Fort', 'type' => 'normal'],
            ['name' => 'Bambalapitiya - Wellawatta', 'start_point' => 'Bambalapitiya', 'end_point' => 'Wellawatta', 'type' => 'normal'],
            ['name' => 'Nugegoda - Maharagama', 'start_point' => 'Nugegoda', 'end_point' => 'Maharagama', 'type' => 'normal'],
            ['name' => 'Dehiwala - Mount Lavinia', 'start_point' => 'Dehiwala', 'end_point' => 'Mount Lavinia', 'type' => 'normal'],
            
            // ========== ADDITIONAL POPULAR ROUTES ==========
            ['name' => 'Kandy - Colombo Airport', 'start_point' => 'Kandy', 'end_point' => 'Katunayake', 'type' => 'normal'],
            ['name' => 'Galle - Colombo Airport', 'start_point' => 'Galle', 'end_point' => 'Katunayake', 'type' => 'normal'],
            ['name' => 'Kandy - Negombo', 'start_point' => 'Kandy', 'end_point' => 'Negombo', 'type' => 'normal'],
            ['name' => 'Galle - Kandy', 'start_point' => 'Galle', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Matara - Kandy', 'start_point' => 'Matara', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Jaffna - Kandy', 'start_point' => 'Jaffna', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Trincomalee - Kandy', 'start_point' => 'Trincomalee', 'end_point' => 'Kandy', 'type' => 'normal'],
            ['name' => 'Batticaloa - Kandy', 'start_point' => 'Batticaloa', 'end_point' => 'Kandy', 'type' => 'normal'],
        ];

        $inserted = 0;
        $skipped = 0;

        foreach ($routes as $routeData) {
            // Check if route already exists
            $exists = DB::table('routes')
                ->where('name', $routeData['name'])
                ->where('start_point', $routeData['start_point'])
                ->where('end_point', $routeData['end_point'])
                ->first();

            if (!$exists) {
                try {
                    DB::table('routes')->insert([
                        'route_number' => null,
                        'name' => $routeData['name'],
                        'start_point' => $routeData['start_point'],
                        'end_point' => $routeData['end_point'],
                        'start_location' => $routeData['start_point'],
                        'end_location' => $routeData['end_point'],
                        'distance' => 0,
                        'fare' => 0,
                        'metadata' => json_encode([
                            'type' => $routeData['type'],
                            'created_by' => 'system'
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $this->command->warn("Failed to insert route: {$routeData['name']} - {$e->getMessage()}");
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        $this->command->info("Sri Lankan routes seeded successfully!");
        $this->command->info("Inserted: {$inserted} routes");
        $this->command->info("Skipped (already exist): {$skipped} routes");
    }
}
