<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\FranchiseeDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'franchiseeDetail'])->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Validate common user fields
            $validated = $request->validate([
                'username' => 'required|string|max:50|unique:users',
                'email' => 'required|email|max:100|unique:users',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'role_id' => 'required|exists:roles,id',
            ]);
            
            // Additional validation for franchisee role
            if ($request->role_id == 3) { // Assuming franchisee role ID is 3
                $request->validate([
                    'company_name' => 'required|string|max:100',
                    'address' => 'required|string',
                    'city' => 'required|string|max:100',
                    'state' => 'required|string|max:100',
                    'postal_code' => 'required|string|max:20',
                    'contact_name' => 'nullable|string|max:100',
                ]);
                
                // Debugging
                \Log::info('Franchisee validation passed', [
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                ]);
            }
            
            // Hash the password
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
            
            // Create the user
            $user = User::create($validated);
            \Log::info('User created', ['user_id' => $user->id]);
            
            // Create franchisee details if role is franchisee
            if ($request->role_id == 3) {
                $franchiseeDetail = FranchiseeDetail::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'contact_name' => $request->contact_name ?? null,
                ]);
                
                \Log::info('Franchisee details created', [
                    'franchisee_detail_id' => $franchiseeDetail->id ?? 'failed',
                    'user_id' => $user->id
                ]);
            }
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');
                
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            // Log the error
            \Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('franchiseeDetail'); // Eager load franchisee details
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Validate common user fields
            $validated = $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'email' => 'required|email|max:100|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'role_id' => 'required|exists:roles,id',
            ]);
            
            // Additional validation for franchisee role
            if ($request->role_id == 3) { // Assuming franchisee role ID is 2
                $request->validate([
                    'company_name' => 'required|string|max:100',
                    'address' => 'required|string',
                    'city' => 'required|string|max:100',
                    'state' => 'required|string|max:100',
                    'postal_code' => 'required|string|max:20',
                    'contact_name' => 'nullable|string|max:100',
                ]);
            }
            
            // Handle password update if provided
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'string|min:8',
                ]);
                $validated['password_hash'] = Hash::make($request->password);
            }
            
            // Update the user
            $user->update($validated);
            
            // Update franchisee details if role is franchisee
            if ($request->role_id == 3) {
                $franchiseeData = [
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'contact_name' => $request->contact_name,
                ];
                
                // Update or create franchisee details
                if ($user->franchiseeDetail) {
                    $user->franchiseeDetail->update($franchiseeData);
                } else {
                    FranchiseeDetail::create(array_merge(['user_id' => $user->id], $franchiseeData));
                }
            } else if ($user->franchiseeDetail) {
                // Remove franchisee details if role changed from franchisee to something else
                $user->franchiseeDetail->delete();
            }
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');
                
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Prevent deleting the currently logged-in user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Start transaction
        DB::beginTransaction();
        
        try {
            // Delete franchisee details if they exist
            if ($user->franchiseeDetail) {
                $user->franchiseeDetail->delete();
            }
            
            // Delete the user
            $user->delete();
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
                
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return redirect()->route('admin.users.index')
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
}