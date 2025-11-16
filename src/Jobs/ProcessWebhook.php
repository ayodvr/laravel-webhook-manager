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

        // Fire the WebhookReceived event for developers to handle
        event(new WebhookReceived($this->webhookEvent));

        // If we reach here, processing was successful
        $this->webhookEvent->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
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
