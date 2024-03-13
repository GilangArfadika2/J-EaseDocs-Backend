<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRoleAndCookie
{
    public function handle($request, Closure $next)
    {
        // Check if the request has cookies
        if (!$request->hasCookie('_vercel_jwt')) {
            return response()->json(['message' => 'Missing token cookie'], 400);
        }

        $token = $request->cookie('_vercel_jwt');

        // Check if the user has the required role
        // $user = Auth::user();
        error_log($token);
        $user = Auth::guard('web')->setToken($token)->user();
        if (!$user || !in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['message' => 'User is unauthorized'], 400);
        }

        return $next($request);
    }
}