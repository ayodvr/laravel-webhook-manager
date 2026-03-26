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

class ProcessWebhook implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public WebhookEvent $webhookEvent;

    public int $tries = 3;

    public function __construct(WebhookEvent $webhookEvent)
    {
        $this->webhookEvent = $webhookEvent;
    }

    public function handle(): void
    {
        $this->webhookEvent->increment('attempts');

        try {
            $payload = json_decode($this->webhookEvent->payload, true);

            switch ($this->webhookEvent->provider) {
                case 'paystack':
                    $this->processPaystackWebhook($payload);
                    break;
                case 'stripe':
                    $this->processStripeWebhook($payload);
                    break;
                default:
                    $this->processGenericWebhook($payload);
            }

            event(new WebhookReceived($this->webhookEvent));

            $this->webhookEvent->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->failed($exception);
            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->webhookEvent->update([
            'status' => 'failed',
        ]);

        \Log::error("Webhook processing failed: {$exception->getMessage()}", [
            'webhook_id' => $this->webhookEvent->id,
            'exception' => $exception,
        ]);
    }

    public function uniqueId(): string
    {
        return (string) $this->webhookEvent->id;
    }

    protected function processPaystackWebhook(array $payload): void
    {
        switch ($payload['event'] ?? '') {
            case 'charge.success':
                $reference = $payload['data']['reference'] ?? null;
                $amount = ($payload['data']['amount'] ?? 0) / 100;
                $customerEmail = $payload['data']['customer']['email'] ?? null;
                break;

            case 'charge.dispute.create':
                break;

            case 'invoice.create':
            case 'invoice.update':
                break;

            case 'subscription.create':
            case 'subscription.disable':
                break;

            default:
                $eventName = $payload['event'] ?? 'unknown';
                \Log::info("Unknown Paystack webhook event: {$eventName}");
                break;
        }
    }

    protected function processStripeWebhook(array $payload): void
    {
        if (($payload['type'] ?? '') === 'payment.succeeded') {
            // handle payment success
        }
    }

    protected function processGenericWebhook(array $payload): void
    {
        // log or store data
    }
}
