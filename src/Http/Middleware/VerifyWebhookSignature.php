<?php

namespace Cybrox\WebhookManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify webhook signature for authenticity
 */
class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = Config::get('webhook-manager.signature_secret');
        $signature = $request->header('X-Webhook-Signature');

        if (!$secret || !$signature || !$this->verifySignature($request->getContent(), $signature, $secret)) {
            return response('Unauthorized', 401);
        }

        return $next($request);
    }

    /**
     * Verify the signature of the payload
     */
    protected function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($signature, $expectedSignature);
    }
}
