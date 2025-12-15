<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;

class BusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bus::with(['route:id,name,start_point,end_point', 'driver:id,name,email,phone']);
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $buses = $query->get()->map(function ($bus) {
            return [
                'id' => $bus->id,
                'number' => $bus->bus_number,
                'type' => $bus->type ?? 'normal',
                'route_id' => $bus->route_id,
                'capacity' => $bus->capacity,
                'driver_id' => $bus->driver_id,
                'route' => $bus->route,
                'driver' => $bus->driver,
            ];
        });
        
        return response()->json($buses);
    }

    public function show($id)
    {
        $bus = Bus::with(['route:id,name,start_point,end_point', 'driver:id,name,email,phone'])->findOrFail($id);
        
        return response()->json([
            'id' => $bus->id,
            'number' => $bus->bus_number,
            'type' => $bus->type ?? 'normal',
            'route_id' => $bus->route_id,
            'capacity' => $bus->capacity,
            'driver_id' => $bus->driver_id,
            'route' => $bus->route,
            'driver' => $bus->driver,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:50',
            'type' => 'required|in:expressway,normal',
            'route_id' => 'nullable|exists:routes,id',
            'capacity' => 'required|integer|min:1',
            'driver_id' => 'nullable|exists:users,id',
            'model' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,maintenance,inactive',
        ]);

        // Map 'number' to 'bus_number' for database
        $busData = [
            'bus_number' => $data['number'],
            'type' => $data['type'],
            'route_id' => $data['route_id'] ?? null,
            'capacity' => $data['capacity'],
            'driver_id' => $data['driver_id'] ?? null,
            'model' => $data['model'] ?? 'Unknown',
            'status' => $data['status'] ?? 'active',
        ];

        $bus = Bus::create($busData);
        
        $bus->load(['route', 'driver']);
        
        return response()->json([
            'id' => $bus->id,
            'number' => $bus->bus_number,
            'type' => $bus->type ?? 'normal',
            'route_id' => $bus->route_id,
            'capacity' => $bus->capacity,
            'driver_id' => $bus->driver_id,
            'route' => $bus->route,
            'driver' => $bus->driver,
        ], 201);
    }
}