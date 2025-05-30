<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeepLinkController extends Controller
{
    /**
     * Handle deep link routing for mobile app integration
     */
    public function handle(Request $request)
    {
        $appLink = $request->query('app');
        $webLink = $request->query('web');
        $fallbackLink = $request->query('fallback', $webLink);
        
        // Check if the request is from a mobile device
        $isMobile = $this->isMobileDevice($request);
        
        if ($isMobile && $appLink) {
            // Return a view that attempts to open the app and falls back to web
            return view('deeplink.redirect', [
                'appLink' => $appLink,
                'webLink' => $webLink,
                'fallbackLink' => $fallbackLink,
                'appStoreUrl' => config('app.ios_app_store_url'),
                'playStoreUrl' => config('app.android_play_store_url')
            ]);
        }
        
        // For desktop or when app link is not available, redirect to web link
        return redirect($webLink);
    }

    /**
     * Check if the request is from a mobile device
     */
    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        
        // Check for mobile patterns in user agent
        $mobilePatterns = [
            '/Android/i',
            '/iPhone/i',
            '/iPad/i',
            '/iPod/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/Mobile/i'
        ];
        
        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
}