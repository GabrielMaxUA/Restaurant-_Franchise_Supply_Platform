<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * API authentication methods
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        // Check if the user is blocked
        if ($user->isBlocked()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been blocked. Please contact the administrator.'],
            ]);
        }
        
        $user->load('role');
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Web authentication methods
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
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
    
    public function webLogout()
    {
        Auth::logout();
        return redirect('/login');
    }
}