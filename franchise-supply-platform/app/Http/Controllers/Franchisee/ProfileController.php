<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FranchiseeProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user profile.
     * Supports both web and API requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $user->franchiseeProfile;
        
        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];
            
            $profileData = null;
            if ($profile) {
                $profileData = [
                    'id' => $profile->id,
                    'contact_name' => $profile->contact_name,
                    'company_name' => $profile->company_name,
                    'address' => $profile->address,
                    'city' => $profile->city,
                    'state' => $profile->state,
                    'postal_code' => $profile->postal_code,
                    'logo_url' => $profile->logo_path ? url('storage/' . $profile->logo_path) : null
                ];
            }
            
            return response()->json([
                'success' => true,
                'user' => $userData,
                'profile' => $profileData
            ]);
        }
        
        // Web response
        return view('franchisee.profile', compact('user', 'profile'));
    }
    
    /**
     * Update the user's profile.
     * Supports both web and API requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Log the request for debugging (excluding logo binary data)
        Log::info('Profile Update Request:', $request->except(['logo']));
        
        DB::beginTransaction();
        
        try {
            // Validate all inputs
            $request->validate([
                // User table fields
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'email' => 'required|email|max:100|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                
                // Franchisee profile fields
                'contact_name' => 'nullable|string|max:100',
                'company_name' => 'required|string|max:100',
                'address' => 'required|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            // 1. Update user data
            $user->username = $request->username;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->updated_at = now();
            $user->updated_by = $user->username;
            $user->save();
            
            // 2. Update or create franchisee profile
            $profile = $user->franchiseeProfile;
            
            $profileData = [
                'contact_name' => $request->contact_name,
                'company_name' => $request->company_name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'updated_by' => $user->username
            ];
            
            // Handle remove logo checkbox
            if ($request->has('remove_logo') && $request->remove_logo) {
                // Delete the old logo if it exists
                if ($profile && $profile->logo_path) {
                    Storage::disk('public')->delete($profile->logo_path);
                    Log::info('Logo removed for user #' . $user->id);
                }
                
                // Set logo_path to null
                $profileData['logo_path'] = null;
            }
            // Only handle logo upload if the remove checkbox is not checked
            else if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                // Get the uploaded file
                $logo = $request->file('logo');
                
                // Generate a unique filename
                $filename = 'company_logo_' . $user->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
                
                // Store the file in the public storage
                $path = $logo->storeAs('franchisee_logos', $filename, 'public');
                
                // Delete the old logo if it exists
                if ($profile && $profile->logo_path) {
                    Storage::disk('public')->delete($profile->logo_path);
                }
                
                // Add the path to profile data
                $profileData['logo_path'] = $path;
                
                Log::info('Logo uploaded successfully: ' . $path);
            }
            
            $logoUrl = null;
            
            if ($profile) {
                // Update existing profile
                $profile->fill($profileData);
                $profile->save();
                
                // Get the logo URL for the API response
                $logoUrl = $profile->logo_path ? url('storage/' . $profile->logo_path) : null;
            } else {
                // Create new profile
                $profileData['user_id'] = $user->id;
                $profile = FranchiseeProfile::create($profileData);
                
                // Get the logo URL for the API response
                $logoUrl = $profile && $profile->logo_path ? url('storage/' . $profile->logo_path) : null;
            }
            
            DB::commit();
            
            // Check if this is an API request
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ],
                    'profile' => [
                        'id' => $profile->id,
                        'contact_name' => $profile->contact_name,
                        'company_name' => $profile->company_name,
                        'address' => $profile->address,
                        'city' => $profile->city,
                        'state' => $profile->state,
                        'postal_code' => $profile->postal_code,
                        'logo_url' => $logoUrl
                    ]
                ]);
            }
            
            // Web response
            return redirect()->route('franchisee.profile')
                ->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile Update Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Check if this is an API request
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile: ' . $e->getMessage()
                ], 500);
            }
            
            // Web response
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
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
        $profile = $user->franchiseeProfile;
        
        return view('franchisee.settings', compact('user', 'profile'));
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
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                ],
            ], [
                'new_password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
            ]);
            
            // Check current password
            if (!Hash::check($request->current_password, $user->password_hash)) {
                throw new \Exception('Current password is incorrect.');
            }
            
            // Update password
            $user->password_hash = Hash::make($request->new_password);
            $user->updated_at = now();
            $user->save();
            
            DB::commit();
            
            return redirect()->route('franchisee.settings')
                ->with('success', 'Password updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the franchisee's address details for use in checkout.
     * This method is already API-friendly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress(Request $request)
    {
        $user = Auth::user();
        
        // Check if the user is a franchisee and has franchisee profile
        if ($user->isFranchisee() && $user->franchiseeProfile) {
            $profile = $user->franchiseeProfile;
            
            // Check if profile has all required address fields
            if ($profile->address && $profile->city && $profile->state && $profile->postal_code) {
                return response()->json([
                    'success' => true,
                    'address' => $profile->address,
                    'city' => $profile->city,
                    'state' => $profile->state,
                    'postal_code' => $profile->postal_code,
                    'company_name' => $profile->company_name,
                    'contact_name' => $profile->contact_name,
                    'phone' => $user->phone,
                ]);
            }
        }
        
        // Return an error if no address is found
        return response()->json([
            'success' => false,
            'message' => 'No franchisee address found'
        ]);
    }
    
    /**
     * Delete the company logo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLogo()
    {
        $user = Auth::user();
        $profile = $user->franchiseeProfile;
        
        if (!$profile || !$profile->logo_path) {
            return redirect()->route('franchisee.profile')
                ->with('error', 'No logo found to delete.');
        }
        
        try {
            // Delete the file from storage
            Storage::disk('public')->delete($profile->logo_path);
            
            // Remove the path from the profile
            $profile->logo_path = null;
            $profile->save();
            
            return redirect()->route('franchisee.profile')
                ->with('success', 'Company logo deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Logo deletion error: ' . $e->getMessage());
            
            return redirect()->route('franchisee.profile')
                ->with('error', 'Failed to delete logo: ' . $e->getMessage());
        }
    }
}