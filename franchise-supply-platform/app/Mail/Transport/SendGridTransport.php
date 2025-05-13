<?php

namespace App\Mail\Transport;

use SendGrid;
use SendGrid\Mail\Mail as SendGridMail;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class SendGridTransport extends Transport
{
    /**
     * The SendGrid API client instance.
     *
     * @var \SendGrid
     */
    protected $sendGrid;
    
    /**
     * The API key for SendGrid.
     * 
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new SendGrid transport instance.
     *
     * @param  string  $apiKey
     * @return void
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->sendGrid = new SendGrid($this->apiKey);
    }

    /**
     * {@inheritdoc}
     */
    public function send(SentMessage $message, array $failedRecipients = []): ?SentMessage
    {
        $email = $message->getOriginalMessage();

        $sgMail = new SendGridMail();

        // Set From
        $from = $email->getFrom();
        if (count($from) > 0) {
            $sgMail->setFrom($from[0]->getAddress(), $from[0]->getName() ?: null);
        }

        // Set Subject
        $sgMail->setSubject($email->getSubject() ?: '');

        // Set To Recipients
        foreach ($email->getTo() as $to) {
            $sgMail->addTo($to->getAddress(), $to->getName() ?: null);
        }

        // Set CC Recipients
        foreach ($email->getCc() as $cc) {
            $sgMail->addCc($cc->getAddress(), $cc->getName() ?: null);
        }

        // Set BCC Recipients
        foreach ($email->getBcc() as $bcc) {
            $sgMail->addBcc($bcc->getAddress(), $bcc->getName() ?: null);
        }

        // Set Reply-To
        $replyTo = $email->getReplyTo();
        if (count($replyTo) > 0) {
            $sgMail->setReplyTo($replyTo[0]->getAddress(), $replyTo[0]->getName() ?: null);
        }

        // Set Content (HTML and Plain Text)
        $this->setContent($email, $sgMail);

        // Set Attachments
        $this->setAttachments($email, $sgMail);

        // Set tracking settings
        $this->setTrackingSettings($sgMail);

        // Set custom headers
        foreach ($email->getHeaders()->all() as $header) {
            if (!in_array($header->getName(), ['From', 'To', 'Cc', 'Bcc', 'Subject', 'Content-Type'])) {
                $sgMail->addHeader($header->getName(), $header->getBodyAsString());
            }
        }

        // Send the message
        $response = $this->sendGrid->send($sgMail);
        
        // Log response status and message ID for tracking
        $statusCode = $response->statusCode();
        $headers = $response->headers();
        $messageId = isset($headers['X-Message-Id']) ? $headers['X-Message-Id'] : null;
        
        if ($statusCode >= 200 && $statusCode < 300) {
            Log::info('SendGrid email sent successfully', [
                'status_code' => $statusCode,
                'message_id' => $messageId,
                'to' => $email->getTo(),
                'subject' => $email->getSubject()
            ]);
        } else {
            $body = json_decode($response->body(), true);
            Log::error('SendGrid email sending failed', [
                'status_code' => $statusCode,
                'error' => $body['errors'] ?? 'Unknown error',
                'to' => $email->getTo(),
                'subject' => $email->getSubject()
            ]);
            
            // Collect failed recipients for reporting
            foreach ($email->getTo() as $to) {
                $failedRecipients[] = $to->getAddress();
            }
        }

        return $message;
    }

    /**
     * Set the content on the SendGrid message.
     *
     * @param  \Symfony\Component\Mime\Email  $email
     * @param  \SendGrid\Mail\Mail  $sgMail
     * @return void
     */
    protected function setContent(Email $email, SendGridMail $sgMail): void
    {
        if ($email->getHtmlBody()) {
            $sgMail->addContent('text/html', $email->getHtmlBody());
        }

        if ($email->getTextBody()) {
            $sgMail->addContent('text/plain', $email->getTextBody());
        }

        // If no content was set, set empty plain text content to satisfy SendGrid API
        if (!$email->getHtmlBody() && !$email->getTextBody()) {
            $sgMail->addContent('text/plain', ' ');
        }
    }

    /**
     * Set the attachments on the SendGrid message.
     *
     * @param  \Symfony\Component\Mime\Email  $email
     * @param  \SendGrid\Mail\Mail  $sgMail
     * @return void
     */
    protected function setAttachments(Email $email, SendGridMail $sgMail): void
    {
        foreach ($email->getAttachments() as $attachment) {
            $sgMail->addAttachment(
                base64_encode($attachment->getBody()),
                $attachment->getContentType(),
                $attachment->getName() ?: 'attachment',
                'attachment'
            );
        }
    }

    /**
     * Set tracking settings on the SendGrid message.
     *
     * @param  \SendGrid\Mail\Mail  $sgMail
     * @return void
     */
    protected function setTrackingSettings(SendGridMail $sgMail): void
    {
        // Set up click tracking
        if (config('services.sendgrid.tracking.click_tracking', true)) {
            $sgMail->setClickTracking(true, true);
        }

        // Set up open tracking
        if (config('services.sendgrid.tracking.open_tracking', true)) {
            $sgMail->setOpenTracking(true);
        }
    }
}