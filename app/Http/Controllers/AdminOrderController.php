<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminOrderRequest;
use App\Http\Requests\UpdateAdminOrderRequest;
use App\Http\Requests\UpdateOrderPaymentRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\SendOrderEmail;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Services\OrderEmailService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    private const PACKAGES = [
        'barkadahan' => [
            'name' => 'The Bellyssimo Barkadahan',
            'weight' => '3 kg',
            'price' => 2000,
            'image' => 'images/lechon-barkadahan-3kg-480.jpg',
        ],
        'salo-salo' => [
            'name' => 'Bellyssimo Salo-Salo',
            'weight' => '4 kg',
            'price' => 2550,
            'image' => 'images/lechon-salo-salo-4kg-480.jpg',
        ],
        'handaan' => [
            'name' => 'Bellyssimo Handaan',
            'weight' => '5 kg',
            'price' => 2950,
            'image' => 'images/lechon-handaan-5kg-480.jpg',
        ],
    ];

    public function index(): View
    {
        $orderModels = Order::query()->latest('submitted_at')->get();
        $orders = $orderModels
            ->take(200)
            ->map(fn (Order $order): array => $this->dashboardPayload($order))
            ->values();
        $ordersByCustomer = $orderModels->groupBy(
            fn (Order $order): string => $this->customerKey($order->customer_name, $order->contact_number),
        );
        $customers = Customer::query()
            ->latest('last_order_at')
            ->get()
            ->map(function (Customer $customer) use ($ordersByCustomer): array {
                $customerOrders = $ordersByCustomer->get(
                    $this->customerKey($customer->name, $customer->contact_number),
                    collect(),
                );
                $completedOrders = $customerOrders
                    ->where('status', 'Completed')
                    ->where('payment_status', 'Paid');

                return [
                    'database_id' => $customer->getKey(),
                    'name' => $customer->name,
                    'phone' => $customer->contact_number,
                    'email' => $customer->email,
                    'orders' => $customerOrders->count(),
                    'completed_orders' => $completedOrders->count(),
                    'lifetime_value' => $completedOrders->sum('order_total'),
                    'last_order' => $customer->last_order_at?->format('M j, Y') ?? 'No order date',
                    'notes' => $customer->notes,
                ];
            });
        $inventoryItems = InventoryItem::query()->orderBy('name')->get();

        return view('admin.orders', [
            'orders' => $orders,
            'customers' => $customers,
            'inventoryItems' => $inventoryItems,
            'dashboard' => $this->dashboardData($orderModels, $inventoryItems),
        ]);
    }

    public function schedule(Request $request): View
    {
        $requestedMonth = $request->query('month');
        $month = is_string($requestedMonth) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestedMonth) === 1
            ? CarbonImmutable::createFromFormat('!Y-m', $requestedMonth)->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();
        $monthEnd = $month->endOfMonth();
        $orderModels = Order::query()
            ->whereNotIn('status', ['Declined', 'Cancelled'])
            ->whereBetween('preferred_date', [$month->toDateString(), $monthEnd->toDateString()])
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->get();
        $orders = $orderModels
            ->map(fn (Order $order): array => $this->dashboardPayload($order))
            ->values();
        $ordersByDate = $orders->groupBy(
            fn (array $order): string => substr($order['schedule'], 0, 10),
        );
        $calendarStart = $month->startOfWeek(CarbonInterface::SUNDAY);
        $days = collect(range(0, 41))->map(function (int $offset) use ($calendarStart, $month, $ordersByDate): array {
            $date = $calendarStart->addDays($offset);

            return [
                'date' => $date,
                'is_current_month' => $date->month === $month->month,
                'is_today' => $date->isToday(),
                'orders' => $ordersByDate->get($date->toDateString(), collect()),
            ];
        });

        return view('admin.schedule', [
            'month' => $month,
            'days' => $days,
            'orders' => $orders,
            'allOrdersCount' => Order::query()->count(),
            'totalOrders' => $orderModels->count(),
            'totalBellies' => $orderModels->sum('quantity'),
            'pickupCount' => $orderModels->where('fulfillment', 'Pickup')->count(),
            'deliveryCount' => $orderModels->where('fulfillment', 'Delivery')->count(),
            'pendingCount' => $orderModels->where('status', 'Pending confirmation')->count(),
        ]);
    }

    public function store(StoreAdminOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $package = self::PACKAGES[$data['package']];
        $spicyUpgrade = $data['flavor'] === 'Spicy' ? 50 : 0;
        $schedule = CarbonImmutable::parse($data['schedule']);
        $item = [
            'package_key' => $data['package'],
            'package' => $package,
            'flavor' => $data['flavor'],
            'spicy_upgrade' => $spicyUpgrade,
            'line_total' => $package['price'] + $spicyUpgrade,
        ];

        $order = Order::query()->create([
            'reference' => 'TB-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'items' => [$item],
            'quantity' => 1,
            'package_subtotal' => $package['price'],
            'spicy_subtotal' => $spicyUpgrade,
            'order_total' => $package['price'] + $spicyUpgrade,
            'amount_paid' => $data['payment'] === 'Paid' ? $package['price'] + $spicyUpgrade : 0,
            'fulfillment' => $data['fulfillment'],
            'delivery_location' => $data['delivery_location'] ?? null,
            'customer_name' => $data['customer'],
            'contact_number' => '+63'.$data['phone'],
            'email' => $data['email'] ?? null,
            'preferred_date' => $schedule->toDateString(),
            'preferred_time' => $schedule->format('H:i'),
            'notes' => $data['note'] ?? null,
            'status' => 'Pending confirmation',
            'payment_status' => $data['payment'],
            'payment_method' => $data['payment_method'],
            'submitted_at' => now(),
        ]);
        $this->syncCustomer($order);

        if ($order->email) {
            SendOrderEmail::dispatch(
                $order->getKey(),
                OrderEmailService::CONFIRMATION,
                null,
                'order-confirmation-'.$order->reference,
            )->afterResponse();
        }

        return response()->json([
            'message' => 'Order added to the dashboard.',
            'order' => $this->dashboardPayload($order),
        ], 201);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $status = $request->validated('status');
        $previousStatus = $order->status;
        $updates = ['status' => $status];

        if ($status === 'Completed') {
            $updates['payment_status'] = 'Paid';
            $updates['amount_paid'] = $order->order_total;
            $updates['completed_at'] = $order->completed_at ?? now();
        }

        $order->update($updates);
        $order->refresh();

        $emailKinds = match ($status) {
            'Confirmed' => array_values(array_filter([
                OrderEmailService::CONFIRMED,
                $order->payment_method === 'GCash' ? OrderEmailService::GCASH_PAYMENT : null,
            ])),
            'Declined' => [OrderEmailService::DECLINED],
            default => [],
        };

        if ($previousStatus !== $status && $order->email && $emailKinds !== []) {
            $transition = hash('sha256', $previousStatus.'|'.$status.'|'.$order->updated_at?->toISOString());

            foreach ($emailKinds as $emailKind) {
                SendOrderEmail::dispatch(
                    $order->getKey(),
                    $emailKind,
                    $status,
                    'order-status-'.$order->reference.'-'.$emailKind.'-'.substr($transition, 0, 32),
                )->afterResponse();
            }
        }

        return response()->json([
            'message' => $status === 'Completed'
                ? 'Order marked Completed and Paid.'
                : 'Order status updated.',
            'order' => $this->dashboardPayload($order),
        ]);
    }

    public function update(
        UpdateAdminOrderRequest $request,
        Order $order,
    ): JsonResponse {
        $data = $request->validated();
        $schedule = CarbonImmutable::parse($data['schedule']);

        $order->update([
            'customer_name' => $data['customer'],
            'contact_number' => '+63'.$data['phone'],
            'email' => $data['email'] ?? null,
            'preferred_date' => $schedule->toDateString(),
            'preferred_time' => $schedule->format('H:i'),
            'fulfillment' => $data['fulfillment'],
            'delivery_location' => $data['delivery_location'] ?? null,
            'payment_method' => $data['payment_method'],
            'notes' => $data['note'] ?? null,
        ]);
        $order->refresh();
        $this->syncCustomer($order);

        return response()->json([
            'message' => 'Order details updated.',
            'order' => $this->dashboardPayload($order),
        ]);
    }

    public function updatePayment(UpdateOrderPaymentRequest $request, Order $order): JsonResponse
    {
        $amountPaid = (int) $request->validated('amount_paid');
        $paymentStatus = match (true) {
            $amountPaid === 0 => 'Unpaid',
            $amountPaid >= $order->order_total => 'Paid',
            default => 'Deposit received',
        };

        $order->update([
            'amount_paid' => $amountPaid,
            'payment_status' => $paymentStatus,
        ]);
        $order->refresh();

        return response()->json([
            'message' => $amountPaid > 0 ? 'Payment amount saved.' : 'Payment amount cleared.',
            'order' => $this->dashboardPayload($order),
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $reference = $order->reference;
        $order->delete();

        return response()->json([
            'message' => "Order {$reference} was deleted.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardPayload(Order $order): array
    {
        $items = collect($order->items)->map(function (array $item): array {
            return [
                'name' => $item['package']['name'],
                'weight' => $item['package']['weight'],
                'flavor' => $item['flavor'],
                'line_total' => $item['line_total'],
                'image' => asset($item['package']['image']),
            ];
        })->values();
        $packageSummary = $items->count() === 1
            ? $items->first()['name'].' ('.$items->first()['weight'].')'
            : $items->count().' bellies · '.$items->map(fn (array $item): string => $item['weight'])->join(' + ');
        $spicyCount = $items->where('flavor', 'Spicy')->count();
        $flavorSummary = match (true) {
            $spicyCount === 0 => 'Regular',
            $spicyCount === $items->count() => 'Spicy (+₱'.number_format($order->spicy_subtotal).')',
            default => 'Mixed · '.$spicyCount.' spicy, '.($items->count() - $spicyCount).' regular',
        };
        $fulfillment = $order->fulfillment === 'Delivery'
            ? 'Delivery · '.$order->delivery_location
            : 'Pickup · Perayra Village, Maningcol';

        return [
            'database_id' => $order->getKey(),
            'id' => $order->reference,
            'customer' => $order->customer_name,
            'phone' => $order->contact_number,
            'email' => $order->email,
            'package' => $packageSummary,
            'items' => $items,
            'flavor' => $flavorSummary,
            'schedule' => $order->preferred_date->format('Y-m-d').'T'.substr($order->preferred_time, 0, 5),
            'fulfillment' => $fulfillment,
            'fulfillment_method' => $order->fulfillment,
            'delivery_location' => $order->delivery_location,
            'payment' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'total' => $order->order_total,
            'amount_paid' => $order->amount_paid,
            'remaining_balance' => max(0, $order->order_total - $order->amount_paid),
            'status' => $order->status,
            'created' => 'Added '.$order->submitted_at->diffForHumans(),
            'note' => $order->notes ?: 'No customer note.',
            'note_raw' => $order->notes,
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @param  Collection<int, InventoryItem>  $inventoryItems
     * @return array<string, mixed>
     */
    private function dashboardData(Collection $orders, Collection $inventoryItems): array
    {
        $today = CarbonImmutable::now('Asia/Manila')->startOfDay();
        $completed = $orders
            ->where('status', 'Completed')
            ->where('payment_status', 'Paid');
        $statusOrder = ['Pending confirmation', 'Confirmed', 'Completed', 'Declined', 'Cancelled'];
        $statusCounts = collect($statusOrder)->mapWithKeys(
            fn (string $status): array => [$status => $orders->where('status', $status)->count()],
        );
        $months = collect(range(5, 0))->map(
            fn (int $offset): CarbonImmutable => $today->subMonths($offset)->startOfMonth(),
        );
        $monthly = $months->map(function (CarbonImmutable $month) use ($completed): array {
            $monthOrders = $completed->filter(function (Order $order) use ($month): bool {
                $completedAt = $order->completed_at?->copy()->setTimezone('Asia/Manila');

                return $completedAt?->format('Y-m') === $month->format('Y-m');
            });

            return [
                'label' => $month->format('M'),
                'revenue' => $monthOrders->sum('order_total'),
                'orders' => $monthOrders->count(),
            ];
        });
        $monthlyMax = max(1, (int) $monthly->max('revenue'));
        $monthly = $monthly->map(fn (array $month): array => [
            ...$month,
            'height' => max(4, (int) round(($month['revenue'] / $monthlyMax) * 100)),
        ]);
        $lowStock = $inventoryItems->filter(
            fn (InventoryItem $item): bool => (float) $item->quantity <= (float) $item->low_stock_threshold,
        );
        $upcomingConfirmed = $orders->filter(function (Order $order) use ($today): bool {
            $date = CarbonImmutable::parse($order->preferred_date->toDateString(), 'Asia/Manila');

            return $order->status === 'Confirmed'
                && $date->betweenIncluded($today, $today->addDays(7));
        });
        $overduePending = $orders->filter(function (Order $order) use ($today): bool {
            return $order->status === 'Pending confirmation'
                && $order->preferred_date->toDateString() < $today->toDateString();
        });
        $monthRevenue = $completed->filter(function (Order $order) use ($today): bool {
            return $order->completed_at?->copy()->setTimezone('Asia/Manila')->format('Y-m') === $today->format('Y-m');
        })->sum('order_total');
        $packageMix = ['3 kg' => 0, '4 kg' => 0, '5 kg' => 0];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $weight = $item['package']['weight'] ?? null;
                if (is_string($weight) && array_key_exists($weight, $packageMix)) {
                    $packageMix[$weight]++;
                }
            }
        }

        $packageMix = collect($packageMix);
        $packageMax = max(1, (int) $packageMix->max());

        return [
            'pending' => $statusCounts['Pending confirmation'],
            'confirmed' => $statusCounts['Confirmed'],
            'completed' => $statusCounts['Completed'],
            'declined' => $statusCounts['Declined'],
            'cancelled' => $statusCounts['Cancelled'],
            'total_orders' => $orders->count(),
            'total_revenue' => $completed->sum('order_total'),
            'month_revenue' => $monthRevenue,
            'average_order' => $completed->count() > 0 ? (int) round($completed->avg('order_total')) : 0,
            'upcoming_confirmed' => $upcomingConfirmed->count(),
            'overdue_pending' => $overduePending->count(),
            'low_stock_count' => $lowStock->count(),
            'low_stock_items' => $lowStock->take(5),
            'status_counts' => $statusCounts,
            'monthly' => $monthly,
            'package_mix' => $packageMix->map(fn (int $count, string $weight): array => [
                'weight' => $weight,
                'count' => $count,
                'width' => max(3, (int) round(($count / $packageMax) * 100)),
            ])->values(),
        ];
    }

    private function syncCustomer(Order $order): void
    {
        Customer::query()->updateOrCreate(
            [
                'name' => $order->customer_name,
                'contact_number' => $order->contact_number,
            ],
            [
                'email' => $order->email,
                'last_order_at' => $order->submitted_at,
            ],
        );
    }

    private function customerKey(string $name, string $contactNumber): string
    {
        return mb_strtolower(trim($name)).'|'.preg_replace('/\D+/', '', $contactNumber);
    }
}
