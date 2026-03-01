<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\DriverAssignment;
use Illuminate\Http\Request;

class BusSearchController extends Controller
{
    public function searchBuses(Request $request)
    {
        $query = Bus::with(['route', 'driver'])
            ->where('status', 'active');

        if ($request->has('from')) {
            $query->whereHas('route', function($q) use ($request) {
                $q->where('start_point', 'like', '%' . $request->from . '%');
            });
        }

        if ($request->has('to')) {
            $query->whereHas('route', function($q) use ($request) {
                $q->where('end_point', 'like', '%' . $request->to . '%');
            });
        }

        $buses = $query->get()->flatMap(function($bus) {
            // Generate multiple trips for each bus (e.g., 3 trips per day)
            $trips = [];
            $tripTimes = ['08:00 AM', '12:00 PM', '04:00 PM'];
            
            for ($tripNumber = 1; $tripNumber <= 3; $tripNumber++) {
                $trips[] = [
                    'id' => $bus->id,
                    'trip_number' => $tripNumber,
                    'name' => $bus->bus_number . ' - ' . ($bus->type ?? 'Express') . ' (Trip ' . $tripNumber . ')',
                    'route' => $bus->route ? $bus->route->route_number : 'N/A',
                    'from' => $bus->route ? $bus->route->start_point : 'N/A',
                    'to' => $bus->route ? $bus->route->end_point : 'N/A',
                    'time' => $tripTimes[$tripNumber - 1],
                    'duration' => $bus->route && $bus->route->metadata && isset($bus->route->metadata['duration']) 
                        ? $bus->route->metadata['duration'] 
                        : '3h 00m',
                    'price' => $bus->route ? $bus->route->fare : 0,
                ];
            }
            
            return $trips;
        });

        return response()->json($buses);
    }
}
