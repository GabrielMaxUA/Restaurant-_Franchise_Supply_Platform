<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
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
            Auth::login($user);
            
            // Make sure role relationship is loaded
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            // Redirect based on role
            if ($user->role->name === 'admin') {
                return redirect()->route('admin.dashboard');
                session([
                  'welcome_back' => true,
                  'user_name' => $user->username,  // Store the name in session
                ]);
            } else if ($user->role->name === 'warehouse') {
                return redirect()->route('admin.dashboard'); // For now, redirect to same dashboard
            } else if ($user->role->name === 'franchisee') { // Fixed the typo here from "franchesee" to "franchisee"
                // Set welcome message for franchisees
                // In the AuthController webLogin method
              session([
                'welcome_back' => true,
                'user_name' => $user->username,  // Store the name in session
                // 'pending_orders' => $pendingOrdersCount,
                // 'low_stock_items' => $lowStockItemsCount
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