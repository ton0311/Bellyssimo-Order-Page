<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderEmailDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResendWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_delivery_webhook_updates_the_matching_email_and_is_idempotent(): void
    {
        $secret = 'whsec_'.base64_encode(random_bytes(32));
        config(['services.resend.webhook.secret' => $secret]);
        $order = Order::factory()->create();
        $emailId = (string) Str::uuid();
        $delivery = OrderEmailDelivery::query()->create([
            'order_id' => $order->getKey(),
            'order_reference' => $order->reference,
            'email_kind' => 'confirmation',
            'idempotency_key' => 'order-confirmation-'.$order->reference,
            'resend_email_id' => $emailId,
            'status' => 'sent',
        ]);
        $payload = json_encode([
            'type' => 'email.delivered',
            'created_at' => now()->toISOString(),
            'data' => [
                'email_id' => $emailId,
                'to' => ['customer@example.com'],
            ],
        ], JSON_THROW_ON_ERROR);
        $eventId = 'msg_'.Str::random(20);
        $timestamp = (string) time();
        $headers = $this->signedHeaders($payload, $eventId, $timestamp, $secret);

        $this->call('POST', route('webhooks.resend'), [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJsonPath('status', 'processed');

        $this->assertSame('delivered', $delivery->refresh()->status);
        $this->assertDatabaseHas('resend_webhook_events', [
            'webhook_event_id' => $eventId,
            'resend_email_id' => $emailId,
            'event_type' => 'email.delivered',
        ]);

        $this->call('POST', route('webhooks.resend'), [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('resend_webhook_events', 1);
    }

    public function test_webhook_rejects_invalid_signatures_without_storing_the_payload(): void
    {
        config(['services.resend.webhook.secret' => 'whsec_'.base64_encode(random_bytes(32))]);
        $payload = json_encode([
            'type' => 'email.delivered',
            'created_at' => now()->toISOString(),
            'data' => ['email_id' => (string) Str::uuid()],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('webhooks.resend'), [], [], [], [
            'HTTP_SVIX_ID' => 'msg_invalid',
            'HTTP_SVIX_TIMESTAMP' => (string) time(),
            'HTTP_SVIX_SIGNATURE' => 'v1,invalid',
        ], $payload)->assertUnauthorized();

        $this->assertDatabaseCount('resend_webhook_events', 0);
    }

    public function test_webhook_fails_closed_when_no_signing_secret_is_configured(): void
    {
        config(['services.resend.webhook.secret' => null]);

        $this->postJson(route('webhooks.resend'), [])->assertServiceUnavailable();
    }

    /** @return array<string, string> */
    private function signedHeaders(string $payload, string $eventId, string $timestamp, string $secret): array
    {
        $decodedSecret = base64_decode(substr($secret, strlen('whsec_')), true);
        $signature = base64_encode(hash_hmac(
            'sha256',
            "{$eventId}.{$timestamp}.{$payload}",
            $decodedSecret,
            true,
        ));

        return [
            'HTTP_SVIX_ID' => $eventId,
            'HTTP_SVIX_TIMESTAMP' => $timestamp,
            'HTTP_SVIX_SIGNATURE' => 'v1,'.$signature,
            'CONTENT_TYPE' => 'application/json',
        ];
    }
}
