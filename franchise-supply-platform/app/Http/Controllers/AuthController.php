<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods except these
        $this->middleware('auth:api', ['except' => ['login', 'showLoginForm', 'webLogin']]);
    }

    /**
     * API authentication methods with JWT
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            
            $credentials = [
                'email' => $request->email,
                'password' => $request->password
            ];
            
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            
            $user = auth('api')->user();
            
            // Load the role relationship
            $user->load('role');
            
            // Check if user is blocked
            if (isset($user->status) && (int)$user->status === 0) {
                auth('api')->logout();
                return response()->json(['error' => 'Your account has been blocked'], 403);
            }
            
            return response()->json([
              'token' => $token,
              'token_type' => 'bearer',
              'expires_in' => auth('api')->factory()->getTTL() * 60,
              'user' => [
                  'id' => $user->id,
                  'email' => $user->email,
                  'username' => $user->username,
                  'role' => $user->role ? $user->role->name : null,
                  'status' => $user->isActive() ? 'active' : 'blocked',
                  'company_name' => $user->getCompanyNameAttribute(),
                  'full_address' => $user->getFullAddressAttribute()
              ]
          ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth('api')->logout();
        
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
    
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('api')->user();
        
        // Load user role to include in response
        if ($user) {
            $user->load('role');
        }
        
        return response()->json($user);
    }
    
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();
        
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    
    /**
     * Web authentication methods
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function webLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password_hash)) {
            // Check if the user is blocked
            if ($user->isBlocked()) {
                return back()->withErrors([
                    'email' => 'Your account has been blocked. Please contact the administrator.',
                ]);
            }
            
            Auth::login($user);
            
            // Make sure role relationship is loaded
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            // Redirect based on role
            if ($user->role->name === 'admin') {
                session([
                    'welcome_back' => true,
                    'user_name' => $user->username,  // Store the name in session
                ]);
                return redirect()->route('admin.dashboard');
            } else if ($user->role->name === 'warehouse') {
                // Set warehouse-specific session data
                session([
                    'welcome_back' => true,
                    'user_name' => $user->username,
                    'low_stock_items' => Product::where('inventory_count', '<=', 10)
                        ->where('inventory_count', '>', 0)->count(),
                    'out_of_stock_items' => Product::where('inventory_count', 0)->count()
                ]);
                return redirect()->route('warehouse.dashboard'); // Redirect to warehouse dashboard
            } else if ($user->role->name === 'franchisee') {
                // Set welcome message for franchisees
                session([
                    'welcome_back' => true,
                    'user_name' => $user->username  // Store the name in session
                ]);
                return redirect()->route('franchisee.dashboard');
            } else {
                return redirect('/'); // Redirect other roles to home
            }
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function webLogout()
    {
        Auth::logout();
        return redirect('/login');
    }
}