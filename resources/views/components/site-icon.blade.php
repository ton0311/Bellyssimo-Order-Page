@props(['name'])

<svg
    {{ $attributes->merge(['class' => 'inline-block shrink-0']) }}
    aria-hidden="true"
    fill="none"
    focusable="false"
    height="1em"
    stroke="currentColor"
    stroke-linecap="round"
    stroke-linejoin="round"
    stroke-width="2"
    viewBox="0 0 24 24"
    width="1em"
>
    @switch($name)
        @case('local_fire_department')
        @case('whatshot')
            <path d="M12 22c4.4 0 8-3.4 8-7.6 0-3-1.7-5.6-4.4-7.7.1 2.2-.8 3.7-2.1 4.6.2-4.3-2.4-7.4-5.8-9.8.2 3.2-1.3 5.6-2.9 7.6C3.7 10.6 3 12.4 4 15.6 5 19.3 8 22 12 22Z" />
            <path d="M9.5 18.5c0-1.6 1-2.8 2.5-4.2 1.5 1.4 2.5 2.6 2.5 4.2a2.5 2.5 0 0 1-5 0Z" />
            @break

        @case('arrow_forward')
            <path d="M5 12h14" />
            <path d="m13 6 6 6-6 6" />
            @break

        @case('restaurant_menu')
            <path d="M7 3v8" />
            <path d="M4 3v4a3 3 0 0 0 6 0V3" />
            <path d="M7 11v10" />
            <path d="M17 3v18" />
            <path d="M17 3c-2 2-3 4.5-3 7h3" />
            @break

        @case('scale')
            <path d="M12 3v18" />
            <path d="M7 21h10" />
            <path d="M4 7h16" />
            <path d="m6 7-3 6h6L6 7Z" />
            <path d="m18 7-3 6h6l-3-6Z" />
            @break

        @case('groups')
        @case('family_restroom')
            <circle cx="9" cy="8" r="3" />
            <circle cx="17" cy="9" r="2.5" />
            <path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3A4.5 4.5 0 0 1 15 18.5V20" />
            <path d="M15 15h1.5a4 4 0 0 1 4 4v1" />
            @break

        @case('celebration')
            <path d="m4 21 4-12 7 7-11 5Z" />
            <path d="m8 9 7 7" />
            <path d="M14 4V2" />
            <path d="m18 7 2-2" />
            <path d="M17 12h3" />
            <path d="m10 5 1-2" />
            @break

        @case('verified')
            <path d="m12 2 2.2 2 3-.2.8 2.9 2.5 1.7-1.2 2.8 1.2 2.8-2.5 1.7-.8 2.9-3-.2-2.2 2-2.2-2-3 .2-.8-2.9L3.5 14l1.2-2.8-1.2-2.8L6 6.7l.8-2.9 3 .2L12 2Z" />
            <path d="m8.5 12 2.2 2.2 4.8-4.8" />
            @break

        @case('cake')
            <path d="M4 11h16v10H4z" />
            <path d="M4 15c2 0 2 2 4 2s2-2 4-2 2 2 4 2 2-2 4-2" />
            <path d="M8 11V8" />
            <path d="M12 11V7" />
            <path d="M16 11V8" />
            <path d="M12 3c1 1 1 2 0 3-1-1-1-2 0-3Z" />
            @break

        @case('sports_bar')
            <path d="M5 8h11v12H7a2 2 0 0 1-2-2V8Z" />
            <path d="M16 11h2a3 3 0 0 1 0 6h-2" />
            <path d="M7 8V5h2v3" />
            <path d="M11 8V4h2v4" />
            @break

        @case('festival')
            <path d="M3 21 6 8h12l3 13" />
            <path d="M8 21v-7l4-4 4 4v7" />
            <path d="M5 8 12 3l7 5" />
            @break

        @case('format_list_bulleted')
            <path d="M9 6h11" />
            <path d="M9 12h11" />
            <path d="M9 18h11" />
            <circle cx="4" cy="6" r="1" fill="currentColor" stroke="none" />
            <circle cx="4" cy="12" r="1" fill="currentColor" stroke="none" />
            <circle cx="4" cy="18" r="1" fill="currentColor" stroke="none" />
            @break

        @case('calendar_month')
            <rect width="18" height="17" x="3" y="4" rx="2" />
            <path d="M8 2v4" />
            <path d="M16 2v4" />
            <path d="M3 9h18" />
            <path d="M8 13h.01" />
            <path d="M12 13h.01" />
            <path d="M16 13h.01" />
            <path d="M8 17h.01" />
            <path d="M12 17h.01" />
            @break

        @case('verified_user')
            <path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.8 7.5 9.5 4.4-1.7 7.5-4.9 7.5-9.5V6L12 3Z" />
            <path d="m8.5 12 2.2 2.2 4.8-4.8" />
            @break

        @case('info')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5" />
            <path d="M12 8h.01" />
            @break

        @case('location_on')
        @case('pin_drop')
            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @case('chat')
            <path d="M21 15a3 3 0 0 1-3 3H8l-5 3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v9Z" />
            <path d="M8 9h8" />
            <path d="M8 13h5" />
            @break

        @case('call')
            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9Z" />
            @break

        @case('event_available')
            <rect width="18" height="17" x="3" y="4" rx="2" />
            <path d="M8 2v4" />
            <path d="M16 2v4" />
            <path d="M3 9h18" />
            <path d="m8 15 2 2 5-5" />
            @break

        @case('home')
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v11h14V10" />
            <path d="M9 21v-7h6v7" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
