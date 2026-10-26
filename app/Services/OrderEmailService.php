<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNotificationSetting;
use Illuminate\Support\Facades\Log;
use Resend\Laravel\Facades\Resend;
use RuntimeException;

class OrderEmailService
{
    public const CONFIRMATION = 'confirmation';

    public const OWNER_NOTIFICATION = 'owner_notification';

    public const CONFIRMED = 'confirmed';

    public const DECLINED = 'declined';

    public const COMPLETED = 'completed';

    public const GCASH_PAYMENT = 'gcash_payment';

    public function send(Order $order, string $kind, ?string $status, string $idempotencyKey): ?string
    {
        if ($kind === self::OWNER_NOTIFICATION) {
            return $this->sendOwnerNotification($order, $idempotencyKey);
        }

        if (! is_string($order->email) || $order->email === '') {
            return null;
        }

        $templateId = $this->templateId($kind);
        $apiKey = config('services.resend.key');

        if (! is_string($apiKey) || $apiKey === '' || $templateId === null) {
            Log::warning('Order email skipped because Resend is not fully configured.', [
                'order_reference' => $order->reference,
                'email_kind' => $kind,
            ]);

            return null;
        }

        $variables = $kind === self::COMPLETED
            ? ['CUSTOMER_NAME' => $order->customer_name, 'ORDER_REFERENCE' => $order->reference]
            : $this->variables($order, $status);

        if ($kind === self::GCASH_PAYMENT) {
            if ($order->payment_method !== 'GCash') {
                return null;
            }

            $gcashQrUrl = config('services.resend.gcash.qr_url');

            if (! is_string($gcashQrUrl) || ! str_starts_with($gcashQrUrl, 'https://')) {
                Log::warning('GCash payment email skipped because the QR code URL is not configured.', [
                    'order_reference' => $order->reference,
                    'email_kind' => $kind,
                ]);

                return null;
            }

            $variables['GCASH_QR_URL'] = $gcashQrUrl;
        }

        $email = Resend::emails()->send([
            'from' => sprintf('%s <%s>', config('mail.from.name'), config('mail.from.address')),
            'to' => [$order->email],
            'template' => [
                'id' => $templateId,
                'variables' => $variables,
            ],
            'tags' => [
                ['name' => 'order_reference', 'value' => $order->reference],
                ['name' => 'email_kind', 'value' => $kind],
            ],
        ], [
            'idempotency_key' => $idempotencyKey,
        ]);

        if (! is_string($email->id) || $email->id === '') {
            throw new RuntimeException('Resend did not return an email ID.');
        }

        Log::info('Order email sent through Resend.', [
            'order_reference' => $order->reference,
            'email_kind' => $kind,
            'resend_email_id' => $email->id,
        ]);

        return $email->id;
    }

    private function sendOwnerNotification(Order $order, string $idempotencyKey): ?string
    {
        $recipients = collect(
            OrderNotificationSetting::query()->find(1)?->recipient_emails
                ?? config('services.resend.order_notifications.emails', []),
        )
            ->filter(fn (mixed $email): bool => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
        $apiKey = config('services.resend.key');

        if (! is_string($apiKey) || $apiKey === '' || $recipients === []) {
            Log::warning('New order notification skipped because the owner email or Resend API key is not configured.', [
                'order_reference' => $order->reference,
                'email_kind' => self::OWNER_NOTIFICATION,
            ]);

            return null;
        }

        $payload = [
            'from' => sprintf('%s <%s>', config('mail.from.name'), config('mail.from.address')),
            'to' => $recipients,
            'subject' => sprintf('New booking %s — %s — ₱%s', $order->reference, $order->customer_name, number_format($order->order_total)),
            'html' => view('emails.orders.new-booking', ['order' => $order])->render(),
            'text' => view('emails.orders.new-booking-text', ['order' => $order])->render(),
            'tags' => [
                ['name' => 'order_reference', 'value' => $order->reference],
                ['name' => 'email_kind', 'value' => self::OWNER_NOTIFICATION],
            ],
        ];

        if (is_string($order->email) && filter_var($order->email, FILTER_VALIDATE_EMAIL) !== false) {
            $payload['reply_to'] = [$order->email];
        }

        $email = Resend::emails()->send($payload, [
            'idempotency_key' => $idempotencyKey,
        ]);

        if (! is_string($email->id) || $email->id === '') {
            throw new RuntimeException('Resend did not return an email ID.');
        }

        Log::info('New order notification sent through Resend.', [
            'order_reference' => $order->reference,
            'email_kind' => self::OWNER_NOTIFICATION,
            'resend_email_id' => $email->id,
        ]);

        return $email->id;
    }

    private function templateId(string $kind): ?string
    {
        $templateId = match ($kind) {
            self::CONFIRMATION => config('services.resend.templates.order_confirmation'),
            self::CONFIRMED => config('services.resend.templates.order_confirmed'),
            self::DECLINED => config('services.resend.templates.order_declined'),
            self::COMPLETED => config('services.resend.templates.order_completed'),
            self::GCASH_PAYMENT => config('services.resend.templates.order_gcash_payment'),
            default => throw new RuntimeException("Unsupported order email kind [{$kind}]."),
        };

        return is_string($templateId) && $templateId !== '' ? $templateId : null;
    }

    /** @return array<string, string> */
    private function variables(Order $order, ?string $status): array
    {
        $orderStatus = $status ?? $order->status;
        $fulfillment = $order->fulfillment === 'Delivery'
            ? 'Delivery to '.$order->delivery_location.' (delivery fee to be confirmed)'
            : 'Pickup at Perayra Village, Maningcol, Ozamiz City';
        $items = collect($order->items)
            ->map(fn (array $item, int $index): string => sprintf(
                '%d. %s, %s, %s — ₱%s',
                $index + 1,
                $item['package']['name'],
                $item['package']['weight'],
                $item['flavor'],
                number_format($item['line_total']),
            ))
            ->join(' | ');

        return [
            'CUSTOMER_NAME' => $order->customer_name,
            'ORDER_REFERENCE' => $order->reference,
            'ORDER_ITEMS' => $items,
            'ORDER_TOTAL' => '₱'.number_format($order->order_total),
            'FULFILLMENT' => $fulfillment,
            'PREFERRED_SCHEDULE' => $order->preferred_date->format('F j, Y').' at '.date('g:i A', strtotime($order->preferred_time)),
            'PAYMENT_METHOD' => $order->payment_method,
            'ORDER_STATUS' => $orderStatus,
            'STATUS_MESSAGE' => $this->statusMessage($orderStatus),
        ];
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'Confirmed' => 'Your booking is confirmed for the preferred schedule shown below.',
            'Completed' => 'Your order has been completed. Thank you for choosing Toño’s Bellyssimo.',
            'Declined' => 'We’re very sorry, but we’re unable to accommodate your booking request at this time, so your order did not push through. We sincerely apologize for the inconvenience. Please contact Toño’s Bellyssimo if you would like help choosing another schedule.',
            'Cancelled' => 'Your booking request has been cancelled. Please contact Toño’s Bellyssimo if you need assistance.',
            default => 'Your booking request is waiting for availability review.',
        };
    }
}
