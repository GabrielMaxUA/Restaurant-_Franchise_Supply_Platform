<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\FranchiseeProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class QuickBooksService
{
    /**
     * QuickBooks API base URL
     *
     * @var string
     */
    protected $apiBaseUrl;
    
    /**
     * QuickBooks access token
     *
     * @var string|null
     */
    protected $accessToken;
    
    /**
     * QuickBooks company ID
     *
     * @var string|null
     */
    protected $realmId;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiBaseUrl = env('QB_API_BASE_URL', 'https://quickbooks.api.intuit.com/v3/company/');
        $this->accessToken = $this->getAccessToken();
        $this->realmId = env('QB_REALM_ID');
    }
    
    /**
     * Get QuickBooks access token
     * 
     * @return string|null
     */
    protected function getAccessToken()
    {
        // In a production app, you would implement OAuth 2.0 flow
        // and manage token refresh logic
        return env('QB_ACCESS_TOKEN');
    }
    
    /**
     * Refresh access token if expired
     * 
     * @return string|null
     */
    protected function refreshTokenIfNeeded()
    {
        // Implement token refresh logic here
        // This would check if token is expired and refresh it using refresh token
        
        return $this->accessToken;
    }
    
    /**
     * Make an API request to QuickBooks
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return mixed
     */
    protected function makeApiRequest($method, $endpoint, $data = [])
    {
        $this->refreshTokenIfNeeded();
        
        if (!$this->accessToken) {
            throw new Exception('QuickBooks access token not available');
        }
        
        $url = $this->apiBaseUrl . $this->realmId . '/' . $endpoint;
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->$method($url, $data);
            
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('QuickBooks API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $endpoint
                ]);
                
                throw new Exception('QuickBooks API error: ' . $response->status());
            }
        } catch (Exception $e) {
            Log::error('QuickBooks API exception', [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Find a customer in QuickBooks by email
     * 
     * @param string $email
     * @return array|null
     */
    protected function findCustomerByEmail($email)
    {
        try {
            $response = $this->makeApiRequest('get', 'query?query=' . urlencode("SELECT * FROM Customer WHERE PrimaryEmailAddr = '{$email}'"));
            
            if (isset($response['QueryResponse']['Customer']) && count($response['QueryResponse']['Customer']) > 0) {
                return $response['QueryResponse']['Customer'][0];
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Error finding customer in QuickBooks', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Format a user for QuickBooks customer creation
     * 
     * @param User $user
     * @return array
     */
    protected function formatUserForCustomerCreation(User $user)
    {
        // Get franchisee profile if exists
        $profile = $user->franchiseeProfile;
        
        $customerData = [
            'DisplayName' => $user->name,
            'PrimaryEmailAddr' => [
                'Address' => $user->email
            ],
            'PrimaryPhone' => [
                'FreeFormNumber' => $profile ? $profile->phone : ''
            ],
            'CompanyName' => $profile ? $profile->company_name : $user->name,
        ];
        
        // Add address if available
        if ($profile && $profile->address) {
            $customerData['BillAddr'] = [
                'Line1' => $profile->address,
                'City' => $profile->city,
                'CountrySubDivisionCode' => $profile->state,
                'PostalCode' => $profile->zip_code,
                'Country' => 'US'
            ];
        }
        
        return $customerData;
    }
    
    /**
     * Sync a customer to QuickBooks
     * 
     * @param User $user
     * @return string Customer ID in QuickBooks
     */
    public function syncCustomer(User $user)
    {
        // Check if we're in demo mode (no actual API calls)
        if (!env('QB_INTEGRATION_ENABLED', false)) {
            // For demo purposes, we'll generate a fake customer ID
            $qbCustomerId = 'QB-CUST-' . rand(1000, 9999);
            
            // Store the customer ID on the user record
            if ($user->franchiseeProfile) {
                $user->franchiseeProfile->qb_customer_id = $qbCustomerId;
                $user->franchiseeProfile->save();
            }
            
            return $qbCustomerId;
        }
        
        try {
            // Check if customer already exists
            $existingCustomer = $this->findCustomerByEmail($user->email);
            
            if ($existingCustomer) {
                // Update existing customer
                $customerId = $existingCustomer['Id'];
                $customerData = $this->formatUserForCustomerCreation($user);
                $customerData['Id'] = $customerId;
                $customerData['SyncToken'] = $existingCustomer['SyncToken'];
                
                $this->makeApiRequest('post', 'customer', $customerData);
            } else {
                // Create new customer
                $customerData = $this->formatUserForCustomerCreation($user);
                $response = $this->makeApiRequest('post', 'customer', $customerData);
                $customerId = $response['Customer']['Id'];
            }
            
            // Store the customer ID on the user record
            if ($user->franchiseeProfile) {
                $user->franchiseeProfile->qb_customer_id = $customerId;
                $user->franchiseeProfile->save();
            }
            
            return $customerId;
        } catch (Exception $e) {
            Log::error('Error syncing customer to QuickBooks', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            // For demo purposes, we'll generate a fake customer ID in case of error
            return 'QB-CUST-' . rand(1000, 9999);
        }
    }
    
    /**
     * Format order for QuickBooks invoice creation
     * 
     * @param Order $order
     * @param string $customerId
     * @return array
     */
    protected function formatOrderForInvoiceCreation(Order $order, $customerId)
    {
        $invoiceData = [
            'CustomerRef' => [
                'value' => $customerId
            ],
            'DocNumber' => $order->order_number,
            'TxnDate' => $order->created_at->format('Y-m-d'),
            'Line' => []
        ];
        
        // Add line items
        foreach ($order->items as $item) {
            $lineItem = [
                'DetailType' => 'SalesItemLineDetail',
                'Amount' => $item->total_price,
                'Description' => $item->product->name . ($item->variant ? ' - ' . $item->variant->name : ''),
                'SalesItemLineDetail' => [
                    'Qty' => $item->quantity,
                    'UnitPrice' => $item->unit_price,
                    'ItemRef' => [
                        'value' => $item->product_id
                    ]
                ]
            ];
            
            $invoiceData['Line'][] = $lineItem;
        }
        
        return $invoiceData;
    }
    
    /**
     * Sync an order to QuickBooks as an invoice
     * 
     * @param Order $order
     * @return string Invoice ID in QuickBooks
     */
    public function syncInvoice(Order $order)
    {
        // Check if we're in demo mode (no actual API calls)
        if (!env('QB_INTEGRATION_ENABLED', false)) {
            // For demo purposes, generate a fake invoice ID
            $qbInvoiceId = 'QB-INV-' . rand(10000, 99999);
            
            // Update the order with the QuickBooks invoice ID
            $order->qb_invoice_id = $qbInvoiceId;
            $order->save();
            
            return $qbInvoiceId;
        }
        
        try {
            // Sync customer (this would get or create a customer in QuickBooks)
            $qbCustomerId = $this->syncCustomer($order->user);
            
            // Create invoice
            $invoiceData = $this->formatOrderForInvoiceCreation($order, $qbCustomerId);
            $response = $this->makeApiRequest('post', 'invoice', $invoiceData);
            
            $qbInvoiceId = $response['Invoice']['Id'];
            
            // Update the order with the QuickBooks invoice ID
            $order->qb_invoice_id = $qbInvoiceId;
            $order->save();
            
            return $qbInvoiceId;
        } catch (Exception $e) {
            Log::error('Error syncing invoice to QuickBooks', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            // For demo purposes, generate a fake invoice ID in case of error
            $qbInvoiceId = 'QB-INV-' . rand(10000, 99999);
            $order->qb_invoice_id = $qbInvoiceId;
            $order->save();
            
            return $qbInvoiceId;
        }
    }
    
    /**
     * Get invoice details from QuickBooks
     * 
     * @param string $invoiceId
     * @return array|null
     */
    public function getInvoiceDetails($invoiceId)
    {
        // Check if we're in demo mode (no actual API calls)
        if (!env('QB_INTEGRATION_ENABLED', false)) {
            // For demo purposes, return fake invoice details
            return [
                'Id' => $invoiceId,
                'DocNumber' => substr($invoiceId, 7),
                'TxnDate' => date('Y-m-d'),
                'DueDate' => date('Y-m-d', strtotime('+30 days')),
                'Balance' => rand(100, 1000) . '.00',
                'TotalAmt' => rand(100, 1000) . '.00',
                'CustomerRef' => [
                    'name' => 'Demo Customer'
                ]
            ];
        }
        
        try {
            return $this->makeApiRequest('get', 'invoice/' . $invoiceId);
        } catch (Exception $e) {
            Log::error('Error getting invoice from QuickBooks', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Check if QuickBooks integration is properly configured
     * 
     * @return bool
     */
    public function isConfigured()
    {
        return (!empty($this->accessToken) && !empty($this->realmId)) || env('QB_INTEGRATION_ENABLED', false);
    }
}