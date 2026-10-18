New booking request: {{ $order->reference }}

{{ $order->customer_name }} booked {{ $order->quantity }} {{ Str::plural('belly', $order->quantity) }} totaling ₱{{ number_format($order->order_total) }}.

ORDER DETAILS
@foreach ($order->items as $index => $item)
{{ $index + 1 }}. {{ $item['package']['name'] }} · {{ $item['package']['weight'] }} · {{ $item['flavor'] }} — ₱{{ number_format($item['line_total']) }}
@endforeach

Total: ₱{{ number_format($order->order_total) }}

CUSTOMER AND SCHEDULE
Customer: {{ $order->customer_name }}
Phone: {{ $order->contact_number }}
Email: {{ $order->email }}
Preferred schedule: {{ $order->preferred_date->format('F j, Y') }} at {{ date('g:i A', strtotime($order->preferred_time)) }}
Fulfillment: {{ $order->fulfillment }}@if ($order->fulfillment === 'Delivery') — {{ $order->delivery_location }}@endif
Payment: {{ $order->payment_method }} · {{ $order->payment_status }}
Notes: {{ $order->notes ?: 'None' }}

Review order: {{ route('admin.orders') }}

Reply to this email to contact the customer directly.
