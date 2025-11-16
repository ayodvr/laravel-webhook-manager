<?php

namespace Cybrox\WebhookManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model for storing webhook events
 */
class WebhookEvent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'webhook_events';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider',
        'event_type',
        'payload',
        'signature',
        'status',
        'processed_at',
        'attempts',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'attempts' => 'integer',
        'status' => 'string',
    ];
}
