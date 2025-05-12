<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            // For API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                try {
                    // Check if the user is authenticated with JWT
                    $user = auth('api')->user();
                    
                    if ($user && isset($user->status) && (int)$user->status === 0) {
                        // User is blocked
                        return response()->json([
                            'success' => false,
                            'message' => 'Your account has been blocked. Please contact the administrator.'
                        ], 403);
                    }
                } catch (\Exception $e) {
                    // Log JWT-specific errors
                    Log::error('JWT Error in CheckUserStatus middleware: ' . $e->getMessage());
                }
            } 
            // For web requests
            else if (Auth::check()) {
                $user = Auth::user();
                
                // Check if status is explicitly 0 (blocked)
                if (isset($user->status) && (int)$user->status === 0) {
                    // User is blocked, log them out
                    Auth::logout();
                    
                    // Redirect to login with an error message
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