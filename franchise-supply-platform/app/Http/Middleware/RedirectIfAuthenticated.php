<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Get the authenticated user
                $user = Auth::guard($guard)->user();
                
                // Make sure role relationship is loaded
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                // Redirect based on role
                if ($user->role->name === 'admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role->name === 'warehouse') {
                    return redirect()->route('warehouse.dashboard');
                } elseif ($user->role->name === 'franchisee') {
                    return redirect()->route('franchisee.dashboard');
                } else {
                    return redirect('/');
                }
            }
        }

        return $next($request);
    }
}