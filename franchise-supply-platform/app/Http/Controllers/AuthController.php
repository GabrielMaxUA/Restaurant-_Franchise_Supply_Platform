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
            
            // Redirect based on role
            if ($user->role->name === 'admin') {
                return redirect()->route('admin.dashboard');
            } else if ($user->role->name === 'warehouse') {
                return redirect()->route('admin.dashboard'); // For now, redirect to same dashboard
            } else {
                return redirect('/'); // Redirect franchisees to home
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