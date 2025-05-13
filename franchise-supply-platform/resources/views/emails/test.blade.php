<x-mail::message>
# Email Configuration Test

This email confirms that your email logging/sending system is working correctly.

## System Configuration
- **Mail Driver**: {{ $config['mail_driver'] }}
- **Mail Mailer**: {{ $config['mail_mailer'] }}
- **Mail Host**: {{ $config['mail_host'] }}
- **Mail Port**: {{ $config['mail_port'] }}
- **Mail From**: {{ $config['mail_from'] }}
- **Mail Encryption**: {{ $config['mail_encryption'] }}
- **Notifications Enabled**: {{ $config['notifications_enabled'] ? 'Yes' : 'No' }}
- **Admin Email**: {{ $config['admin_email'] }}
- **Warehouse Email**: {{ $config['warehouse_email'] }}

<x-mail::button :url="url('/')">
Visit Dashboard
</x-mail::button>

This is a test email to verify your notification system. If you received this, your email configuration is working correctly.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
