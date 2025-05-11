<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
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
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user is blocked
        if ($user->isBlocked()) {
            Auth::logout();
            
            return redirect()->route('login')
                ->with('error', 'Your account has been blocked. Please contact the administrator.');
        }
        
        // Make sure role relationship is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Check if user has the required role
        if ($user->role->name !== $role) {
            if ($user->role->name === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have permission to access that page.');
            } elseif ($user->role->name === 'warehouse') {
                return redirect()->route('warehouse.dashboard')
                    ->with('error', 'You do not have permission to access that page.');
            } elseif ($user->role->name === 'franchisee') {
                return redirect()->route('franchisee.dashboard')
                    ->with('error', 'You do not have permission to access that page.');
            } else {
                return redirect('/')
                    ->with('error', 'You do not have permission to access that page.');
            }
        }
        
        return $next($request);
    }
}