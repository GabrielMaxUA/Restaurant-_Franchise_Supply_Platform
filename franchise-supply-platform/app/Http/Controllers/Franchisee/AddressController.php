<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Get the franchisee's address details for use in checkout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress()
    {
        $user = Auth::user();
        
        // Check if the user is a franchisee and has franchisee details
        if ($user->isFranchisee() && $user->franchiseeDetail) {
            return response()->json([
                'success' => true,
                'address' => $user->franchiseeDetail->address,
                'city' => $user->franchiseeDetail->city,
                'state' => $user->franchiseeDetail->state,
                'postal_code' => $user->franchiseeDetail->postal_code,
            ]);
        }
        
        // Return an error if no address is found
        return response()->json([
            'success' => false,
            'message' => 'No franchisee address found'
        ]);
    }
}