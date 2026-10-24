<?php

namespace App\Http\Controllers;

use App\Models\OrderEmailDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Resend\WebhookSignature;
use Throwable;

class ResendWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.resend.webhook.secret');

        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Webhook is not configured.'], 503);
        }

        $rawPayload = $request->getContent();
        $headers = [
            'svix-id' => $request->header('svix-id'),
            'svix-timestamp' => $request->header('svix-timestamp'),
            'svix-signature' => $request->header('svix-signature'),
        ];

        try {
            WebhookSignature::verify(
                $rawPayload,
                $headers,
                $secret,
                (int) config('services.resend.webhook.tolerance', 300),
            );
        } catch (Throwable) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $payload = json_decode($rawPayload, true);
        $eventId = $headers['svix-id'];
        $eventType = is_array($payload) ? ($payload['type'] ?? null) : null;
        $data = is_array($payload) && is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $resendEmailId = $data['email_id'] ?? null;
        $occurredAt = is_array($payload) ? ($payload['created_at'] ?? null) : null;

        if (! is_string($eventId) || ! is_string($eventType) || ! str_starts_with($eventType, 'email.')
            || ! is_string($resendEmailId) || ! is_string($occurredAt)) {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        try {
            $eventTime = CarbonImmutable::parse($occurredAt);
        } catch (Throwable) {
            return response()->json(['message' => 'Invalid webhook timestamp.'], 422);
        }

        $processed = DB::transaction(function () use ($eventId, $resendEmailId, $eventType, $eventTime, $data): bool {
            $inserted = DB::table('resend_webhook_events')->insertOrIgnore([
                'webhook_event_id' => $eventId,
                'resend_email_id' => $resendEmailId,
                'event_type' => $eventType,
                'occurred_at' => $eventTime,
                'payload' => json_encode($data, JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);

            if ($inserted === 0) {
                return false;
            }

            $delivery = OrderEmailDelivery::query()
                ->where('resend_email_id', $resendEmailId)
                ->lockForUpdate()
                ->first();

            if (! $delivery || ($delivery->last_event_at && $delivery->last_event_at->isAfter($eventTime))) {
                return true;
            }

            $delivery->update([
                'status' => $this->deliveryStatus($eventType, $delivery->status),
                'latest_event_type' => $eventType,
                'failure_reason' => $this->failureReason($eventType, $data),
                'last_event_at' => $eventTime,
            ]);

            return true;
        });

        return response()->json([
            'status' => $processed ? 'processed' : 'duplicate',
        ]);
    }

    private function deliveryStatus(string $eventType, string $currentStatus): string
    {
        return match ($eventType) {
            'email.sent' => 'sent',
            'email.delivered' => 'delivered',
            'email.delivery_delayed' => 'delayed',
            'email.bounced' => 'bounced',
            'email.complained' => 'complained',
            'email.failed' => 'failed',
            'email.suppressed' => 'suppressed',
            default => $currentStatus,
        };
    }

    /** @param array<string, mixed> $data */
    private function failureReason(string $eventType, array $data): ?string
    {
        if (! in_array($eventType, ['email.bounced', 'email.failed', 'email.suppressed'], true)) {
            return null;
        }

        $reason = Arr::get($data, 'bounce.message')
            ?? Arr::get($data, 'failed.reason')
            ?? Arr::get($data, 'suppression.reason');

        return is_string($reason) ? mb_substr($reason, 0, 1000) : 'Resend reported a delivery failure.';
    }
}
