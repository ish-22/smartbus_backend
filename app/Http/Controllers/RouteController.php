<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with('stops')->get();
        return response()->json($routes);
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
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'metadata' => 'nullable|array'
        ]);

        $route = Route::create($data);
        return response()->json($route, 201);
    }
}