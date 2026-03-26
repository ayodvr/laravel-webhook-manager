<?php

namespace Cybrox\WebhookManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $table = 'webhook_events';

    protected $fillable = [
        'provider',
        'webhook_id',
        'event_type',
        'payload',
        'signature',
        'status',
        'processed_at',
        'attempts',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'attempts' => 'integer',
        'status' => 'string',
    ];
}
