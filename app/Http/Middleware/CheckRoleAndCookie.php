<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRoleAndCookie
{
    public function handle($request, Closure $next)
    {
        // Check if the request has cookies
        if (!$request->hasCookie('jwt_token')) {
            return response()->json(['message' => 'Missing token cookie'], 401);
        }

        // Check if the user has the required role
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['message' => 'User is unauthorized'], 403);
        }

        return $next($request);
    }
}
