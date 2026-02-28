<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Get all drivers (for owners/admins to assign)
     * GET /api/users/drivers
     */
    public function getDrivers(Request $request)
    {
        $user = $request->user();
        
        // Only owners and admins can view drivers
        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $drivers = User::where('role', 'driver')
            ->select('id', 'name', 'email', 'phone', 'driver_type', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($drivers);
    }
}
