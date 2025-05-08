<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FranchiseeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $profile = FranchiseeDetail::where('user_id', $user->id)->first();
        
        return view('franchisee.profile', compact('user', 'profile'));
    }
    
    /**
     * Update basic user information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateBasicInfo(Request $request)
    {
        $user = Auth::user();
        
        DB::beginTransaction();
        
        try {
            // Validate inputs
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'email' => 'required|email|max:100|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'contact_name' => 'nullable|string|max:100',
            ]);
            
            // Update user data
            $user->username = $request->username;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->updated_at = now();
            $user->updated_by = $user->id;
            $user->save();
            
            // Update profile contact name if provided
            if ($request->has('contact_name')) {
                $profile = FranchiseeDetail::where('user_id', $user->id)->first();
                
                if ($profile) {
                    $profile->contact_name = $request->contact_name;
                    $profile->updated_at = now();
                    $profile->updated_by = $user->id;
                    $profile->save();
                } else {
                    // Create profile if it doesn't exist
                    FranchiseeDetail::create([
                        'user_id' => $user->id,
                        'contact_name' => $request->contact_name,
                        'updated_by' => $user->id
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('franchisee.profile')
                ->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Update company information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCompanyInfo(Request $request)
    {
        $user = Auth::user();
        
        DB::beginTransaction();
        
        try {
            // Validate company info
            $request->validate([
                'company_name' => 'required|string|max:100',
                'address' => 'required|string',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
            ]);
            
            // Update or create franchisee details
            $profile = FranchiseeDetail::where('user_id', $user->id)->first();
            
            if ($profile) {
                $profile->company_name = $request->company_name;
                $profile->address = $request->address;
                $profile->city = $request->city;
                $profile->state = $request->state;
                $profile->postal_code = $request->postal_code;
                $profile->updated_at = now();
                $profile->updated_by = $user->id;
                $profile->save();
            } else {
                FranchiseeDetail::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'updated_by' => $user->id
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('franchisee.settings')
                ->with('success', 'Company information updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update company information: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the settings page.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        $user = Auth::user();
        $profile = FranchiseeDetail::where('user_id', $user->id)->first();
        
        return view('franchisee.settings', compact('user', 'profile'));
    }
    
    /**
     * Update user settings.
     * 
     * This method handles the original settings update form which could contain
     * either company info, password update, or both.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $hasCompanyUpdate = $request->has('company_name');
        $hasPasswordUpdate = $request->filled('current_password') && $request->filled('new_password');
        $successMessage = 'Settings updated successfully';
        
        DB::beginTransaction();
        
        try {
            // For company info updates
            if ($hasCompanyUpdate) {
                // Validate company info
                $request->validate([
                    'company_name' => 'required|string|max:100',
                    'address' => 'required|string',
                    'city' => 'required|string|max:100',
                    'state' => 'required|string|max:100',
                    'postal_code' => 'required|string|max:20',
                ]);
                
                // Update or create franchisee details
                $profile = FranchiseeDetail::where('user_id', $user->id)->first();
                
                if ($profile) {
                    $profile->company_name = $request->company_name;
                    $profile->address = $request->address;
                    $profile->city = $request->city;
                    $profile->state = $request->state;
                    $profile->postal_code = $request->postal_code;
                    $profile->updated_at = now();
                    $profile->updated_by = $user->id;
                    $profile->save();
                } else {
                    FranchiseeDetail::create([
                        'user_id' => $user->id,
                        'company_name' => $request->company_name,
                        'address' => $request->address,
                        'city' => $request->city,
                        'state' => $request->state,
                        'postal_code' => $request->postal_code,
                        'updated_by' => $user->id
                    ]);
                }
            }
            
            // For password update
            if ($hasPasswordUpdate) {
                // Validate password inputs
                $request->validate([
                    'current_password' => 'required|string',
                    'new_password' => 'required|string|min:8|confirmed',
                ]);
                
                // Check current password
                if (!Hash::check($request->current_password, $user->password_hash)) {
                    throw new \Exception('Current password is incorrect.');
                }
                
                // Update password
                $user->password_hash = Hash::make($request->new_password);
                $user->updated_at = now();
                $user->updated_by = $user->id;
                $user->save();
                
                $successMessage = 'Password updated successfully';
            }
            
            DB::commit();
            
            return redirect()->route('franchisee.settings', $hasPasswordUpdate ? ['tab' => 'password'] : [])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Update user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        DB::beginTransaction();
        
        try {
            // Validate password inputs
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);
            
            // Check current password
            if (!Hash::check($request->current_password, $user->password_hash)) {
                throw new \Exception('Current password is incorrect.');
            }
            
            // Update password
            $user->password_hash = Hash::make($request->new_password);
            $user->updated_at = now();
            $user->updated_by = $user->id;
            $user->save();
            
            DB::commit();
            
            return redirect()->route('franchisee.settings', ['tab' => 'password'])
                ->with('success', 'Password updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
}