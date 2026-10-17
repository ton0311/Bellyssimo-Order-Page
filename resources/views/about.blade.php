<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Toño’s Bellyssimo, browse frequently asked questions, and find pickup and contact details in Ozamiz City.">
    <title>About & FAQs | Toño’s Bellyssimo</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="{{ asset('css/site-v1.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-cream pb-20 font-sans text-ink antialiased selection:bg-gold/60 lg:pb-0">
    <x-site-header active="about" />

    <main class="pt-20">
        <section class="bg-paper py-4">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 text-sm md:px-8 lg:px-14">
                <div class="flex items-center gap-2 text-muted">
                    <a class="transition hover:text-wine" href="{{ route('home') }}">Home</a>
                    <span aria-hidden="true" class="material-symbols-outlined text-base">chevron_right</span>
                    <span class="font-bold text-wine">About, FAQs &amp; Contact</span>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-rust shadow-sm">
                    <span aria-hidden="true" class="material-symbols-outlined text-base">location_on</span>
                    Ozamiz City, Misamis Occidental
                </span>
            </div>
        </section>

        <section class="relative overflow-hidden py-14 lg:py-20">
            <div class="pointer-events-none absolute -right-28 top-4 size-96 rounded-full bg-gold/20 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 md:px-8 lg:grid-cols-12 lg:px-14">
                <div class="lg:col-span-6">
                    <span class="inline-flex items-center gap-2 rounded-full bg-gold/40 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-rust">
                        <span aria-hidden="true" class="material-symbols-outlined text-base">verified</span>
                        Basta belly, diri mismo!
                    </span>
                    <h1 class="mt-5 font-display text-4xl font-extrabold leading-tight tracking-tight text-wine md:text-6xl">Beautifully crispy, dangerously juicy.</h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-muted">Toño’s Bellyssimo is an Ozamiz City lechon belly business established in 2026, serving barkada gatherings, family meals, and special celebrations.</p>

                    <div class="mt-7 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <span class="grid size-10 place-items-center rounded-xl bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">flare</span></span>
                            <h2 class="mt-3 font-bold text-wine">Crispy Skin</h2>
                            <p class="mt-1 text-xs leading-5 text-muted">The crackling centerpiece of every Bellyssimo feast.</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <span class="grid size-10 place-items-center rounded-xl bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">grass</span></span>
                            <h2 class="mt-3 font-bold text-wine">Aromatic Filling</h2>
                            <p class="mt-1 text-xs leading-5 text-muted">Lemongrass and spices inside every savory roll.</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <span class="grid size-10 place-items-center rounded-xl bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">celebration</span></span>
                            <h2 class="mt-3 font-bold text-wine">Made to Share</h2>
                            <p class="mt-1 text-xs leading-5 text-muted">Three sizes for barkadahan, salo-salo, and handaan.</p>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-wine-soft px-6 py-3.5 font-bold text-white shadow-lg transition hover:bg-rust" href="{{ route('order') }}">
                            <span aria-hidden="true" class="material-symbols-outlined">calendar_today</span>
                            Book Lechon Belly
                        </a>
                        <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-blush px-5 py-3.5 font-bold text-wine transition hover:bg-gold/40" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
                            <span aria-hidden="true" class="material-symbols-outlined text-rust">chat</span>
                            Message on Facebook
                        </a>
                        <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-wine/15 bg-white px-5 py-3.5 font-bold text-wine transition hover:border-rust hover:bg-blush" href="tel:+639762271228">
                            <span aria-hidden="true" class="material-symbols-outlined text-rust">call</span>
                            Call 0976-2271-228
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="relative overflow-hidden rounded-3xl border-8 border-white bg-wine shadow-2xl shadow-wine/15">
                        <img class="h-[420px] w-full object-cover object-center" src="{{ asset('images/about-belly-filipino-v2-800.jpg') }}" srcset="{{ asset('images/about-belly-filipino-v2-800.jpg') }} 800w, {{ asset('images/about-belly-filipino-v2-1600.jpg') }} 1600w" sizes="(min-width: 1024px) 50vw, 100vw" width="1600" height="900" loading="lazy" decoding="async" alt="Toño’s Bellyssimo lechon belly presented in a Filipino salo-salo setting with banana leaves, sawsawan, and capiz lighting">
                        <div class="absolute inset-x-4 bottom-4 flex flex-wrap items-end justify-between gap-3 rounded-2xl bg-cream/95 p-4 shadow-lg backdrop-blur">
                            <div>
                                <span class="rounded-full bg-gold/50 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wider text-rust">Toño’s Signature Belly</span>
                                <p class="mt-2 font-display text-xl font-extrabold text-wine">For every salo-salo</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs uppercase tracking-wider text-muted">Starting at</span>
                                <span class="font-display text-2xl font-extrabold text-wine">₱2,000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-blush py-14 lg:py-20">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-14">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Party portions &amp; salo-salo</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold text-wine">Signature Package Guide</h2>
                    <p class="mt-3 leading-7 text-muted">Choose one variety or mix different package sizes and flavors in the same booking request.</p>
                </div>

                <div class="mt-9 grid gap-5 md:grid-cols-3">
                    <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
                        <img class="h-40 w-full object-cover" src="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }}" srcset="{{ asset('images/lechon-barkadahan-3kg-480.jpg') }} 480w, {{ asset('images/lechon-barkadahan-3kg-960.jpg') }} 960w" sizes="(min-width: 768px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="The Bellyssimo Barkadahan 3 kg lechon belly">
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-blush px-3 py-1 text-xs font-bold text-wine">Barkada Choice</span><span class="font-display text-xl font-extrabold text-rust">3 kg</span></div>
                            <h3 class="mt-4 font-display text-xl font-extrabold text-wine">The Bellyssimo Barkadahan</h3>
                            <p class="mt-2 text-sm text-muted">Good for at least 8–10 people.</p>
                            <div class="mt-5 flex items-end justify-between gap-3 border-t border-wine/10 pt-4"><span class="text-xs text-muted">Spicy +₱50</span><span class="font-display text-2xl font-extrabold text-wine">₱2,000</span></div>
                        </div>
                    </article>

                    <article class="relative flex flex-col overflow-hidden rounded-2xl border-2 border-rust bg-white shadow-lg md:-translate-y-2">
                        <span class="absolute inset-x-0 top-0 z-10 bg-rust py-1.5 text-center text-[11px] font-extrabold uppercase tracking-widest text-white">Most Popular</span>
                        <img class="h-40 w-full object-cover" src="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }}" srcset="{{ asset('images/lechon-salo-salo-4kg-480.jpg') }} 480w, {{ asset('images/lechon-salo-salo-4kg-960.jpg') }} 960w" sizes="(min-width: 768px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Salo-Salo 4 kg lechon belly">
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-gold/35 px-3 py-1 text-xs font-bold text-rust">Family Favorite</span><span class="font-display text-xl font-extrabold text-rust">4 kg</span></div>
                            <h3 class="mt-4 font-display text-xl font-extrabold text-wine">Bellyssimo Salo-Salo</h3>
                            <p class="mt-2 text-sm text-muted">Good for at least 10–14 people.</p>
                            <div class="mt-5 flex items-end justify-between gap-3 border-t border-wine/10 pt-4"><span class="text-xs text-muted">Spicy +₱50</span><span class="font-display text-2xl font-extrabold text-wine">₱2,550</span></div>
                        </div>
                    </article>

                    <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
                        <img class="h-40 w-full object-cover" src="{{ asset('images/lechon-handaan-5kg-480.jpg') }}" srcset="{{ asset('images/lechon-handaan-5kg-480.jpg') }} 480w, {{ asset('images/lechon-handaan-5kg-960.jpg') }} 960w" sizes="(min-width: 768px) 31vw, 100vw" width="960" height="506" loading="lazy" decoding="async" alt="Bellyssimo Handaan 5 kg lechon belly">
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-blush px-3 py-1 text-xs font-bold text-wine">Grand Fiesta</span><span class="font-display text-xl font-extrabold text-rust">5 kg</span></div>
                            <h3 class="mt-4 font-display text-xl font-extrabold text-wine">Bellyssimo Handaan</h3>
                            <p class="mt-2 text-sm text-muted">Good for at least 15–18 people.</p>
                            <div class="mt-5 flex items-end justify-between gap-3 border-t border-wine/10 pt-4"><span class="text-xs text-muted">Spicy +₱50</span><span class="font-display text-2xl font-extrabold text-wine">₱2,950</span></div>
                        </div>
                    </article>
                </div>

                <div class="mt-7 text-center">
                    <a class="inline-flex items-center gap-2 rounded-xl bg-wine px-6 py-3 font-bold text-white transition hover:bg-rust" href="{{ route('order') }}">
                        Build Your Mixed Belly Order
                        <span aria-hidden="true" class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>

        <section aria-labelledby="quick-facts-title" class="bg-paper py-12">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-14">
                <div class="text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-rust">Quick feast guide</p>
                    <h2 id="quick-facts-title" class="mt-2 font-display text-3xl font-extrabold text-wine">Bellyssimo at a Glance</h2>
                </div>

                <div aria-label="Bellyssimo quick facts" class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-wine/5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">scale</span></span>
                        <div><h3 class="font-bold text-wine">3 Belly Sizes</h3><p class="mt-0.5 text-sm text-muted">3kg, 4kg, 5kg</p></div>
                    </article>
                    <article class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-wine/5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">groups</span></span>
                        <div><h3 class="font-bold text-wine">Serves 8–18</h3><p class="mt-0.5 text-sm text-muted">Generous feast</p></div>
                    </article>
                    <article class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-wine/5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gold/35 text-rust"><span aria-hidden="true" class="material-symbols-outlined">whatshot</span></span>
                        <div><h3 class="font-bold text-wine">Flavor Options</h3><p class="mt-0.5 text-sm text-muted">Regular / Spicy</p></div>
                    </article>
                    <article class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-wine/5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gold/35 text-rust"><span aria-hidden="true" class="material-symbols-outlined">celebration</span></span>
                        <div><h3 class="font-bold text-wine">For Celebrations</h3><p class="mt-0.5 text-sm text-muted">Fiesta-ready</p></div>
                    </article>
                </div>
            </div>
        </section>

        <section id="faqs" class="py-14 lg:py-20">
            <div class="mx-auto max-w-4xl px-4 md:px-8">
                <div class="text-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blush px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-rust"><span aria-hidden="true" class="material-symbols-outlined text-base">quiz</span>Got questions?</span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold text-wine md:text-4xl">Frequently Asked Questions</h2>
                    <p class="mx-auto mt-3 max-w-2xl leading-7 text-muted">Everything you need to know about packages, mixed orders, spicy upgrades, pickup, delivery, and booking confirmation.</p>
                </div>

                <div class="mt-9 space-y-3">
                    <details class="group rounded-2xl bg-white shadow-sm" open>
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>What package sizes and prices are available?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">We offer The Bellyssimo Barkadahan (3 kg) for ₱2,000, Bellyssimo Salo-Salo (4 kg) for ₱2,550, and Bellyssimo Handaan (5 kg) for ₱2,950.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>How many people does each package serve?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">The 3 kg Barkadahan is good for at least 8–10 people, the 4 kg Salo-Salo for at least 10–14 people, and the 5 kg Handaan for at least 15–18 people. Actual servings may vary with portion size and other food served.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>Is a spicy option available?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">Yes. Any belly package can be made Spicy for an additional ₱50 per belly.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>Is Bellyssimo suitable for celebrations?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">Yes. Our lechon belly packages are fiesta-ready for barkada gatherings, family salo-salo, birthdays, fiestas, and other special occasions. Choose the size that matches your guest count.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>Can I mix different package sizes and flavors?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">Yes. Add multiple belly orders on the booking page, then choose Barkadahan, Salo-Salo, or Handaan and Regular or Spicy separately for each belly.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>Is submitting the form an automatic confirmation?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted"><strong class="text-wine">No.</strong> A submitted reservation is a booking request only. It becomes confirmed after Toño’s Bellyssimo checks availability and contacts you directly.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>How do pickup and delivery work?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">Pickup is at Perayra Village, Maningcol, Ozamiz City. Delivery is also available, with the customer shouldering the fee based on the delivery location. The exact fee is confirmed before the booking is finalized.</div>
                    </details>
                    <details class="group rounded-2xl bg-white shadow-sm">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-bold text-wine"><span>When do I receive payment instructions?</span><span aria-hidden="true" class="material-symbols-outlined text-rust transition group-open:rotate-180">expand_more</span></summary>
                        <div class="px-5 pb-5 leading-7 text-muted">No payment is collected on this website. Toño’s Bellyssimo provides payment instructions after confirming the requested schedule and availability.</div>
                    </details>
                </div>
            </div>
        </section>

        <section id="contact" class="bg-paper py-14 lg:py-20">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 md:px-8 lg:grid-cols-12 lg:px-14">
                <div class="lg:col-span-5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blush px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-rust"><span aria-hidden="true" class="material-symbols-outlined text-base">contact_support</span>Connect with us</span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold text-wine">Contact &amp; Local Inquiries</h2>
                    <p class="mt-3 leading-7 text-muted">Planning a celebration in Ozamiz City? Send a booking request online or message Toño’s Bellyssimo directly for questions and special arrangements.</p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3 rounded-2xl bg-white p-5 shadow-sm">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">location_on</span></span>
                            <div><p class="text-xs font-extrabold uppercase tracking-wider text-muted">Serving Region</p><p class="mt-1 font-bold text-wine">Ozamiz City, Misamis Occidental</p><p class="mt-1 text-sm text-muted">Northern Mindanao, Philippines</p></div>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl bg-white p-5 shadow-sm">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gold/35 text-rust"><span aria-hidden="true" class="material-symbols-outlined">storefront</span></span>
                            <div><p class="text-xs font-extrabold uppercase tracking-wider text-muted">Bellyssimo Pick-Up Location</p><p class="mt-1 font-bold text-wine">Perayra Village, Maningcol, Ozamiz City</p><a class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-rust underline underline-offset-4" href="https://maps.app.goo.gl/3XZ87Ep9kWLDPzh47" rel="noopener noreferrer" target="_blank">Open in Google Maps <span aria-hidden="true" class="material-symbols-outlined text-base">arrow_outward</span></a></div>
                        </div>
                        <a class="flex items-start gap-3 rounded-2xl bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="tel:+639762271228">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blush text-rust"><span aria-hidden="true" class="material-symbols-outlined">call</span></span>
                            <span><span class="block text-xs font-extrabold uppercase tracking-wider text-muted">Phone</span><span class="mt-1 block font-bold text-wine">0976-2271-228</span><span class="mt-1 block text-sm text-muted">Tap to call Toño’s Bellyssimo</span></span>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#583e00] via-[#3c2900] to-wine p-7 text-white shadow-2xl shadow-wine/15 md:p-10">
                        <span aria-hidden="true" class="material-symbols-outlined absolute -bottom-12 -right-6 text-[190px] text-white/5">forum</span>
                        <div class="relative">
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-gold">Direct kitchen assistance</p>
                            <h3 class="mt-3 font-display text-3xl font-extrabold">How can we help with your feast?</h3>
                            <p class="mt-4 max-w-xl leading-7 text-white/70">Use the booking page for package selection, mixed orders, preferred schedule, pickup, or delivery. For custom questions, call us or contact the team through the official Facebook page.</p>
                            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                                <a class="flex items-center justify-center gap-2 rounded-xl bg-gold px-5 py-4 font-extrabold text-wine transition hover:bg-white" href="{{ route('order') }}">
                                    <span aria-hidden="true" class="material-symbols-outlined">event_available</span>
                                    Start Booking Request
                                </a>
                                <a class="flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-4 font-extrabold text-wine transition hover:bg-gold" href="tel:+639762271228">
                                    <span aria-hidden="true" class="material-symbols-outlined">call</span>
                                    Call 0976-2271-228
                                </a>
                                <a class="flex items-center justify-center gap-2 rounded-xl border border-white/25 px-5 py-4 font-bold text-white transition hover:bg-white/10" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
                                    <span aria-hidden="true" class="material-symbols-outlined">chat</span>
                                    Message on Facebook
                                </a>
                            </div>
                            <div class="mt-7 rounded-2xl bg-white/10 p-5 text-sm leading-6 text-white/75">
                                <strong class="text-gold">Booking reminder:</strong> your preferred schedule remains subject to availability until Toño’s Bellyssimo contacts you directly.
                            </div>
                        </div>
                    </div>
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

    <nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around bg-cream/95 px-3 py-2 shadow-[0_-2px_10px_rgba(0,0,0,0.08)] backdrop-blur lg:hidden" aria-label="Mobile navigation">
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('home') }}"><span aria-hidden="true" class="material-symbols-outlined text-xl">home</span><span class="text-[11px] font-bold">Home</span></a>
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('belly') }}"><span aria-hidden="true" class="material-symbols-outlined text-xl">restaurant_menu</span><span class="text-[11px] font-bold">Belly</span></a>
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 text-muted" href="{{ route('order') }}"><span aria-hidden="true" class="material-symbols-outlined text-xl">event_available</span><span class="text-[11px] font-bold">Book</span></a>
        <a class="flex flex-col items-center gap-0.5 px-3 py-1 font-bold text-wine" href="#contact" aria-current="page"><span aria-hidden="true" class="material-symbols-outlined text-xl">contact_support</span><span class="text-[11px] font-bold">About</span></a>
    </nav>
</body>
</html>
