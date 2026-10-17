<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const BUSINESS_TIMEZONE = 'Asia/Manila';

    private const PACKAGES = [
        'barkadahan' => [
            'name' => 'The Bellyssimo Barkadahan',
            'weight' => '3 kg',
            'serves' => '8–10 pax',
            'price' => 2000,
            'image' => 'images/lechon-barkadahan-3kg.png',
        ],
        'salo-salo' => [
            'name' => 'Bellyssimo Salo-Salo',
            'weight' => '4 kg',
            'serves' => '10–14 pax',
            'price' => 2550,
            'image' => 'images/lechon-salo-salo-4kg.png',
        ],
        'handaan' => [
            'name' => 'Bellyssimo Handaan',
            'weight' => '5 kg',
            'serves' => '15–18 pax',
            'price' => 2950,
            'image' => 'images/lechon-handaan-5kg.png',
        ],
    ];

    public function create(Request $request): View
    {
        return view('order', [
            'draft' => $request->session()->get('order.draft'),
        ]);
    }

    public function confirm(ConfirmOrderRequest $request): RedirectResponse
    {
        $request->session()->put('order.draft', $this->buildOrder($request->validated()));
        $request->session()->forget('order.placed');

        return redirect()->route('order.confirmation');
    }

    public function confirmation(Request $request): View|RedirectResponse
    {
        $order = $request->session()->get('order.draft');

        if (! is_array($order)) {
            return redirect()->route('order');
        }

        return view('order-confirmation', [
            'order' => $order,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $order = $request->session()->get('order.draft');

        if (! is_array($order)) {
            if ($request->session()->has('order.placed')) {
                return redirect()->route('order.thank-you');
            }

            return redirect()->route('order');
        }

        $submittedAt = now();
        $order['reference'] = 'TB-'.$submittedAt->copy()->setTimezone(self::BUSINESS_TIMEZONE)->format('Ymd').'-'.Str::upper(Str::random(4));
        $order['submitted_at'] = $this->formatSubmittedAt($submittedAt);

        $createdOrder = Order::query()->create([
            'reference' => $order['reference'],
            'items' => $order['items'],
            'quantity' => $order['quantity'],
            'package_subtotal' => $order['package_subtotal'],
            'spicy_subtotal' => $order['spicy_subtotal'],
            'order_total' => $order['order_total'],
            'fulfillment' => $order['fulfillment'],
            'delivery_location' => $order['delivery_location'],
            'customer_name' => $order['customer_name'],
            'contact_number' => $order['contact_number'],
            'preferred_date' => $order['preferred_date'],
            'preferred_time' => $order['preferred_time'],
            'notes' => $order['notes'],
            'status' => 'Pending confirmation',
            'payment_status' => 'Unpaid',
            'payment_method' => $order['payment_method'],
            'submitted_at' => $submittedAt,
        ]);
        Customer::query()->updateOrCreate(
            [
                'name' => $createdOrder->customer_name,
                'contact_number' => $createdOrder->contact_number,
            ],
            [
                'last_order_at' => $createdOrder->submitted_at,
            ],
        );

        $request->session()->put('order.placed', $order);
        $request->session()->forget('order.draft');

        return redirect()->route('order.thank-you');
    }

    public function thankYou(Request $request): View|RedirectResponse
    {
        $order = $request->session()->get('order.placed');

        if (! is_array($order)) {
            return redirect()->route('order');
        }

        $storedOrder = isset($order['reference'])
            ? Order::query()->where('reference', $order['reference'])->first()
            : null;

        if ($storedOrder?->submitted_at) {
            $order['submitted_at'] = $this->formatSubmittedAt($storedOrder->submitted_at);
        }

        return view('order-thank-you', [
            'order' => $order,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildOrder(array $data): array
    {
        $items = array_map(function (array $item): array {
            $package = self::PACKAGES[$item['package']];
            $spicyUpgrade = $item['flavor'] === 'Spicy' ? 50 : 0;

            return [
                'package_key' => $item['package'],
                'package' => $package,
                'flavor' => $item['flavor'],
                'spicy_upgrade' => $spicyUpgrade,
                'line_total' => $package['price'] + $spicyUpgrade,
            ];
        }, array_values($data['items']));
        $quantity = count($items);
        $spicyCount = count(array_filter(
            $items,
            fn (array $item): bool => $item['flavor'] === 'Spicy',
        ));
        $packageSubtotal = array_sum(array_column(array_column($items, 'package'), 'price'));
        $spicySubtotal = $spicyCount * 50;

        return [
            'items' => $items,
            'quantity' => $quantity,
            'spicy_count' => $spicyCount,
            'package_subtotal' => $packageSubtotal,
            'spicy_subtotal' => $spicySubtotal,
            'order_total' => $packageSubtotal + $spicySubtotal,
            'fulfillment' => $data['fulfillment'],
            'delivery_location' => $data['delivery_location'] ?? null,
            'payment_method' => $data['payment_method'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'customer_name' => Str::squish($data['first_name'].' '.$data['last_name']),
            'contact_number' => '+63'.$data['contact_number'],
            'preferred_date' => $data['preferred_date'],
            'preferred_date_display' => CarbonImmutable::parse($data['preferred_date'])->format('F j, Y'),
            'preferred_time' => $data['preferred_time'],
            'preferred_time_display' => CarbonImmutable::createFromFormat('H:i', $data['preferred_time'])->format('g:i A'),
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function formatSubmittedAt(CarbonInterface $submittedAt): string
    {
        return $submittedAt
            ->copy()
            ->setTimezone(self::BUSINESS_TIMEZONE)
            ->format('F j, Y • g:i A');
    }
}
