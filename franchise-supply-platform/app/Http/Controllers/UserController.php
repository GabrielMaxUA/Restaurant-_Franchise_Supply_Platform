<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('role')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id']
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('role');
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'role_id' => 'sometimes|exists:roles,id'
        ]);

        if ($request->has('password')) {
            $validated['password_hash'] = Hash::make($request->password);
        }

        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}