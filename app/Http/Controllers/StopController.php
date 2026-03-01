<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stop;

class StopController extends Controller
{
    public function index()
    {
        $stops = Stop::with('route')->get();
        return response()->json($stops);
    }

    public function byRoute($routeId)
    {
        $stops = Stop::where('route_id', $routeId)->orderBy('sequence')->get();
        return response()->json($stops);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'name' => 'required|string|max:191',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'sequence' => 'integer|min:0'
        ]);

        $stop = Stop::create($data);
        return response()->json($stop->load('route'), 201);
    }

    public function update(Request $request, $id)
    {
        $stop = Stop::findOrFail($id);
        
        $data = $request->validate([
            'route_id' => 'sometimes|exists:routes,id',
            'name' => 'sometimes|string|max:191',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'sequence' => 'sometimes|integer|min:0'
        ]);

        $stop->update($data);
        return response()->json($stop->load('route'));
    }

    public function destroy($id)
    {
        $stop = Stop::findOrFail($id);
        $stop->delete();
        return response()->json(['message' => 'Stop deleted successfully']);
    }
}