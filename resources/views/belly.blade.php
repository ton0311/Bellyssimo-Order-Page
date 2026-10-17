<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore Toño’s Bellyssimo lechon belly packages for gatherings and celebrations in Ozamiz City.">
    <title>Our Belly | Toño’s Bellyssimo</title>
    <link rel="preload" as="image" href="{{ asset('images/our-belly-hero-v3-800.jpg') }}" imagesrcset="{{ asset('images/our-belly-hero-v3-800.jpg') }} 800w, {{ asset('images/our-belly-hero-v3-1600.jpg') }} 1600w" imagesizes="100vw" fetchpriority="high">
    <link href="{{ asset('css/site-v2.css') }}" rel="stylesheet">
</head>
<body id="top" class="min-h-screen bg-cream pb-16 font-sans text-ink antialiased selection:bg-gold/60 lg:pb-0">
    <x-site-header active="belly" />

    <main class="pt-20">
        <section aria-label="Our Belly hero" class="relative isolate min-h-[560px] overflow-hidden py-14 md:min-h-[620px] md:py-20">
            <picture aria-hidden="true" class="absolute inset-0 -z-20 block">
                <source media="(max-width: 767px)" srcset="{{ asset('images/our-belly-hero-v3-800.jpg') }}">
                <img src="{{ asset('images/our-belly-hero-v3-1600.jpg') }}" srcset="{{ asset('images/our-belly-hero-v3-800.jpg') }} 800w, {{ asset('images/our-belly-hero-v3-1600.jpg') }} 1600w" sizes="100vw" width="1600" height="900" alt="" class="h-full w-full object-cover object-center" fetchpriority="high" decoding="async">
            </picture>
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-[#230704]/95 via-[#45120c]/76 to-black/20"></div>
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-[#210705]/75 via-transparent to-black/20"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-20 -z-10 size-80 rounded-full bg-rust/25 blur-3xl"></div>
            <div class="relative mx-auto flex min-h-[450px] max-w-7xl items-center px-4 md:min-h-[480px] md:px-8 lg:px-14">
                <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/90 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-rust shadow-lg backdrop-blur-sm">
                    <span aria-hidden="true" class="material-symbols-outlined text-lg">local_fire_department</span>
                    Three Bellyssimo Packages
                </div>
                <h1 class="mt-5 max-w-3xl font-display text-4xl font-extrabold leading-tight tracking-tight text-white drop-shadow-lg md:text-6xl">Our Signature Lechon Belly Packages</h1>
                <p class="mt-4 font-display text-lg font-bold italic text-gold md:text-xl">“Beautifully crispy, dangerously juicy”</p>
                <p class="mt-4 max-w-2xl text-base leading-7 text-white/85 drop-shadow-sm">Choose the whole lechon belly size that fits your barkada, family salo-salo, or special handaan.</p>
                <div class="mt-7 flex max-w-2xl flex-col items-center gap-3 rounded-2xl border border-white/25 bg-white/90 p-4 shadow-xl backdrop-blur-md sm:flex-row sm:text-left">
                    <span class="grid size-11 shrink-0 place-items-center" aria-label="Spicy" role="img">
                        <svg aria-hidden="true" class="size-10" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                            <path d="M48.7 14.6c3.2-4.2 3.1-8.2.6-11.7 5.4-.6 8.5 3.6 6.5 8.5-.7 1.7-1.9 3.4-3.6 5l-3.5-1.8Z" fill="#7fa800"/>
                            <path d="M50.9 14.2c6.4 4.2 7.3 12.8 3 21.2C46.8 49.3 27.4 59.5 7.7 55.7 2.9 54.8 1.9 50.8 6.4 49c15.3-5.9 23.9-11.2 32.5-22.9 4.9-6.7 7.4-14.9 12-11.9Z" fill="#ef3500"/>
                            <path d="M46.2 20.4c-5.2 1.7-9.9 5.3-13.7 10.6" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="3.8"/>
                            <path d="M28.5 35.8c-1.2 1.5-2.4 2.7-3.8 3.8" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="3.8"/>
                        </svg>
                    </span>
                    <div>
                        <p class="font-bold text-wine">Fiesta upgrade: Make it Spicy <span class="ml-1 rounded-full bg-gold/55 px-2 py-1 text-xs">+₱50</span></p>
                        <p class="mt-1 text-sm text-muted">Available for all three package sizes.</p>
                    </div>
                </div>
                </div>
            </div>
        </section>

        <section id="packages" class="mx-auto max-w-7xl px-4 py-14 md:px-8 lg:px-14 lg:py-20">
            <div class="mb-9 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Crispy rolled belly</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold text-wine md:text-4xl">Choose Your Gathering Size</h2>
                </div>
                <p class="max-w-md text-sm leading-6 text-muted">Serving estimates may vary depending on portion size and the other food served.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-xl shadow-wine/5">
                    <div class="relative">
                        <img class="h-60 w-full object-cover" src="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }}" srcset="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }} 480w, {{ asset('images/lechon-barkadahan-3kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="The Bellyssimo Barkadahan with three sliced portions showing lemongrass and spices">
                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-extrabold text-wine shadow">3 KG ROASTED ROLL</span>
                        <span class="absolute bottom-4 right-4 rounded-full bg-wine px-3 py-1.5 text-xs font-bold text-white shadow">8–10 PAX</span>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-rust">Barkada gatherings</p>
                        <h3 class="mt-1 font-display text-2xl font-extrabold text-wine">The Bellyssimo Barkadahan</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">A 3 kg whole belly package, good for at least 8–10 people.</p>
                        <dl class="mt-5 space-y-3 rounded-xl bg-paper p-4 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-muted">Weight</dt><dd class="font-bold">3 kg</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-muted">Flavor</dt><dd class="font-bold">Regular / Spicy (+₱50)</dd></div>
                        </dl>
                        <div class="mt-auto flex items-end justify-between gap-4 pt-6">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted">Package price</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,000</span>
                        </div>
                        <a class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-blush px-5 py-3.5 font-bold text-wine transition hover:bg-wine hover:text-white" href="{{ route('order', ['package' => 'barkadahan']) }}">Choose This Package <span aria-hidden="true" class="material-symbols-outlined text-lg">arrow_forward</span></a>
                    </div>
                </article>

                <article class="relative flex flex-col overflow-hidden rounded-2xl border-2 border-rust bg-white shadow-2xl shadow-wine/10 lg:-translate-y-2">
                    <div class="bg-rust py-1.5 text-center text-[11px] font-extrabold uppercase tracking-widest text-white">Most Popular • Family Feast Favorite</div>
                    <div class="relative">
                        <img class="h-60 w-full object-cover" src="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }}" srcset="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }} 480w, {{ asset('images/lechon-salo-salo-4kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Salo-Salo with four sliced portions showing lemongrass and spices">
                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-extrabold text-wine shadow">4 KG SIGNATURE FEAST</span>
                        <span class="absolute bottom-4 right-4 rounded-full bg-rust px-3 py-1.5 text-xs font-bold text-white shadow">10–14 PAX</span>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-rust">Family gathering size</p>
                        <h3 class="mt-1 font-display text-2xl font-extrabold text-wine">Bellyssimo Salo-Salo</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">A 4 kg whole belly package, good for at least 10–14 people.</p>
                        <dl class="mt-5 space-y-3 rounded-xl bg-paper p-4 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-muted">Weight</dt><dd class="font-bold">4 kg</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-muted">Flavor</dt><dd class="font-bold">Regular / Spicy (+₱50)</dd></div>
                        </dl>
                        <div class="mt-auto flex items-end justify-between gap-4 pt-6">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted">Package price</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,550</span>
                        </div>
                        <a class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-wine-soft px-5 py-3.5 font-bold text-white shadow transition hover:bg-rust" href="{{ route('order', ['package' => 'salo-salo']) }}">Choose This Package <span aria-hidden="true" class="material-symbols-outlined text-lg">arrow_forward</span></a>
                    </div>
                </article>

                <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-xl shadow-wine/5">
                    <div class="relative">
                        <img class="h-60 w-full object-cover" src="{{ asset('images/lechon-handaan-5kg-480.jpg') }}" srcset="{{ asset('images/lechon-handaan-5kg-480.jpg') }} 480w, {{ asset('images/lechon-handaan-5kg-960.jpg') }} 960w" sizes="(min-width: 1024px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Handaan feast with five sliced portions showing lemongrass and spices">
                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-extrabold text-wine shadow">5 KG GRAND SIZE</span>
                        <span class="absolute bottom-4 right-4 rounded-full bg-wine px-3 py-1.5 text-xs font-bold text-white shadow">15–18 PAX</span>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-rust">Fiesta and party size</p>
                        <h3 class="mt-1 font-display text-2xl font-extrabold text-wine">Bellyssimo Handaan</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">A 5 kg whole belly package, good for at least 15–18 people.</p>
                        <dl class="mt-5 space-y-3 rounded-xl bg-paper p-4 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-muted">Weight</dt><dd class="font-bold">5 kg</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-muted">Flavor</dt><dd class="font-bold">Regular / Spicy (+₱50)</dd></div>
                        </dl>
                        <div class="mt-auto flex items-end justify-between gap-4 pt-6">
                            <span class="text-xs font-semibold uppercase tracking-wide text-muted">Package price</span>
                            <span class="font-display text-3xl font-extrabold text-wine">₱2,950</span>
                        </div>
                        <a class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-blush px-5 py-3.5 font-bold text-wine transition hover:bg-wine hover:text-white" href="{{ route('order', ['package' => 'handaan']) }}">Choose This Package <span aria-hidden="true" class="material-symbols-outlined text-lg">arrow_forward</span></a>
                    </div>
                </article>
            </div>
        </section>

        <section class="bg-paper py-14 lg:py-20">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-14">
                <div class="mb-8 text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Side-by-side guide</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold text-wine md:text-4xl">Package Breakdown</h2>
                </div>
                <div class="overflow-x-auto rounded-2xl bg-white shadow-xl shadow-wine/5">
                    <table class="w-full min-w-[720px] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-wine/10">
                                <th class="p-5 font-bold text-muted">Package</th>
                                <th class="p-5 font-display text-lg font-extrabold text-wine">Barkadahan</th>
                                <th class="bg-paper p-5 font-display text-lg font-extrabold text-wine">Salo-Salo</th>
                                <th class="p-5 font-display text-lg font-extrabold text-wine">Handaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wine/10">
                            <tr><th class="p-5 font-bold">Weight</th><td class="p-5">3 kg</td><td class="bg-paper p-5 font-bold">4 kg</td><td class="p-5">5 kg</td></tr>
                            <tr><th class="p-5 font-bold">Serving estimate</th><td class="p-5">8–10 pax</td><td class="bg-paper p-5 font-bold">10–14 pax</td><td class="p-5">15–18 pax</td></tr>
                            <tr><th class="p-5 font-bold">Regular price</th><td class="p-5 font-display text-xl font-extrabold text-wine">₱2,000</td><td class="bg-paper p-5 font-display text-xl font-extrabold text-wine">₱2,550</td><td class="p-5 font-display text-xl font-extrabold text-wine">₱2,950</td></tr>
                            <tr><th class="p-5 font-bold">Spicy price</th><td class="p-5 font-bold text-rust">₱2,050</td><td class="bg-paper p-5 font-bold text-rust">₱2,600</td><td class="p-5 font-bold text-rust">₱3,000</td></tr>
                            <tr>
                                <th class="p-5 font-bold">Select</th>
                                <td class="p-5"><a class="inline-flex rounded-lg bg-blush px-4 py-2 font-bold text-wine transition hover:bg-wine hover:text-white" href="{{ route('order', ['package' => 'barkadahan']) }}">Select 3 kg</a></td>
                                <td class="bg-paper p-5"><a class="inline-flex rounded-lg bg-wine-soft px-4 py-2 font-bold text-white transition hover:bg-rust" href="{{ route('order', ['package' => 'salo-salo']) }}">Select 4 kg</a></td>
                                <td class="p-5"><a class="inline-flex rounded-lg bg-blush px-4 py-2 font-bold text-wine transition hover:bg-wine hover:text-white" href="{{ route('order', ['package' => 'handaan']) }}">Select 5 kg</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex items-start gap-3 rounded-2xl bg-blush p-5">
                    <span aria-hidden="true" class="material-symbols-outlined shrink-0 text-wine">info</span>
                    <div>
                        <p class="font-bold text-wine">Booking Confirmation Advisory</p>
                        <p class="mt-1 text-sm leading-6 text-muted">Your preferred schedule is subject to availability. A booking is confirmed only after Toño’s Bellyssimo contacts you directly.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-wine px-4 py-14 text-center text-white md:px-8 lg:py-16">
            <div class="mx-auto max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-gold">Ready for your salo-salo?</p>
                <h2 class="mt-3 font-display text-3xl font-extrabold md:text-4xl">Choose your belly and send a booking request</h2>
                <p class="mx-auto mt-4 max-w-2xl leading-7 text-white/75">Select a package, flavor, and preferred schedule. Our team will check availability and confirm your request directly.</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <a class="rounded-xl bg-gold px-6 py-3.5 font-extrabold text-wine transition hover:bg-white" href="{{ route('order') }}">Book Your Belly</a>
                    <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 px-6 py-3.5 font-bold text-white transition hover:bg-white/10" href="tel:+639762271228"><span aria-hidden="true" class="material-symbols-outlined">call</span>Call 0976-2271-228</a>
                    <a class="rounded-xl border border-white/25 px-6 py-3.5 font-bold text-white transition hover:bg-white/10" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">Message Us on Facebook</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-blush px-4 py-10 text-center md:px-8">
        <p class="font-display text-xl font-extrabold text-wine">Toño’s Bellyssimo</p>
        <a class="mt-2 inline-flex items-center gap-1.5 text-sm font-bold text-wine hover:text-rust" href="tel:+639762271228"><span aria-hidden="true" class="material-symbols-outlined text-lg">call</span>0976-2271-228</a>
        <p class="mt-2 text-sm font-semibold italic text-rust">Beautifully crispy, dangerously juicy</p>
        <p class="mt-3 text-xs text-muted">Est. 2026 • Ozamiz City, Misamis Occidental</p>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-wine/5 bg-cream/95 px-4 py-2 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] backdrop-blur-lg lg:hidden" aria-label="Mobile navigation">
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('home') }}"><span aria-hidden="true" class="material-symbols-outlined text-xl">home</span><span class="text-[11px] font-bold">Home</span></a>
        <a aria-current="page" class="flex flex-col items-center gap-0.5 px-3 py-1 text-wine" href="#top"><span aria-hidden="true" class="material-symbols-outlined text-xl">restaurant_menu</span><span class="text-[11px] font-extrabold">Belly</span></a>
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('order') }}"><span aria-hidden="true" class="material-symbols-outlined text-xl">event_available</span><span class="text-[11px] font-bold">Book</span></a>
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('about') }}#contact"><span aria-hidden="true" class="material-symbols-outlined text-xl">chat</span><span class="text-[11px] font-bold">Contact</span></a>
    </nav>
</body>
</html>
