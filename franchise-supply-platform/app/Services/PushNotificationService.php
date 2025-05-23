<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PushNotificationService
{
    private $projectId;
    private $serviceAccountPath;
    private $fcmUrl;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->serviceAccountPath = config('services.firebase.credentials_path');
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    /**
     * Send push notification to franchisee about order status change
     */
    public function sendOrderStatusNotification(Order $order, string $previousStatus = null)
    {
        $franchisee = $order->user;
        
        if (!$franchisee || !$franchisee->fcm_token) {
            Log::info('No FCM token found for franchisee', ['user_id' => $franchisee?->id]);
            return false;
        }

        $notification = $this->buildOrderStatusNotification($order, $previousStatus);
        
        return $this->sendNotification($franchisee->fcm_token, $notification);
    }

    /**
     * Send invoice ready notification
     */
    public function sendInvoiceReadyNotification(Order $order)
    {
        $franchisee = $order->user;
        
        if (!$franchisee || !$franchisee->fcm_token) {
            Log::info('No FCM token found for franchisee', ['user_id' => $franchisee?->id]);
            return false;
        }

        $notification = [
            'title' => 'Invoice Ready',
            'body' => "Your invoice for order #{$order->id} is ready for download.",
            'order_id' => $order->id,
            'notification_type' => 'invoice_ready',
            'click_action' => 'ORDER_DETAILS'
        ];

        return $this->sendNotification($franchisee->fcm_token, $notification);
    }

    /**
     * Build notification content based on order status
     */
    private function buildOrderStatusNotification(Order $order, ?string $previousStatus)
    {
        $statusMessages = [
            'pending' => 'Your order has been submitted and is awaiting approval.',
            'approved' => 'Great news! Your order has been approved and is being prepared.',
            'rejected' => 'Your order has been rejected. Please contact support for details.',
            'packed' => 'Your order has been packed and is ready for shipping.',
            'shipped' => 'Your order is on its way! Track your shipment for updates.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled.'
        ];

        $title = $this->getStatusTitle($order->status);
        $body = $statusMessages[$order->status] ?? "Order #{$order->id} status updated to {$order->status}.";

        return [
            'title' => $title,
            'body' => $body,
            'order_id' => $order->id,
            'notification_type' => 'order_status_change',
            'new_status' => $order->status,
            'previous_status' => $previousStatus,
            'click_action' => 'ORDER_DETAILS'
        ];
    }

    /**
     * Get notification title based on status
     */
    private function getStatusTitle(string $status): string
    {
        $titles = [
            'pending' => 'Order Submitted',
            'approved' => 'Order Approved',
            'rejected' => 'Order Rejected',
            'packed' => 'Order Packed',
            'shipped' => 'Order Shipped',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled'
        ];

        return $titles[$status] ?? 'Order Update';
    }

    /**
     * Send notification via FCM API V1
     */
    private function sendNotification(string $fcmToken, array $notification): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                Log::error('Failed to get OAuth2 access token');
                return false;
            }

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                    ],
                    'data' => [
                        'order_id' => (string) $notification['order_id'],
                        'notification_type' => $notification['notification_type'],
                        'new_status' => $notification['new_status'] ?? '',
                        'previous_status' => $notification['previous_status'] ?? '',
                        'click_action' => $notification['click_action'],
                    ],
                    'android' => [
                        'priority' => 'high', // ✅ Move priority here (outside notification)
                        'notification' => [
                            'icon' => $this->getStatusIcon($notification['new_status'] ?? 'default'),
                            'sound' => 'default',
                            'click_action' => $notification['click_action'],
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                                'alert' => [
                                    'title' => $notification['title'],
                                    'body' => $notification['body']
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                Log::info('Push notification sent successfully (FCM V1)', [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'notification_type' => $notification['notification_type'],
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send push notification (FCM V1)', [
                    'response_body' => $response->body(),
                    'status' => $response->status(),
                    'notification_type' => $notification['notification_type']
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Push notification error (FCM V1)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get OAuth2 access token using service account
     */
    private function getAccessToken(): ?string
    {
        // Cache the token for 50 minutes (tokens expire in 1 hour)
        return Cache::remember('firebase_access_token', 50 * 60, function () {
            try {
                if (!file_exists($this->serviceAccountPath)) {
                    Log::error('Firebase service account file not found', [
                        'path' => $this->serviceAccountPath
                    ]);
                    return null;
                }

                $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
                
                if (!$serviceAccount) {
                    Log::error('Invalid Firebase service account JSON');
                    return null;
                }

                $jwt = $this->createJWT($serviceAccount);
                
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('OAuth2 token obtained successfully');
                    return $data['access_token'];
                } else {
                    Log::error('Failed to get OAuth2 token', [
                        'response' => $response->body(),
                        'status' => $response->status()
                    ]);
                    return null;
                }
            } catch (\Exception $e) {
                Log::error('OAuth2 token error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return null;
            }
        });
    }

    /**
     * Create JWT for OAuth2 authentication
     */
    private function createJWT(array $serviceAccount): string
    {
        $now = time();
        
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600 // 1 hour
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = '';
        $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
        
        openssl_sign(
            $headerEncoded . '.' . $payloadEncoded,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );
        
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get notification icon based on status
     */
    private function getStatusIcon(string $status): string
    {
        $icons = [
            'pending' => 'ic_pending',
            'approved' => 'ic_approved',
            'rejected' => 'ic_rejected',
            'packed' => 'ic_packed',
            'shipped' => 'ic_shipped',
            'delivered' => 'ic_delivered',
            'cancelled' => 'ic_cancelled'
        ];

        return $icons[$status] ?? 'ic_notification';
    }

    /**
     * Send notification to multiple tokens (for admin/warehouse notifications)
     */
    public function sendMulticastNotification(array $fcmTokens, array $notification): bool
    {
        if (empty($fcmTokens)) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }

        $successCount = 0;
        
        // FCM V1 doesn't support multicast, so we send individual messages
        foreach ($fcmTokens as $token) {
            if ($this->sendNotification($token, $notification)) {
                $successCount++;
            }
        }

        return $successCount > 0;
    }

    /**
     * Test notification for development
     */
    public function sendTestNotification(string $fcmToken): bool
    {
        $notification = [
            'title' => 'Test Notification',
            'body' => 'This is a test push notification from your restaurant supply app.',
            'order_id' => 0,
            'notification_type' => 'test',
            'click_action' => 'MAIN_ACTIVITY'
        ];

        return $this->sendNotification($fcmToken, $notification);
    }

    /**
     * Validate FCM token format
     */
    public function isValidFcmToken(string $token): bool
    {
        // FCM registration tokens are typically 152+ characters long
        return strlen($token) >= 140 && preg_match('/^[A-Za-z0-9_:-]+$/', $token);
    }
}