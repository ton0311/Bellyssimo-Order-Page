@props(['active'])

<header class="fixed inset-x-0 top-0 z-50 bg-[#fff8f6]/90 shadow-[0_1px_8px_rgba(0,0,0,0.04)] backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-2 px-4 sm:gap-4 md:px-8 lg:px-14">
        <a class="flex min-w-0 items-center gap-2 sm:gap-3" href="{{ route('home') }}" aria-label="Toño’s Bellyssimo home">
            <img alt="Toño’s Bellyssimo logo" class="size-10 shrink-0 rounded-full border border-[#f4be52]/50 bg-white object-cover p-0.5 shadow-sm" src="{{ asset('images/bellyssimo-logo.png') }}" width="40" height="40" decoding="async">
            <span class="min-w-0">
                <span class="block truncate font-['Epilogue'] text-base font-extrabold leading-tight tracking-tight text-[#5b0d07] sm:text-xl">Toño’s Bellyssimo</span>
                <span class="hidden truncate text-[11px] font-semibold italic tracking-wide text-[#a84425] sm:block">Basta belly, diri mismo!</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
            <a @class([
                'rounded-lg px-3 py-2 text-sm transition-colors',
                'bg-[#fbebe4] font-bold text-[#5b0d07]' => $active === 'home',
                'font-medium text-[#56423f] hover:text-[#221a16]' => $active !== 'home',
            ]) href="{{ route('home') }}" @if ($active === 'home') aria-current="page" @endif>Home</a>
            <a @class([
                'rounded-lg px-3 py-2 text-sm transition-colors',
                'bg-[#fbebe4] font-bold text-[#5b0d07]' => $active === 'belly',
                'font-medium text-[#56423f] hover:text-[#221a16]' => $active !== 'belly',
            ]) href="{{ route('belly') }}" @if ($active === 'belly') aria-current="page" @endif>Our Belly</a>
            <a @class([
                'rounded-lg px-3 py-2 text-sm transition-colors',
                'bg-[#fbebe4] font-bold text-[#5b0d07]' => $active === 'order',
                'font-medium text-[#56423f] hover:text-[#221a16]' => $active !== 'order',
            ]) href="{{ route('order') }}" @if ($active === 'order') aria-current="page" @endif>Book Now</a>
            <a @class([
                'rounded-lg px-3 py-2 text-sm transition-colors',
                'bg-[#fbebe4] font-bold text-[#5b0d07]' => $active === 'about',
                'font-medium text-[#56423f] hover:text-[#221a16]' => $active !== 'about',
            ]) href="{{ route('about') }}" @if ($active === 'about') aria-current="page" @endif>About &amp; FAQs</a>
            <a class="rounded-lg px-3 py-2 text-sm font-medium text-[#56423f] transition-colors hover:text-[#221a16]" href="{{ route('about') }}#contact">Contact</a>
        </nav>

        <div class="flex shrink-0 items-center gap-2">
            <a class="inline-flex items-center justify-center rounded-lg bg-[#8a2317] px-3 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#a84425] active:scale-95 sm:px-4" href="{{ route('order') }}">Book Now</a>
            <a aria-label="Call Toño’s Bellyssimo at 0976-2271-228" class="hidden size-8 shrink-0 items-center justify-center rounded-full bg-[#5b0d07] sm:flex" href="tel:+639762271228">
                <x-site-icon name="call" class="text-[18px] text-white" />
            </a>
            <a aria-label="Message Toño’s Bellyssimo on Facebook" class="hidden size-8 shrink-0 items-center justify-center rounded-full bg-[#5b0d07] sm:flex" href="https://www.facebook.com/profile.php?id=61572029845712" rel="noopener noreferrer" target="_blank">
                <x-site-icon name="chat" class="text-[18px] text-white" />
            </a>
        </div>
    </div>
</header>
