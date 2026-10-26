@php
    $isDelivery = $order['fulfillment'] === 'Delivery';
@endphp

<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <meta name="description" content="Review and confirm your Toño’s Bellyssimo booking request.">
    <title>Confirm Your Order | Toño’s Bellyssimo</title>
    <link href="{{ asset('css/site-v3.css') }}?v={{ filemtime(public_path('css/site-v3.css')) }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased selection:bg-gold/60">
    <x-site-header active="order" />

    <main class="pt-20">
        <section class="relative overflow-hidden bg-paper py-12 md:py-16">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-gold/25 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-4 md:px-8 lg:px-14">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-gold/35 px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-rust">
                        <span aria-hidden="true" class="material-symbols-outlined text-base">fact_check</span>
                        Final review
                    </span>
                    <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight text-wine md:text-5xl">Confirm Your Booking Request</h1>
                    <p class="mt-4 text-lg leading-8 text-muted">Check every detail below before placing your request. No payment is collected at this stage.</p>
                </div>

                <ol class="mx-auto mt-8 grid max-w-3xl grid-cols-3 gap-2 text-center text-xs font-bold sm:text-sm" aria-label="Booking progress">
                    <li class="rounded-xl bg-white p-3 text-rust shadow-sm"><span class="block text-lg">✓</span>Details</li>
                    <li class="rounded-xl bg-wine p-3 text-white shadow-sm"><span class="block text-lg">2</span>Review</li>
                    <li class="rounded-xl bg-white/65 p-3 text-muted"><span class="block text-lg">3</span>Received</li>
                </ol>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 md:px-8 lg:grid-cols-12 lg:px-14 lg:py-16">
            <div class="space-y-6 lg:col-span-8">
                <article class="rounded-3xl bg-white p-6 shadow-xl shadow-wine/5 md:p-8">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Selected varieties</p>
                            <h2 class="mt-2 font-display text-2xl font-extrabold text-wine">Your Belly Orders</h2>
                        </div>
                        <span class="rounded-full bg-blush px-3 py-1 text-sm font-bold text-wine">{{ $order['quantity'] }} {{ $order['quantity'] === 1 ? 'belly' : 'bellies' }}</span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($order['items'] as $index => $item)
                            <div class="overflow-hidden rounded-2xl border border-wine/10 bg-cream">
                                <picture>
                                    @if (str_ends_with($item['package']['image'], '.jpg'))
                                        <source type="image/avif" srcset="{{ asset(substr($item['package']['image'], 0, -4).'.avif') }}">
                                        <source type="image/webp" srcset="{{ asset(substr($item['package']['image'], 0, -4).'.webp') }}">
                                    @endif
                                    <img class="h-36 w-full object-cover" src="{{ asset($item['package']['image']) }}" width="480" height="253" loading="lazy" decoding="async" alt="{{ $item['package']['name'] }} lechon belly">
                                </picture>
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-extrabold uppercase tracking-wider text-rust">Belly Order #{{ $index + 1 }}</p>
                                            <h3 class="mt-1 font-display text-lg font-extrabold text-wine">{{ $item['package']['name'] }}</h3>
                                        </div>
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-extrabold',
                                            'bg-gold/40 text-rust' => $item['flavor'] === 'Spicy',
                                            'bg-blush text-wine' => $item['flavor'] === 'Regular',
                                        ])>{{ $item['flavor'] }}</span>
                                    </div>
                                    <p class="mt-2 text-xs leading-5 text-muted">{{ $item['package']['weight'] }} • {{ $item['package']['serves'] }}{{ $item['flavor'] === 'Spicy' ? ' • +₱50 spicy' : '' }}</p>
                                    <p class="mt-3 border-t border-wine/10 pt-3 text-right font-display font-extrabold text-wine">₱{{ number_format($item['line_total']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <div class="grid gap-6 md:grid-cols-2">
                    <article class="rounded-3xl bg-white p-6 shadow-lg shadow-wine/5">
                        <h2 class="flex items-center gap-2 font-display text-xl font-extrabold text-wine">
                            <span aria-hidden="true" class="material-symbols-outlined text-rust">calendar_month</span>
                            Schedule and contact
                        </h2>
                        <dl class="mt-5 space-y-4 text-sm">
                            <div><dt class="text-muted">Customer</dt><dd class="mt-1 font-bold">{{ $order['customer_name'] }}</dd></div>
                            <div><dt class="text-muted">Contact number</dt><dd class="mt-1 font-bold">{{ $order['contact_number'] }}</dd></div>
                            <div><dt class="text-muted">Email updates</dt><dd class="mt-1 font-bold">{{ $order['email'] }}</dd></div>
                            <div><dt class="text-muted">Preferred schedule</dt><dd class="mt-1 font-bold">{{ $order['preferred_date_display'] }} at {{ $order['preferred_time_display'] }}</dd></div>
                            @if ($order['notes'])
                                <div><dt class="text-muted">Notes</dt><dd class="mt-1 whitespace-pre-line font-medium">{{ $order['notes'] }}</dd></div>
                            @endif
                        </dl>
                    </article>

                    <article class="rounded-3xl bg-white p-6 shadow-lg shadow-wine/5">
                        <h2 class="flex items-center gap-2 font-display text-xl font-extrabold text-wine">
                            <span aria-hidden="true" class="material-symbols-outlined text-rust">{{ $isDelivery ? 'local_shipping' : 'storefront' }}</span>
                            {{ $order['fulfillment'] }} details
                        </h2>
                        @if ($isDelivery)
                            <p class="mt-5 text-sm leading-6 text-muted">Delivery location</p>
                            <p class="mt-1 whitespace-pre-line font-bold text-wine">{{ $order['delivery_location'] }}</p>
                            <div class="mt-4 rounded-xl bg-gold/20 p-4 text-sm leading-6 text-muted">
                                The customer shoulders the delivery fee. The exact amount will be confirmed according to this location.
                            </div>
                        @else
                            <p class="mt-5 text-sm leading-6 text-muted">Bellyssimo Pick-Up Location</p>
                            <p class="mt-1 font-bold text-wine">Perayra Village, Maningcol, Ozamiz City</p>
                            <a class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-rust underline underline-offset-4" href="https://maps.app.goo.gl/3XZ87Ep9kWLDPzh47" rel="noopener noreferrer" target="_blank">
                                <span aria-hidden="true" class="material-symbols-outlined text-lg">location_on</span>
                                Open in Google Maps
                            </a>
                        @endif
                    </article>
                </div>

                <div class="rounded-2xl bg-gold/20 p-5 text-sm leading-6 text-muted">
                    <strong class="text-wine">Important:</strong> Your request remains subject to availability. It becomes a confirmed booking only after Toño’s Bellyssimo contacts you directly.
                </div>
            </div>

            <aside class="lg:col-span-4">
                <div class="sticky top-28 rounded-3xl bg-wine p-6 text-white shadow-2xl shadow-wine/20 md:p-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-gold">Payment breakdown</p>
                    <div class="mt-6 space-y-4 text-sm">
                        @foreach ($order['items'] as $index => $item)
                            <div class="flex justify-between gap-4">
                                <span class="text-white/70">#{{ $index + 1 }} {{ $item['package']['name'] }} {{ $item['package']['weight'] }}</span>
                                <span class="font-bold">₱{{ number_format($item['line_total']) }}</span>
                            </div>
                        @endforeach
                        <div class="h-px bg-white/15"></div>
                        <div class="flex justify-between gap-4"><span class="text-white/70">Bellies subtotal</span><span class="font-bold">₱{{ number_format($order['package_subtotal']) }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-white/70">Spicy upgrades</span><span class="font-bold">{{ $order['spicy_count'] }} × ₱50 = ₱{{ number_format($order['spicy_subtotal']) }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-white/70">Belly order total</span><span class="font-bold">₱{{ number_format($order['order_total']) }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-white/70">Payment method</span><span class="font-bold">{{ $order['payment_method'] }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-white/70">{{ $isDelivery ? 'Delivery fee' : 'Pickup fee' }}</span><span class="font-bold text-right">{{ $isDelivery ? 'To be confirmed' : '₱0' }}</span></div>
                    </div>
                    <div class="my-6 h-px bg-white/15"></div>
                    <div class="flex items-end justify-between gap-4">
                        <span class="font-bold">Total payment</span>
                        <span class="text-right font-display text-2xl font-extrabold text-gold">₱{{ number_format($order['order_total']) }}{{ $isDelivery ? ' + fee' : '' }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-white/65">
                        @if ($order['payment_method'] === 'GCash')
                            GCash account details and payment instructions are provided only after schedule and availability confirmation.
                        @else
                            Cash payment is arranged after confirmation and collected at the confirmed pickup or delivery hand-off.
                        @endif
                    </p>

                    <form class="mt-7" method="POST" action="{{ route('order.place') }}">
                        @csrf
                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-gold px-5 py-4 font-extrabold text-wine shadow-lg transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-white/30" type="submit">
                            <span aria-hidden="true" class="material-symbols-outlined">send</span>
                            Place Booking Request
                        </button>
                    </form>
                    <a class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10" href="{{ route('order', ['edit' => 1]) }}">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg">edit</span>
                        Edit Order Details
                    </a>
                </div>
            </aside>
        </section>
    </main>

    <footer class="bg-blush px-4 py-10 text-center md:px-8">
        <p class="font-display text-xl font-extrabold text-wine">Toño’s Bellyssimo</p>
        <p class="mt-2 text-sm font-semibold italic text-rust">Beautifully crispy, dangerously juicy</p>
        <p class="mt-3 text-xs text-muted">Est. 2026 • Ozamiz City, Misamis Occidental</p>
    </footer>
</body>
</html>
