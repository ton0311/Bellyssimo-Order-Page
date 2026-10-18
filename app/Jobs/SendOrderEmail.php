<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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

        $orderEmail->send($order, $this->kind, $this->status, $this->idempotencyKey);
    }
}
