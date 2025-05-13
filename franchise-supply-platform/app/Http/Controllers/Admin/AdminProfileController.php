<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AdminDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    /**
     * Display the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $adminDetail = $user->adminDetail;
        
        return view('admin.profile', compact('user', 'adminDetail'));
    }

    /**
     * Show the form for editing the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function settings(Request $request)
    {
        $user = Auth::user();
        $adminDetail = $user->adminDetail;
        $tab = $request->query('tab');

        // For debugging - log whether we have admin details
        \Log::info('Admin settings page loaded', [
            'user_id' => $user->id,
            'username' => $user->username,
            'has_admin_detail' => !is_null($adminDetail),
            'admin_detail_id' => $adminDetail ? $adminDetail->id : null,
            'admin_detail_company' => $adminDetail ? $adminDetail->company_name : null
        ]);

        return view('admin.settings', compact('user', 'adminDetail', 'tab'));
    }
/**
 * Update the user's profile.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function update(Request $request)
{
    $user = Auth::user();
    
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:50|unique:users,username,' . $user->id,
        'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'company_name' => 'nullable|string|max:100',
        'address' => 'nullable|string',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'postal_code' => 'nullable|string|max:20',
        'website' => 'nullable|string|max:100',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    
    if ($validator->fails()) {
        return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
    }
    
    DB::beginTransaction();
    
    try {
        // Update user data
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->updated_by = Auth::user()->username;
        $user->updated_at = Carbon::now();
        $user->save();

        // Check if admin details exist for this user
        $existingAdminDetail = AdminDetail::where('user_id', $user->id)->first();

        if ($existingAdminDetail) {
            // Use existing record
            $adminDetail = $existingAdminDetail;
        } else {
            // Create new record
            $adminDetail = new AdminDetail();
            $adminDetail->user_id = $user->id;
            $adminDetail->created_by = Auth::user()->username;
        }
        
        $adminDetail->company_name = $request->company_name;
        $adminDetail->address = $request->address;
        $adminDetail->city = $request->city;
        $adminDetail->state = $request->state;
        $adminDetail->postal_code = $request->postal_code;
        $adminDetail->email = $request->email;
        $adminDetail->phone = $request->phone;
        $adminDetail->website = $request->website;
        $adminDetail->updated_by = Auth::user()->username;
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($adminDetail->logo_path && Storage::disk('public')->exists($adminDetail->logo_path)) {
                Storage::disk('public')->delete($adminDetail->logo_path);
            }
            
            // Store new logo
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $adminDetail->logo_path = $logoPath;
        }
        
        $adminDetail->save();
        
        DB::commit();
        
        return redirect()
            ->back()
            ->with('success', 'Profile updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to update profile: ' . $e->getMessage());
    }
}

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('admin.profile.settings', ['tab' => 'password'])
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return redirect()
                ->route('admin.profile.settings', ['tab' => 'password'])
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }
        
        $user->password_hash = Hash::make($request->new_password);
        $user->updated_by = Auth::user()->username;
        $user->updated_at = Carbon::now();
        $user->save();
        
        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Password changed successfully');
    }
}