<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Supports single role or multiple roles separated by comma
     * Example: 'role:owner' or 'role:owner,admin'
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles  Comma-separated list of allowed roles (or multiple role arguments)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        
        // Log for debugging
        Log::info('CheckRole middleware called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'roles_param' => $roles,
            'roles_count' => count($roles),
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_exists' => $user !== null,
        ]);

        if (!$user) {
            Log::warning('CheckRole: User not authenticated');
            return response()->json(['message' => 'Unauthorized. Please log in.'], 401);
        }

        // Collect all allowed roles
        $allowedRoles = [];
        
        // Process roles - Laravel middleware parameters can be passed in different ways
        // For 'role:owner,admin', Laravel may pass it as one string or separate arguments
        if (!empty($roles)) {
            foreach ($roles as $role) {
                // Handle both string and array cases
                $roleValue = is_array($role) ? implode(',', $role) : $role;
                // Split by comma to handle multiple roles in one parameter
                $roleList = explode(',', $roleValue);
                foreach ($roleList as $r) {
                    $trimmed = strtolower(trim($r));
                    if (!empty($trimmed) && !in_array($trimmed, $allowedRoles)) {
                        $allowedRoles[] = $trimmed;
                    }
                }
            }
        }
        
        Log::info('CheckRole processing', [
            'allowed_roles' => $allowedRoles,
            'user_role' => strtolower($user->role),
        ]);

        // If no roles specified, deny access
        if (empty($allowedRoles)) {
            Log::warning('CheckRole: No roles specified', [
                'user_id' => $request->user()->id,
                'user_role' => $request->user()->role,
            ]);
            return response()->json([
                'message' => 'Unauthorized. No roles specified.'
            ], 403);
        }

        $userRole = strtolower($request->user()->role);

        if (!in_array($userRole, $allowedRoles)) {
            Log::warning('CheckRole: User role not in allowed roles', [
                'user_id' => $request->user()->id,
                'user_role' => $userRole,
                'allowed_roles' => $allowedRoles,
            ]);
            return response()->json([
                'message' => 'Unauthorized. Required role(s): ' . implode(', ', $allowedRoles) . '. Your role: ' . $userRole
            ], 403);
        }

        Log::info('CheckRole: Access granted', [
            'user_id' => $request->user()->id,
            'user_role' => $userRole,
            'allowed_roles' => $allowedRoles,
        ]);

        return $next($request);
    }
}
