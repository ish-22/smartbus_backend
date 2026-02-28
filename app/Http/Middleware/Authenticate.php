<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * For API routes, always return null to get JSON 401 response.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Always return null for API routes and JSON requests
        // This prevents RouteNotFoundException and returns proper JSON 401
        return null;
    }
}
