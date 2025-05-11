<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if the user is authenticated
            if (Auth::check()) {
                $user = Auth::user();
                
                // Check if status is explicitly 0 (blocked)
                // Using direct comparison instead of isBlocked() method for extra safety
                if (isset($user->status) && (int)$user->status === 0) {
                    // User is blocked, log them out
                    Auth::logout();
                    
                    // If it's an API request, return a JSON response
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Your account has been blocked. Please contact the administrator.'
                        ], 403);
                    }
                    
                    // For web requests, redirect to login with an error message
                    return redirect()->route('login')
                        ->with('error', 'Your account has been blocked. Please contact the administrator.');
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't block the request
            Log::error('Error in CheckUserStatus middleware: ' . $e->getMessage());
            
            // In development, you might want to see the stack trace too
            if (config('app.debug')) {
                Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        }
        
        // Allow the request to proceed
        return $next($request);
    }
}