<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Order;
use App\Models\AdminDetail; 
use App\Models\FranchiseeProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the users with search functionality.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all roles for the dropdown
        $roles = Role::all();
        
        // Start with a base query
        $query = User::query();
        
        // Eager load relationships
        $query->with(['role', 'franchiseeProfile']);
        
        // Apply search filters if provided
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('username', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by role if selected
        if ($request->filled('role')) {
            $query->where('role_id', $request->input('role'));
        }
        
        // Filter by status if selected
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by company/franchise name if provided
        if ($request->filled('company')) {
            $companySearch = $request->input('company');
            $query->whereHas('franchiseeProfile', function ($q) use ($companySearch) {
                $q->where('company_name', 'like', "%{$companySearch}%");
            });
        }
        
        // Get paginated results
        $users = $query->orderBy('id', 'desc')->paginate(15);
        
        // Return the view with users and roles
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

     /**
    * Store a newly created user in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\RedirectResponse
    */
   public function store(Request $request)
   {
       $request->validate([
           'username' => 'required|string|max:50|unique:users',
           'email' => 'required|string|email|max:100|unique:users',
           'password' => 'required|string|min:8|confirmed',
           'phone' => 'nullable|string|max:20',
           'role_id' => 'required|exists:roles,id',
           'status' => 'boolean',
           // Franchisee fields (optional)
           'company_name' => 'nullable|string|max:255',
           'address' => 'nullable|string|max:255',
           'city' => 'nullable|string|max:100',
           'state' => 'nullable|string|max:100',
           'postal_code' => 'nullable|string|max:20',
           'contact_name' => 'nullable|string|max:100',
           'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
       ]);
   
       // Create the user
       $user = User::create([
           'username' => $request->username,
           'email' => $request->email,
           'password_hash' => Hash::make($request->password),
           'phone' => $request->phone,
           'role_id' => $request->role_id,
           'status' => $request->has('status') ? $request->status : 1, // Default to active if not provided
           'updated_by' => Auth::user()->username,
       ]);
   
       // If role is franchisee and company details provided, create franchisee profile
       if ($user->isFranchisee() && $request->filled('company_name')) {
           $profileData = [
               'user_id' => $user->id,
               'company_name' => $request->company_name,
               'address' => $request->address,
               'city' => $request->city,
               'state' => $request->state,
               'postal_code' => $request->postal_code,
               'contact_name' => $request->contact_name,
               'updated_by' => Auth::user()->username,
           ];
           
           // Handle logo upload if provided
           if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
               $logo = $request->file('logo');
               $filename = 'company_logo_' . $user->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
               $path = $logo->storeAs('franchisee_logos', $filename, 'public');
               $profileData['logo_path'] = $path;
               
               Log::info('Logo uploaded for new user #' . $user->id . ': ' . $path);
           }
           
           FranchiseeProfile::create($profileData);
       }
   
       return redirect()->route('admin.users.index')
           ->with('success', 'User created successfully!');
   }
   
   /**
    * Update the specified user in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Models\User  $user
    * @return \Illuminate\Http\RedirectResponse
    */
   public function update(Request $request, User $user)
   {
       $request->validate([
           'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
           'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
           'password' => 'nullable|string|min:8|confirmed',
           'phone' => 'nullable|string|max:20',
           'role_id' => 'required|exists:roles,id',
           'status' => 'boolean',
           // Franchisee fields (optional)
           'company_name' => 'nullable|string|max:255',
           'address' => 'nullable|string|max:255',
           'city' => 'nullable|string|max:100',
           'state' => 'nullable|string|max:100',
           'postal_code' => 'nullable|string|max:20',
           'contact_name' => 'nullable|string|max:100',
           'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
       ]);
   
       // Prevent blocking your own account
       if ($user->id === auth()->id() && $request->has('status') && !$request->status) {
           return redirect()->back()
               ->with('error', 'You cannot block your own account!')
               ->withInput();
       }
   
       // Update the user
       $user->username = $request->username;
       $user->email = $request->email;
       $user->phone = $request->phone;
       $user->role_id = $request->role_id;
       $user->updated_by = Auth::user()->username;
       
       // Update status if provided
       if ($request->has('status')) {
           $user->status = $request->status;
       }
       
       if ($request->filled('password')) {
           $user->password_hash = Hash::make($request->password);
       }
       
       $user->save();
   
       // Handle franchisee profile
       if ($user->isFranchisee() && $request->filled('company_name')) {
           // Prepare profile data
           $profileData = [
               'company_name' => $request->company_name,
               'address' => $request->address,
               'city' => $request->city,
               'state' => $request->state,
               'postal_code' => $request->postal_code,
               'contact_name' => $request->contact_name,
               'updated_by' => Auth::user()->username,
           ];
           
           // Get existing profile if it exists
           $profile = $user->franchiseeProfile;
           
           // Handle logo upload if provided
           if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
               // Delete old logo if it exists
               if ($profile && $profile->logo_path) {
                   Storage::disk('public')->delete($profile->logo_path);
               }
               
               // Upload new logo
               $logo = $request->file('logo');
               $filename = 'company_logo_' . $user->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
               $path = $logo->storeAs('franchisee_logos', $filename, 'public');
               $profileData['logo_path'] = $path;
               
               Log::info('Logo updated for user #' . $user->id . ': ' . $path);
           }
           
           // Handle logo removal checkbox
           if ($request->has('remove_logo') && $profile && $profile->logo_path) {
               Storage::disk('public')->delete($profile->logo_path);
               $profileData['logo_path'] = null;
               
               Log::info('Logo removed for user #' . $user->id);
           }
           
           // Update or create the franchisee profile
           FranchiseeProfile::updateOrCreate(
               ['user_id' => $user->id],
               $profileData
           );
       } elseif (!$user->isFranchisee() && $user->franchiseeProfile) {
           // If user is no longer a franchisee but has a profile, we can optionally delete it
           // Uncomment the following line if you want to remove the profile when role changes
           // $user->franchiseeProfile->delete();
       }
   
       return redirect()->route('admin.users.index')
           ->with('success', 'User updated successfully!');
   }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $franchiseeProfile = $user->franchiseeProfile;
        
        return view('admin.users.edit', compact('user', 'roles', 'franchiseeProfile'));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Don't allow deleting your own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }
        
        // Delete logo file if it exists
        if ($user->franchiseeProfile && $user->franchiseeProfile->logo_path) {
            Storage::disk('public')->delete($user->franchiseeProfile->logo_path);
        }
        
        // Delete associated franchisee profile if it exists
        if ($user->franchiseeProfile) {
            $user->franchiseeProfile->delete();
        }
        
        // Delete the user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Get detailed user information for the modal display.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo($id)
    {
        try {
            // Log the request for debugging
            Log::info("Getting user info for ID: $id");
            
            // Find the user with role information
            $user = User::with(['role'])->find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Initialize variables
            $profile = null;
            $stats = null;
            $activity = [];
            
            // Determine user type and get profile - with error handling
            try {
                if ($user->role && $user->role->name === 'franchisee') {
                    $profile = FranchiseeProfile::where('user_id', $user->id)->first();
                } elseif ($user->role && in_array($user->role->name, ['admin', 'warehouse'])) {
                    $profile = AdminDetail::where('user_id', $user->id)->first();
                }
            } catch (Exception $e) {
                Log::error("Error getting user profile: " . $e->getMessage());
                // Continue execution without profile
            }
            
            // Get order statistics if the user is a franchisee - with error handling
            if ($user->role && $user->role->name === 'franchisee') {
                try {
                    // Check if Orders table and model exist
                    if (class_exists('App\\Models\\Order')) {
                        $orders = Order::where('user_id', $user->id)->get();
                        
                        if ($orders->count() > 0) {
                            // Calculate stats for all orders
                            $totalOrders = $orders->count();
                            $totalSpent = $orders->sum('total_amount');
                            
                            // Calculate stats for non-rejected orders
                            $activeOrders = $orders->filter(function ($order) {
                                return !in_array($order->status, ['rejected', 'cancelled']);
                            });
                            
                            $activeOrdersCount = $activeOrders->count();
                            $activeOrdersSpent = $activeOrders->sum('total_amount');
                            
                            $stats = [
                                'total_orders' => $totalOrders,
                                'total_spent' => $totalSpent,
                                'last_order_date' => $orders->sortByDesc('created_at')->first()->created_at ?? null,
                                'active_orders_count' => $activeOrdersCount,
                                'active_orders_spent' => $activeOrdersSpent,
                                'last_active_order_date' => $activeOrders->sortByDesc('created_at')->first()->created_at ?? null
                            ];
                            
                            // Generate activity timeline - last 5 orders
                            $recentOrders = $orders->sortByDesc('created_at')->take(5);
                            foreach ($recentOrders as $order) {
                                $itemCount = 0;
                                
                                // Check if the order has an items relationship and it exists
                                if (method_exists($order, 'items') && $order->items) {
                                    $itemCount = $order->items->count();
                                }
                                
                                $description = 'Ordered ' . $itemCount . ' items totaling $' . 
                                    number_format($order->total_amount, 2);
                                
                                // Add note if the order is rejected or cancelled
                                if (in_array($order->status, ['rejected', 'cancelled'])) {
                                    $description .= ' (' . ucfirst($order->status) . ')';
                                }
                                
                                $activity[] = [
                                    'date' => $order->created_at,
                                    'title' => 'Placed Order #' . $order->id,
                                    'description' => $description
                                ];
                            }
                        } else {
                            $stats = [
                                'total_orders' => 0,
                                'total_spent' => 0,
                                'last_order_date' => null,
                                'active_orders_count' => 0,
                                'active_orders_spent' => 0,
                                'last_active_order_date' => null
                            ];
                        }
                    } else {
                        // Order model doesn't exist or can't be found
                        $stats = [
                            'total_orders' => 'N/A',
                            'total_spent' => 'N/A',
                            'last_order_date' => null,
                            'active_orders_count' => 'N/A',
                            'active_orders_spent' => 'N/A',
                            'last_active_order_date' => null
                        ];
                    }
                } catch (Exception $e) {
                    Log::error("Error getting order statistics: " . $e->getMessage());
                    $stats = [
                        'total_orders' => 'Error',
                        'total_spent' => 'Error',
                        'last_order_date' => null,
                        'active_orders_count' => 'Error',
                        'active_orders_spent' => 'Error',
                        'last_active_order_date' => null
                    ];
                }
            }
            
            // Add account creation to timeline
            $activity[] = [
                'date' => $user->created_at,
                'title' => 'Account Created',
                'description' => 'User account was created'
            ];
            
            // Sort activity by date descending
            usort($activity, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            // Format response data
            $response = [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => [
                        'id' => $user->role ? $user->role->id : null,
                        'name' => $user->role ? $user->role->name : 'unknown'
                    ],
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'updated_by' => $user->updated_by,
                ],
                'profile' => $profile ? [
                    'id' => $profile->id,
                    'company_name' => $profile->company_name ?? null,
                    'address' => $profile->address ?? null,
                    'city' => $profile->city ?? null,
                    'state' => $profile->state ?? null,
                    'postal_code' => $profile->postal_code ?? null,
                    'contact_name' => $profile->contact_name ?? null,
                    'logo_path' => $profile->logo_path ?? null,
                    'website' => $profile->website ?? null,
                    'phone' => $profile->phone ?? null,
                    'email' => $profile->email ?? $user->email,
                ] : null,
                'stats' => $stats,
                'activity' => $activity,
            ];
            
            return response()->json($response);
            
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error("Error in getUserInfo: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching user information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   /**
   * Toggle the status of a user (active/blocked).
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\User  $user
   * @return \Illuminate\Http\JsonResponse
   */
  public function toggleStatus(Request $request, User $user)
  {
      try {
          // Don't allow blocking your own account
          if ($user->id === auth()->id()) {
              return response()->json([
                  'success' => false,
                  'message' => 'You cannot block your own account!'
              ], 403);
          }
          
          // Instead of toggling, set the status directly based on the checkbox value
          // checkbox is checked = status 1 (active), unchecked = status 0 (blocked)
          $newStatus = $request->has('new_status') ? (int)$request->input('new_status') : !$user->status;
          
          // Set the new status
          $user->status = $newStatus;
          $user->updated_by = Auth::user()->username;
          $user->save();
          
          $statusText = $user->status ? 'activated' : 'blocked';
          
          Log::info("User #{$user->id} ({$user->username}) was {$statusText} by " . Auth::user()->username);
          
          return response()->json([
              'success' => true,
              'message' => "User has been {$statusText} successfully",
              'user' => [
                  'id' => $user->id,
                  'status' => $user->status,
                  'statusText' => $user->statusText,
                  'statusBadgeClass' => $user->statusBadgeClass
              ]
          ]);
      } catch (\Exception $e) {
          Log::error("Error toggling user status: " . $e->getMessage());
          
          return response()->json([
              'success' => false,
              'message' => 'An error occurred while updating user status'
          ], 500);
      }
  }
}