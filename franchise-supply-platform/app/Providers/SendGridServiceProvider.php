<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailManager;
use App\Mail\Transport\SendGridTransport;
use SendGrid;

class SendGridServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add custom transport driver for SendGrid
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('sendgrid', function ($config) {
                return new SendGridTransport(
                    $config['api_key'] ?? config('services.sendgrid.api_key')
                );
            });
        });
    }
}