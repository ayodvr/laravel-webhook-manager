<?php

namespace Cybrox\WebhookManager\Jobs;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Cybrox\WebhookManager\Events\WebhookReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to process a webhook event asynchronously
 */
class ProcessWebhook implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The webhook event instance.
     */
    public WebhookEvent $webhookEvent;

    /**
     * The number of tries for this job.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(WebhookEvent $webhookEvent)
    {
        $this->webhookEvent = $webhookEvent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update attempts count
        $this->webhookEvent->increment('attempts');

        // Parse payload for processing
        $payload = json_decode($this->webhookEvent->payload, true);

        // Basic provider-specific processing examples
        // Override this method in your custom job or listen to WebhookReceived event
        switch ($this->webhookEvent->provider) {
            case 'paystack':
                $this->processPaystackWebhook($payload);
                break;
            case 'stripe':
                $this->processStripeWebhook($payload);
                break;
            default:
                // Handle other providers or log for manual processing
                $this->processGenericWebhook($payload);
        }

        // Fire the WebhookReceived event for additional custom handling
        event(new WebhookReceived($this->webhookEvent));

        // If we reach here, processing was successful
        $this->webhookEvent->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Process Paystack-specific webhook events
     */
    protected function processPaystackWebhook(array $payload): void
    {
        // Example: Handle charge success
        if (($payload['event'] ?? '') === 'charge.success') {
            // Access customer email: $payload['data']['customer']['email']
            // Access amount: $payload['data']['amount'] / 100 (convert kobo to naira)
            // Update order status, send notifications, etc.
        }

        // Example: Handle subscription events
        if (str_contains($payload['event'] ?? '', 'subscription')) {
            // Handle subscription logic
        }
    }

    /**
     * Process Stripe-specific webhook events
     */
    protected function processStripeWebhook(array $payload): void
    {
        // Example: Handle payment success
        if (($payload['type'] ?? '') === 'payment.succeeded') {
            // Access customer: $payload['data']['object']['customer']
            // Access amount: $payload['data']['object']['amount']
            // Process payment logic
        }
    }

    /**
     * Process generic webhooks for other providers
     */
    protected function processGenericWebhook(array $payload): void
    {
        // Log or store data for manual processing
        // You can implement custom logic here based on your needs
    }

    /**
     * Handle job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->webhookEvent->update(['status' => 'failed']);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->webhookEvent->id;
    }
}
