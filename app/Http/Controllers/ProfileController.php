<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get user profile by user_id
     * GET /api/profile/{user_id}
     */
    public function show(Request $request, $user_id)
    {
        // Ensure user can only access their own profile
        $authenticatedUser = $request->user();
        
        if (!$authenticatedUser) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user is trying to access their own profile
        // Convert both to integers for proper comparison (handles string IDs from frontend)
        if ((int)$authenticatedUser->id !== (int)$user_id) {
            return response()->json([
                'message' => 'Unauthorized. You can only access your own profile.'
            ], 403);
        }

        // Get user profile
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Return user data (excluding sensitive information)
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        // Add license_number if available (for drivers and owners)
        if ($user->license_number) {
            $userData['license_number'] = $user->license_number;
        }

        return response()->json([
            'user' => $userData
        ]);
    }

    /**
     * Update user profile
     * PUT /api/profile/update/{user_id}
     */
    public function update(Request $request, $user_id)
    {
        // Ensure user can only update their own profile
        $authenticatedUser = $request->user();
        
        if (!$authenticatedUser) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user is trying to update their own profile
        // Convert both to integers for proper comparison (handles string IDs from frontend)
        if ((int)$authenticatedUser->id !== (int)$user_id) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own profile.'
            ], 403);
        }

        // Get user
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validate input
        $data = $request->validate([
            'name' => 'sometimes|string|max:191',
            'email' => 'sometimes|nullable|email',
            'password' => 'sometimes|string|min:6|nullable',
            'license_number' => 'sometimes|nullable|string|max:191',
        ]);

        // Check uniqueness manually
        if (isset($data['email']) && $data['email'] && $data['email'] != $user->email) {
            if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                return response()->json(['message' => 'The email has already been taken.'], 422);
            }
        }

        // Update user fields
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        // Update license_number if provided (for owners and drivers)
        if (isset($data['license_number'])) {
            $user->license_number = $data['license_number'];
        }

        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Return updated user data
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'updated_at' => $user->updated_at,
        ];

        // Add license_number if available
        if ($user->license_number) {
            $userData['license_number'] = $user->license_number;
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $userData
        ]);
    }
}

