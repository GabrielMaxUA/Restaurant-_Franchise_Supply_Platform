<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !$request->user()->role) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        foreach ($roles as $role) {
            if ($request->user()->role->name === $role) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}