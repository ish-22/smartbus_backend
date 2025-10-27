<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;

class BusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bus::query();
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }
        if ($request->has('route_id')) {
            $query->where('route_id', $request->query('route_id'));
        }
        return response()->json($query->get());
    }

    public function show($id)
    {
        $bus = Bus::with('route','driver')->findOrFail($id);
        return response()->json($bus);
    }
}
