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
                
                // Redirect based on role
                if ($user->role->name === 'admin' || $user->role->name === 'warehouse') {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect('/'); // For franchisees, you could redirect to a different page
                }
            }
        }

        return $next($request);
    }
}