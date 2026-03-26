<?php

namespace App\Listeners;

use Cybrox\WebhookManager\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class HandlePaystackDemo
{
    public function handle(WebhookReceived $event): void
    {
        $webhookEvent = $event->webhookEvent;

        if ($webhookEvent->provider === 'paystack') {
            $payload = json_decode($webhookEvent->payload, true);
            Log::info("Demo Listener: Processing Paystack payment for " . ($payload['data']['customer']['email'] ?? 'unknown'));
        }
    }
}
