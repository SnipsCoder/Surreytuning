<?php

namespace App\Services;

use App\Models\Setting;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey($this->secretKey());
    }

    /**
     * This tenant's Stripe secret key, taken from Settings if configured,
     * otherwise the shared .env fallback.
     */
    private function secretKey(): ?string
    {
        try {
            $key = Setting::get()->stripe_secret_key;
        } catch (\Throwable) {
            $key = null;
        }

        return $key ?: config('services.stripe.secret');
    }

    /**
     * This tenant's Stripe webhook signing secret, Settings first then .env.
     */
    private function webhookSecret(): ?string
    {
        try {
            $secret = Setting::get()->stripe_webhook_secret;
        } catch (\Throwable) {
            $secret = null;
        }

        return $secret ?: config('services.stripe.webhook_secret');
    }

    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl, array $metadata = []): Session
    {
        return Session::create([
            'mode' => 'payment',
            'currency' => 'gbp',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    public function constructWebhookEvent(string $payload, string $sigHeader): Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret());
    }
}
