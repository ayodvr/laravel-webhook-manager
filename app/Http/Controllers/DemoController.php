<?php

namespace App\Http\Controllers;

use Cybrox\WebhookManager\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class DemoController
{
    public function index()
    {
        $events = WebhookEvent::latest()->take(10)->get();
        return view('demo', compact('events'));
    }

    public function simulatePaystack(Request $request)
    {
        $payload = [
            'event' => 'charge.success',
            'data' => [
                'id' => 12345678,
                'domain' => 'test',
                'status' => 'success',
                'reference' => 'ref_' . uniqid(),
                'amount' => 500000,
                'gateway_response' => 'Successful',
                'paid_at' => now()->toIso8601String(),
                'created_at' => now()->toIso8601String(),
                'channel' => 'card',
                'currency' => 'NGN',
                'ip_address' => '127.0.0.1',
                'customer' => [
                    'id' => 1234,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'customer_code' => 'CUS_12345',
                    'phone' => '08012345678',
                ],
            ],
        ];

        $jsonPayload = json_encode($payload);
        $secret = Config::get('webhook-manager.signature_secret') ?: 'demo-secret';
        $signature = hash_hmac('sha256', $jsonPayload, $secret);

        $response = Http::withHeaders([
            'X-Webhook-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->post(url('/webhooks/paystack'), $payload);

        if ($response->successful()) {
            return redirect()->route('demo.index')->with('success', 'Paystack payment simulated successfully!');
        }

        return redirect()->route('demo.index')->with('error', 'Simulation failed: ' . $response->body());
    }
}
