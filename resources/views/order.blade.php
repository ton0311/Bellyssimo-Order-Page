@php
    $draft ??= null;
    $packageCatalog = [
        'barkadahan' => ['name' => 'The Bellyssimo Barkadahan', 'short_name' => 'Barkadahan', 'weight' => '3 kg', 'serves' => '8–10 pax', 'price' => 2000],
        'salo-salo' => ['name' => 'Bellyssimo Salo-Salo', 'short_name' => 'Salo-Salo', 'weight' => '4 kg', 'serves' => '10–14 pax', 'price' => 2550],
        'handaan' => ['name' => 'Bellyssimo Handaan', 'short_name' => 'Handaan', 'weight' => '5 kg', 'serves' => '15–18 pax', 'price' => 2950],
    ];
    $requestedPackage = request('package', 'salo-salo');
    $requestedPackage = array_key_exists($requestedPackage, $packageCatalog) ? $requestedPackage : 'salo-salo';
    $draftItemsInput = old('items', $draft['items'] ?? [['package' => $requestedPackage, 'flavor' => 'Regular']]);
    $draftItemsInput = is_array($draftItemsInput) ? array_slice(array_values($draftItemsInput), 0, 20) : [];
    $draftItems = [];

    foreach ($draftItemsInput as $item) {
        if (! is_array($item)) {
            continue;
        }

        $packageKey = $item['package'] ?? $item['package_key'] ?? null;
        $packageKey = is_string($packageKey) && array_key_exists($packageKey, $packageCatalog) ? $packageKey : 'salo-salo';
        $flavor = ($item['flavor'] ?? null) === 'Spicy' ? 'Spicy' : 'Regular';
        $draftItems[] = ['package' => $packageKey, 'flavor' => $flavor];
    }

    $draftItems = $draftItems === [] ? [['package' => $requestedPackage, 'flavor' => 'Regular']] : $draftItems;
    $selectedPackage = $draftItems[0]['package'];
    $selectedFulfillment = old('fulfillment', $draft['fulfillment'] ?? 'Pickup');
    $selectedFulfillment = in_array($selectedFulfillment, ['Pickup', 'Delivery'], true) ? $selectedFulfillment : 'Pickup';
    $selectedPaymentMethod = old('payment_method', $draft['payment_method'] ?? 'Cash');
    $selectedPaymentMethod = in_array($selectedPaymentMethod, ['Cash', 'GCash'], true) ? $selectedPaymentMethod : 'Cash';
    $legacyName = trim((string) ($draft['customer_name'] ?? ''));
    $legacyNameParts = preg_split('/\s+/', $legacyName, 2) ?: [];
    $draftFirstName = old('first_name', $draft['first_name'] ?? ($legacyNameParts[0] ?? ''));
    $draftLastName = old('last_name', $draft['last_name'] ?? ($legacyNameParts[1] ?? ''));
    $draftContactNumber = (string) old('contact_number', $draft['contact_number'] ?? '');
    $draftContactNumber = preg_replace('/\D+/', '', $draftContactNumber) ?? '';
    $draftContactNumber = str_starts_with($draftContactNumber, '63') && strlen($draftContactNumber) === 12
        ? substr($draftContactNumber, 2)
        : (str_starts_with($draftContactNumber, '0') && strlen($draftContactNumber) === 11 ? substr($draftContactNumber, 1) : $draftContactNumber);
    $draftEmail = old('email', $draft['email'] ?? '');
    $selectedPreferredTime = old('preferred_time', $draft['preferred_time'] ?? '');
    $preferredTimeOptions = array_map(
        fn (int $minutes): array => [
            'value' => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
            'label' => \Carbon\CarbonImmutable::createFromTime(intdiv($minutes, 60), $minutes % 60)->format('g:i A'),
        ],
        range(11 * 60, 19 * 60, 30),
    );
    $bookingCutoff = \Carbon\CarbonImmutable::now('Asia/Manila')->addHours(24);
@endphp

