<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Get the authenticated user using the api guard
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Make sure role relationship is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Check if user has the required role
        if (!$user->role || $user->role->name !== $role) {
            return response()->json(['error' => 'Access denied. You do not have the required role.'], 403);
        }
        
        return $next($request);
    }
}