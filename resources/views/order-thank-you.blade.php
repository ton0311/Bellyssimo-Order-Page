@php
    $isDelivery = $order['fulfillment'] === 'Delivery';
@endphp

<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your Toño’s Bellyssimo booking request has been received.">
    <title>Thank You | Toño’s Bellyssimo</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-cream: #fff8f6;
            --color-paper: #fff1eb;
            --color-blush: #f5e5de;
            --color-wine: #5b0d07;
            --color-wine-soft: #7a241a;
            --color-rust: #9c4502;
            --color-gold: #f4be52;
            --color-ink: #221a16;
            --color-muted: #6f5853;
            --font-sans: "Plus Jakarta Sans", sans-serif;
            --font-display: "Epilogue", sans-serif;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            main { padding-top: 0 !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased selection:bg-gold/60">
    <div class="no-print">
        <x-site-header active="order" />
    </div>

    <main class="pt-20">
        <section class="relative overflow-hidden bg-paper py-12 md:py-16">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-gold/30 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-4 md:px-8 lg:px-14">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gold text-wine shadow-lg shadow-gold/20">
                            <span aria-hidden="true" class="material-symbols-outlined text-3xl">check</span>
                        </span>
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gold/35 px-3 py-1 text-xs font-extrabold uppercase tracking-wider text-rust">Request received</span>
                            <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-wine md:text-5xl">Thank you, {{ $order['customer_name'] }}!</h1>
                            <p class="mt-3 max-w-2xl text-lg leading-8 text-muted">Your booking request has been placed successfully. Toño’s Bellyssimo will review the schedule and contact you directly.</p>
                        </div>
                    </div>
                    <button class="no-print inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-wine shadow-sm transition hover:bg-blush" type="button" onclick="window.print()">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg text-rust">print</span>
                        Print / Save Request
                    </button>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-8 md:px-8 lg:px-14">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#583e00] via-[#3c2900] to-wine p-6 text-white shadow-xl md:p-8">
                <span aria-hidden="true" class="material-symbols-outlined absolute -bottom-12 right-0 text-[180px] text-white/5">verified</span>
                <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-gold">Booking reference</p>
                        <p class="mt-2 font-display text-3xl font-extrabold">#{{ $order['reference'] }}</p>
                        <p class="mt-2 text-sm text-white/70">Submitted {{ $order['submitted_at'] }} <span class="font-bold text-gold">PHT</span></p>
                        <p class="mt-1 text-xs text-white/55">Philippine Standard Time (Asia/Manila)</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-gold px-4 py-2 text-sm font-extrabold text-wine">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg">hourglass_top</span>
                        Pending Availability Confirmation
                    </span>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-8 px-4 pb-16 md:px-8 lg:grid-cols-12 lg:px-14">
            <div class="space-y-6 lg:col-span-8">
                <article class="rounded-3xl bg-white p-6 shadow-xl shadow-wine/5 md:p-8">
                    <div class="border-b border-wine/10 pb-6">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Complete order summary</p>
                        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
                            <h2 class="font-display text-2xl font-extrabold text-wine">Your Belly Orders</h2>
                            <span class="rounded-full bg-blush px-3 py-1 text-sm font-bold text-wine">{{ $order['quantity'] }} {{ $order['quantity'] === 1 ? 'belly' : 'bellies' }}</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        <div>
                            <h3 class="font-bold text-wine">Package and flavor varieties</h3>
                            <ul class="mt-3 space-y-2 text-sm">
                                @foreach ($order['items'] as $index => $item)
                                    <li class="rounded-xl bg-cream px-4 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="font-bold text-wine">Belly Order #{{ $index + 1 }}</span>
                                            <span class="font-bold text-wine">₱{{ number_format($item['line_total']) }}</span>
                                        </div>
                                        <span class="mt-1 block text-muted">{{ $item['package']['name'] }} • {{ $item['package']['weight'] }} • {{ $item['flavor'] }}{{ $item['flavor'] === 'Spicy' ? ' (+₱50)' : '' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <dl class="space-y-4 text-sm">
                            <div><dt class="text-muted">Preferred schedule</dt><dd class="mt-1 font-bold">{{ $order['preferred_date_display'] }} at {{ $order['preferred_time_display'] }}</dd></div>
                            <div><dt class="text-muted">Contact number</dt><dd class="mt-1 font-bold">{{ $order['contact_number'] }}</dd></div>
                            <div><dt class="text-muted">Fulfillment</dt><dd class="mt-1 font-bold">{{ $order['fulfillment'] }}</dd></div>
                            <div><dt class="text-muted">Payment method</dt><dd class="mt-1 font-bold">{{ $order['payment_method'] }}</dd></div>
                            @if ($isDelivery)
                                <div><dt class="text-muted">Delivery location</dt><dd class="mt-1 whitespace-pre-line font-bold">{{ $order['delivery_location'] }}</dd></div>
                            @else
                                <div><dt class="text-muted">Pickup location</dt><dd class="mt-1 font-bold">Perayra Village, Maningcol, Ozamiz City</dd></div>
                            @endif
                        </dl>
                    </div>

                    <div class="mt-6 rounded-2xl bg-blush p-5">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4"><span class="text-muted">Bellies subtotal</span><span class="font-bold">₱{{ number_format($order['package_subtotal']) }}</span></div>
                            <div class="flex justify-between gap-4"><span class="text-muted">Spicy upgrades</span><span class="font-bold">{{ $order['spicy_count'] }} × ₱50 = ₱{{ number_format($order['spicy_subtotal']) }}</span></div>
                            <div class="flex justify-between gap-4"><span class="text-muted">Payment method</span><span class="font-bold">{{ $order['payment_method'] }}</span></div>
                            <div class="flex justify-between gap-4"><span class="text-muted">{{ $isDelivery ? 'Delivery fee' : 'Pickup fee' }}</span><span class="font-bold text-right">{{ $isDelivery ? 'To be confirmed by location' : '₱0' }}</span></div>
                        </div>
                        <div class="mt-5 flex items-end justify-between gap-4 border-t border-wine/10 pt-5">
                            <span class="font-extrabold text-wine">Total payment</span>
                            <span class="text-right font-display text-2xl font-extrabold text-wine">₱{{ number_format($order['order_total']) }}{{ $isDelivery ? ' + fee' : '' }}</span>
                        </div>
                    </div>

                    @if ($order['notes'])
                        <div class="mt-6 rounded-xl border border-wine/10 p-4 text-sm">
                            <p class="font-bold text-wine">Your notes</p>
                            <p class="mt-2 whitespace-pre-line leading-6 text-muted">{{ $order['notes'] }}</p>
                        </div>
                    @endif
                </article>

                <article class="rounded-3xl bg-white p-6 shadow-lg shadow-wine/5 md:p-8">
                    <h2 class="font-display text-2xl font-extrabold text-wine">What happens next?</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-gold/25 p-5"><span class="text-xs font-extrabold uppercase tracking-wider text-rust">Step 1 • Current</span><h3 class="mt-2 font-bold text-wine">Request Review</h3><p class="mt-2 text-sm leading-6 text-muted">The team reviews your schedule, quantity, flavors, and fulfillment details.</p></div>
                        <div class="rounded-2xl bg-cream p-5"><span class="text-xs font-extrabold uppercase tracking-wider text-muted">Step 2</span><h3 class="mt-2 font-bold text-wine">Direct Contact</h3><p class="mt-2 text-sm leading-6 text-muted">Toño’s Bellyssimo contacts you to confirm availability and payment instructions.</p></div>
                        <div class="rounded-2xl bg-cream p-5"><span class="text-xs font-extrabold uppercase tracking-wider text-muted">Step 3</span><h3 class="mt-2 font-bold text-wine">Feast Day</h3><p class="mt-2 text-sm leading-6 text-muted">Your belly is prepared for pickup or delivery at the confirmed schedule.</p></div>
                    </div>
                </article>

                <div class="rounded-2xl bg-gold/20 p-5 text-sm leading-6 text-muted">
                    <strong class="text-wine">Please wait for confirmation:</strong> this request is subject to roasting capacity and schedule availability. No payment is collected on this website.
                </div>
            </div>

            <aside class="no-print lg:col-span-4">
                <div class="sticky top-28 rounded-3xl bg-wine p-6 text-center text-white shadow-2xl shadow-wine/20 md:p-8">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-gold text-wine">
                        <span aria-hidden="true" class="material-symbols-outlined text-2xl">chat</span>
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-extrabold">Direct Kitchen Assist</h2>
                    <p class="mt-3 text-sm leading-6 text-white/70">Need to clarify timing, delivery location, or celebration details? Message the team directly.</p>
                    <a class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gold px-5 py-4 font-extrabold text-wine transition hover:bg-white" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
                        <span aria-hidden="true" class="material-symbols-outlined">forum</span>
                        Message on Facebook
                    </a>
                    <a class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10" href="tel:+639762271228">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg">call</span>
                        Call 0976-2271-228
                    </a>
                    <a class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10" href="{{ route('belly') }}">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg">restaurant_menu</span>
                        View Belly Packages
                    </a>
                    <a class="mt-3 flex w-full items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white/75 transition hover:text-white" href="{{ route('home') }}">
                        <span aria-hidden="true" class="material-symbols-outlined text-lg">home</span>
                        Back to Home
                    </a>
                </div>
            </aside>
        </section>
    </main>

    <footer class="no-print bg-blush px-4 py-10 text-center md:px-8">
        <p class="font-display text-xl font-extrabold text-wine">Toño’s Bellyssimo</p>
        <p class="mt-2 text-sm font-semibold italic text-rust">Beautifully crispy, dangerously juicy</p>
        <p class="mt-3 text-xs text-muted">Est. 2026 • Ozamiz City, Misamis Occidental</p>
    </footer>
</body>
</html>
