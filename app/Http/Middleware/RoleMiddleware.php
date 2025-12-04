<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Convert role to uppercase for consistency
        $requiredRole = strtoupper($role);
        $userRole = strtoupper($request->user()->role);

        if ($userRole !== $requiredRole) {
            return response()->json([
                'message' => 'Unauthorized. Required role: ' . $requiredRole
            ], 403);
        }

        return $next($request);
    }
}