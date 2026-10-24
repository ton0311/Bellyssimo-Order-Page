<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    private const CUSTOMERS_PER_PAGE = 50;

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));

        $customers = Customer::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('contact_number', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->latest('last_order_at')->orderByDesc('id')
            ->paginate(self::CUSTOMERS_PER_PAGE)
            ->withQueryString();

        $contactNumbers = collect($customers->items())
            ->pluck('contact_number')
            ->filter()
            ->values();

        $orderStats = $contactNumbers->isEmpty()
            ? collect()
            : Order::query()
                ->whereIn('contact_number', $contactNumbers)
                ->select(['customer_name', 'contact_number'])
                ->selectRaw('COUNT(*) as orders_count')
                ->selectRaw(
                    'SUM(CASE WHEN status = ? AND payment_status = ? THEN 1 ELSE 0 END) as completed_orders',
                    ['Completed', 'Paid'],
                )
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN status = ? AND payment_status = ? THEN order_total ELSE 0 END), 0) as lifetime_value',
                    ['Completed', 'Paid'],
                )
                ->groupBy('customer_name', 'contact_number')
                ->get()
                ->groupBy(fn (Order $order): string => $this->customerKey(
                    $order->customer_name,
                    $order->contact_number,
                ))->map(fn ($groups): array => [
                    'orders_count' => $groups->sum('orders_count'),
                    'completed_orders' => $groups->sum('completed_orders'),
                    'lifetime_value' => $groups->sum('lifetime_value'),
                ]);

        return response()->json([
            'customers' => collect($customers->items())->map(function (Customer $customer) use ($orderStats): array {
                $stats = $orderStats->get($this->customerKey($customer->name, $customer->contact_number));

                return [
                    'database_id' => $customer->getKey(),
                    'name' => $customer->name,
                    'phone' => $customer->contact_number,
                    'email' => $customer->email,
                    'orders' => (int) ($stats['orders_count'] ?? 0),
                    'completed_orders' => (int) ($stats['completed_orders'] ?? 0),
                    'lifetime_value' => (int) ($stats['lifetime_value'] ?? 0),
                    'last_order' => $customer->last_order_at?->format('M j, Y') ?? 'No order date',
                    'notes' => $customer->notes,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => max(1, $customers->lastPage()),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $name = $customer->name;
        $customer->delete();

        return response()->json([
            'message' => "Customer details for {$name} were deleted.",
        ]);
    }

    private function customerKey(string $name, string $contactNumber): string
    {
        return mb_strtolower(trim($name)).'|'.preg_replace('/\D+/', '', $contactNumber);
    }
}
