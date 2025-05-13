<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Exception;

class QuickBooksController extends Controller
{
    /**
     * Show QuickBooks settings page
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $isConfigured = !empty(config('services.quickbooks.client_id')) && 
                        !empty(config('services.quickbooks.client_secret'));
        
        $isConnected = Session::has('quickbooks_access_token') && 
                      Session::has('quickbooks_realm_id');
        
        return view('admin.settings.quickbooks', [
            'isConfigured' => $isConfigured,
            'isConnected' => $isConnected,
            'realmId' => Session::get('quickbooks_realm_id'),
            'tokenExpiry' => Session::get('quickbooks_token_expires_at')
        ]);
    }
    
    /**
     * Initiate OAuth flow to connect to QuickBooks
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect()
    {
        // Generate a random state value for CSRF protection
        $state = Str::random(40);
        Session::put('quickbooks_auth_state', $state);
        
        // Build the authorization URL
        $authUrl = config('services.quickbooks.auth_endpoint') . '?' . http_build_query([
            'client_id' => config('services.quickbooks.client_id'),
            'response_type' => 'code',
            'scope' => config('services.quickbooks.scope'),
            'redirect_uri' => config('services.quickbooks.redirect_uri'),
            'state' => $state
        ]);
        
        return redirect($authUrl);
    }
    
    /**
     * Handle callback from QuickBooks OAuth
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        // Verify the state parameter matches what we stored (CSRF protection)
        if ($request->state !== Session::get('quickbooks_auth_state')) {
            return redirect()->route('admin.settings.quickbooks')
                ->with('error', 'Invalid state parameter. Authentication failed.');
        }
        
        // Clear the state from the session
        Session::forget('quickbooks_auth_state');
        
        // Check if there's an error parameter in the callback
        if ($request->has('error')) {
            return redirect()->route('admin.settings.quickbooks')
                ->with('error', 'Authentication denied: ' . $request->error_description);
        }
        
        // Make sure we have an authorization code
        if (!$request->has('code')) {
            return redirect()->route('admin.settings.quickbooks')
                ->with('error', 'No authorization code received');
        }
        
        try {
            // Exchange the authorization code for an access token
            $response = Http::asForm()->post(config('services.quickbooks.token_endpoint'), [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.quickbooks.redirect_uri'),
                'client_id' => config('services.quickbooks.client_id'),
                'client_secret' => config('services.quickbooks.client_secret'),
            ]);
            
            if (!$response->successful()) {
                Log::error('QuickBooks token exchange failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return redirect()->route('admin.settings.quickbooks')
                    ->with('error', 'Failed to obtain access token: ' . $response->json()['error_description'] ?? 'Unknown error');
            }
            
            $tokenData = $response->json();
            
            // Store the tokens in the session
            Session::put('quickbooks_access_token', $tokenData['access_token']);
            Session::put('quickbooks_refresh_token', $tokenData['refresh_token']);
            Session::put('quickbooks_realm_id', $request->realmId);
            Session::put('quickbooks_token_expires_at', now()->addSeconds($tokenData['expires_in']));
            
            // You might want to store these in a more permanent storage
            // like the database for a production application
            
            return redirect()->route('admin.settings.quickbooks')
                ->with('success', 'Successfully connected to QuickBooks!');
            
        } catch (Exception $e) {
            Log::error('QuickBooks authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.quickbooks')
                ->with('error', 'An error occurred during authentication: ' . $e->getMessage());
        }
    }
    
    /**
     * Disconnect from QuickBooks
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        // Clear all QuickBooks-related session data
        Session::forget([
            'quickbooks_access_token',
            'quickbooks_refresh_token',
            'quickbooks_realm_id',
            'quickbooks_token_expires_at'
        ]);
        
        return redirect()->route('admin.settings.quickbooks')
            ->with('success', 'Successfully disconnected from QuickBooks');
    }
    
    /**
     * Refresh the QuickBooks access token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken()
    {
        if (!Session::has('quickbooks_refresh_token')) {
            return response()->json([
                'success' => false,
                'message' => 'No refresh token available'
            ], 400);
        }
        
        try {
            $response = Http::asForm()->post(config('services.quickbooks.token_endpoint'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => Session::get('quickbooks_refresh_token'),
                'client_id' => config('services.quickbooks.client_id'),
                'client_secret' => config('services.quickbooks.client_secret'),
            ]);
            
            if (!$response->successful()) {
                Log::error('QuickBooks token refresh failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to refresh access token: ' . 
                                ($response->json()['error_description'] ?? 'Unknown error')
                ], 400);
            }
            
            $tokenData = $response->json();
            
            // Update the stored tokens
            Session::put('quickbooks_access_token', $tokenData['access_token']);
            Session::put('quickbooks_refresh_token', $tokenData['refresh_token']);
            Session::put('quickbooks_token_expires_at', now()->addSeconds($tokenData['expires_in']));
            
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'expires_at' => Session::get('quickbooks_token_expires_at')->toDateTimeString()
            ]);
            
        } catch (Exception $e) {
            Log::error('QuickBooks token refresh error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during token refresh: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test the QuickBooks connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        if (!Session::has('quickbooks_access_token') || !Session::has('quickbooks_realm_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Not connected to QuickBooks'
            ], 400);
        }
        
        try {
            $realmId = Session::get('quickbooks_realm_id');
            $accessToken = Session::get('quickbooks_access_token');
            
            // Test the connection by fetching company info
            $url = config('services.quickbooks.base_url') . $realmId . '/companyinfo/' . $realmId;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json'
            ])->get($url);
            
            if (!$response->successful()) {
                Log::error('QuickBooks connection test failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed: ' . $response->status()
                ], 400);
            }
            
            $companyInfo = $response->json();
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully connected to QuickBooks',
                'company_name' => $companyInfo['CompanyInfo']['CompanyName'] ?? 'Unknown',
                'company_address' => $companyInfo['CompanyInfo']['CompanyAddr'] ?? null
            ]);
            
        } catch (Exception $e) {
            Log::error('QuickBooks connection test error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during connection test: ' . $e->getMessage()
            ], 500);
        }
    }
}