<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <meta name="description" content="Choose a Toño’s Bellyssimo lechon belly package and prepare your booking request in Ozamiz City.">
    <title>Book Your Belly | Toño’s Bellyssimo</title>
    <link rel="preload" as="image" href="{{ asset('images/order-hero-filipino-v2-800.jpg') }}" imagesrcset="{{ asset('images/order-hero-filipino-v2-800.jpg') }} 800w, {{ asset('images/order-hero-filipino-v2-1600.jpg') }} 1600w" imagesizes="100vw" fetchpriority="high">
    <link href="{{ asset('css/site-v3.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased selection:bg-gold/60">
    <x-site-header active="order" />

    <main class="pt-20">
        <section aria-label="Booking hero" class="relative isolate min-h-[520px] overflow-hidden py-14 md:min-h-[580px] md:py-20">
            <picture aria-hidden="true" class="absolute inset-0 -z-20 block">
                <source media="(max-width: 767px)" srcset="{{ asset('images/order-hero-filipino-v2-800.jpg') }}">
                <img src="{{ asset('images/order-hero-filipino-v2-1600.jpg') }}" srcset="{{ asset('images/order-hero-filipino-v2-800.jpg') }} 800w, {{ asset('images/order-hero-filipino-v2-1600.jpg') }} 1600w" sizes="100vw" width="1600" height="900" alt="" class="h-full w-full object-cover object-right" fetchpriority="high" decoding="async">
            </picture>
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-[#210705]/85 via-[#2b0905]/40 to-transparent"></div>
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-[#210705]/70 via-transparent to-black/15"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-20 -z-10 size-80 rounded-full bg-rust/25 blur-3xl"></div>
            <div class="relative mx-auto flex min-h-[410px] max-w-7xl items-center px-4 md:min-h-[440px] md:px-8 lg:px-14">
                <div class="max-w-3xl">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/90 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-rust shadow-lg backdrop-blur-sm">
                        <span aria-hidden="true" class="material-symbols-outlined text-base">location_on</span>
                        Ozamiz City, Misamis Occidental
                    </div>
                    <h1 class="max-w-3xl font-display text-4xl font-extrabold leading-tight tracking-tight text-white drop-shadow-lg md:text-6xl">Book Your Belly</h1>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-white/85 drop-shadow-sm">Mix package sizes and flavors in one booking request, then prepare the details we need to check your preferred schedule.</p>
                    <div class="mt-6 flex flex-wrap gap-3 text-sm font-bold">
                        <span class="rounded-full border border-white/25 bg-white/90 px-4 py-2 text-wine shadow-lg backdrop-blur-md">3 kg • 4 kg • 5 kg</span>
                        <span class="rounded-full border border-gold/40 bg-gold/90 px-4 py-2 text-wine shadow-lg backdrop-blur-md">Spicy +₱50</span>
                        <span class="rounded-full border border-white/25 bg-white/90 px-4 py-2 text-wine shadow-lg backdrop-blur-md">Est. 2026</span>
                    </div>
                    <div class="mt-7">
                        <a class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-5 py-3 font-bold text-white shadow-lg ring-1 ring-white/25 backdrop-blur-md transition hover:bg-white hover:text-wine" href="#packages">
                            Explore the Packages
                            <span aria-hidden="true" class="material-symbols-outlined text-xl">south</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="packages" class="mx-auto max-w-7xl px-4 py-14 md:px-8 lg:px-14 lg:py-20">
            <div class="mb-9 max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Step 1 • Explore the packages</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-wine md:text-4xl">Mix and match your feast</h2>
                <p class="mt-3 leading-7 text-muted">Choose a starting package here. In the booking form, every belly can have a different package size and its own Regular or Spicy flavor.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <label class="package-card relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 border-transparent bg-white shadow-lg shadow-wine/5 transition duration-300">
                    <input class="peer sr-only" type="radio" name="package-card" value="barkadahan" data-package-label="The Bellyssimo Barkadahan" data-price="2000" data-serves="8–10 pax" @checked($selectedPackage === 'barkadahan')>
                    <img class="h-48 w-full object-cover object-center" src="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }}" srcset="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }} 480w, {{ asset('images/lechon-barkadahan-3kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="The Bellyssimo Barkadahan lechon belly with three sliced portions showing lemongrass and spices">
                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-wine shadow">3 KG</span>
                    <span class="absolute right-4 top-4 hidden size-8 place-items-center rounded-full bg-rust text-white peer-checked:grid"><span aria-hidden="true" class="material-symbols-outlined text-lg">check</span></span>
                    <span class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-rust">Barkada gatherings</span>
                        <span class="mt-1 font-display text-2xl font-bold text-wine">The Bellyssimo Barkadahan</span>
                        <span class="mt-3 text-sm leading-6 text-muted">3 kg belly, good for at least 8–10 people.</span>
                        <span class="mt-6 flex items-end justify-between gap-3">
                            <span class="text-sm font-semibold text-muted">Whole belly</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,000</span>
                        </span>
                    </span>
                </label>

                <label class="package-card relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 border-rust bg-white shadow-xl shadow-wine/10 transition duration-300 lg:-translate-y-2">
                    <input class="peer sr-only" type="radio" name="package-card" value="salo-salo" data-package-label="Bellyssimo Salo-Salo" data-price="2550" data-serves="10–14 pax" @checked($selectedPackage === 'salo-salo')>
                    <span class="absolute inset-x-0 top-0 z-10 bg-rust py-1.5 text-center text-[11px] font-extrabold uppercase tracking-widest text-white">Most popular</span>
                    <img class="h-48 w-full object-cover object-center" src="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }}" srcset="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }} 480w, {{ asset('images/lechon-salo-salo-4kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Salo-Salo lechon belly with four sliced portions showing lemongrass and spices">
                    <span class="absolute left-4 top-10 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-wine shadow">4 KG</span>
                    <span class="absolute right-4 top-10 hidden size-8 place-items-center rounded-full bg-rust text-white peer-checked:grid"><span aria-hidden="true" class="material-symbols-outlined text-lg">check</span></span>
                    <span class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-rust">Family gathering size</span>
                        <span class="mt-1 font-display text-2xl font-bold text-wine">Bellyssimo Salo-Salo</span>
                        <span class="mt-3 text-sm leading-6 text-muted">4 kg belly, good for at least 10–14 people.</span>
                        <span class="mt-6 flex items-end justify-between gap-3">
                            <span class="text-sm font-semibold text-muted">Whole belly</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,550</span>
                        </span>
                    </span>
                </label>

                <label class="package-card relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 border-transparent bg-white shadow-lg shadow-wine/5 transition duration-300">
                    <input class="peer sr-only" type="radio" name="package-card" value="handaan" data-package-label="Bellyssimo Handaan" data-price="2950" data-serves="15–18 pax" @checked($selectedPackage === 'handaan')>
                    <img class="h-48 w-full object-cover object-center" src="{{ asset('images/lechon-handaan-5kg-480.jpg') }}" srcset="{{ asset('images/lechon-handaan-5kg-480.jpg') }} 480w, {{ asset('images/lechon-handaan-5kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Handaan feast with five sliced lechon belly portions showing lemongrass and spices">
                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-wine shadow">5 KG</span>
                    <span class="absolute right-4 top-4 hidden size-8 place-items-center rounded-full bg-rust text-white peer-checked:grid"><span aria-hidden="true" class="material-symbols-outlined text-lg">check</span></span>
                    <span class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-rust">Fiesta and party size</span>
                        <span class="mt-1 font-display text-2xl font-bold text-wine">Bellyssimo Handaan</span>
                        <span class="mt-3 text-sm leading-6 text-muted">5 kg belly, good for at least 15–18 people.</span>
                        <span class="mt-6 flex items-end justify-between gap-3">
                            <span class="text-sm font-semibold text-muted">Whole belly</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,950</span>
                        </span>
                    </span>
                </label>
            </div>
        </section>

        <section id="booking-form" class="bg-paper py-14 lg:py-20">
            <div class="mx-auto max-w-4xl px-4 md:px-8 lg:px-14">
                <div>
                    <div class="rounded-3xl bg-white p-6 shadow-xl shadow-wine/5 md:p-8">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Step 2 • Your details</p>
                        <h2 class="mt-2 font-display text-3xl font-extrabold text-wine">Prepare your booking request</h2>
                        <p class="mt-3 text-sm leading-6 text-muted">Complete the form to create a request summary. You’ll still need to send it to Toño’s Bellyssimo for availability checking.</p>

                        @if ($errors->any())
                            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                                <p class="font-bold">Please review the following details:</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="mt-8 space-y-6" id="order-form" method="POST" action="{{ route('order.confirm') }}">
                            @csrf
                            <fieldset>
                                <legend class="text-sm font-bold text-ink">Your belly orders</legend>
                                <p class="mt-1 text-xs leading-5 text-muted">Choose the package and flavor separately for every lechon belly. You can add up to 20 bellies in one request.</p>
                                <div class="mt-4 space-y-4" id="order-item-list"></div>
                                <button class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-rust/35 bg-gold/10 px-4 py-3.5 text-sm font-extrabold text-wine transition hover:border-rust hover:bg-gold/20 focus:outline-none focus:ring-4 focus:ring-rust/10" id="add-order-item" type="button">
                                    <span aria-hidden="true" class="material-symbols-outlined">add_circle</span>
                                    Add Another Belly
                                </button>
                                <p class="mt-2 text-xs leading-5 text-muted" id="order-limit-note">Each added belly may use a different Barkadahan, Salo-Salo, or Handaan package.</p>
                            </fieldset>

                            <fieldset>
                                <legend class="mb-3 text-sm font-bold text-ink">Pickup or delivery</legend>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-wine/10 bg-cream p-4 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="mt-1 size-4 accent-rust" type="radio" name="fulfillment" value="Pickup" @checked($selectedFulfillment === 'Pickup')>
                                        <span>
                                            <span class="block font-bold text-wine">Pickup</span>
                                            <span class="text-xs leading-5 text-muted">No fulfillment fee</span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-wine/10 bg-cream p-4 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="mt-1 size-4 accent-rust" type="radio" name="fulfillment" value="Delivery" @checked($selectedFulfillment === 'Delivery')>
                                        <span>
                                            <span class="block font-bold text-wine">Delivery</span>
                                            <span class="text-xs leading-5 text-muted">Customer shoulders the location-based fee</span>
                                        </span>
                                    </label>
                                </div>

                                <div @class(['mt-3 rounded-xl border border-rust/15 bg-gold/15 p-4', 'hidden' => $selectedFulfillment === 'Delivery']) id="pickup-details">
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-rust">Bellyssimo Pick-Up Location</p>
                                    <p class="mt-1 font-bold text-wine">Perayra Village, Maningcol, Ozamiz City</p>
                                    <a class="mt-2 inline-flex items-center gap-1.5 text-sm font-bold text-rust underline decoration-rust/30 underline-offset-4 hover:text-wine" href="https://maps.app.goo.gl/3XZ87Ep9kWLDPzh47" rel="noopener noreferrer" target="_blank">
                                        <span aria-hidden="true" class="material-symbols-outlined text-lg">location_on</span>
                                        Open in Google Maps
                                    </a>
                                </div>

                                <div @class(['mt-3', 'hidden' => $selectedFulfillment !== 'Delivery']) id="delivery-details">
                                    <label class="block text-sm font-bold text-ink" for="delivery-location">Delivery location
                                        <textarea class="mt-2 min-h-24 w-full resize-y rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" id="delivery-location" name="delivery_location" maxlength="300" placeholder="Barangay, street, landmark, and city">{{ old('delivery_location', $draft['delivery_location'] ?? '') }}</textarea>
                                    </label>
                                    <p class="mt-2 text-xs leading-5 text-muted">Delivery fee will be shouldered by the customer.</p>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="mb-3 text-sm font-bold text-ink">Payment option</legend>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-wine/10 bg-cream p-4 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="mt-1 size-4 accent-rust" type="radio" name="payment_method" value="Cash" required @checked($selectedPaymentMethod === 'Cash')>
                                        <span class="flex gap-3">
                                            <span aria-hidden="true" class="material-symbols-outlined text-rust">payments</span>
                                            <span>
                                                <span class="block font-bold text-wine">Cash</span>
                                                <span class="text-xs leading-5 text-muted">Pay at the confirmed pickup or delivery hand-off.</span>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-wine/10 bg-cream p-4 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="mt-1 size-4 accent-rust" type="radio" name="payment_method" value="GCash" required @checked($selectedPaymentMethod === 'GCash')>
                                        <span class="flex gap-3">
                                            <span aria-hidden="true" class="material-symbols-outlined text-rust">account_balance_wallet</span>
                                            <span>
                                                <span class="block font-bold text-wine">GCash</span>
                                                <span class="text-xs leading-5 text-muted">GCash payment details will be sent to your email after your booking is confirmed.</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <p class="mt-2 text-xs leading-5 text-muted">Your selection records your preferred payment method. No payment is collected while sending this booking request.</p>
                            </fieldset>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <label class="block text-sm font-bold text-ink">First name <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <input class="mt-2 w-full rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" type="text" name="first_name" autocomplete="given-name" required maxlength="60" value="{{ $draftFirstName }}" placeholder="e.g. Juan">
                                </label>
                                <label class="block text-sm font-bold text-ink">Last name <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <input class="mt-2 w-full rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" type="text" name="last_name" autocomplete="family-name" required maxlength="60" value="{{ $draftLastName }}" placeholder="e.g. Dela Cruz">
                                </label>
                                <label class="block text-sm font-bold text-ink sm:col-span-2">Contact number <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <span class="mt-2 flex overflow-hidden rounded-xl border border-wine/15 bg-cream transition focus-within:border-rust focus-within:ring-4 focus-within:ring-rust/10">
                                        <span class="flex items-center border-r border-wine/10 bg-blush px-4 font-extrabold text-wine">+63</span>
                                        <input class="min-w-0 flex-1 bg-transparent px-4 py-3 font-normal outline-none" type="tel" name="contact_number" autocomplete="tel-national" required inputmode="numeric" pattern="9[0-9]{9}" maxlength="10" value="{{ $draftContactNumber }}" placeholder="917 123 4567" aria-describedby="contact-number-note">
                                    </span>
                                    <span class="mt-2 block text-xs font-normal leading-5 text-muted" id="contact-number-note">Do not include the first 0. Enter the 10-digit mobile number starting with 9.</span>
                                </label>
                                <label class="block text-sm font-bold text-ink sm:col-span-2">Email address <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <input class="mt-2 w-full rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" type="email" name="email" autocomplete="email" required maxlength="254" value="{{ $draftEmail }}" placeholder="e.g. juan@example.com" aria-describedby="email-note">
                                    <span class="mt-2 block text-xs font-normal leading-5 text-muted" id="email-note">Your booking confirmation and future status updates will be sent here.</span>
                                </label>
                                <label class="block text-sm font-bold text-ink">Preferred date <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <input class="mt-2 w-full rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" type="date" name="preferred_date" required value="{{ old('preferred_date', $draft['preferred_date'] ?? '') }}">
                                    <span class="mt-2 block text-xs font-normal leading-5 text-muted">Please book at least 24 hours before your preferred pickup or delivery schedule.</span>
                                </label>
                                <label class="block text-sm font-bold text-ink">Preferred pickup or delivery time <span aria-hidden="true" style="color: #b91c1c;">*</span>
                                    <select class="mt-2 w-full rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" name="preferred_time" required>
                                        <option value="">Choose a pickup or delivery time</option>
                                        @foreach ($preferredTimeOptions as $timeOption)
                                            <option value="{{ $timeOption['value'] }}" @selected($selectedPreferredTime === $timeOption['value'])>{{ $timeOption['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <span class="mt-2 block text-xs font-normal leading-5 text-muted">Select the time you want to pick up your order or receive the delivery. Available from 11:00 AM to 7:00 PM.</span>
                                </label>
                            </div>

                            <label class="block text-sm font-bold text-ink">Notes <span class="font-normal text-muted">(optional)</span>
                                <textarea class="mt-2 min-h-28 w-full resize-y rounded-xl border border-wine/15 bg-cream px-4 py-3 font-normal outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" name="notes" maxlength="300" placeholder="Share any details you want the team to know.">{{ old('notes', $draft['notes'] ?? '') }}</textarea>
                            </label>

                            <div class="rounded-xl bg-blush p-4 text-sm leading-6 text-muted">
                                <strong class="text-wine">Booking request only:</strong> submitting this form does not confirm your order. Your preferred schedule remains subject to availability until Toño’s Bellyssimo confirms it directly.
                            </div>

                            <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-wine-soft px-5 py-4 font-bold text-white shadow-lg shadow-wine/15 transition hover:bg-rust focus:outline-none focus:ring-4 focus:ring-gold/50" type="submit">
                                <span aria-hidden="true" class="material-symbols-outlined">receipt_long</span>
                                Review Booking Request
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <footer class="bg-blush px-4 py-10 text-center md:px-8">
        <p class="font-display text-xl font-extrabold text-wine">Toño’s Bellyssimo</p>
        <p class="mt-2 text-sm font-semibold italic text-rust">Beautifully crispy, dangerously juicy</p>
        <p class="mt-3 text-xs text-muted">Est. 2026 • Ozamiz City, Misamis Occidental</p>
    </footer>

    <script>
        const packageCatalog = {{ Js::from($packageCatalog) }};
        const packageInputs = [...document.querySelectorAll('input[name="package-card"]')];
        const fulfillmentInputs = [...document.querySelectorAll('input[name="fulfillment"]')];
        const orderItemList = document.querySelector('#order-item-list');
        const addOrderItemButton = document.querySelector('#add-order-item');
        const orderLimitNote = document.querySelector('#order-limit-note');
        const pickupDetails = document.querySelector('#pickup-details');
        const deliveryDetails = document.querySelector('#delivery-details');
        const deliveryLocationInput = document.querySelector('#delivery-location');
        const pesos = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });
        let orderItems = {{ Js::from($draftItems) }};

        function selectedStarterPackage() {
            return packageInputs.find((input) => input.checked)?.value ?? 'salo-salo';
        }

        function selectedFulfillment() {
            return fulfillmentInputs.find((input) => input.checked)?.value ?? 'Pickup';
        }

        function packageOptions(selectedPackage) {
            return Object.entries(packageCatalog).map(([key, packageDetails]) => `
                <option value="${key}" ${key === selectedPackage ? 'selected' : ''}>
                    ${packageDetails.short_name} • ${packageDetails.weight} • ${pesos.format(packageDetails.price)}
                </option>
            `).join('');
        }

        function renderOrderItems() {
            orderItemList.innerHTML = orderItems.map((item, index) => {
                const packageDetails = packageCatalog[item.package];
                const spicyUpgrade = item.flavor === 'Spicy' ? 50 : 0;
                const lineTotal = packageDetails.price + spicyUpgrade;

                return `
                    <article class="rounded-2xl border border-wine/10 bg-cream p-4 shadow-sm md:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-display text-base font-extrabold text-wine">Belly Order #${index + 1}</p>
                                <p class="mt-1 text-xs text-muted">${packageDetails.name} • ${item.flavor}</p>
                            </div>
                            <button class="${orderItems.length === 1 ? 'hidden' : 'inline-flex'} size-9 shrink-0 items-center justify-center rounded-full text-muted transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-4 focus:ring-red-100" type="button" data-remove-index="${index}" aria-label="Remove belly order ${index + 1}">
                                <span aria-hidden="true" class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-[1.2fr_1fr]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-muted">
                                Package variety
                                <select class="mt-2 w-full rounded-xl border border-wine/15 bg-white px-3 py-3 text-sm font-bold normal-case tracking-normal text-wine outline-none transition focus:border-rust focus:ring-4 focus:ring-rust/10" name="items[${index}][package]" data-item-index="${index}" data-item-field="package" required>
                                    ${packageOptions(item.package)}
                                </select>
                                <span class="mt-2 block font-normal normal-case tracking-normal text-muted">${packageDetails.serves} • ${pesos.format(packageDetails.price)}</span>
                            </label>
                            <fieldset>
                                <legend class="text-xs font-bold uppercase tracking-wider text-muted">Flavor</legend>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-wine/10 bg-white px-2 py-3 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="size-4 accent-rust" type="radio" name="items[${index}][flavor]" value="Regular" data-item-index="${index}" data-item-field="flavor" ${item.flavor === 'Regular' ? 'checked' : ''} required>
                                        <span class="text-sm font-bold text-wine">Regular</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-wine/10 bg-white px-2 py-3 has-checked:border-rust has-checked:ring-2 has-checked:ring-rust/15">
                                        <input class="size-4 accent-rust" type="radio" name="items[${index}][flavor]" value="Spicy" data-item-index="${index}" data-item-field="flavor" ${item.flavor === 'Spicy' ? 'checked' : ''} required>
                                        <span class="text-sm font-bold text-wine">Spicy <span class="text-xs font-normal text-muted">+₱50</span></span>
                                    </label>
                                </div>
                            </fieldset>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-wine/10 pt-3 text-sm">
                            <span class="text-muted">Belly #${index + 1} total</span>
                            <span class="font-display font-extrabold text-wine">${pesos.format(lineTotal)}</span>
                        </div>
                    </article>
                `;
            }).join('');

            const reachedLimit = orderItems.length >= 20;
            addOrderItemButton.disabled = reachedLimit;
            addOrderItemButton.classList.toggle('opacity-50', reachedLimit);
            addOrderItemButton.classList.toggle('cursor-not-allowed', reachedLimit);
            orderLimitNote.textContent = reachedLimit
                ? 'Maximum of 20 bellies reached. Contact Toño’s Bellyssimo directly for a larger order.'
                : 'Each added belly may use a different Barkadahan, Salo-Salo, or Handaan package.';
        }

        function updateFulfillmentDetails() {
            const isDelivery = selectedFulfillment() === 'Delivery';

            pickupDetails.classList.toggle('hidden', isDelivery);
            deliveryDetails.classList.toggle('hidden', !isDelivery);
            deliveryLocationInput.required = isDelivery;
        }

        function renderOrder() {
            renderOrderItems();
        }

        packageInputs.forEach((input) => input.addEventListener('change', () => {
            if (orderItems.length === 1) {
                orderItems[0].package = input.value;
                renderOrder();
            }
        }));
        orderItemList.addEventListener('change', (event) => {
            const itemIndex = Number(event.target.dataset.itemIndex);
            const itemField = event.target.dataset.itemField;

            if (!Number.isInteger(itemIndex) || !['package', 'flavor'].includes(itemField)) {
                return;
            }

            orderItems[itemIndex][itemField] = event.target.value;
            renderOrder();
        });
        orderItemList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-index]');

            if (!removeButton || orderItems.length === 1) {
                return;
            }

            orderItems.splice(Number(removeButton.dataset.removeIndex), 1);
            renderOrder();
        });
        addOrderItemButton.addEventListener('click', () => {
            if (orderItems.length >= 20) {
                return;
            }

            orderItems.push({ package: selectedStarterPackage(), flavor: 'Regular' });
            renderOrder();
        });
        fulfillmentInputs.forEach((input) => input.addEventListener('change', updateFulfillmentDetails));

        const preferredDateInput = document.querySelector('input[name="preferred_date"]');
        const preferredTimeSelect = document.querySelector('select[name="preferred_time"]');
        const bookingCutoff = new Date(@json($bookingCutoff->toIso8601String()));

        function updateAvailableBookingTimes() {
            preferredTimeSelect.querySelectorAll('option[value]').forEach((option) => {
                if (!option.value || !preferredDateInput.value) {
                    option.disabled = false;
                    return;
                }

                const requestedSchedule = new Date(`${preferredDateInput.value}T${option.value}:00+08:00`);
                option.disabled = requestedSchedule < bookingCutoff;
            });

            if (preferredTimeSelect.selectedOptions[0]?.disabled) {
                preferredTimeSelect.value = '';
            }
        }

        preferredDateInput.min = @json($bookingCutoff->toDateString());
        preferredDateInput.addEventListener('change', updateAvailableBookingTimes);
        updateAvailableBookingTimes();
        renderOrder();
        updateFulfillmentDetails();
    </script>
</body>
</html>
