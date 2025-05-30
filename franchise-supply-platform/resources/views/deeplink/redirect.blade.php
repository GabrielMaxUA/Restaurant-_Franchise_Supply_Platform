<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opening {{ config('app.name') }}...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            margin: 1rem;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .button-secondary {
            background-color: #6c757d;
        }
        .button-secondary:hover {
            background-color: #545b62;
        }
        .store-buttons {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .store-buttons p {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .store-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .store-badge {
            height: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="/images/myLogo.png" alt="{{ config('app.name') }}" class="logo">
        
        <h1>Opening {{ config('app.name') }}...</h1>
        
        <p>We're trying to open your order in the app. If it doesn't open automatically, you can:</p>
        
        <div class="button-group">
            <a href="{{ $appLink }}" class="button" id="appLink">Open in App</a>
            <a href="{{ $webLink }}" class="button button-secondary">Continue in Browser</a>
        </div>
        
        @if($appStoreUrl || $playStoreUrl)
        <div class="store-buttons">
            <p>Don't have the app yet?</p>
            <div class="store-links">
                @if($appStoreUrl && $appStoreUrl !== '#')
                    <a href="{{ $appStoreUrl }}" target="_blank">
                        <img src="https://developer.apple.com/app-store/marketing/guidelines/images/badge-download-on-the-app-store.svg" 
                             alt="Download on the App Store" class="store-badge">
                    </a>
                @endif
                @if($playStoreUrl && $playStoreUrl !== '#')
                    <a href="{{ $playStoreUrl }}" target="_blank">
                        <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" 
                             alt="Get it on Google Play" class="store-badge">
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <script>
        // Attempt to open the app automatically
        (function() {
            var appLink = '{{ $appLink }}';
            var webLink = '{{ $webLink }}';
            var clicked = false;
            
            // Try to open the app
            function tryOpenApp() {
                // Create an invisible iframe to attempt app launch
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = appLink;
                document.body.appendChild(iframe);
                
                // Set a timeout to redirect to web if app doesn't open
                setTimeout(function() {
                    document.body.removeChild(iframe);
                    if (!clicked) {
                        // Check if the page is still visible (app didn't open)
                        if (!document.hidden && document.visibilityState === 'visible') {
                            // App didn't open, but don't auto-redirect
                            // User can choose to click browser link
                        }
                    }
                }, 2000);
            }
            
            // Mark when user clicks a button
            document.querySelectorAll('.button').forEach(function(button) {
                button.addEventListener('click', function() {
                    clicked = true;
                });
            });
            
            // Try to open app on page load
            tryOpenApp();
            
            // Also handle the app link click
            document.getElementById('appLink').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = appLink;
                
                // Fallback to web after a delay if app doesn't open
                setTimeout(function() {
                    if (!document.hidden && document.visibilityState === 'visible') {
                        window.location.href = webLink;
                    }
                }, 1000);
            });
        })();
    </script>
</body>
</html>