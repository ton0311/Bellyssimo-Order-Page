<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New booking {{ $order->reference }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f8f1e7; font-family:Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="width:100%; background-color:#f8f1e7;">
        <tr>
            <td align="center" style="padding-top:24px; padding-right:12px; padding-bottom:24px; padding-left:12px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="width:100%; max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td bgcolor="#5b0d07" style="background-color:#5b0d07; padding-top:28px; padding-right:28px; padding-bottom:28px; padding-left:28px;">
                            <p style="margin-top:0; margin-right:0; margin-bottom:8px; margin-left:0; color:#f4be52; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:18px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">New booking request</p>
                            <h1 style="margin-top:0; margin-right:0; margin-bottom:8px; margin-left:0; color:#ffffff; font-family:Georgia, 'Times New Roman', serif; font-size:28px; line-height:34px; font-weight:700;">{{ $order->reference }}</h1>
                            <p style="margin-top:0; margin-right:0; margin-bottom:0; margin-left:0; color:#f8e8dc; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:24px;">{{ $order->customer_name }} booked {{ $order->quantity }} {{ Str::plural('belly', $order->quantity) }} totaling ₱{{ number_format($order->order_total) }}.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:28px; padding-right:28px; padding-bottom:12px; padding-left:28px;">
                            <h2 style="margin-top:0; margin-right:0; margin-bottom:12px; margin-left:0; color:#5b0d07; font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:24px; font-weight:700;">Order details</h2>
                            @foreach ($order->items as $index => $item)
                                <p style="margin-top:0; margin-right:0; margin-bottom:8px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>{{ $index + 1 }}.</strong> {{ $item['package']['name'] }} · {{ $item['package']['weight'] }} · {{ $item['flavor'] }} — ₱{{ number_format($item['line_total']) }}</p>
                            @endforeach
                            <p style="margin-top:14px; margin-right:0; margin-bottom:0; margin-left:0; color:#5b0d07; font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:26px; font-weight:700;">Total: ₱{{ number_format($order->order_total) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:16px; padding-right:28px; padding-bottom:12px; padding-left:28px;">
                            <h2 style="margin-top:0; margin-right:0; margin-bottom:12px; margin-left:0; color:#5b0d07; font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:24px; font-weight:700;">Customer and schedule</h2>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Customer:</strong> {{ $order->customer_name }}</p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Phone:</strong> <a href="tel:{{ $order->contact_number }}" style="color:#9c4502; text-decoration:underline;">{{ $order->contact_number }}</a></p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Email:</strong> <a href="mailto:{{ $order->email }}" style="color:#9c4502; text-decoration:underline;">{{ $order->email }}</a></p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Preferred schedule:</strong> {{ $order->preferred_date->format('F j, Y') }} at {{ date('g:i A', strtotime($order->preferred_time)) }}</p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Fulfillment:</strong> {{ $order->fulfillment }}@if ($order->fulfillment === 'Delivery') — {{ $order->delivery_location }}@endif</p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:6px; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Payment:</strong> {{ $order->payment_method }} · {{ $order->payment_status }}</p>
                            <p style="margin-top:0; margin-right:0; margin-bottom:0; margin-left:0; color:#2d2520; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px;"><strong>Notes:</strong> {{ $order->notes ?: 'None' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:20px; padding-right:28px; padding-bottom:32px; padding-left:28px;">
                            <table cellpadding="0" cellspacing="0" border="0" role="presentation">
                                <tr>
                                    <td bgcolor="#9c4502" style="background-color:#9c4502; border-radius:10px;">
                                        <a href="{{ route('admin.orders') }}" style="display:inline-block; padding-top:13px; padding-right:20px; padding-bottom:13px; padding-left:20px; color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:700; text-decoration:none;">Review order in dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin-top:14px; margin-right:0; margin-bottom:0; margin-left:0; color:#6f625a; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:18px;">Reply to this email to contact the customer directly.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
