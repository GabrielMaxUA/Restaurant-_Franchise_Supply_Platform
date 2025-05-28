<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate a PDF invoice for an order
     *
     * @param Order $order
     * @return string|null Returns the file path of the generated PDF or null on failure
     */
    public function generateInvoicePDF(Order $order): ?string
    {
        try {
            // Only generate invoice for approved orders
            if (!in_array($order->status, ['approved', 'packed', 'shipped', 'delivered'])) {
                Log::warning('Attempted to generate invoice for non-approved order', [
                    'order_id' => $order->id,
                    'status' => $order->status
                ]);
                return null;
            }

            // Load necessary relationships
            $order->load(['items.product', 'items.variant', 'user.franchiseeProfile']);

            // Generate invoice number if not already set
            $invoiceNumber = $order->invoice_number ?? config('company.invoice_prefix', 'INV-') . $order->id . '-' . date('Ymd');

            // Get the admin profile for company information
            $adminUser = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->first();

            $adminDetail = null;
            if ($adminUser) {
                $adminDetail = $adminUser->adminDetail;
            }

            // Prepare data for the invoice
            $data = [
                'order' => $order,
                'invoiceNumber' => $invoiceNumber,
                'adminDetail' => $adminDetail,
                'currentDate' => date('F d, Y'),
                'dueDate' => date('F d, Y', strtotime('+30 days'))
            ];

            // Generate PDF
            $pdf = Pdf::loadView('franchisee.invoice', $data);
            $pdf->setPaper('a4', 'portrait');
            
            // Create a unique filename
            $filename = 'invoices/invoice_' . $order->id . '_' . date('YmdHis') . '.pdf';
            
            // Store the PDF in the storage/app/invoices directory
            $path = storage_path('app/' . $filename);
            
            // Create directory if it doesn't exist
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save the PDF
            $pdf->save($path);
            
            Log::info('Invoice PDF generated successfully', [
                'order_id' => $order->id,
                'invoice_number' => $invoiceNumber,
                'file_path' => $filename
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Delete a temporary invoice file
     *
     * @param string $filepath
     * @return void
     */
    public function deleteTemporaryInvoice(string $filepath): void
    {
        try {
            if (file_exists($filepath)) {
                unlink($filepath);
                Log::info('Temporary invoice file deleted', ['filepath' => $filepath]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete temporary invoice file', [
                'filepath' => $filepath,
                'error' => $e->getMessage()
            ]);
        }
    }
}