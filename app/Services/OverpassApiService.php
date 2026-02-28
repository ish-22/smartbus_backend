<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OverpassApiService
{
    private const OVERPASS_API_URL = 'https://overpass-api.de/api/interpreter';
    
    /**
     * Fetch bus routes from OpenStreetMap for Sri Lanka
     * 
     * @return array Array of bus routes with route information
     */
    public function fetchBusRoutes(): array
    {
        $query = $this->buildOverpassQuery();
        
        try {
            // Overpass API expects form-encoded data
            $response = Http::timeout(60)->asForm()->post(self::OVERPASS_API_URL, [
                'data' => $query
            ]);
            
            if (!$response->successful()) {
                Log::error('Overpass API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }
            
            $data = $response->json();
            
            if (!isset($data['elements']) || empty($data['elements'])) {
                Log::info('No bus routes found in OpenStreetMap for Sri Lanka');
                return [];
            }
            
            return $this->parseRoutes($data['elements']);
            
        } catch (\Exception $e) {
            Log::error('Error fetching bus routes from Overpass API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Build Overpass QL query to fetch bus routes in Sri Lanka
     * Uses bounding box for Sri Lanka: lat 5.916666 to 9.833333, lon 79.694722 to 81.879722
     * 
     * @return string Overpass QL query string
     */
    private function buildOverpassQuery(): string
    {
        // Sri Lanka bounding box (approximate)
        // Latitude: 5.916666 to 9.833333
        // Longitude: 79.694722 to 81.879722
        $bbox = "5.916666,79.694722,9.833333,81.879722";
        
        // Query to fetch bus routes in Sri Lanka
        // This searches for relations and ways tagged with route=bus
        return "
[out:json][timeout:60];
(
  // Fetch bus routes (relations) in Sri Lanka
  relation[\"route\"=\"bus\"]({$bbox});
  // Also fetch ways tagged as bus routes
  way[\"route\"=\"bus\"]({$bbox});
);
out body;
>;
out skel qt;
";
    }
    
    /**
     * Parse Overpass API response into structured route data
     * 
     * @param array $elements Raw elements from Overpass API
     * @return array Parsed routes
     */
    private function parseRoutes(array $elements): array
    {
        $routes = [];
        $processedRefs = []; // Track processed route numbers to avoid duplicates
        
        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            
            // Skip if not a bus route
            if (!isset($tags['route']) || $tags['route'] !== 'bus') {
                continue;
            }
            
            // Extract route number (ref, route_ref, or name)
            $routeNumber = $this->extractRouteNumber($tags);
            
            // Skip if no route number or already processed
            if (empty($routeNumber) || isset($processedRefs[$routeNumber])) {
                continue;
            }
            
            // Extract route information
            $routeName = $this->extractRouteName($tags);
            $startPoint = $this->extractStartPoint($tags);
            $endPoint = $this->extractEndPoint($tags);
            $routeType = $this->determineRouteType($tags, $routeNumber);
            
            // Extract coordinates if available
            $coordinates = $this->extractCoordinates($element);
            
            // Extract stops if available
            $stops = $this->extractStops($element, $tags);
            
            $routes[] = [
                'route_number' => $routeNumber,
                'name' => $routeName,
                'start_point' => $startPoint,
                'end_point' => $endPoint,
                'type' => $routeType,
                'coordinates' => $coordinates,
                'stops' => $stops,
                'tags' => $tags, // Keep original tags for reference
            ];
            
            $processedRefs[$routeNumber] = true;
        }
        
        return $routes;
    }
    
    /**
     * Extract route number from tags
     * Priority: ref > route_ref > name (if it looks like a number)
     * 
     * @param array $tags OSM tags
     * @return string|null Route number
     */
    private function extractRouteNumber(array $tags): ?string
    {
        // Priority order for route number
        $routeNumberFields = ['ref', 'route_ref', 'network:ref', 'local_ref'];
        
        foreach ($routeNumberFields as $field) {
            if (isset($tags[$field]) && !empty($tags[$field])) {
                return trim($tags[$field]);
            }
        }
        
        // Try to extract from name if it starts with a number
        if (isset($tags['name'])) {
            $name = $tags['name'];
            // Pattern: number at start (e.g., "100 Colombo - Kandy" -> "100")
            if (preg_match('/^([0-9]+[A-Z]?[0-9]*-?[0-9]*)\s/', $name, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Extract route name from tags
     * 
     * @param array $tags OSM tags
     * @return string Route name
     */
    private function extractRouteName(array $tags): string
    {
        $nameFields = ['name', 'name:en', 'from', 'to'];
        
        foreach ($nameFields as $field) {
            if (isset($tags[$field]) && !empty($tags[$field])) {
                $name = trim($tags[$field]);
                // If it's just "from - to", construct name
                if ($field === 'from' && isset($tags['to'])) {
                    return $name . ' - ' . $tags['to'];
                }
                return $name;
            }
        }
        
        // Construct name from from/to
        if (isset($tags['from']) && isset($tags['to'])) {
            return $tags['from'] . ' - ' . $tags['to'];
        }
        
        return 'Bus Route';
    }
    
    /**
     * Extract start point from tags
     * 
     * @param array $tags OSM tags
     * @return string|null Start point
     */
    private function extractStartPoint(array $tags): ?string
    {
        $startFields = ['from', 'start', 'origin'];
        
        foreach ($startFields as $field) {
            if (isset($tags[$field]) && !empty($tags[$field])) {
                return trim($tags[$field]);
            }
        }
        
        return null;
    }
    
    /**
     * Extract end point from tags
     * 
     * @param array $tags OSM tags
     * @return string|null End point
     */
    private function extractEndPoint(array $tags): ?string
    {
        $endFields = ['to', 'end', 'destination'];
        
        foreach ($endFields as $field) {
            if (isset($tags[$field]) && !empty($tags[$field])) {
                return trim($tags[$field]);
            }
        }
        
        return null;
    }
    
    /**
     * Determine route type (expressway or normal)
     * 
     * @param array $tags OSM tags
     * @param string $routeNumber Route number
     * @return string 'expressway' or 'normal'
     */
    private function determineRouteType(array $tags, string $routeNumber): string
    {
        // Check tags for expressway indicators
        $expresswayIndicators = ['expressway', 'highway', 'E', 'H'];
        
        foreach ($expresswayIndicators as $indicator) {
            if (stripos($routeNumber, $indicator) !== false) {
                return 'expressway';
            }
            if (isset($tags['network']) && stripos($tags['network'], $indicator) !== false) {
                return 'expressway';
            }
        }
        
        return 'normal';
    }
    
    /**
     * Extract coordinates from element
     * 
     * @param array $element OSM element
     * @return array|null Coordinates [lat, lon] or null
     */
    private function extractCoordinates(array $element): ?array
    {
        if (isset($element['lat']) && isset($element['lon'])) {
            return [
                'lat' => $element['lat'],
                'lon' => $element['lon']
            ];
        }
        
        if (isset($element['members']) && is_array($element['members'])) {
            // For relations, get first member's coordinates
            foreach ($element['members'] as $member) {
                if (isset($member['lat']) && isset($member['lon'])) {
                    return [
                        'lat' => $member['lat'],
                        'lon' => $member['lon']
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract bus stops from element
     * 
     * @param array $element OSM element
     * @param array $tags OSM tags
     * @return array Array of stops
     */
    private function extractStops(array $element, array $tags): array
    {
        $stops = [];
        
        // Check if there are stop members in relation
        if (isset($element['members']) && is_array($element['members'])) {
            foreach ($element['members'] as $member) {
                if (isset($member['role']) && $member['role'] === 'platform' || $member['role'] === 'stop') {
                    $memberTags = $member['tags'] ?? [];
                    $stopName = $memberTags['name'] ?? ($memberTags['ref'] ?? null);
                    
                    if ($stopName) {
                        $stops[] = [
                            'name' => $stopName,
                            'lat' => $member['lat'] ?? null,
                            'lon' => $member['lon'] ?? null,
                        ];
                    }
                }
            }
        }
        
        return $stops;
    }
}

