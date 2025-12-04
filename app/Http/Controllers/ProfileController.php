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
        if ($authenticatedUser->id != $user_id) {
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
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
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
        if ($authenticatedUser->id != $user_id) {
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
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user_id)
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('users', 'phone')->ignore($user_id)
            ],
            'password' => 'sometimes|string|min:6|nullable',
        ]);

        // Update user fields
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }

        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Return updated user data
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }
}

