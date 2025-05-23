<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
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
            // Try to authenticate the user via the token
            $user = JWTAuth::parseToken()->authenticate();
            
            // If no user is found (token is valid but user doesn't exist)
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Load the user's role to check permissions
            $user->load('role');
            
            // Check if user is blocked/inactive
            if (isset($user->status) && (int)$user->status === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked'
                ], 403);
            }
            
            // For mobile app routes, ensure only franchisees can access
            if ($request->is('api/mobile/*') || $request->header('X-App-Type') === 'mobile') {
                if (!$user->role || $user->role->name !== 'franchisee') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mobile app access is only available for franchisees'
                    ], 403);
                }
            }
            
            // Set the authenticated user in the request
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
            
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
            
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token not found',
                'error_code' => 'TOKEN_NOT_FOUND'
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error_code' => 'AUTH_FAILED',
                'debug_message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
        
        return $next($request);
    }
}