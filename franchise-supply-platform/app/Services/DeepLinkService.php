<?php

namespace App\Services;

use App\Models\Order;

class DeepLinkService
{
    /**
     * Generate a deep link for order tracking that works on both web and mobile
     */
    public function generateOrderTrackingLink(Order $order, string $action = 'details', bool $testMode = false): string
    {
        // Base web URL
        $webUrl = config('app.url') . '/franchisee/orders/' . $order->id . '/' . $action;
        
        // For authenticated users, add login redirect
        $webUrlWithLogin = config('app.url') . '/login?intended=' . urlencode($webUrl);
        
        // In test mode or if app isn't published, use direct web link
        if ($testMode || $this->isTestingMode()) {
            return $webUrlWithLogin;
        }
        
        // Generate the deep link URL that handles both web and mobile
        return $this->createUniversalLink($webUrl, $webUrlWithLogin, 'order', $order->id, $action);
    }

    /**
     * Create a universal deep link that works for both web and mobile
     */
    private function createUniversalLink(string $webUrl, string $webUrlWithLogin, string $type, int $id, string $action): string
    {
        // Get app scheme from config
        $appScheme = config('app.deep_link_scheme', 'restaurantfranchise');
        
        // Create the app deep link
        $appDeepLink = sprintf('%s://%s/%d/%s', $appScheme, $type, $id, $action);
        
        // Use a universal link pattern that can be intercepted by the app
        // Avoid double URL encoding by not encoding the webUrlWithLogin again
        $universalLink = config('app.url') . '/deeplink?' . http_build_query([
            'app' => $appDeepLink,
            'web' => $webUrlWithLogin,
            'fallback' => $webUrl
        ], '', '&', PHP_QUERY_RFC3986);
        
        return $universalLink;
    }

    /**
     * Generate a direct order tracking link (no deep linking)
     */
    public function generateDirectOrderLink(Order $order, string $action = 'details'): string
    {
        $webUrl = config('app.url') . '/franchisee/orders/' . $order->id . '/' . $action;
        return config('app.url') . '/login?intended=' . urlencode($webUrl);
    }

    /**
     * Generate invoice tracking link
     */
    public function generateInvoiceTrackingLink(Order $order): string
    {
        return $this->generateOrderTrackingLink($order, 'invoice');
    }

    /**
     * Get app store URLs for app download prompts
     */
    public function getAppStoreUrls(): array
    {
        return [
            'ios' => config('app.ios_app_store_url', '#'),
            'android' => config('app.android_play_store_url', '#')
        ];
    }

    /**
     * Check if we're in testing mode (app not published)
     */
    private function isTestingMode(): bool
    {
        // Always return false to enable deep linking for mobile testing
        // Set to true later when you want to disable deep linking
        return false;
        
        // Original logic (commented out for testing):
        // $iosUrl = config('app.ios_app_store_url', '#');
        // $androidUrl = config('app.android_play_store_url', '#');
        // return ($iosUrl === '#' || empty($iosUrl)) && ($androidUrl === '#' || empty($androidUrl));
    }

    /**
     * Generate a deep link for testing purposes
     */
    public function generateTestDeepLink(Order $order, string $action = 'details'): string
    {
        // Get app scheme from config
        $appScheme = config('app.deep_link_scheme', 'restaurantfranchise');
        
        // Create the app deep link that your local app can handle
        $appDeepLink = sprintf('%s://%s/%d/%s', $appScheme, 'order', $order->id, $action);
        
        return $appDeepLink;
    }
}