<?php

namespace Cybrox\WebhookManager\Http\Controllers;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Cybrox\WebhookManager\Jobs\ProcessWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class WebhookController
{
    public function handle(Request $request, string $provider)
    {
        $signature = $request->header('X-Webhook-Signature');

        $webhookId = $this->extractWebhookId($request, $provider);

        $query = WebhookEvent::query();
        if ($webhookId) {
            $query->where('webhook_id', $webhookId);
        } elseif ($signature) {
            $query->where('signature', $signature);
        }

        if ($query->exists()) {
            return response()->json(['status' => 'already_received'], 200);
        }

        $eventType = $this->extractEventType($request, $provider);
        $payload = json_encode($request->all());

        $webhookEvent = WebhookEvent::create([
            'provider' => $provider,
            'webhook_id' => $webhookId,
            'event_type' => $eventType,
            'payload' => $payload,
            'signature' => $signature,
            'status' => 'pending',
        ]);

        ProcessWebhook::dispatch($webhookEvent)->onQueue(config('webhook-manager.queue', 'webhooks'));

        return response()->json(['status' => 'received'], 200);
    }

    protected function extractWebhookId(Request $request, string $provider): ?string
    {
        return match ($provider) {
            'stripe' => $request->input('id'),
            'paystack' => $request->input('data.id') ?? $request->input('data.reference'),
            'github' => $request->header('X-GitHub-Delivery'),
            default => $request->input('id') ?? $request->input('webhook_id'),
        };
    }

    protected function extractEventType(Request $request, string $provider = null): string
    {
        if ($provider === 'paystack' && $request->has('event')) {
            return $request->input('event');
        }

        if ($request->has('type')) {
            return $request->input('type');
        }

        return $request->input('event') ?: 'unknown';
    }
}
