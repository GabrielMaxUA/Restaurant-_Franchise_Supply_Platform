<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The system configuration information.
     *
     * @var array
     */
    public $config;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->config = [
            'mail_driver' => config('mail.default'),
            'mail_mailer' => config('mail.mailer'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_from' => config('mail.from.address'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'notifications_enabled' => config('company.notifications_enabled', true),
            'admin_email' => config('company.admin_notification_email', 'maxgabrielua@gmail.com'),
            'warehouse_email' => config('company.warehouse_notification_email', 'warehouse@restaurantfranchisesupply.com'),
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.test',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
