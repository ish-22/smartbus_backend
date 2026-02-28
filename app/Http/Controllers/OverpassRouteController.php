<?php

namespace App\Http\Controllers;

use App\Services\OverpassApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OverpassRouteController extends Controller
{
    private OverpassApiService $overpassService;
    
    public function __construct(OverpassApiService $overpassService)
    {
        $this->overpassService = $overpassService;
    }
    
    /**
     * Fetch routes from OpenStreetMap Overpass API
     * GET /api/routes/overpass/fetch
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRoutes()
    {
        try {
            $routes = $this->overpassService->fetchBusRoutes();
            
            if (empty($routes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bus routes found in OpenStreetMap for Sri Lanka',
                    'routes' => [],
                    'count' => 0
                ], 200);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched bus routes from OpenStreetMap',
                'routes' => $routes,
                'count' => count($routes)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error in OverpassRouteController::fetchRoutes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch routes from OpenStreetMap',
                'error' => $e->getMessage(),
                'routes' => []
            ], 500);
        }
    }
    
    /**
     * Test Overpass API connection
     * GET /api/routes/overpass/test
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->post('https://overpass-api.de/api/interpreter', [
                'data' => '[out:json][timeout:25];relation["ISO3166-1"="LK"]["admin_level"="2"];out geom;'
            ]);
            
            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() ? 'Overpass API is accessible' : 'Overpass API request failed',
                'data_available' => $response->successful()
            ], $response->successful() ? 200 : 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Overpass API',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

