# Laravel Webhook Manager

[![Latest Stable Version](https://poser.pugx.org/ayodvr/laravel-webhook-manager/v/stable)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)
[![Total Downloads](https://poser.pugx.org/ayodvr/laravel-webhook-manager/downloads)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)
[![License](https://poser.pugx.org/ayodvr/laravel-webhook-manager/license)](https://packagist.org/packages/ayodvr/laravel-webhook-manager)

A reusable Laravel package for receiving, processing, and managing incoming webhooks reliably. It handles webhooks from multiple providers, prevents duplicates, and ensures high reliability for critical backend systems.

## Features

- **Signature Verification**: Ensures incoming requests are authentic using HMAC-SHA256.
- **Automatic Duplicate Prevention**: Uses unique job IDs to prevent duplicate processing.
- **Reliable Processing**: Queue-based asynchronous processing with automatic retries.
- **Event Dispatching**: Fires Laravel events (`WebhookReceived`) for easy integration.
- **Configurable**: Customizable retry intervals, queue names, signature secrets, and more.
- **Multiple Providers**: Support for Stripe, PayPal, GitHub, and more.

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

### For Paystack Setup

1. **Get your webhook secret** from the Paystack Dashboard → Settings → API Keys & Webhooks
2. **Set webhook URL** to: `https://your-domain.com/webhooks/paystack`
3. **Test webhook** using Paystack's dashboard webhook tester

## Usage

### Receiving Webhooks

Webhooks are automatically handled at the `/webhooks/{provider}` route. For example:

- Stripe: `POST /webhooks/stripe`
- PayPal: `POST /webhooks/paypal`

Configure your webhook providers to send POST requests to these URLs with a `X-Webhook-Signature` header containing the HMAC-SHA256 signature of the request body.

### Listening to Webhooks

Listen to the `WebhookReceived` event to process webhook data:

```php
<?php

namespace App\Listeners;

use Cybrox\WebhookManager\Events\WebhookReceived;

class ProcessStripeWebhook
{
    public function handle(WebhookReceived $event): void
    {
        $webhookEvent = $event->webhookEvent;

        // Process the webhook based on provider and event type
        switch ($webhookEvent->provider) {
            case 'stripe':
                $this->handleStripeWebhook($webhookEvent);
                break;
            case 'paypal':
                $this->handlePaypalWebhook($webhookEvent);
                break;
            // Add more providers as needed
        }
    }

    private function handleStripeWebhook($event)
    {
        $payload = json_decode($event->payload, true);

        // Example: Handle payment succeeded
        if ($event->event_type === 'payment.succeeded') {
            // Update order status, send notifications, etc.
        }
    }
}
```

Register the listener in `EventServiceProvider`:

```php
protected $listen = [
    WebhookReceived::class => [
        ProcessStripeWebhook::class,
    ],
];
```

### Configuration Options

Edit `config/webhook-manager.php` to customize behavior:

```php
return [
    'signature_secret' => env('WEBHOOK_SIGNATURE_SECRET'),
    'max_attempts' => env('WEBHOOK_MAX_ATTEMPTS', 3),
    'queue' => env('WEBHOOK_QUEUE', 'webhooks'),
    'providers' => [
        'stripe',
        'paypal',
        'github',
    ],
];
```


## Security

- Webhooks are verified using HMAC-SHA256 signatures.
- Use HTTPS in production.
- Store signature secrets securely as environment variables.

## Testing

Run the test suite:

```bash
php artisan test tests/Feature/WebhookTest.php
```

## Contributing

Contributions are welcome! Please see the [contributing guide](CONTRIBUTING.md) for more details.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
