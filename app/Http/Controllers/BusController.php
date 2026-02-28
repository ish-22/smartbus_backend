<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;

class BusController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Bus::with(['route:id,name,route_number,start_point,end_point', 'driver:id,name,email,phone', 'owner:id,name']);
        
        // If owner is authenticated, ONLY show their buses - strict filtering
        if ($user && strtolower(trim($user->role ?? '')) === 'owner') {
            $query->where('owner_id', $user->id);
            \Log::info('BusController::index - Filtering by owner_id', [
                'owner_id' => $user->id,
                'owner_email' => $user->email,
                'user_role' => $user->role,
            ]);
        }
        // For all other cases (admin, driver, passenger, or no auth), show all buses
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $buses = $query->get()->map(function ($bus) {
            return [
                'id' => $bus->id,
                'number' => $bus->bus_number,
                'bus_number' => $bus->bus_number,
                'type' => $bus->type ?? 'normal',
                'route_id' => $bus->route_id,
                'capacity' => $bus->capacity,
                'driver_id' => $bus->driver_id,
                'owner_id' => $bus->owner_id,
                'model' => $bus->model,
                'status' => $bus->status ?? 'active',
                'route' => $bus->route ? (is_array($bus->route) ? $bus->route : [
                    'id' => $bus->route->id,
                    'name' => $bus->route->name,
                    'route_number' => $bus->route->route_number ?? null,
                    'start_point' => $bus->route->start_point ?? null,
                    'end_point' => $bus->route->end_point ?? null,
                ]) : null,
                'driver' => $bus->driver,
                'owner' => $bus->owner,
                'created_at' => $bus->created_at?->toDateTimeString(),
            ];
        });
        
        return response()->json($buses);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $query = Bus::with(['route:id,name,route_number,start_point,end_point', 'driver:id,name,email,phone', 'owner:id,name']);
        
        // If owner is authenticated, only allow viewing their own buses
        if ($user && strtolower(trim($user->role ?? '')) === 'owner') {
            $query->where('owner_id', $user->id);
            \Log::info('BusController::show - Filtering by owner_id', [
                'owner_id' => $user->id,
                'bus_id' => $id,
            ]);
        }
        
        $bus = $query->findOrFail($id);
        
        // Double check ownership for owners
        if ($user && strtolower(trim($user->role ?? '')) === 'owner') {
            if ($bus->owner_id !== $user->id) {
                \Log::warning('BusController::show - Owner trying to view other owner\'s bus', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'bus_id' => $bus->id,
                    'bus_owner_id' => $bus->owner_id,
                ]);
                return response()->json([
                    'message' => 'Unauthorized. You can only view your own buses.'
                ], 403);
            }
        }
        
        return response()->json([
            'id' => $bus->id,
            'number' => $bus->bus_number,
            'bus_number' => $bus->bus_number,
            'type' => $bus->type ?? 'normal',
            'route_id' => $bus->route_id,
            'capacity' => $bus->capacity,
            'driver_id' => $bus->driver_id,
            'owner_id' => $bus->owner_id,
            'model' => $bus->model,
            'status' => $bus->status ?? 'active',
            'route' => $bus->route ? [
                'id' => $bus->route->id,
                'name' => $bus->route->name,
                'route_number' => $bus->route->route_number ?? null,
                'start_point' => $bus->route->start_point ?? null,
                'end_point' => $bus->route->end_point ?? null,
            ] : null,
            'driver' => $bus->driver,
            'owner' => $bus->owner,
            'created_at' => $bus->created_at?->toDateTimeString(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Only owners and admins can create buses
        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Only bus owners and admins can register buses.'
            ], 403);
        }

        $data = $request->validate([
            'number' => 'required|string|max:50',
            'type' => 'required|in:expressway,normal',
            'route_id' => 'nullable|integer|exists:routes,id',
            'capacity' => 'required|integer|min:1',
            'model' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,maintenance,inactive',
        ]);

        // Map 'number' to 'bus_number' for database
        $busData = [
            'bus_number' => $data['number'],
            'type' => $data['type'],
            'capacity' => $data['capacity'],
            'model' => $data['model'] ?? null,
            'status' => $data['status'] ?? 'active',
            'owner_id' => $user->role === 'owner' ? $user->id : ($request->owner_id ?? null),
        ];

        // Only set route_id if provided and valid
        if (isset($data['route_id']) && $data['route_id'] > 0) {
            $busData['route_id'] = $data['route_id'];
        }

        try {
            $bus = Bus::create($busData);
            
            $bus->load(['route:id,name,route_number,start_point,end_point', 'driver:id,name,email,phone', 'owner:id,name']);
            
            return response()->json([
            'id' => $bus->id,
            'number' => $bus->bus_number,
            'bus_number' => $bus->bus_number,
            'type' => $bus->type ?? 'normal',
            'route_id' => $bus->route_id,
            'capacity' => $bus->capacity,
            'driver_id' => $bus->driver_id,
            'owner_id' => $bus->owner_id,
            'model' => $bus->model,
            'status' => $bus->status ?? 'active',
            'route' => $bus->route ? [
                'id' => $bus->route->id,
                'name' => $bus->route->name,
                'route_number' => $bus->route->route_number ?? null,
                'start_point' => $bus->route->start_point ?? null,
                'end_point' => $bus->route->end_point ?? null,
            ] : null,
            'driver' => $bus->driver,
            'owner' => $bus->owner ? [
                'id' => $bus->owner->id,
                'name' => $bus->owner->name,
            ] : null,
            'created_at' => $bus->created_at?->toDateTimeString(),
        ], 201);
        } catch (\Exception $e) {
            \Log::error('Bus creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create bus: ' . $e->getMessage()
            ], 500);
        }
    }
}