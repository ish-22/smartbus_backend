<?php

namespace App\Http\Controllers;

use App\Models\DriverAssignment;
use App\Models\Bus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverAssignmentController extends Controller
{
    /**
     * Assign a driver to a bus
     */
    public function assignBus(Request $request, $driverId)
    {
        // Verify the authenticated user is the driver
        if ($request->user()->id != $driverId || $request->user()->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'bus_id' => 'required|exists:buses,id',
            'driver_type' => 'required|in:expressway,normal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if bus exists and matches driver type
        $bus = Bus::find($request->bus_id);
        if (!$bus) {
            return response()->json(['message' => 'Bus not found'], 404);
        }

        // Get driver's registered driver type
        $driver = User::find($driverId);
        $registeredDriverType = $driver->driver_type ?? null;
        
        // If driver has a registered type, validate it matches
        if ($registeredDriverType && $registeredDriverType !== $request->driver_type) {
            return response()->json([
                'message' => 'Selected driver type does not match your registered driver type. You are registered as a ' . $registeredDriverType . ' driver.'
            ], 422);
        }

        // Verify bus type matches driver type (if bus has type field)
        if (isset($bus->type) && $bus->type !== $request->driver_type) {
            return response()->json([
                'message' => 'Bus type does not match selected driver type'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // End any active assignments for this driver today
            DriverAssignment::where('driver_id', $driverId)
                ->whereDate('assigned_at', today())
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Create new assignment
            $assignment = DriverAssignment::create([
                'driver_id' => $driverId,
                'bus_id' => $request->bus_id,
                'driver_type' => $request->driver_type,
                'assigned_at' => now(),
            ]);

            // Update bus driver_id (if needed for backward compatibility)
            $bus->update(['driver_id' => $driverId]);

            DB::commit();

            return response()->json([
                'message' => 'Bus assigned successfully',
                'assignment' => $assignment->load(['bus', 'driver'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to assign bus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current assignment for a driver
     */
    public function getCurrentAssignment(Request $request, $driverId)
    {
        // Verify the authenticated user is the driver
        if ($request->user()->id != $driverId || $request->user()->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignment = DriverAssignment::where('driver_id', $driverId)
            ->whereDate('assigned_at', today())
            ->whereNull('ended_at')
            ->with(['bus', 'driver'])
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'No active assignment found'], 404);
        }

        return response()->json($assignment);
    }

    /**
     * End current assignment
     */
    public function endAssignment(Request $request, $driverId, $assignmentId)
    {
        // Verify the authenticated user is the driver
        if ($request->user()->id != $driverId || $request->user()->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignment = DriverAssignment::where('id', $assignmentId)
            ->where('driver_id', $driverId)
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        if ($assignment->ended_at) {
            return response()->json(['message' => 'Assignment already ended'], 422);
        }

        $assignment->update(['ended_at' => now()]);

        return response()->json([
            'message' => 'Assignment ended successfully',
            'assignment' => $assignment
        ]);
    }

    /**
     * Get assignment history for a driver
     */
    public function getAssignmentHistory(Request $request, $driverId)
    {
        // Verify the authenticated user is the driver
        if ($request->user()->id != $driverId || $request->user()->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignments = DriverAssignment::where('driver_id', $driverId)
            ->with(['bus', 'driver'])
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        return response()->json($assignments);
    }
}

