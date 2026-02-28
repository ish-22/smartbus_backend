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
            'driver_type' => 'nullable|in:expressway,normal',
            // Driver-specific detailed fields
            'license_number' => 'nullable|string|max:191',
            'license_expiry_date' => 'nullable|date|after:today',
            'address' => 'nullable|string',
            'nic_number' => 'nullable|string|max:191',
            'date_of_birth' => 'nullable|date|before:today',
            'emergency_contact_name' => 'nullable|string|max:191',
            'emergency_contact_phone' => 'nullable|string|max:191',
            'experience_years' => 'nullable|integer|min:0|max:50'
        ]);

        // Validate driver-specific fields if role is driver
        if ($data['role'] === 'driver') {
            // Driver type is required
            if (empty($data['driver_type'])) {
                return response()->json([
                    'message' => 'Driver type is required for driver registration. Please specify if you drive on normal routes or expressway/highway.'
                ], 422);
            }

            // Required driver fields
            if (empty($data['license_number'])) {
                return response()->json([
                    'message' => 'License number is required for driver registration'
                ], 422);
            }

            if (empty($data['license_expiry_date'])) {
                return response()->json([
                    'message' => 'License expiry date is required for driver registration'
                ], 422);
            }

            if (empty($data['nic_number'])) {
                return response()->json([
                    'message' => 'NIC number is required for driver registration'
                ], 422);
            }

            if (empty($data['date_of_birth'])) {
                return response()->json([
                    'message' => 'Date of birth is required for driver registration'
                ], 422);
            }

            if (empty($data['address'])) {
                return response()->json([
                    'message' => 'Address is required for driver registration'
                ], 422);
            }

            if (empty($data['emergency_contact_name']) || empty($data['emergency_contact_phone'])) {
                return response()->json([
                    'message' => 'Emergency contact name and phone are required for driver registration'
                ], 422);
            }
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
            'driver_type' => $data['driver_type'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'license_expiry_date' => $data['license_expiry_date'] ?? null,
            'address' => $data['address'] ?? null,
            'nic_number' => $data['nic_number'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
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
