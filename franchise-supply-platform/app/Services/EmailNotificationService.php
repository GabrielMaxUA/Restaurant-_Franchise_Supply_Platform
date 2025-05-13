<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationEmail;
use App\Notifications\NewOrderNotification;

class EmailNotificationService
{
    /**
     * Create a new email notification service instance.
     */
    public function __construct()
    {
        // No initialization needed for standard Laravel mail
    }

    /**
     * Send a new order notification to all admin users.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendAdminOrderNotification(Order $order): bool
    {
        // Collect admin emails with fallbacks
        $adminEmails = [];
        
        // Get admin emails from database
        $dbAdminEmails = User::getAdminEmails();
        Log::info('Found ' . count($dbAdminEmails) . ' admin emails in database');

        // Add database emails if they exist
        if (!empty($dbAdminEmails)) {
            $adminEmails = array_merge($adminEmails, $dbAdminEmails);
        }

        // Add fallback from environment if no admin emails found
        if (empty($adminEmails)) {
            $configAdminEmail = config('company.admin_notification_email') ?: env('ADMIN_EMAIL');
            if ($configAdminEmail) {
                Log::info('Using fallback admin email from config: ' . $configAdminEmail);
                $adminEmails[] = $configAdminEmail;
            }
        }

        // Remove duplicate emails and ensure valid format
        $validAdminEmails = array_unique(array_filter($adminEmails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
        
        if (empty($validAdminEmails)) {
            Log::warning('No valid admin emails found for order notification');
            return false;
        }

        try {
            // Use Laravel mail system
            foreach ($validAdminEmails as $email) {
                $this->sendEmail([$email], new NewOrderNotification($order, true));
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send admin order notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a new order notification to all warehouse users.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendWarehouseOrderNotification(Order $order): bool
    {
        // Collect warehouse emails with fallbacks
        $warehouseEmails = [];
        
        // Get warehouse emails from database
        $dbWarehouseEmails = User::getWarehouseEmails();
        Log::info('Found ' . count($dbWarehouseEmails) . ' warehouse emails in database');

        // Add database emails if they exist
        if (!empty($dbWarehouseEmails)) {
            $warehouseEmails = array_merge($warehouseEmails, $dbWarehouseEmails);
        }

        // Add fallback from environment if no warehouse emails found
        if (empty($warehouseEmails)) {
            $fallbackEmail = config('company.warehouse_notification_email') ?: env('WAREHOUSE_EMAIL');
            if ($fallbackEmail) {
                Log::info('Using fallback warehouse email from config: ' . $fallbackEmail);
                $warehouseEmails[] = $fallbackEmail;
            }
        }

        // Remove duplicate emails and ensure valid format
        $validWarehouseEmails = array_unique(array_filter($warehouseEmails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
        
        if (empty($validWarehouseEmails)) {
            Log::warning('No valid warehouse emails found for order notification');
            return false;
        }

        try {
            // Use Laravel mail system
            foreach ($validWarehouseEmails as $email) {
                $this->sendEmail([$email], new NewOrderNotification($order, false));
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send warehouse order notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an order confirmation to the customer.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendCustomerOrderConfirmation(Order $order): bool
    {
        $customerEmail = $order->user->email;
        if (!$customerEmail || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('No valid customer email found for order confirmation');
            return false;
        }

        try {
            // Use Laravel mail system
            return $this->sendEmail([$customerEmail], new OrderConfirmationEmail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send customer order confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a standard email through Laravel's mail system.
     *
     * @param  array  $to
     * @param  object  $mailable
     * @return bool
     */
    protected function sendEmail(array $to, $mailable): bool
    {
        try {
            Mail::to($to)->send($mailable);

            // If no exception was thrown, consider it successful
            Log::info('Email sent successfully', [
                'to' => $to,
                'subject' => $mailable->envelope->subject ?? 'Unknown subject',
                'type' => get_class($mailable)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'to' => $to
            ]);
            return false;
        }
    }

