<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'in:passenger,driver,owner',
            'driver_type' => 'nullable|in:expressway,normal'
        ]);

        // Validate driver_type is required if role is driver
        if ($data['role'] === 'driver' && empty($data['driver_type'])) {
            return response()->json([
                'message' => 'Driver type is required for driver registration'
            ], 422);
        }

        // Ensure at least email or phone is provided
        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'message' => 'Either email or phone is required'
            ], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'passenger',
            'driver_type' => $data['driver_type'] ?? null
        ]);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    // POST /api/auth/login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'password' => 'required|string'
        ]);

        // Ensure at least email or phone is provided
        if (empty($credentials['email']) && empty($credentials['phone'])) {
            return response()->json([
                'message' => 'Either email or phone is required'
            ], 422);
        }

        $user = null;
        if (!empty($credentials['email'])) {
            $user = User::where('email', $credentials['email'])->first();
        } elseif (!empty($credentials['phone'])) {
            $user = User::where('phone', $credentials['phone'])->first();
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
