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
use App\Services\OrderAuditLogger;
use App\Services\OrderEmailService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    private const ORDERS_PER_PAGE = 50;

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
        $orderModels = Order::query()
            ->latest('submitted_at')->orderByDesc('id')
            ->limit(self::ORDERS_PER_PAGE)
            ->get();
        $orders = $orderModels
            ->map(fn (Order $order): array => $this->dashboardPayload($order))
            ->values();
        $inventoryItems = InventoryItem::query()->orderBy('name')->get();
        $totalOrders = Order::query()->count();

        return view('admin.orders', [
            'orders' => $orders,
            'ordersPagination' => [
                'current_page' => 1,
                'last_page' => max(1, (int) ceil($totalOrders / self::ORDERS_PER_PAGE)),
                'per_page' => self::ORDERS_PER_PAGE,
                'total' => $totalOrders,
            ],
            'customerCount' => Customer::query()->count(),
            'inventoryItems' => $inventoryItems,
            'dashboard' => $this->dashboardData(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,Pending confirmation,Confirmed,Completed,Declined,Cancelled'],
            'date' => ['nullable', 'in:all,today,upcoming'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $paginator = $this->filteredOrdersQuery($filters)
            ->latest('submitted_at')->orderByDesc('id')
            ->paginate(self::ORDERS_PER_PAGE)
            ->withQueryString();

        return response()->json($this->paginatedOrdersPayload($paginator));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,Pending confirmation,Confirmed,Completed,Declined,Cancelled'],
            'date' => ['nullable', 'in:all,today,upcoming'],
        ]);

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'wb');
            $writeCsv = static fn (array $fields): int|false => fputcsv($output, $fields, ',', '"', '');
            $writeCsv([
                'Order ID',
                'Customer',
                'Phone',
                'Email',
                'Package',
                'Flavor',
                'Schedule',
                'Fulfillment',
                'Payment Method',
                'Payment Status',
                'Total',
                'Amount Paid',
                'Remaining Balance',
                'Status',
                'Note',
            ]);

            foreach ($this->filteredOrdersQuery($filters)->latest('submitted_at')->orderByDesc('id')->cursor() as $order) {
                $payload = $this->dashboardPayload($order);
                $writeCsv([
                    $payload['id'],
                    $payload['customer'],
                    $payload['phone'],
                    $payload['email'],
                    $payload['package'],
                    $payload['flavor'],
                    $payload['schedule'],
                    $payload['fulfillment'],
                    $payload['payment_method'],
                    $payload['payment'],
                    $payload['total'],
                    $payload['amount_paid'],
                    $payload['remaining_balance'],
                    $payload['status'],
                    $payload['note'],
                ]);
            }

            fclose($output);
        }, 'bellyssimo-orders-'.now('Asia/Manila')->toDateString().'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'dashboard' => $this->dashboardData(),
            'customer_count' => Customer::query()->count(),
            'inventory_count' => InventoryItem::query()->count(),
        ]);
    }

    public function reports(): JsonResponse
    {
        $packageMix = ['3 kg' => 0, '4 kg' => 0, '5 kg' => 0];

        Order::query()
            ->select(['id', 'items'])
            ->orderBy('id')
            ->chunkById(500, function ($orders) use (&$packageMix): void {
                foreach ($orders as $order) {
                    foreach ($order->items as $item) {
                        $weight = $item['package']['weight'] ?? null;

                        if (is_string($weight) && array_key_exists($weight, $packageMix)) {
                            $packageMix[$weight]++;
                        }
                    }
                }
            });

        $packageMax = max(1, ...array_values($packageMix));

        return response()->json([
            'package_mix' => collect($packageMix)->map(
                fn (int $count, string $weight): array => [
                    'weight' => $weight,
                    'count' => $count,
                    'width' => max(3, (int) round(($count / $packageMax) * 100)),
                ],
            )->values(),
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

    public function store(StoreAdminOrderRequest $request, OrderAuditLogger $auditLogger): JsonResponse
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
        $auditLogger->record(
            $order,
            'admin_created',
            [],
            [
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'amount_paid' => $order->amount_paid,
                'order_total' => $order->order_total,
            ],
            $request,
        );

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

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderAuditLogger $auditLogger,
    ): JsonResponse {
        $status = $request->validated('status');
        $previousStatus = $order->status;
        $beforeValues = $order->only(['status', 'payment_status', 'amount_paid', 'completed_at']);
        $updates = ['status' => $status];

        if ($status === 'Completed') {
            $updates['payment_status'] = 'Paid';
            $updates['amount_paid'] = $order->order_total;
            $updates['completed_at'] = $order->completed_at ?? now();
        }

        $order->update($updates);
        $order->refresh();

        if ($beforeValues !== $order->only(array_keys($beforeValues))) {
            $auditLogger->record(
                $order,
                'status_updated',
                $beforeValues,
                $order->only(array_keys($beforeValues)),
                $request,
            );
        }

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

    public function updatePayment(
        UpdateOrderPaymentRequest $request,
        Order $order,
        OrderAuditLogger $auditLogger,
    ): JsonResponse {
        $beforeValues = $order->only(['amount_paid', 'payment_status']);
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

        if ($beforeValues !== $order->only(array_keys($beforeValues))) {
            $auditLogger->record(
                $order,
                'payment_updated',
                $beforeValues,
                $order->only(array_keys($beforeValues)),
                $request,
            );
        }

        return response()->json([
            'message' => $amountPaid > 0 ? 'Payment amount saved.' : 'Payment amount cleared.',
            'order' => $this->dashboardPayload($order),
        ]);
    }

    public function destroy(Request $request, Order $order, OrderAuditLogger $auditLogger): JsonResponse
    {
        $reference = $order->reference;
        $auditLogger->record(
            $order,
            'deleted',
            [
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'amount_paid' => $order->amount_paid,
                'order_total' => $order->order_total,
            ],
            [],
            $request,
        );
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
     * @param  array<string, mixed>  $filters
     */
    private function filteredOrdersQuery(array $filters): Builder
    {
        $query = Order::query();
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where('reference', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $status = $filters['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $date = $filters['date'] ?? 'all';
        $today = CarbonImmutable::now('Asia/Manila')->toDateString();
        if ($date === 'today') {
            $query->whereDate('preferred_date', $today);
        } elseif ($date === 'upcoming') {
            $query->whereDate('preferred_date', '>', $today);
        }

        return $query;
    }

    /**
     * @param  LengthAwarePaginator<int, Order>  $paginator
     * @return array<string, mixed>
     */
    private function paginatedOrdersPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'orders' => collect($paginator->items())
                ->map(fn (Order $order): array => $this->dashboardPayload($order))
                ->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => max(1, $paginator->lastPage()),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardData(): array
    {
        $today = CarbonImmutable::now('Asia/Manila')->startOfDay();
        $todayDate = $today->toDateString();
        $weekEndDate = $today->addDays(7)->toDateString();

        $summary = Order::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_revenue')
            ->selectRaw(
                'COALESCE(AVG(CASE WHEN status = ? AND payment_status = ? THEN order_total END), 0) as average_order',
                ['Completed', 'Paid'],
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? AND preferred_date BETWEEN ? AND ? THEN 1 ELSE 0 END) as upcoming_confirmed',
                ['Confirmed', $todayDate, $weekEndDate],
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? AND preferred_date < ? THEN 1 ELSE 0 END) as overdue_pending',
                ['Pending confirmation', $todayDate],
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending',
                ['Pending confirmation'],
            )
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed', ['Confirmed'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['Completed'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as declined', ['Declined'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled', ['Cancelled'])
            ->first();

        $monthlyQuery = Order::query()->selectRaw('COUNT(*) as ignored_total');
        $months = collect(range(5, 0))->map(
            fn (int $offset): CarbonImmutable => $today->startOfMonth()->subMonths($offset),
        );

        foreach ($months as $index => $month) {
            $from = $month->utc()->format('Y-m-d H:i:s');
            $until = $month->addMonth()->utc()->format('Y-m-d H:i:s');

            $monthlyQuery
                ->selectRaw(
                    "COALESCE(SUM(CASE WHEN amount_paid > 0 AND submitted_at >= ? AND submitted_at < ? THEN amount_paid ELSE 0 END), 0) as revenue_{$index}",
                    [$from, $until],
                )
                ->selectRaw(
                    "SUM(CASE WHEN amount_paid > 0 AND submitted_at >= ? AND submitted_at < ? THEN 1 ELSE 0 END) as orders_{$index}",
                    [$from, $until],
                );
        }

        $monthlySummary = $monthlyQuery->first();
        $monthly = $months->map(fn (CarbonImmutable $month, int $index): array => [
            'label' => $month->format('M'),
            'revenue' => (int) $monthlySummary->{"revenue_{$index}"},
            'orders' => (int) $monthlySummary->{"orders_{$index}"},
        ]);
        $monthlyMax = max(1, (int) $monthly->max('revenue'));
        $monthly = $monthly->map(fn (array $month): array => [
            ...$month,
            'height' => max(4, (int) round(($month['revenue'] / $monthlyMax) * 100)),
        ]);

        $lowStockQuery = InventoryItem::query()
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockItems = $lowStockQuery
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        $statusCounts = collect([
            'Pending confirmation' => (int) $summary->pending,
            'Confirmed' => (int) $summary->confirmed,
            'Completed' => (int) $summary->completed,
            'Declined' => (int) $summary->declined,
            'Cancelled' => (int) $summary->cancelled,
        ]);

        return [
            'pending' => (int) $summary->pending,
            'confirmed' => (int) $summary->confirmed,
            'completed' => (int) $summary->completed,
            'declined' => (int) $summary->declined,
            'cancelled' => (int) $summary->cancelled,
            'total_orders' => (int) $summary->total_orders,
            'total_revenue' => (int) $summary->total_revenue,
            'month_revenue' => (int) $monthly->last()['revenue'],
            'average_order' => (int) round((float) $summary->average_order),
            'upcoming_confirmed' => (int) $summary->upcoming_confirmed,
            'overdue_pending' => (int) $summary->overdue_pending,
            'low_stock_count' => $lowStockCount,
            'low_stock_items' => $lowStockItems,
            'status_counts' => $statusCounts,
            'monthly' => $monthly,
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
}
