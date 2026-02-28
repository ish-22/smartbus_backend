<?php

namespace App\Http\Controllers;

use App\Models\DriverAssignment;
use App\Models\Bus;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverAssignmentController extends Controller
{
    /**
     * Assign a driver to a bus (Only Owners/Admins can do this)
     * POST /api/driver-assignments
     */
    public function assignDriverToBus(Request $request)
    {
        $user = $request->user();
        
        // Log for debugging
        \Log::info('assignDriverToBus called', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_email' => $user?->email,
            'request_data' => $request->all(),
        ]);
        
        // Check authentication
        if (!$user) {
            \Log::warning('assignDriverToBus: User not authenticated');
            return response()->json([
                'message' => 'Unauthorized. Please log in.'
            ], 401);
        }
        
        // Only owners and admins can assign drivers
        $userRole = strtolower(trim($user->role ?? ''));
        $allowedRoles = ['owner', 'admin'];
        
        if (!in_array($userRole, $allowedRoles)) {
            \Log::warning('assignDriverToBus: Invalid role', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'normalized_role' => $userRole,
                'allowed_roles' => $allowedRoles,
            ]);
            return response()->json([
                'message' => 'Unauthorized. Only bus owners and admins can assign drivers. Your role: ' . ($user->role ?? 'none'),
                'debug' => [
                    'user_role' => $user->role,
                    'normalized' => $userRole,
                    'allowed' => $allowedRoles
                ]
            ], 403);
        }
        
        \Log::info('assignDriverToBus: Role check passed, proceeding with validation', [
            'user_id' => $user->id,
            'user_role' => $userRole,
        ]);

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'bus_id' => 'required|exists:buses,id',
            'assignment_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get driver and bus
        $driver = User::findOrFail($request->driver_id);
        
        // Verify driver role
        if ($driver->role !== 'driver') {
            return response()->json([
                'message' => 'Selected user is not a driver'
            ], 422);
        }

        $bus = Bus::findOrFail($request->bus_id);
        
        // If user is owner, verify they own the bus
        if ($userRole === 'owner') {
            // If bus has no owner_id set, allow assignment (for new buses)
            if ($bus->owner_id && $bus->owner_id !== $user->id) {
                \Log::warning('assignDriverToBus: Owner trying to assign to bus they don\'t own', [
                    'user_id' => $user->id,
                    'bus_id' => $bus->id,
                    'bus_owner_id' => $bus->owner_id,
                ]);
                return response()->json([
                    'message' => 'You can only assign drivers to your own buses'
                ], 403);
            }
            // If bus has no owner, set it to this owner
            if (!$bus->owner_id) {
                $bus->owner_id = $user->id;
                $bus->save();
                \Log::info('assignDriverToBus: Set bus owner_id', [
                    'bus_id' => $bus->id,
                    'owner_id' => $user->id,
                ]);
            }
        }

        // Verify driver type matches bus type (if bus type is set)
        if ($bus->type && $driver->driver_type && $bus->type !== $driver->driver_type) {
            return response()->json([
                'message' => 'Driver type (' . $driver->driver_type . ') does not match bus type (' . $bus->type . ')'
            ], 422);
        }

        // Use assignment_date or default to today
        $assignmentDate = $request->assignment_date ? date('Y-m-d', strtotime($request->assignment_date)) : today()->toDateString();

        DB::beginTransaction();
        try {
            // Check if driver already has an active assignment for this date
            $existingAssignment = DriverAssignment::where('driver_id', $driver->id)
                ->whereDate('assignment_date', $assignmentDate)
                ->whereNull('ended_at')
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'message' => 'Driver already has an active assignment for ' . $assignmentDate . '. Please end the current assignment first.'
                ], 422);
            }

            // Check if bus already has an active assignment for this date
            $busAssignment = DriverAssignment::where('bus_id', $bus->id)
                ->whereDate('assignment_date', $assignmentDate)
                ->whereNull('ended_at')
                ->first();

            if ($busAssignment) {
                return response()->json([
                    'message' => 'Bus is already assigned to another driver for ' . $assignmentDate
                ], 422);
            }

            // Create new assignment
            $assignment = DriverAssignment::create([
                'driver_id' => $driver->id,
                'bus_id' => $bus->id,
                'driver_type' => $driver->driver_type ?? $bus->type ?? 'normal',
                'assigned_at' => now(),
                'assignment_date' => $assignmentDate,
                'assigned_by' => $user->id,
            ]);

            // Update bus driver_id for backward compatibility
            $bus->update(['driver_id' => $driver->id]);

            // Reload bus with relationships for notification
            $bus->refresh();
            $bus->load('route');
            
            // Build route description for notification
            $routeDescription = '';
            if ($bus->route) {
                if ($bus->route->name) {
                    $routeDescription = ' on route: ' . $bus->route->name;
                } elseif ($bus->route->start_point || $bus->route->end_point) {
                    $routeDescription = ' on route: ' . ($bus->route->start_point ?? 'Start') . ' - ' . ($bus->route->end_point ?? 'End');
                }
            }
            
            // Format assignment date
            $formattedDate = date('F j, Y', strtotime($assignmentDate));
            $isToday = $assignmentDate === today()->toDateString();
            
            // Create notification for the driver
            Notification::create([
                'user_id' => $driver->id,
                'title'   => 'New Bus Assignment',
                'message' => sprintf(
                    'You have been assigned to drive Bus %s%s for %s. %s',
                    $bus->bus_number,
                    $routeDescription,
                    $formattedDate,
                    $isToday ? 'Please check your dashboard for complete bus and route details.' : 'This assignment is scheduled for this date.'
                ),
                'type'    => 'success',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Driver assigned to bus successfully',
                'assignment' => $assignment->load(['bus.route', 'driver', 'assignedBy'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to assign driver to bus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current assignment for a driver (Driver can view their assignment)
     * GET /api/drivers/{driverId}/current-assignment
     */
    public function getCurrentAssignment(Request $request, $driverId)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Driver can only view their own assignment
        if ($user->role === 'driver' && $user->id != $driverId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Owners can only view assignments for their own buses
        if ($user->role === 'owner') {
            // Will check after fetching assignment
        } elseif ($user->role === 'admin') {
            // Admins can view any assignment
        } elseif ($user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = DriverAssignment::where('driver_id', $driverId)
            ->whereDate('assignment_date', today())
            ->whereNull('ended_at')
            ->with(['bus.route', 'bus.owner', 'driver', 'assignedBy']);
        
        // If owner, only show assignments for their buses
        if ($user->role === 'owner') {
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('bus_id', $ownerBusIds);
        }
        
        $assignment = $query->latest('assigned_at')->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'No active assignment found for today',
                'assignment' => null
            ]);
        }
        
        // Additional check for owners - ensure bus belongs to them
        if ($user->role === 'owner' && $assignment->bus->owner_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only view assignments for your own buses.'
            ], 403);
        }

        return response()->json([
            'assignment' => $assignment,
            'bus_details' => [
                'id' => $assignment->bus->id,
                'bus_number' => $assignment->bus->bus_number,
                'model' => $assignment->bus->model,
                'capacity' => $assignment->bus->capacity,
                'type' => $assignment->bus->type,
                'status' => $assignment->bus->status,
                'route' => $assignment->bus->route,
                'owner' => $assignment->bus->owner ? [
                    'id' => $assignment->bus->owner->id,
                    'name' => $assignment->bus->owner->name,
                    'phone' => $assignment->bus->owner->phone,
                ] : null,
            ]
        ]);
    }

    /**
     * End current assignment (Owner/Admin or Driver can end)
     * POST /api/drivers/{driverId}/assignments/{assignmentId}/end
     */
    public function endAssignment(Request $request, $driverId, $assignmentId)
    {
        $user = $request->user();
        
        $assignment = DriverAssignment::where('id', $assignmentId)
            ->where('driver_id', $driverId)
            ->with('bus')
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // Driver can only end their own assignment
        if ($user->role === 'driver' && $user->id != $driverId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Owner can only end assignments for their own buses
        if ($user->role === 'owner' && $assignment->bus->owner_id !== $user->id) {
            return response()->json([
                'message' => 'You can only end assignments for your own buses'
            ], 403);
        }

        if ($assignment->ended_at) {
            return response()->json(['message' => 'Assignment already ended'], 422);
        }

        $assignment->update(['ended_at' => now()]);

        // Create notification
        Notification::create([
            'user_id' => $driverId,
            'title'   => 'Assignment Ended',
            'message' => sprintf('Your assignment to bus %s has been ended.', $assignment->bus->bus_number),
            'type'    => 'info',
        ]);

        return response()->json([
            'message' => 'Assignment ended successfully',
            'assignment' => $assignment->load(['bus', 'driver'])
        ]);
    }

    /**
     * Get assignment history for a driver (Owner/Admin/Driver can view)
     * GET /api/drivers/{driverId}/assignments
     */
    public function getAssignmentHistory(Request $request, $driverId)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Driver can only view their own history
        if ($user->role === 'driver' && $user->id != $driverId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = DriverAssignment::where('driver_id', $driverId)
            ->with(['bus.route', 'bus.owner', 'driver', 'assignedBy']);
        
        // If owner, only show assignments for their buses
        if ($user->role === 'owner') {
            $ownerBusIds = Bus::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('bus_id', $ownerBusIds);
        }
        
        $assignments = $query->orderBy('assignment_date', 'desc')
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        return response()->json($assignments);
    }

    /**
     * Get all assignments for owner's buses (Owner only)
     * GET /api/owner/driver-assignments
     */
    public function getOwnerAssignments(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        if ($user->role !== 'owner') {
            return response()->json([
                'message' => 'Unauthorized. Only bus owners can access this endpoint.'
            ], 403);
        }

        // Only get assignments for buses owned by this owner
        $busIds = Bus::where('owner_id', $user->id)->pluck('id');
        
        if ($busIds->isEmpty()) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'total' => 0,
                'message' => 'No buses found for this owner'
            ]);
        }
        
        $assignments = DriverAssignment::whereIn('bus_id', $busIds)
            ->with(['bus.route', 'bus.owner', 'driver', 'assignedBy'])
            ->orderBy('assignment_date', 'desc')
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        return response()->json($assignments);
    }
}
