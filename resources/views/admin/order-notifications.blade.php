<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="robots" content="noindex, nofollow">
    <title>Email Alerts | Toño's Bellyssimo Admin</title>
    <x-admin-fonts />
    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-notifications.css') }}">
</head>
<body>
    <aside class="sidebar" id="sidebar" aria-label="Admin navigation">
        <div>
            <a class="brand" href="{{ route('admin.dashboard') }}" aria-label="Toño's Bellyssimo admin home">
                <span class="brand-mark" aria-hidden="true"><img alt="" src="{{ asset('images/bellyssimo-logo.png') }}" width="96" height="96" decoding="async"></span>
                <span><strong>Toño's</strong><small>Bellyssimo</small></span>
            </a>
            <nav class="nav-list">
                <p>Workspace</p>
                <a href="{{ route('admin.dashboard') }}#overview"><span class="material-symbols-rounded">space_dashboard</span>Overview</a>
                <a href="{{ route('admin.dashboard') }}#orders"><span class="material-symbols-rounded">receipt_long</span>Orders</a>
                <a href="{{ route('admin.schedule') }}"><span class="material-symbols-rounded">calendar_month</span>Roasting schedule</a>
                <a href="{{ route('admin.dashboard') }}#customers"><span class="material-symbols-rounded">group</span>Customers</a>
                <p>Manage</p>
                <a href="{{ route('admin.dashboard') }}#reports"><span class="material-symbols-rounded">monitoring</span>Reports</a>
                <a class="active" href="{{ route('admin.notifications.edit') }}" aria-current="page"><span class="material-symbols-rounded">notifications_active</span>Email alerts</a>
                <a href="{{ route('admin.password.edit') }}"><span class="material-symbols-rounded">lock</span>Change password</a>
            </nav>
        </div>
        <div class="sidebar-foot">
            <div class="demo-note"><span class="material-symbols-rounded">mark_email_read</span><div><strong>Booking alerts</strong><small>Delivered through Resend.</small></div></div>
            <div class="admin-profile">
                <span class="avatar">T</span>
                <div><strong>{{ session('admin.name', config('admin.name')) }}</strong><small>Administrator</small></div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="logout-button" type="submit" aria-label="Sign out"><span class="material-symbols-rounded">logout</span></button></form>
            </div>
        </div>
    </aside>

    <button class="sidebar-backdrop" id="sidebar-backdrop" aria-label="Close navigation"></button>

    <main class="main-shell">
        <header class="topbar">
            <button class="icon-button menu-button" id="menu-button" aria-label="Open navigation" aria-expanded="false"><span class="material-symbols-rounded">menu</span></button>
            <div class="topbar-copy"><strong>{{ now('Asia/Manila')->format('l, F j') }}</strong><span><i></i>Booking notification settings</span></div>
            <div class="topbar-actions"><a class="store-link" href="{{ route('admin.dashboard') }}"><span class="material-symbols-rounded">space_dashboard</span>Dashboard</a></div>
        </header>

        <div class="page-wrap notification-page">
            <section class="page-heading notification-heading">
                <div><p class="eyebrow">Owner notifications</p><h1>Booking email alerts</h1><p>Everyone in this list receives the complete order details after a customer places a booking request.</p></div>
                <div @class(['delivery-chip', 'ready' => $deliveryConfigured, 'warning' => ! $deliveryConfigured])><span class="material-symbols-rounded">{{ $deliveryConfigured ? 'verified' : 'warning' }}</span>{{ $deliveryConfigured ? 'Email delivery ready' : 'Resend setup required' }}</div>
            </section>

            <div class="notification-layout">
                <article class="notification-card">
                    <div class="notification-card-head">
                        <span class="notification-icon material-symbols-rounded">group</span>
                        <div><p class="eyebrow">Active recipients</p><h2>{{ count($recipientEmails) }} {{ Str::plural('address', count($recipientEmails)) }}</h2></div>
                    </div>

                    @if (session('status'))
                        <div class="notification-alert success" role="status"><span class="material-symbols-rounded">check_circle</span><div><strong>Settings updated</strong><small>{{ session('status') }}</small></div></div>
                    @endif

                    @if ($errors->any())
                        <div class="notification-alert error" role="alert"><span class="material-symbols-rounded">error</span><div><strong>Check the email address</strong><small>{{ $errors->first() }}</small></div></div>
                    @endif

                    <div class="recipient-list">
                        @forelse ($recipientEmails as $email)
                            <div class="recipient-row">
                                <span class="recipient-avatar material-symbols-rounded">mail</span>
                                <div><strong>{{ $email }}</strong><small>Receives every new booking alert</small></div>
                                <form method="POST" action="{{ route('admin.notifications.destroy') }}" onsubmit="return confirm('Remove this address from booking alerts?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <button class="remove-recipient" type="submit" aria-label="Remove {{ $email }}"><span class="material-symbols-rounded">delete</span>Remove</button>
                                </form>
                            </div>
                        @empty
                            <div class="recipient-empty"><span class="material-symbols-rounded">notifications_off</span><h3>Owner alerts are paused</h3><p>Add an email address below to start receiving new booking notifications.</p></div>
                        @endforelse
                    </div>

                    <form class="add-recipient" method="POST" action="{{ route('admin.notifications.store') }}">
                        @csrf
                        <label for="notification-email">Add another email address</label>
                        <div class="email-input-row">
                            <span class="email-input"><span class="material-symbols-rounded">alternate_email</span><input id="notification-email" name="email" type="email" autocomplete="email" maxlength="254" required placeholder="name@example.com" value="{{ old('email') }}"></span>
                            <button class="primary-button" type="submit"><span class="material-symbols-rounded">add</span>Add recipient</button>
                        </div>
                        <small>You can keep up to 10 addresses. Duplicate addresses are ignored.</small>
                    </form>
                </article>

                <aside class="notification-guidance">
                    <div class="guidance-mark"><span class="material-symbols-rounded">forward_to_inbox</span></div>
                    <p class="eyebrow">What gets sent</p>
                    <h2>Everything needed to respond quickly</h2>
                    <ul>
                        <li><span class="material-symbols-rounded">check_circle</span>Customer name, phone, and email</li>
                        <li><span class="material-symbols-rounded">check_circle</span>Packages, flavors, quantity, and total</li>
                        <li><span class="material-symbols-rounded">check_circle</span>Preferred schedule and fulfillment</li>
                        <li><span class="material-symbols-rounded">check_circle</span>Payment choice and customer notes</li>
                        <li><span class="material-symbols-rounded">check_circle</span>Direct link to review the order</li>
                    </ul>
                    <div class="privacy-note"><span class="material-symbols-rounded">privacy_tip</span><p><strong>Keep this list private</strong><small>These emails contain customer contact and booking details. Only add trusted business owners or staff.</small></p></div>
                </aside>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.querySelector('#sidebar');
        const backdrop = document.querySelector('#sidebar-backdrop');
        const menuButton = document.querySelector('#menu-button');
        const closeSidebar = () => { sidebar.classList.remove('open'); backdrop.classList.remove('show'); menuButton.setAttribute('aria-expanded', 'false'); };
        menuButton.addEventListener('click', () => { const open = !sidebar.classList.contains('open'); sidebar.classList.toggle('open', open); backdrop.classList.toggle('show', open); menuButton.setAttribute('aria-expanded', String(open)); });
        backdrop.addEventListener('click', closeSidebar);
    </script>
</body>
</html>
