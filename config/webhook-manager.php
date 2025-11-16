<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Table Name
    |--------------------------------------------------------------------------
    |
    | Specifies the table name used to store webhook events.
    |
    */
    'table_name' => 'webhook_events',

    /*
    |--------------------------------------------------------------------------
    | Signature Secret
    |--------------------------------------------------------------------------
    |
    | The secret key used to validate webhook signatures. This should be set
    | as an environment variable for security reasons.
    |
    */
    'signature_secret' => env('WEBHOOK_SIGNATURE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Defines the maximum number of times the processing of a webhook should
    | be retried in case of failures.
    |
    */
    'max_attempts' => env('WEBHOOK_MAX_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Queue Name
    |--------------------------------------------------------------------------
    |
    | The name of the queue on which webhook processing jobs are dispatched.
    |
    */
    'queue' => env('WEBHOOK_QUEUE', 'webhooks'),

    /*
    |--------------------------------------------------------------------------
    | Supported Providers
    |--------------------------------------------------------------------------
    |
    | List of supported webhook providers. Can be used for validation or
    | provider-specific logic mapping.
    |
    */
    'providers' => [
        'stripe',
        'paypal',
        'github',
        'paystack',
        // Add more as needed
    ],
];
