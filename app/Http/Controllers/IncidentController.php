<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    /**
     * Get incidents for the authenticated driver
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Incident::where('driver_id', $user->id)
            ->with(['bus:id,bus_number', 'driver:id,name,email,phone', 'resolver:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity if provided
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidents = $query->paginate($request->get('per_page', 20));

        return response()->json($incidents);
    }

    /**
     * Get all incidents (admin only)
     */
    public function getAll(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Incident::with([
            'driver:id,name,email,phone',
            'bus:id,bus_number',
            'resolver:id,name'
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by driver
        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        $incidents = $query->paginate($request->get('per_page', 20));

        return response()->json($incidents);
    }

    /**
     * Get incident statistics (admin only)
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total' => Incident::count(),
            'by_status' => [
                'reported' => Incident::where('status', 'reported')->count(),
                'in_progress' => Incident::where('status', 'in_progress')->count(),
                'resolved' => Incident::where('status', 'resolved')->count(),
                'closed' => Incident::where('status', 'closed')->count(),
            ],
            'by_severity' => [
                'low' => Incident::where('severity', 'low')->count(),
                'medium' => Incident::where('severity', 'medium')->count(),
                'high' => Incident::where('severity', 'high')->count(),
                'critical' => Incident::where('severity', 'critical')->count(),
            ],
            'by_type' => [
                'traffic_delay' => Incident::where('type', 'traffic_delay')->count(),
                'mechanical_issue' => Incident::where('type', 'mechanical_issue')->count(),
                'accident' => Incident::where('type', 'accident')->count(),
                'emergency' => Incident::where('type', 'emergency')->count(),
                'other' => Incident::where('type', 'other')->count(),
            ],
            'unresolved' => Incident::whereIn('status', ['reported', 'in_progress'])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Store a new incident report
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:traffic_delay,mechanical_issue,accident,emergency,other',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string|min:10',
            'location' => 'nullable|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'bus_id' => 'nullable|exists:buses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $incident = Incident::create([
            'driver_id' => $user->id,
            'bus_id' => $request->bus_id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'severity' => $request->severity,
            'status' => 'reported',
        ]);

        $incident->load(['bus:id,bus_number', 'driver:id,name,email,phone']);

        return response()->json([
            'message' => 'Incident reported successfully',
            'incident' => $incident
        ], 201);
    }

    /**
     * Get a specific incident
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $incident = Incident::with(['driver', 'bus', 'resolver'])->findOrFail($id);

        // Drivers can only view their own incidents
        if ($user->role === 'driver' && $incident->driver_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($incident);
    }

    /**
     * Update incident status (admin only)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:reported,in_progress,resolved,closed',
            'admin_response' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $incident = Incident::findOrFail($id);

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->has('admin_response')) {
            $updateData['admin_response'] = $request->admin_response;
        }

        // Set resolved_by and resolved_at if status is resolved or closed
        if (in_array($request->status, ['resolved', 'closed'])) {
            $updateData['resolved_by'] = $user->id;
            $updateData['resolved_at'] = now();
        } else {
            $updateData['resolved_by'] = null;
            $updateData['resolved_at'] = null;
        }

        $incident->update($updateData);
        $incident->load(['driver', 'bus', 'resolver']);

        return response()->json([
            'message' => 'Incident status updated successfully',
            'incident' => $incident
        ]);
    }

    /**
     * Delete an incident (admin only)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $incident = Incident::findOrFail($id);
        $incident->delete();

        return response()->json(['message' => 'Incident deleted successfully']);
    }

    /**
     * Get incidents for bus owner (filtered by their buses)
     */
    public function getOwnerIncidents(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get all incidents (for now, owners can see all incidents)
        // TODO: Filter by owner's buses when owner-bus relationship is established
        $query = Incident::with([
            'driver:id,name,email,phone',
            'bus:id,bus_number',
            'resolver:id,name'
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by bus_id if provided
        if ($request->has('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        $incidents = $query->paginate($request->get('per_page', 20));

        return response()->json($incidents);
    }

    /**
     * Get incidents for passengers (public view - all incidents)
     */
    public function getPassengerIncidents(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($user->role !== 'passenger') {
                return response()->json(['message' => 'Unauthorized. Only passengers can access this endpoint.'], 403);
            }

            // Get all incidents (public information for passengers)
            $query = Incident::with([
                'driver:id,name,email,phone',
                'bus:id,bus_number',
            ])->orderBy('created_at', 'desc');

            // Filter by status - show all incidents by default for passengers
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
            // No default filter - show all incidents for transparency

            // Filter by severity
            if ($request->has('severity') && $request->severity !== 'all') {
                $query->where('severity', $request->severity);
            }

            // Filter by bus_id if provided
            if ($request->has('bus_id') && $request->bus_id) {
                $query->where('bus_id', $request->bus_id);
            }

            $perPage = $request->get('per_page', 20);
            $incidents = $query->paginate($perPage);

            // Log for debugging
            Log::info('Passenger incidents query result', [
                'total' => $incidents->total(),
                'count' => $incidents->count(),
                'current_page' => $incidents->currentPage(),
            ]);

            // Return Laravel pagination response
            return response()->json($incidents);
        } catch (\Exception $e) {
            Log::error('Error in getPassengerIncidents: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'An error occurred while fetching incidents',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}

