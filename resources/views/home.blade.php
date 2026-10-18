<!DOCTYPE html>
<html class="scroll-smooth" lang="en" style="">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<x-favicon />
<meta content="web_standard" name="shell-type">
<title>Toño's Bellyssimo | Basta belly, diri mismo!</title>
<link rel="preload" as="image" href="{{ asset('images/home-hero-filipino-v2-800.jpg') }}" imagesrcset="{{ asset('images/home-hero-filipino-v2-800.jpg') }} 800w, {{ asset('images/home-hero-filipino-v2-1600.jpg') }} 1600w" imagesizes="100vw" fetchpriority="high">
<link href="{{ asset('css/site-v3.css') }}" rel="stylesheet">
</head>
<body id="top" class="min-h-screen overflow-x-hidden bg-background pb-16 font-body-md text-on-surface antialiased selection:bg-secondary-fixed selection:text-on-secondary-fixed lg:pb-0">
<x-site-header active="home" />
<main class="w-full pt-20 bg-surface flex-grow">
<div class="flex flex-col w-full">
<!-- HERO SECTION -->
<section aria-label="Toño’s Bellyssimo hero" class="relative isolate min-h-[640px] w-full overflow-hidden py-space-2xl md:min-h-[680px]">
<picture aria-hidden="true" class="absolute inset-0 -z-20 block">
<source media="(max-width: 767px)" srcset="{{ asset('images/home-hero-filipino-v2-800.jpg') }}">
<img src="{{ asset('images/home-hero-filipino-v2-1600.jpg') }}" srcset="{{ asset('images/home-hero-filipino-v2-800.jpg') }} 800w, {{ asset('images/home-hero-filipino-v2-1600.jpg') }} 1600w" sizes="100vw" width="1600" height="900" alt="" class="h-full w-full object-cover object-right" fetchpriority="high" decoding="async">
</picture>
<div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-[#210705]/85 via-[#2b0905]/40 to-transparent"></div>
<div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-[#210705]/70 via-transparent to-black/15"></div>
<div aria-hidden="true" class="absolute -bottom-36 -left-24 -z-10 size-96 rounded-full bg-secondary/20 blur-3xl"></div>
<div class="relative mx-auto flex min-h-[560px] max-w-7xl items-center px-gutter md:min-h-[600px] md:px-margin-md lg:px-margin-lg">
<div class="flex w-full max-w-3xl flex-col space-y-space-md">
<div class="inline-flex w-fit items-center gap-space-xs rounded-full border border-white/20 bg-tertiary-fixed/95 px-3 py-1.5 text-on-tertiary-fixed-variant shadow-lg backdrop-blur-sm">
<span class="material-symbols-outlined text-[18px] text-secondary">local_fire_department</span>
<span class="font-label-sm text-label-sm uppercase tracking-wider font-bold">Est. 2026 • Ozamiz City</span>
</div>
<div class="space-y-space-xs">
<p class="font-headline-sm text-headline-sm font-bold tracking-tight text-tertiary-fixed-dim italic">
              Basta belly, diri mismo!
            </p>
<h1 class="font-display-lg text-display-lg-mobile font-black leading-[1.08] tracking-tight text-white drop-shadow-lg md:text-display-lg">
              Beautifully crispy, <br>
<span class="text-tertiary-fixed-dim">dangerously juicy.</span>
</h1>
</div>
<p class="font-body-lg text-body-lg max-w-2xl leading-relaxed text-white/85 drop-shadow-sm">Serving crispy and juicy lechon belly for barkada gatherings, family meals, and special celebrations in Ozamiz City, Misamis Occidental.</p>
<!-- CTAs -->
<div class="grid grid-cols-1 gap-space-sm pt-space-xs sm:flex sm:flex-wrap sm:items-center sm:gap-space-md">
<a class="group inline-flex min-w-0 items-center justify-center gap-2 rounded-lg bg-tertiary-fixed px-space-lg py-3.5 font-label-lg text-label-lg text-on-tertiary-fixed shadow-lg transition-all hover:bg-white active:scale-95" data-path="book-now" href="{{ route('order') }}">
<span class="">Book Your Belly</span>
<span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
<a class="inline-flex min-w-0 items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/15 px-space-lg py-3.5 font-label-lg text-label-lg text-white shadow-lg backdrop-blur-md transition-all hover:bg-white hover:text-primary active:scale-95" href="{{ route('belly') }}">
<span class="material-symbols-outlined text-[20px]">restaurant_menu</span>
<span class="">View Packages</span>
</a>
</div>
<!-- Trust Badges Bar -->
<div aria-label="Bellyssimo quick facts" class="pt-space-md grid grid-cols-2 sm:grid-cols-4 gap-space-sm">
<div class="flex min-w-0 items-center gap-2 rounded-lg border border-white/25 bg-white/90 p-2.5 shadow-lg backdrop-blur-md">
<div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-primary shrink-0">
<span class="material-symbols-outlined text-[18px]">scale</span>
</div>
<div class="flex flex-col min-w-0">
<span class="font-label-sm text-label-sm text-on-surface font-bold leading-tight">3 Belly Sizes</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">3kg, 4kg, 5kg</span>
</div>
</div>
<div class="flex min-w-0 items-center gap-2 rounded-lg border border-white/25 bg-white/90 p-2.5 shadow-lg backdrop-blur-md">
<div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-secondary shrink-0">
<span class="material-symbols-outlined text-[18px]">groups</span>
</div>
<div class="flex flex-col min-w-0">
<span class="font-label-sm text-label-sm text-on-surface font-bold leading-tight">Serves 8–18</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Generous feast</span>
</div>
</div>
<div class="flex min-w-0 items-center gap-2 rounded-lg border border-white/25 bg-white/90 p-2.5 shadow-lg backdrop-blur-md">
<div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-primary shrink-0">
<span class="material-symbols-outlined text-[18px]">whatshot</span>
</div>
<div class="flex flex-col min-w-0">
<span class="font-label-sm text-label-sm text-on-surface font-bold leading-tight">Flavor Options</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Regular / Spicy</span>
</div>
</div>
<div class="flex min-w-0 items-center gap-2 rounded-lg border border-white/25 bg-white/90 p-2.5 shadow-lg backdrop-blur-md">
<div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-tertiary-fixed-dim shrink-0">
<span class="material-symbols-outlined text-[18px] text-tertiary">celebration</span>
</div>
<div class="flex flex-col min-w-0">
<span class="font-label-sm text-label-sm text-on-surface font-bold leading-tight">For Celebrations</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Fiesta-ready</span>
</div>
</div>
</div>
<div class="hidden w-fit items-center gap-1.5 rounded-full border border-white/20 bg-black/25 px-4 py-2 text-white shadow-lg backdrop-blur-md sm:flex">
<span class="material-symbols-outlined text-[18px]">verified</span>
<span class="font-label-sm text-label-sm font-black uppercase">3 kg • 4 kg • 5 kg &nbsp;·&nbsp; Starts at ₱2,000</span>
</div>
</div>
</div>
</section>
<!-- OCCASION / SERVING SUGGESTIONS SECTION -->
<section class="w-full py-space-2xl bg-surface-container-low">
<div class="max-w-7xl mx-auto px-gutter md:px-margin-md lg:px-margin-lg">
<div class="max-w-2xl mb-space-xl">
<span class="font-label-sm text-label-sm text-secondary font-bold tracking-widest uppercase">
          Festive Salo-Salo Moments
        </span>
<h2 class="font-headline-lg text-headline-lg text-primary font-black mt-1">
          Made for Every Special Occasion in Ozamiz
        </h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-2">
          From heartfelt milestone birthdays to lively Sunday reunions, our crispy lechon belly is the showstopper every dining table deserves.
        </p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter-md">
<!-- Occasion 1 -->
<div class="rounded-xl bg-surface p-space-md shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
<div>
<div class="w-12 h-12 rounded-lg bg-surface-container-high text-primary flex items-center justify-center mb-space-md group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined text-[26px]">cake</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-1">Birthdays &amp; Milestones</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Elevate anniversaries, debuts, and birthday handaans with the mouthwatering crunch of roasted pork belly.
            </p>
</div>
<div class="pt-space-md flex items-center gap-1 text-secondary font-label-sm text-label-sm font-bold">
<span class="">Recommended: 4kg or 5kg</span>
</div>
</div>
<!-- Occasion 2 -->
<div class="rounded-xl bg-surface p-space-md shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
<div>
<div class="w-12 h-12 rounded-lg bg-surface-container-high text-secondary flex items-center justify-center mb-space-md group-hover:bg-secondary group-hover:text-on-secondary transition-colors">
<span class="material-symbols-outlined text-[26px]">family_restroom</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-1">Family Sunday Salu-Salo</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Nothing gathers the lolo, lola, cousins, and kids faster than hot crackling paired with steamed white rice.
            </p>
</div>
<div class="pt-space-md flex items-center gap-1 text-secondary font-label-sm text-label-sm font-bold">
<span class="">Recommended: 3kg or 4kg</span>
</div>
</div>
<!-- Occasion 3 -->
<div class="rounded-xl bg-surface p-space-md shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
<div>
<div class="w-12 h-12 rounded-lg bg-surface-container-high text-primary flex items-center justify-center mb-space-md group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined text-[26px]">sports_bar</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-1">Barkada Nights &amp; Reunions</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              The premier pulutan and centerpiece dish for alumni homecomings, late-night chats, and weekend drinks.
            </p>
</div>
<div class="pt-space-md flex items-center gap-1 text-secondary font-label-sm text-label-sm font-bold">
<span class="">Try the Spicy Variant!</span>
</div>
</div>
<!-- Occasion 4 -->
<div class="rounded-xl bg-surface p-space-md shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
<div>
<div class="w-12 h-12 rounded-lg bg-surface-container-high text-tertiary flex items-center justify-center mb-space-md group-hover:bg-tertiary group-hover:text-on-tertiary transition-colors">
<span class="material-symbols-outlined text-[26px]">festival</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-1">Fiestas &amp; Office Celebrations</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              Ozamiz patron fiestas, corporate milestones, and team wins made memorable with crisp lechon belly slabs.
            </p>
</div>
<div class="pt-space-md flex items-center gap-1 text-secondary font-label-sm text-label-sm font-bold">
<span class="">Recommended: 5kg Multiple Rolls</span>
</div>
</div>
</div>
</div>
</section>
<!-- 3-STEP RESERVATION GUIDE -->
<section id="how-to-book" class="w-full py-space-2xl bg-surface">
<div class="max-w-7xl mx-auto px-gutter md:px-margin-md lg:px-margin-lg">
<div class="text-center max-w-xl mx-auto mb-space-xl">
<span class="font-label-sm text-label-sm text-secondary font-bold tracking-widest uppercase">
          Easy Pre-Order Process
        </span>
<h2 class="font-headline-lg text-headline-lg text-primary font-black mt-1">
          Simple 3-Step Reservation Guide
        </h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-2">
          Send your preferred date and time so our team can check availability for your celebration.
        </p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter-md relative">
<!-- Step 1 -->
<div class="p-space-lg rounded-xl bg-surface-container-low relative">
<div class="flex items-center justify-between mb-space-md">
<span class="w-10 h-10 rounded-full bg-primary text-on-primary font-headline-sm text-headline-sm font-bold flex items-center justify-center shadow-sm">
              1
            </span>
<span class="material-symbols-outlined text-secondary text-[28px]">format_list_bulleted</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-2">Choose Your Belly</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">Pick from our 3kg, 4kg, or 5kg lechon belly packages. Choose Regular or add the Spicy option for ₱50.</p>
</div>
<!-- Step 2 -->
<div class="p-space-lg rounded-xl bg-surface-container-low relative">
<div class="flex items-center justify-between mb-space-md">
<span class="w-10 h-10 rounded-full bg-secondary text-on-secondary font-headline-sm text-headline-sm font-bold flex items-center justify-center shadow-sm">
              2
            </span>
<span class="material-symbols-outlined text-secondary text-[28px]">calendar_month</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-2">Select Preferred Schedule</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Send your preferred date and time through the booking page, Facebook Messenger, or call 0976-2271-228.
          </p>
</div>
<!-- Step 3 -->
<div class="p-space-lg rounded-xl bg-surface-container-low relative">
<div class="flex items-center justify-between mb-space-md">
<span class="w-10 h-10 rounded-full bg-primary-container text-on-primary font-headline-sm text-headline-sm font-bold flex items-center justify-center shadow-sm">
              3
            </span>
<span class="material-symbols-outlined text-primary text-[28px]">verified_user</span>
</div>
<h3 class="font-title-md text-title-md text-on-surface font-bold mb-2">Wait for Confirmation</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Our team checks availability and contacts you directly to confirm your booking details.
          </p>
</div>
</div>
<!-- Critical Notice Callout -->
<div class="mt-space-lg p-space-md rounded-xl bg-surface-container-highest flex items-start gap-space-sm">
<span class="material-symbols-outlined text-primary text-[24px] shrink-0 mt-0.5">info</span>
<div>
<span class="font-label-md text-label-md text-primary font-bold">Booking Advisory</span>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Please book at least 48 hours before your preferred pickup or delivery. Your reservation is subject to availability and is confirmed only after Toño’s Bellyssimo contacts you directly.
          </p>
</div>
</div>
</div>
</section>
<!-- OZAMIZ CITY LOCAL PRIDE & CONTACT BANNER -->
<section id="contact" class="w-full py-space-2xl bg-surface-container-high relative overflow-hidden">
<div class="max-w-7xl mx-auto px-gutter md:px-margin-md lg:px-margin-lg relative z-10">
<div class="bg-surface rounded-2xl p-space-lg md:p-space-xl shadow-lg">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-md items-center">
<div class="lg:col-span-8 space-y-space-sm">
<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container text-secondary font-label-sm text-label-sm font-bold">
<span class="material-symbols-outlined text-[16px]">location_on</span>
              Ozamiz City, Misamis Occidental
            </div>
<h2 class="font-headline-lg text-headline-lg text-primary font-black">
              Proudly Serving Ozamiz City, Misamis Occidental
            </h2>
<p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
              Planning a gathering in Ozamiz City? Message us about package sizes, the spicy option, or your preferred reservation schedule.
            </p>
<div class="pt-space-xs flex flex-wrap items-center gap-space-sm">
<a class="inline-flex items-center gap-2 px-space-md py-3 rounded-lg bg-surface-container-highest text-on-surface font-label-md text-label-md font-bold hover:bg-secondary-fixed transition-colors" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
<span class="material-symbols-outlined text-secondary text-[20px]">chat</span>
<span class="">Message Us on Facebook</span>
</a>
<a class="inline-flex items-center gap-2 px-space-md py-3 rounded-lg bg-surface-container-highest text-on-surface font-label-md text-label-md font-bold hover:bg-secondary-fixed transition-colors" href="tel:+639762271228">
<span class="material-symbols-outlined text-secondary text-[20px]">call</span>
<span>Call 0976-2271-228</span>
</a>
<a class="inline-flex items-center gap-2 px-space-md py-3 rounded-lg bg-primary-container text-on-primary font-label-md text-label-md font-bold hover:bg-secondary transition-colors shadow-sm" data-path="book-now" href="{{ route('order') }}">
<span class="material-symbols-outlined text-[20px]">event_available</span>
<span class="">Book Now</span>
</a>
</div>
</div>
<!-- Quick Stat Badge / Map Callout -->
<div class="lg:col-span-4 flex flex-col space-y-space-xs p-space-md rounded-xl bg-surface-container-low">
<span class="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider">Booking Details</span>
<div class="flex items-center justify-between pt-1">
<span class="font-body-sm text-body-sm text-on-surface">Choose a Package</span>
<span class="font-label-md text-label-md text-on-surface font-bold">3kg, 4kg, or 5kg</span>
</div>
<div class="flex items-center justify-between pt-1">
<span class="font-body-sm text-body-sm text-on-surface">Send Your Schedule</span>
<span class="font-label-md text-label-md text-on-surface font-bold">Subject to confirmation</span>
</div>
<div class="pt-2 text-center">
<span class="font-label-sm text-label-sm text-on-surface-variant italic">
                “Basta belly, diri mismo sa Toño’s Bellyssimo!”
              </span>
</div>
</div>
</div>
</div>
</div>
</section>
</div>
</main>
<footer class="w-full bg-surface-container-high mt-auto">
<div class="max-w-7xl mx-auto px-gutter md:px-margin-md lg:px-margin-lg pt-space-xl pb-space-lg">
<div class="grid grid-cols-1 items-start gap-y-space-xl md:grid-cols-2 md:gap-x-gutter-lg lg:grid-cols-4 lg:gap-x-space-xl pb-space-lg">
<div class="flex min-w-0 flex-col gap-space-sm">
<div class="flex min-h-[1.75rem] items-center gap-space-sm">
<span class="font-headline-md text-headline-md text-primary font-bold">Toño’s Bellyssimo</span>
</div>
<p class="font-body-md text-body-md text-on-surface-variant italic leading-relaxed">“Beautifully crispy, dangerously juicy”</p>
<p class="font-label-md text-label-md text-secondary font-semibold">Basta belly, diri mismo!</p>
<span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-surface-container px-3 py-1 font-label-sm text-label-sm text-on-surface">
<span class="material-symbols-outlined text-[15px] text-secondary">pin_drop</span>Ozamiz City, Misamis Occidental</span>
</div>
<div class="flex min-w-0 flex-col gap-space-sm">
<h3 class="flex min-h-[1.75rem] items-center font-title-md text-title-md text-on-surface">Fiesta Menu &amp; Info</h3>
<ul class="flex flex-col gap-space-xs font-body-md text-body-md text-on-surface-variant">
<li class="">
<a class="hover:text-primary transition-colors" data-path="home" href="#top">Home Feast</a>
</li>
<li class="">
<a class="hover:text-primary transition-colors" data-path="our-belly" href="{{ route('belly') }}">Our Roasted Lechon Belly</a>
</li>
<li class="">
<a class="hover:text-primary transition-colors" data-path="book-now" href="{{ route('order') }}">Pre-order &amp; Celebrations</a>
</li>
<li class="">
<a class="hover:text-primary transition-colors" data-path="about-faqs" href="{{ route('about') }}">Ozamiz Heritage &amp; FAQs</a>
</li>
<li class="">
<a class="hover:text-primary transition-colors" data-path="contact" href="{{ route('about') }}#contact">Store Location &amp; Contact</a>
</li>
</ul>
</div>
<div class="flex min-w-0 flex-col gap-space-sm">
<h3 class="flex min-h-[1.75rem] items-center font-title-md text-title-md text-on-surface">Booking Advisory</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-normal">Please book at least 48 hours before pickup or delivery. Reservations are subject to availability and confirmed only after Toño’s Bellyssimo contacts you.</p>
<div class="p-space-sm bg-surface rounded-lg">
<p class="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider mb-1">Reservation Requests</p>
<p class="font-body-sm text-body-sm text-on-surface-variant">Message us with your preferred date and time. We will confirm availability directly.</p>
</div>
</div>
<div class="flex min-w-0 flex-col gap-space-sm">
<h3 class="flex min-h-[1.75rem] items-center font-title-md text-title-md text-on-surface">Community</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">Connect directly with our team in Ozamiz City:</p>
<a class="inline-flex w-full items-center gap-2 rounded-lg bg-surface-container px-4 py-2.5 font-label-md text-label-md text-on-surface shadow-sm transition-all hover:bg-secondary-container hover:text-on-secondary-container" href="tel:+639762271228">
<span class="material-symbols-outlined text-[18px] text-secondary">call</span>0976-2271-228</a>
<a class="inline-flex w-full items-center gap-2 rounded-lg bg-surface-container px-4 py-2.5 font-label-md text-label-md text-on-surface shadow-sm transition-all hover:bg-secondary-container hover:text-on-secondary-container" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
<span class="material-symbols-outlined text-[18px] text-secondary">chat</span>Message Us on Facebook</a>
<p class="font-label-sm text-label-sm text-on-surface-variant">Est. 2026 • Ozamiz City, Misamis Occidental, Philippines</p>
</div>
</div>
<div class="flex flex-col items-center justify-between gap-space-sm border-t border-outline-variant/60 pt-space-md text-center font-body-sm text-body-sm text-on-surface-variant sm:flex-row sm:text-left">
<p class="">© 2026 Toño’s Bellyssimo. All rights reserved.</p>
<p class="font-label-sm text-label-sm text-on-surface-variant">Made for celebrations in Ozamiz City.</p>
</div>
</div>
</footer>
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-surface/95 backdrop-blur-lg px-gutter py-space-sm flex items-center justify-around shadow-[0_-2px_10px_rgba(0,0,0,0.06)]">
<a class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-primary" data-path="home" href="#top">
<span class="material-symbols-outlined text-[20px]">home</span>
<span class="font-label-sm text-label-sm">Home</span>
</a>
<a class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-primary" data-path="our-belly" href="{{ route('belly') }}">
<span class="material-symbols-outlined text-[20px]">restaurant_menu</span>
<span class="font-label-sm text-label-sm">Belly</span>
</a>
<a class="flex flex-col items-center gap-0.5 text-primary font-bold" data-path="book-now" href="{{ route('order') }}">
<span class="material-symbols-outlined text-[20px]">event_available</span>
<span class="font-label-sm text-label-sm">Book</span>
</a>
<a class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-primary" data-path="contact" href="#contact">
<span class="material-symbols-outlined text-[20px]">call</span>
<span class="font-label-sm text-label-sm">Contact</span>
</a>
</div>

</body>
</html>
