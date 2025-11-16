<?php

namespace Cybrox\WebhookManager\Events;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event fired when a webhook is received and ready for processing
 */
class WebhookReceived
{
    use Dispatchable;

    /**
     * The webhook event instance.
     */
    public WebhookEvent $webhookEvent;

    /**
     * Create a new event instance.
     */
    public function __construct(WebhookEvent $webhookEvent)
    {
        $this->webhookEvent = $webhookEvent;
    }
}
