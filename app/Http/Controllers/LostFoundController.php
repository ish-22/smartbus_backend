<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LostFound;
use App\Models\Bus;
use App\Models\Notification;

class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = LostFound::with(['user:id,name,email', 'bus:id,bus_number']);

            // Role-based filtering
            if ($user && $user->role === 'passenger') {
                // Passengers see only items they reported
                $query->where('user_id', $user->id);
            } elseif ($user && $user->role === 'driver') {
                // Drivers can see all lost & found items so they can help return items
                // TODO: In future, filter by buses assigned to this driver once the relationship exists
            } elseif ($user && $user->role === 'owner') {
                // Owners currently see all items
                // TODO: Optionally filter by buses owned by this owner
            }
            // Admin sees all by default

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $items = $query->orderBy('created_at', 'desc')->get();
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'item_name' => 'required|string|max:255',
                'description' => 'required|string',
                'found_location' => 'required|string|max:255',
                'found_date' => 'required|date',
                'status' => 'required|in:lost,found,returned',
                'bus_id' => 'nullable|exists:buses,id',
            ]);

            $user = $request->user();
            $data['user_id'] = $user->id;
            $item = LostFound::create($data);

            // Notify admins about a new lost & found report
            $adminUsers = \App\Models\User::where('role', 'admin')->get(['id']);
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'New Lost & Found Reported',
                    'message' => sprintf(
                        "User %s reported a '%s' item.",
                        $user->name,
                        $item->item_name
                    ),
                    'type'    => 'info',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item reported successfully',
                'data' => $item->load(['user:id,name', 'bus:id,bus_number'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = LostFound::with(['user:id,name,email', 'bus:id,bus_number'])->findOrFail($id);
            return response()->json(['data' => $item]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Item not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $item = LostFound::findOrFail($id);
            $user = $request->user();

            // Check permissions
            if ($user->role !== 'admin' && $item->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = $request->validate([
                'item_name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'found_location' => 'sometimes|string|max:255',
                'found_date' => 'sometimes|date',
                'status' => 'sometimes|in:lost,found,returned',
                'bus_id' => 'nullable|exists:buses,id',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => $item->load(['user:id,name', 'bus:id,bus_number'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $item = LostFound::findOrFail($id);
            $user = $request->user();

            if ($user->role !== 'admin' && $item->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $item->delete();
            return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function myItems(Request $request)
    {
        try {
            $items = LostFound::with(['bus:id,bus_number'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $item = LostFound::findOrFail($id);
            $user = $request->user();

            // Only admin, driver, or owner can update status
            if (!in_array($user->role, ['admin', 'driver', 'owner'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = $request->validate([
                'status' => 'required|in:lost,found,returned',
            ]);

            $item->update($data);

            // Notify original reporter when item is returned
            if ($data['status'] === 'returned' && $item->user_id) {
                Notification::create([
                    'user_id' => $item->user_id,
                    'title'   => 'Lost Item Returned',
                    'message' => sprintf(
                        "Your lost item '%s' has been marked as returned.",
                        $item->item_name
                    ),
                    'type'    => 'success',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $item->load(['user:id,name', 'bus:id,bus_number'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            $query = LostFound::query();

            if ($user->role === 'driver') {
                // For now, show all stats for drivers
                // TODO: Implement proper bus-driver relationship
            }

            return response()->json([
                'total' => $query->count(),
                'lost' => (clone $query)->where('status', 'lost')->count(),
                'found' => (clone $query)->where('status', 'found')->count(),
                'returned' => (clone $query)->where('status', 'returned')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
