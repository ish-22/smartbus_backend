<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LostFound;
use App\Models\Bus;

class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = LostFound::with(['user:id,name,email', 'bus:id,bus_number']);

            // Role-based filtering
            if ($user->role === 'passenger') {
                $query->where('user_id', $user->id);
            } elseif ($user->role === 'driver') {
                // For now, drivers see only their own items
                // TODO: Implement proper bus-driver relationship
                $query->where('user_id', $user->id);
            } elseif ($user->role === 'owner') {
                // Owner sees all items (or implement custom logic based on your bus ownership structure)
                // If you have a separate bus_owners table, adjust this query accordingly
            }
            // Admin sees all

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

            $data['user_id'] = $request->user()->id;
            $item = LostFound::create($data);

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
