<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::with(['route', 'driver'])->get();
        return response()->json($buses);
    }

    public function show($id)
    {
        $bus = Bus::with(['route', 'driver'])->findOrFail($id);
        return response()->json($bus);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:50',
            'type' => 'required|in:expressway,normal',
            'route_id' => 'nullable|exists:routes,id',
            'capacity' => 'integer|min:1',
            'driver_id' => 'nullable|exists:users,id'
        ]);

        $bus = Bus::create($data);
        return response()->json($bus->load(['route', 'driver']), 201);
    }
}