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

        try {
            $drivers = User::where('role', 'driver')
                ->select('id', 'name', 'email', 'phone', 'driver_type', 'created_at')
                ->orderBy('name')
                ->get();

            return response()->json($drivers);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching drivers', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all owners (for admins)
     * GET /api/users/owners
     */
    public function getOwners(Request $request)
    {
        $user = $request->user();
        
        // Only admins can view owners
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $owners = User::where('role', 'owner')
            ->select('id', 'name', 'email', 'phone', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($owners);
    }

    /**
     * Get all passengers (for admins)
     * GET /api/users/passengers
     */
    public function getPassengers(Request $request)
    {
        $user = $request->user();
        
        // Only admins can view passengers
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $passengers = User::where('role', 'passenger')
            ->select('id', 'name', 'email', 'phone', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($passengers);
    }

    /**
     * Get owner statistics (for admins)
     * GET /api/users/owner-stats
     */
    public function getOwnerStats(Request $request)
    {
        $user = $request->user();
        
        // Only admins can view owner stats
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $totalOwners = User::where('role', 'owner')->count();
            $totalBuses = \App\Models\Bus::count();
            $activeBuses = \App\Models\Bus::where('status', 'active')->count();
            $pendingBuses = \App\Models\Bus::where('status', 'pending')->count();

            $stats = [
                'total_owners' => $totalOwners,
                'total_buses' => $totalBuses,
                'active_buses' => $activeBuses,
                'pending_buses' => $pendingBuses,
                'pending_transfers' => 0,
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching owner stats', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAdmins(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'role', 'status', 'last_login', 'permissions', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($admins);
    }

    public function updateAdmin(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'status' => 'sometimes|in:active,inactive',
            'permissions' => 'sometimes|array',
        ]);

        $admin = User::findOrFail($id);
        $admin->update($request->only(['name', 'email', 'status', 'permissions']));

        return response()->json($admin);
    }

    public function createAdmin(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'permissions' => 'sometimes|array',
        ]);

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
            'status' => 'active',
            'permissions' => $request->permissions ?? ['All Access'],
        ]);

        return response()->json($admin, 201);
    }

    public function deleteAdmin(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->id == $id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        $admin = User::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
