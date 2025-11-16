<?php

namespace Tests\Feature;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Cybrox\WebhookManager\Jobs\ProcessWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_webhook_event_and_dispatches_job()
    {
        Queue::fake();

        $secret = 'webhook-secret';
        config(['webhook-manager.signature_secret' => $secret]);

        $payload = json_encode(['type' => 'payment.succeeded', 'data' => []]);
        $signature = hash_hmac('sha256', $payload, $secret);

        $response = $this->withHeaders([
            'X-Webhook-Signature' => $signature,
        ])->postJson('/webhooks/paystack', json_decode($payload, true));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'received']);

        $webhookEvent = WebhookEvent::first();
        $this->assertNotNull($webhookEvent);
        $this->assertEquals('paystack', $webhookEvent->provider);
        $this->assertEquals('payment.succeeded', $webhookEvent->event_type);
        $this->assertEquals($payload, $webhookEvent->payload);
        $this->assertEquals($signature, $webhookEvent->signature);
        $this->assertEquals('pending', $webhookEvent->status);

        Queue::assertPushed(ProcessWebhook::class, function ($job) use ($webhookEvent) {
            return $job->webhookEvent->id === $webhookEvent->id;
        });
    }

    /** @test */
    public function it_rejects_invalid_signature()
    {
        $response = $this->withHeaders([
            'X-Webhook-Signature' => 'invalid-signature',
        ])->postJson('/webhooks/stripe', ['type' => 'test']);

        $response->assertStatus(401);
        $this->assertEquals(0, WebhookEvent::count());
    }
}
