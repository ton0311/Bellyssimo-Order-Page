<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderEmailDelivery;
use App\Services\OrderEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 30;

    public function __construct(
        public readonly int $orderId,
        public readonly string $kind,
        public readonly ?string $status,
        public readonly string $idempotencyKey,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(OrderEmailService $orderEmail): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order) {
            Log::warning('Order email skipped because the order no longer exists.', [
                'order_id' => $this->orderId,
                'email_kind' => $this->kind,
            ]);

            return;
        }

        $delivery = OrderEmailDelivery::query()->updateOrCreate(
            ['idempotency_key' => $this->idempotencyKey],
            [
                'order_id' => $order->getKey(),
                'order_reference' => $order->reference,
                'email_kind' => $this->kind,
                'status' => 'processing',
                'failure_reason' => null,
            ],
        );

        try {
            $resendEmailId = $orderEmail->send($order, $this->kind, $this->status, $this->idempotencyKey);

            $delivery->update([
                'resend_email_id' => $resendEmailId,
                'status' => $resendEmailId === null ? 'skipped' : 'sent',
            ]);
        } catch (Throwable $exception) {
            $delivery->update([
                'status' => 'retrying',
                'failure_reason' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        OrderEmailDelivery::query()
            ->where('idempotency_key', $this->idempotencyKey)
            ->update([
                'status' => 'failed',
                'failure_reason' => $exception
                    ? mb_substr($exception->getMessage(), 0, 1000)
                    : 'The queued email job failed without an exception message.',
            ]);

        Log::critical('Order email job exhausted all retry attempts.', [
            'order_id' => $this->orderId,
            'email_kind' => $this->kind,
            'idempotency_key' => $this->idempotencyKey,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
