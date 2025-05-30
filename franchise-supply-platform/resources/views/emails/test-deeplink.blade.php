<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Link Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f0f8ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .link-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            word-break: break-all;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #218838;
        }
        .button-secondary {
            background-color: #6c757d;
        }
        .button-secondary:hover {
            background-color: #545b62;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .test-section {
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deep Link Test - Order #{{ $order->id }}</h1>
        
        <div class="info-box">
            <h3>How Deep Linking Works:</h3>
            <p>When users click the link in their email:</p>
            <ul>
                <li><strong>On Desktop:</strong> Redirects to login page, then to order details</li>
                <li><strong>On Mobile:</strong> Shows a page offering to open in app (if installed) or continue in browser</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>Test Links</h2>
            
            @if($isTestMode ?? false)
            <div class="info-box" style="background-color: #fff3cd; border-color: #ffc107;">
                <strong>Testing Mode Active:</strong> Since your app isn't published yet, emails will use direct web links instead of deep links.
            </div>
            @endif
            
            <h3>1. Current Email Link (Testing Mode)</h3>
            <div class="link-box">
                <code>{{ $deepLink }}</code>
            </div>
            <a href="{{ $deepLink }}" class="button" target="_blank">Test Email Link</a>
            <p><small>This is what users will see in emails while in testing mode.</small></p>
            
            <h3>2. Direct App Deep Link</h3>
            <div class="link-box">
                <code>{{ $testAppLink }}</code>
            </div>
            <a href="{{ $testAppLink }}" class="button button-secondary">Test App Deep Link</a>
            <p><small>Use this to test with your local mobile app. The app must be installed and configured to handle <code>restaurantfranchise://</code> URLs.</small></p>
            
            <h3>3. Direct Web Link</h3>
            <div class="link-box">
                <code>{{ $directLink }}</code>
            </div>
            <a href="{{ $directLink }}" class="button button-secondary" target="_blank">Test Direct Link</a>
        </div>

        <div class="test-section">
            <h2>Configuration</h2>
            <p>Add these to your <code>.env</code> file:</p>
            <div class="link-box">
                <pre>APP_DEEP_LINK_SCHEME=restaurantfranchise
APP_IOS_STORE_URL=https://apps.apple.com/app/your-app-id
APP_ANDROID_STORE_URL=https://play.google.com/store/apps/details?id=com.yourcompany.app</pre>
            </div>
        </div>

        <div class="test-section">
            <h2>Mobile App Integration</h2>
            <p>Your mobile app needs to register the URL scheme <code>restaurantfranchise://</code> to handle deep links:</p>
            
            <h4>iOS (Info.plist):</h4>
            <div class="link-box">
                <pre>&lt;key&gt;CFBundleURLTypes&lt;/key&gt;
&lt;array&gt;
    &lt;dict&gt;
        &lt;key&gt;CFBundleURLSchemes&lt;/key&gt;
        &lt;array&gt;
            &lt;string&gt;restaurantfranchise&lt;/string&gt;
        &lt;/array&gt;
    &lt;/dict&gt;
&lt;/array&gt;</pre>
            </div>
            
            <h4>Android (AndroidManifest.xml):</h4>
            <div class="link-box">
                <pre>&lt;intent-filter&gt;
    &lt;action android:name="android.intent.action.VIEW" /&gt;
    &lt;category android:name="android.intent.category.DEFAULT" /&gt;
    &lt;category android:name="android.intent.category.BROWSABLE" /&gt;
    &lt;data android:scheme="restaurantfranchise" /&gt;
&lt;/intent-filter&gt;</pre>
            </div>
        </div>

        <div class="test-section">
            <h2>Testing Methods Without App Stores</h2>
            
            <h3>Option 1: Test with Local Mobile App</h3>
            <ol>
                <li>Install your development app on a mobile device/emulator</li>
                <li>Make sure the app registers the <code>restaurantfranchise://</code> URL scheme</li>
                <li>Click the "Test App Deep Link" button above on the mobile device</li>
                <li>The app should open directly to the order details</li>
            </ol>
            
            <h3>Option 2: Use Browser Developer Tools</h3>
            <ol>
                <li>Open Chrome/Firefox Developer Tools (F12)</li>
                <li>Toggle device toolbar (Ctrl+Shift+M)</li>
                <li>Select a mobile device (iPhone/Android)</li>
                <li>Visit <code>{{ url('/test-deeplink') }}</code></li>
                <li>You'll see the mobile redirect page (without actual app launching)</li>
            </ol>
            
            <h3>Option 3: Manual Testing with ngrok</h3>
            <ol>
                <li>Install ngrok: <code>brew install ngrok</code> (Mac) or download from ngrok.com</li>
                <li>Run: <code>ngrok http 80</code> (or your XAMPP port)</li>
                <li>Use the ngrok URL to test on real mobile devices</li>
                <li>The deep link redirect page will appear on mobile browsers</li>
            </ol>
        </div>

        <div class="info-box">
            <p><strong>Production Mode:</strong> When you're ready to go live, update your <code>.env</code> file with real app store URLs:</p>
            <div class="link-box">
                <pre>APP_IOS_STORE_URL=https://apps.apple.com/app/your-real-app-id
APP_ANDROID_STORE_URL=https://play.google.com/store/apps/details?id=com.yourcompany.app</pre>
            </div>
            <p>The system will automatically switch to full deep linking mode when app store URLs are configured.</p>
        </div>
    </div>
</body>
</html>