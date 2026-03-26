<?php

namespace Cybrox\WebhookManager\Events;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Illuminate\Foundation\Events\Dispatchable;

class WebhookReceived
{
    use Dispatchable;

    public WebhookEvent $webhookEvent;

    public function __construct(WebhookEvent $webhookEvent)
    {
        $this->webhookEvent = $webhookEvent;
    }
}
