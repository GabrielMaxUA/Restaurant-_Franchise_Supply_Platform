<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class WarehouseProfileController extends Controller
{
    /**
     * Display the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        return view('warehouse.profile', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function settings(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab');
        
        return view('warehouse.settings', compact('user', 'tab'));
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
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->updated_by = $user->id;
        $user->updated_at = Carbon::now(); // Explicitly update the timestamp
        $user->save();
        
        return redirect()
            ->back()
            ->with('success', 'Profile updated successfully');
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
                ->route('warehouse.settings', ['tab' => 'password'])
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return redirect()
                ->route('warehouse.settings', ['tab' => 'password'])
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }
        
        $user->password_hash = Hash::make($request->new_password);
        $user->updated_by = $user->id;
        $user->updated_at = Carbon::now(); // Explicitly update the timestamp
        $user->save();
        
        // Redirect to profile page after successful password change
        return redirect()
            ->route('warehouse.profile')
            ->with('success', 'Password changed successfully');
    }
}