<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Route::query();
            
            // Filter by type if provided (expressway or normal)
            if ($request->has('type') && $request->type) {
                $type = $request->type;
                $query->where(function($q) use ($type) {
                    $q->whereJsonContains('metadata->type', $type)
                      ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.type")) = ?', [$type])
                      ->orWhereRaw('JSON_EXTRACT(metadata, "$.type") = ?', [json_encode($type)]);
                });
            }
            
            // Filter by start_point if provided
            if ($request->has('start_point') && $request->start_point) {
                $query->where('start_point', 'like', '%' . $request->start_point . '%');
            }
            
            // Filter by end_point if provided
            if ($request->has('end_point') && $request->end_point) {
                $query->where('end_point', 'like', '%' . $request->end_point . '%');
            }
            
            $routes = $query->orderBy('name')->get();
            
            return response()->json($routes);
        } catch (\Exception $e) {
            \Log::error('Error fetching routes: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch routes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $route = Route::with(['stops', 'buses'])->findOrFail($id);
        return response()->json($route);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'route_number' => 'nullable|string|max:191',
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'metadata' => 'nullable|array'
        ]);

        $route = Route::create($data);
        return response()->json($route, 201);
    }

    public function update(Request $request, $id)
    {
        $route = Route::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'sometimes|string|max:191',
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'metadata' => 'nullable|array'
        ]);

        $route->update($data);
        return response()->json($route);
    }

    public function destroy($id)
    {
        $route = Route::findOrFail($id);
        $route->delete();
        return response()->json(['message' => 'Route deleted successfully']);
    }
}