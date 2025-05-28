<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\OrderConfirmationEmail;
use App\Services\InvoiceService;

class EmailNotificationService
{
    /**
     * Send order status change notification to the franchisee.
     *
     * @param  \App\Models\Order  $order
     * @param  string|null  $oldStatus
     * @return bool
     */
    public function sendOrderStatusChangeNotification(Order $order, ?string $oldStatus = null): bool
    {
        $customerEmail = $order->user->email;
        if (!$customerEmail || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('No valid customer email found for order status change notification', [
                'order_id' => $order->id,
                'status' => $order->status
            ]);
            return false;
        }

        // Check for duplicate email to prevent sending twice
        $emailKey = "status_email_{$order->id}_{$order->status}_{$customerEmail}";
        if ($this->isEmailAlreadySent($emailKey)) {
            Log::info('Duplicate email prevented for order status change', [
                'order_id' => $order->id,
                'status' => $order->status,
                'email' => $customerEmail
            ]);
            return true; // Return true since email was already sent
        }

        try {
            // Get status details
            $subject = $this->getEmailSubject($order->status);
            $statusMessage = $this->getStatusMessage($order->status);
            $statusColor = $this->getStatusColor($order->status);
            $recipientName = $order->user->franchiseeProfile->contact_name ?? $order->user->username;
            
            // Create the email content
            $emailData = [
                'order' => $order,
                'oldStatus' => $oldStatus,
                'recipientName' => $recipientName,
                'statusMessage' => $statusMessage,
                'statusColor' => $statusColor,
                'trackingUrl' => url('/franchisee/orders/' . $order->id . '/details')
            ];

            // Send the email using Laravel's mail system
            Mail::send('emails.orders.status-change', $emailData, function ($message) use ($customerEmail, $recipientName, $subject) {
                $message->to($customerEmail, $recipientName)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            // Mark email as sent to prevent duplicates
            $this->markEmailAsSent($emailKey);

            Log::info('Order status change email sent successfully', [
                'order_id' => $order->id,
                'recipient' => $customerEmail,
                'new_status' => $order->status,
                'old_status' => $oldStatus
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order status change notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $order->status,
                'old_status' => $oldStatus,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send a new order notification to admin users.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendAdminOrderNotification(Order $order): bool
    {
        // Check for duplicate to prevent sending twice for new orders
        $emailKey = "admin_new_order_{$order->id}";
        if ($this->isEmailAlreadySent($emailKey)) {
            Log::info('Duplicate admin new order email prevented', ['order_id' => $order->id]);
            return true;
        }

        $adminEmails = User::getAdminEmails();
        
        if (empty($adminEmails)) {
            Log::warning('No admin emails found for new order notification');
            return false;
        }

        try {
            $emailData = [
                'order' => $order,
                'franchiseeName' => $order->user->franchiseeProfile->company_name ?? $order->user->username,
                'actionUrl' => url('/admin/orders/' . $order->id),
                'actionText' => 'Review Order'
            ];

            foreach ($adminEmails as $email) {
                Mail::send('emails.orders.new-order', $emailData, function ($message) use ($email, $order) {
                    $message->to($email)
                            ->subject('New Order #' . $order->id . ' - Requires Approval')
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });
            }

            // Mark email as sent
            $this->markEmailAsSent($emailKey);

            Log::info('Admin order notification sent successfully', [
                'order_id' => $order->id,
                'recipients' => $adminEmails
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send admin order notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send a new order notification to warehouse users.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendWarehouseOrderNotification(Order $order): bool
    {
        // Check for duplicate to prevent sending twice for new orders
        $emailKey = "warehouse_new_order_{$order->id}";
        if ($this->isEmailAlreadySent($emailKey)) {
            Log::info('Duplicate warehouse new order email prevented', ['order_id' => $order->id]);
            return true;
        }

        $warehouseEmails = User::getWarehouseEmails();
        
        if (empty($warehouseEmails)) {
            Log::warning('No warehouse emails found for new order notification');
            return false;
        }

        try {
            $emailData = [
                'order' => $order,
                'franchiseeName' => $order->user->franchiseeProfile->company_name ?? $order->user->username,
                'actionUrl' => url('/warehouse/orders/' . $order->id),
                'actionText' => 'View Order'
            ];

            foreach ($warehouseEmails as $email) {
                Mail::send('emails.orders.new-order', $emailData, function ($message) use ($email, $order) {
                    $message->to($email)
                            ->subject('New Order #' . $order->id . ' - For Processing')
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });
            }

            // Mark email as sent
            $this->markEmailAsSent($emailKey);

            Log::info('Warehouse order notification sent successfully', [
                'order_id' => $order->id,
                'recipients' => $warehouseEmails
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send warehouse order notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);
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

        // Check for duplicate to prevent sending twice for new orders
        $emailKey = "customer_confirmation_{$order->id}_{$customerEmail}";
        if ($this->isEmailAlreadySent($emailKey)) {
            Log::info('Duplicate customer confirmation email prevented', [
                'order_id' => $order->id,
                'email' => $customerEmail
            ]);
            return true;
        }

        try {
            Mail::to($customerEmail)->send(new OrderConfirmationEmail($order));
            
            // Mark email as sent
            $this->markEmailAsSent($emailKey);
            
            Log::info('Order confirmation email sent successfully', [
                'order_id' => $order->id,
                'recipient' => $customerEmail
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get email subject based on order status
     */
    protected function getEmailSubject(string $status): string
    {
        $subjects = [
            'pending' => 'Order Submitted - Awaiting Approval',
            'approved' => 'Order Approved - Being Processed',
            'rejected' => 'Order Rejected - Action Required',
            'packed' => 'Order Packed - Ready for Shipping',
            'shipped' => 'Order Shipped - On the Way',
            'delivered' => 'Order Delivered - Thank You!',
            'cancelled' => 'Order Cancelled'
        ];

        return $subjects[$status] ?? 'Order Status Update';
    }

    /**
     * Get status message for email
     */
    protected function getStatusMessage(string $status): string
    {
        $messages = [
            'pending' => 'Your order has been submitted and is awaiting approval from our team.',
            'approved' => 'Great news! Your order has been approved and is now being processed.',
            'rejected' => 'Unfortunately, your order has been rejected. Please contact our support team for more information.',
            'packed' => 'Your order has been packed and is ready for shipping.',
            'shipped' => 'Your order has been shipped and is on its way to you!',
            'delivered' => 'Your order has been delivered successfully. Thank you for your business!',
            'cancelled' => 'Your order has been cancelled.'
        ];

        return $messages[$status] ?? 'Your order status has been updated.';
    }

    /**
     * Get status color for email styling
     */
    protected function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => '#ffa500',
            'approved' => '#28a745',
            'rejected' => '#dc3545',
            'packed' => '#17a2b8',
            'shipped' => '#007bff',
            'delivered' => '#28a745',
            'cancelled' => '#6c757d'
        ];

        return $colors[$status] ?? '#333333';
    }

    /**
     * Check if an email has already been sent to prevent duplicates
     */
    private function isEmailAlreadySent(string $emailKey): bool
    {
        return Cache::has($emailKey);
    }

    /**
     * Mark an email as sent to prevent duplicates
     */
    private function markEmailAsSent(string $emailKey): void
    {
        // Store in cache for 24 hours to prevent duplicates within a day
        Cache::put($emailKey, true, now()->addHours(24));
    }

    /**
     * Send invoice email with PDF attachment when order is approved
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function sendInvoiceEmail(Order $order): bool
    {
        $customerEmail = $order->user->email;
        if (!$customerEmail || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('No valid customer email found for invoice email', [
                'order_id' => $order->id
            ]);
            return false;
        }

        // Check for duplicate email to prevent sending twice
        $emailKey = "invoice_email_{$order->id}_{$customerEmail}";
        if ($this->isEmailAlreadySent($emailKey)) {
            Log::info('Duplicate invoice email prevented', [
                'order_id' => $order->id,
                'email' => $customerEmail
            ]);
            return true;
        }

        $invoiceService = new InvoiceService();
        $invoicePath = null;

        try {
            // Generate the invoice PDF
            $invoicePath = $invoiceService->generateInvoicePDF($order);
            
            if (!$invoicePath || !file_exists($invoicePath)) {
                Log::error('Invoice PDF generation failed or file not found', [
                    'order_id' => $order->id,
                    'invoice_path' => $invoicePath
                ]);
                return false;
            }

            $recipientName = $order->user->franchiseeProfile->contact_name ?? $order->user->username;
            $invoiceNumber = $order->invoice_number ?? config('company.invoice_prefix', 'INV-') . $order->id . '-' . date('Ymd');
            
            // Email data
            $emailData = [
                'order' => $order,
                'recipientName' => $recipientName,
                'invoiceNumber' => $invoiceNumber,
                'trackingUrl' => url('/franchisee/orders/' . $order->id . '/details')
            ];

            // Send the email with attachment
            Mail::send('emails.orders.invoice', $emailData, function ($message) use ($customerEmail, $recipientName, $invoicePath, $invoiceNumber) {
                $message->to($customerEmail, $recipientName)
                        ->subject('Invoice ' . $invoiceNumber . ' - Order Approved')
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->attach($invoicePath, [
                            'as' => 'invoice_' . $invoiceNumber . '.pdf',
                            'mime' => 'application/pdf'
                        ]);
            });

            // Mark email as sent to prevent duplicates
            $this->markEmailAsSent($emailKey);

            Log::info('Invoice email sent successfully', [
                'order_id' => $order->id,
                'recipient' => $customerEmail,
                'invoice_number' => $invoiceNumber
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        } finally {
            // Clean up the temporary invoice file
            if ($invoicePath && file_exists($invoicePath)) {
                $invoiceService->deleteTemporaryInvoice($invoicePath);
            }
        }
    }
}