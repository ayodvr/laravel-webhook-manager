# Laravel Webhook Manager

[![Latest Stable Version](https://poser.pugx.org/ayodvr/laravel-webhook-manager/v/stable)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)
[![Total Downloads](https://poser.pugx.org/ayodvr/laravel-webhook-manager/downloads)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)
[![License](https://poser.pugx.org/ayodvr/laravel-webhook-manager/license)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)

A reusable Laravel package for receiving, processing, and managing incoming webhooks reliably. It handles webhooks from multiple providers, prevents duplicates, and ensures high reliability for critical backend systems.

## Features

- **Signature Verification**: Ensures incoming requests are authentic using HMAC-SHA256.
- **Automatic Duplicate Prevention**: Uses unique provider IDs (Stripe ID, GitHub Delivery ID, etc.) or signatures to prevent duplicate processing.
- **Reliable Processing**: Queue-based asynchronous processing with automatic status tracking and retries.
- **Event Dispatching**: Fires Laravel events (`WebhookReceived`) for easy integration.
- **Configurable**: Customizable route prefix, retry intervals, queue names, and signature secrets.
- **Multiple Providers**: Built-in support for Paystack, Stripe, PayPal, GitHub, and more.
- **Interactive Demo**: Includes a built-in dashboard to simulate and monitor webhooks.

## Installation

### Requirements

- **PHP**: ^8.2
- **Laravel**: ^10.0 || ^11.0 || ^12.0

### Via Composer

```bash
composer require ayodvr/laravel-webhook-manager
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=webhook-config
```

### Publish Migration

Run the migration to create the webhook events table:

```bash
php artisan migrate
```

Set your webhook signature secret in your `.env` file:

```env
WEBHOOK_SIGNATURE_SECRET=your-secret-key-here
```

## Interactive Demo

The package comes with a built-in demo dashboard to help you test and visualize the webhook flow.

1. **Start the server**:
   ```bash
   php artisan serve
   ```
2. **Access the dashboard**:
   Navigate to `http://127.0.0.1:8000/demo` in your browser.
3. **Simulate Webhooks**:
   Click the **"Simulate Paystack Payment"** button to trigger a mock signed webhook and watch it process in real-time.

## Usage

### Receiving Webhooks

Webhooks are automatically handled at the `/webhooks/{provider}` route (configurable via `route_prefix`). For example:

- Paystack: `POST /webhooks/paystack`
- Stripe: `POST /webhooks/stripe`
- PayPal: `POST /webhooks/paypal`

### Listening to Webhooks

Listen to the `WebhookReceived` event in your `EventServiceProvider` or using a Listener:

```php
<?php

namespace App\Listeners;

use Cybrox\WebhookManager\Events\WebhookReceived;

class ProcessWebhook
{
    public function handle(WebhookReceived $event): void
    {
        $webhookEvent = $event->webhookEvent;

        // Process based on provider
        if ($webhookEvent->provider === 'paystack') {
            $payload = json_decode($webhookEvent->payload, true);
            // Your logic here...
        }
    }
}
```

### Configuration Options

Edit `config/webhook-manager.php` to customize behavior:

```php
return [
    'route_prefix' => env('WEBHOOK_ROUTE_PREFIX', 'webhooks'),
    'signature_secret' => env('WEBHOOK_SIGNATURE_SECRET'),
    'max_attempts' => env('WEBHOOK_MAX_ATTEMPTS', 3),
    'queue' => env('WEBHOOK_QUEUE', 'webhooks'),
    'providers' => ['stripe', 'paypal', 'github', 'paystack'],
];
```

## Testing

Run the test suite:

```bash
php artisan test
```

## Contributing

Contributions are welcome! Please see the [contributing guide](CONTRIBUTING.md) for more details.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
