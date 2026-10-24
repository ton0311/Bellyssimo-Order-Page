<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderAudit;
use Illuminate\Http\Request;

class OrderAuditLogger
{
    /** @param array<string, mixed> $beforeValues
     * @param  array<string, mixed>  $afterValues
     */
    public function record(
        Order $order,
        string $event,
        array $beforeValues,
        array $afterValues,
        Request $request,
        string $actorType = 'admin',
    ): OrderAudit {
        $actorIdentifier = $actorType === 'admin'
            ? $request->session()->get('admin.login')
            : null;
        $ipAddress = $request->ip();
        $appKey = config('app.key');

        return OrderAudit::query()->create([
            'order_id' => $order->getKey(),
            'order_reference' => $order->reference,
            'event' => $event,
            'actor_type' => $actorType,
            'actor_identifier' => is_string($actorIdentifier) ? $actorIdentifier : null,
            'ip_hash' => is_string($ipAddress) && is_string($appKey) && $appKey !== ''
                ? hash_hmac('sha256', $ipAddress, $appKey)
                : null,
            'before_values' => $beforeValues === [] ? null : $beforeValues,
            'after_values' => $afterValues === [] ? null : $afterValues,
        ]);
    }
}
