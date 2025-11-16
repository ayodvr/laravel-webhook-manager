<?php

namespace Cybrox\WebhookManager\Http\Controllers;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Cybrox\WebhookManager\Jobs\ProcessWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

/**
 * Controller for handling incoming webhook requests
 */
class WebhookController
{
    /**
     * Handle the incoming webhook
     */
    public function handle(Request $request, string $provider)
    {
        // Determine event type from payload or headers
        $eventType = $this->extractEventType($request);
        $payload = json_encode($request->all());
        $signature = $request->header('X-Webhook-Signature');

        // Store the webhook event
        $webhookEvent = WebhookEvent::create([
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $payload,
            'signature' => $signature,
            'status' => 'pending',
        ]);

        // Dispatch job for processing
        ProcessWebhook::dispatch($webhookEvent)->onQueue('webhooks');

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Extract the event type from the request
     */
    protected function extractEventType(Request $request): string
    {
        // Assume it's in 'type' field, configurable later
        return $request->input('type', 'unknown');
    }
}