    /**
     * Prepare template data for admin order notification.
     *
     * @param  \App\Models\Order  $order
     * @return array
     */
    protected function prepareAdminTemplateData(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'order_date' => $order->created_at->format('F j, Y, g:i a'),
            'order_status' => ucfirst($order->status),
            'order_total' => '$' . number_format($order->total_amount, 2),
            'franchisee_name' => $order->user->franchiseeProfile->company_name ?? $order->user->username,
            'franchisee_email' => $order->user->email,
            'franchisee_phone' => $order->user->phone,
            'delivery_address' => $order->shipping_address,
            'delivery_city' => $order->shipping_city,
            'delivery_state' => $order->shipping_state,
            'delivery_zip' => $order->shipping_zip,
            'delivery_date' => $order->delivery_date ? date('F j, Y', strtotime($order->delivery_date)) : 'Not specified',
            'is_express' => $order->delivery_preference === 'express',
            'item_count' => $order->items->sum('quantity'),
            'order_url' => url('/admin/orders/' . $order->id),
            'recipient_type' => 'Admin'
        ];
    }

    /**
     * Prepare template data for warehouse order notification.
     *
     * @param  \App\Models\Order  $order
     * @return array
     */
    protected function prepareWarehouseTemplateData(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'order_date' => $order->created_at->format('F j, Y, g:i a'),
            'order_status' => ucfirst($order->status),
            'order_total' => '$' . number_format($order->total_amount, 2),
            'franchisee_name' => $order->user->franchiseeProfile->company_name ?? $order->user->username,
            'franchisee_email' => $order->user->email,
            'franchisee_phone' => $order->user->phone,
            'delivery_address' => $order->shipping_address,
            'delivery_city' => $order->shipping_city,
            'delivery_state' => $order->shipping_state,
            'delivery_zip' => $order->shipping_zip,
            'delivery_date' => $order->delivery_date ? date('F j, Y', strtotime($order->delivery_date)) : 'Not specified',
            'is_express' => $order->delivery_preference === 'express',
            'item_count' => $order->items->sum('quantity'),
            'order_url' => url('/warehouse/orders/' . $order->id),
            'recipient_type' => 'Warehouse'
        ];
    }

    /**
     * Prepare template data for customer order confirmation.
     *
     * @param  \App\Models\Order  $order
     * @return array
     */
    protected function prepareCustomerTemplateData(Order $order): array
    {
        // Format delivery time
        $deliveryTimeMap = [
            'morning' => 'Morning (8:00 AM - 12:00 PM)',
            'afternoon' => 'Afternoon (12:00 PM - 4:00 PM)',
            'evening' => 'Evening (4:00 PM - 8:00 PM)',
        ];
        $deliveryTime = $deliveryTimeMap[$order->delivery_time] ?? $order->delivery_time ?? 'Not specified';
        
        // Calculate order subtotal and shipping
        $shippingCost = $order->shipping_cost ?? 0;
        $subtotal = $order->total_amount - $shippingCost;
        
        return [
            'recipient_name' => $order->user->franchiseeProfile->contact_name ?? $order->user->username,
            'order_id' => $order->id,
            'order_date' => $order->created_at->format('F j, Y, g:i a'),
            'order_total' => '$' . number_format($order->total_amount, 2),
            'delivery_address' => $order->shipping_address,
            'delivery_city' => $order->shipping_city,
            'delivery_state' => $order->shipping_state,
            'delivery_zip' => $order->shipping_zip,
            'delivery_date' => $order->delivery_date ? date('F j, Y', strtotime($order->delivery_date)) : 'Not specified',
            'delivery_time' => $deliveryTime,
            'is_express' => $order->delivery_preference === 'express',
            'subtotal' => '$' . number_format($subtotal, 2),
            'shipping_cost' => '$' . number_format($shippingCost, 2),
            'tracking_url' => url('/franchisee/orders/' . $order->id . '/details'),
        ];
    }

    // Remove the usesSendGrid method as we're now using standard Laravel mail
}