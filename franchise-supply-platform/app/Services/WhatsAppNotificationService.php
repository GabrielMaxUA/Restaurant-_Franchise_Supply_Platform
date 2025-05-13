<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class WhatsAppNotificationService
{
    /**
     * The Twilio client instance.
     *
     * @var \Twilio\Rest\Client
     */
    protected $twilioClient;
    
    /**
     * The WhatsApp sender phone number.
     *
     * @var string
     */
    protected $fromNumber;

    /**
     * Create a new WhatsApp notification service instance.
     */
    public function __construct()
    {
        if (config('services.twilio.enabled', false)) {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            
            if ($accountSid && $authToken) {
                $this->twilioClient = new TwilioClient($accountSid, $authToken);
                $this->fromNumber = config('services.twilio.whatsapp_from');
            }
        }
    }

    /**
     * Send a new order notification to admin's WhatsApp.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendAdminOrderNotification(Order $order): bool
    {
        if (!$this->canSendWhatsApp()) {
            return false;
        }

        // Get admin users who have phone numbers
        $adminUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })
        ->whereNotNull('phone')
        ->get();
        
        if ($adminUsers->isEmpty()) {
            Log::info('No admin users found with phone numbers for WhatsApp notification');
            return false;
        }

        $template = config('services.twilio.templates.order_notification');
        $success = true;
        
        // Format the template parameters
        $params = [
            $order->id,
            $order->user->franchiseeProfile->company_name ?? $order->user->username,
            '$' . number_format($order->total_amount, 2)
        ];
        
        foreach ($adminUsers as $admin) {
            try {
                $phone = $this->formatPhoneNumber($admin->phone);
                if (!$phone) {
                    continue;
                }
                
                $message = $this->replaceTemplatePlaceholders($template, $params);
                $this->sendWhatsAppMessage($phone, $message);
                
                Log::info('WhatsApp admin notification sent', [
                    'to' => $phone,
                    'message' => $message,
                    'order_id' => $order->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send WhatsApp admin notification: ' . $e->getMessage(), [
                    'admin_id' => $admin->id,
                    'phone' => $admin->phone
                ]);
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Send an order confirmation to customer's WhatsApp.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendCustomerOrderConfirmation(Order $order): bool
    {
        if (!$this->canSendWhatsApp()) {
            return false;
        }

        if (empty($order->user->phone)) {
            Log::info('User has no phone number for WhatsApp notification', [
                'user_id' => $order->user->id
            ]);
            return false;
        }

        $phone = $this->formatPhoneNumber($order->user->phone);
        if (!$phone) {
            return false;
        }

        $template = config('services.twilio.templates.order_confirmation');
        
        // Format the template parameters
        $params = [
            $order->id,
            '$' . number_format($order->total_amount, 2)
        ];

        try {
            $message = $this->replaceTemplatePlaceholders($template, $params);
            $this->sendWhatsAppMessage($phone, $message);
            
            Log::info('WhatsApp customer notification sent', [
                'to' => $phone,
                'message' => $message,
                'order_id' => $order->id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp customer notification: ' . $e->getMessage(), [
                'user_id' => $order->user->id,
                'phone' => $order->user->phone
            ]);
            return false;
        }
    }

    /**
     * Send a WhatsApp message.
     *
     * @param  string  $to
     * @param  string  $message
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    protected function sendWhatsAppMessage(string $to, string $message)
    {
        return $this->twilioClient->messages->create(
            "whatsapp:$to",
            [
                'from' => "whatsapp:{$this->fromNumber}",
                'body' => $message
            ]
        );
    }

    /**
     * Replace template placeholders with actual values.
     *
     * @param  string  $template
     * @param  array  $params
     * @return string
     */
    protected function replaceTemplatePlaceholders(string $template, array $params): string
    {
        foreach ($params as $index => $value) {
            $template = str_replace("{{" . ($index + 1) . "}}", $value, $template);
        }
        return $template;
    }

    /**
     * Format phone number for WhatsApp.
     *
     * @param  string  $phone
     * @return string|null
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if the number is valid
        if (strlen($phone) < 10) {
            return null;
        }
        
        // If the number doesn't start with country code, add +1 (US/Canada)
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }
        
        return '+' . $phone;
    }

    /**
     * Determine if the service can send WhatsApp messages.
     *
     * @return bool
     */
    protected function canSendWhatsApp(): bool
    {
        return config('services.twilio.enabled', false) &&
               $this->twilioClient !== null &&
               $this->fromNumber !== null;
    }
}