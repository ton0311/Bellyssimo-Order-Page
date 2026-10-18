<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="robots" content="noindex, nofollow">
    <title>Roasting Schedule | Toño's Bellyssimo Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-schedule.css') }}">
</head>
<body>
    <aside class="sidebar" id="sidebar" aria-label="Admin navigation">
        <div>
            <a class="brand" href="{{ route('admin.dashboard') }}" aria-label="Toño's Bellyssimo admin home">
                <span class="brand-mark" aria-hidden="true">
                    <img alt="" src="{{ asset('images/bellyssimo-logo.png') }}" width="96" height="96" decoding="async">
                </span>
                <span><strong>Toño's</strong><small>Bellyssimo</small></span>
            </a>

            <nav class="nav-list">
                <p>Workspace</p>
                <a href="{{ route('admin.dashboard') }}#overview"><span class="material-symbols-rounded">space_dashboard</span>Overview</a>
                <a href="{{ route('admin.dashboard') }}#orders"><span class="material-symbols-rounded">receipt_long</span>Orders <b>{{ $allOrdersCount }}</b></a>
                <a class="active" href="{{ route('admin.schedule') }}" aria-current="page"><span class="material-symbols-rounded">calendar_month</span>Roasting schedule</a>
                <a href="{{ route('admin.dashboard') }}#customers"><span class="material-symbols-rounded">group</span>Customers</a>
                <p>Manage</p>
                <a href="{{ route('admin.dashboard') }}#reports"><span class="material-symbols-rounded">monitoring</span>Reports</a>
                <a href="{{ route('admin.password.edit') }}"><span class="material-symbols-rounded">lock</span>Change password</a>
            </nav>
        </div>

        <div class="sidebar-foot">
            <div class="demo-note">
                <span class="material-symbols-rounded">sync</span>
                <div><strong>Live roasting plan</strong><small>Updates with every booking.</small></div>
            </div>
            <div class="admin-profile">
                <span class="avatar">T</span>
                <div><strong>{{ session('admin.name', config('admin.name')) }}</strong><small>Administrator</small></div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" aria-label="Sign out"><span class="material-symbols-rounded">logout</span></button>
                </form>
            </div>
        </div>
    </aside>

    <button class="sidebar-backdrop" id="sidebar-backdrop" aria-label="Close navigation"></button>

    <main class="main-shell">
        <header class="topbar">
            <button class="icon-button menu-button" id="menu-button" aria-label="Open navigation" aria-expanded="false">
                <span class="material-symbols-rounded">menu</span>
            </button>
            <div class="topbar-copy">
                <strong>{{ now()->format('l, F j') }}</strong>
                <span><i></i> Calendar synced with customer orders</span>
            </div>
            <div class="topbar-actions">
                <a class="store-link" href="{{ route('admin.dashboard') }}#orders"><span class="material-symbols-rounded">receipt_long</span>View orders</a>
                <a class="store-link" href="{{ route('home') }}"><span class="material-symbols-rounded">storefront</span>Storefront</a>
            </div>
        </header>

        <div class="page-wrap schedule-page">
            <section class="page-heading schedule-heading">
                <div>
                    <p class="eyebrow">Kitchen production calendar</p>
                    <h1>Roasting schedule</h1>
                    <p>See every scheduled belly by date, time, fulfillment method, and preparation status.</p>
                </div>
                <div class="heading-actions">
                    <a class="secondary-button" href="{{ route('admin.schedule', ['month' => now()->format('Y-m')]) }}"><span class="material-symbols-rounded">today</span>Today</a>
                    <a class="primary-button" href="{{ route('order') }}"><span class="material-symbols-rounded">add</span>New booking</a>
                </div>
            </section>

            <section class="metrics schedule-metrics" aria-label="Monthly schedule summary">
                <article class="metric-card metric-accent">
                    <div><span class="material-symbols-rounded">event_available</span><small>{{ $month->format('F') }}</small></div>
                    <strong>{{ $totalOrders }}</strong>
                    <p>Scheduled orders</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">outdoor_grill</span><small>Kitchen load</small></div>
                    <strong>{{ $totalBellies }}</strong>
                    <p>Total bellies to roast</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">pending_actions</span><small>Needs action</small></div>
                    <strong>{{ $pendingCount }}</strong>
                    <p>Pending confirmation</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">local_shipping</span><small>Hand-off plan</small></div>
                    <strong>{{ $pickupCount }} / {{ $deliveryCount }}</strong>
                    <p>Pickup / delivery</p>
                </article>
            </section>

            <section class="calendar-card" aria-labelledby="calendar-title">
                <div class="calendar-toolbar">
                    <div>
                        <p class="eyebrow">Monthly production plan</p>
                        <h2 id="calendar-title">{{ $month->format('F Y') }}</h2>
                    </div>
                    <nav class="month-nav" aria-label="Calendar month navigation">
                        <a href="{{ route('admin.schedule', ['month' => $month->subMonth()->format('Y-m')]) }}" aria-label="Previous month"><span class="material-symbols-rounded">chevron_left</span></a>
                        <a href="{{ route('admin.schedule', ['month' => $month->addMonth()->format('Y-m')]) }}" aria-label="Next month"><span class="material-symbols-rounded">chevron_right</span></a>
                    </nav>
                </div>

                <p class="calendar-mobile-hint"><span class="material-symbols-rounded">swipe</span>Swipe sideways to view every day of the month.</p>

                <div class="calendar-scroll">
                    <div class="calendar-weekdays" aria-hidden="true">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                            <span>{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="calendar-grid">
                        @foreach ($days as $day)
                            <article @class([
                                'calendar-day',
                                'outside-month' => ! $day['is_current_month'],
                                'today' => $day['is_today'],
                            ])>
                                <header>
                                    <time datetime="{{ $day['date']->toDateString() }}">{{ $day['date']->day }}</time>
                                    @if ($day['orders']->isNotEmpty())
                                        <span>{{ $day['orders']->count() }}</span>
                                    @endif
                                </header>
                                <div class="day-orders">
                                    @foreach ($day['orders'] as $order)
                                        <button class="calendar-order" type="button" data-order-id="{{ $order['id'] }}" data-status="{{ $order['status'] }}">
                                            <span class="event-time">{{ \Carbon\CarbonImmutable::parse($order['schedule'])->format('g:i A') }}</span>
                                            <strong>{{ $order['customer'] }}</strong>
                                            <small>{{ $order['package'] }}</small>
                                        </button>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="calendar-legend" aria-label="Order status legend">
                    @foreach (['Pending confirmation', 'Confirmed', 'Completed', 'Cancelled'] as $status)
                        <span data-status="{{ $status }}"><i></i>{{ $status }}</span>
                    @endforeach
                </div>
            </section>

            <section class="agenda-card" aria-labelledby="agenda-title">
                <div class="agenda-heading">
                    <div><p class="eyebrow">At-a-glance list</p><h2 id="agenda-title">All {{ $month->format('F') }} orders</h2></div>
                    <span>{{ $totalOrders }} {{ $totalOrders === 1 ? 'schedule' : 'schedules' }}</span>
                </div>
                <div class="agenda-list">
                    @forelse ($orders as $order)
                        <button class="agenda-order" type="button" data-order-id="{{ $order['id'] }}">
                            <time datetime="{{ $order['schedule'] }}"><strong>{{ \Carbon\CarbonImmutable::parse($order['schedule'])->format('M j') }}</strong><span>{{ \Carbon\CarbonImmutable::parse($order['schedule'])->format('g:i A') }}</span></time>
                            <span class="agenda-customer"><strong>{{ $order['customer'] }}</strong><small>{{ $order['package'] }} · {{ $order['fulfillment'] }}</small></span>
                            <span class="status-badge" data-status="{{ $order['status'] }}">{{ $order['status'] }}</span>
                            <span class="agenda-total">₱{{ number_format($order['total']) }}</span>
                            <span class="material-symbols-rounded">chevron_right</span>
                        </button>
                    @empty
                        <div class="agenda-empty">
                            <span class="material-symbols-rounded">event_busy</span>
                            <h3>No scheduled orders this month</h3>
                            <p>New confirmed booking requests will automatically appear on this calendar.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </main>

    <dialog class="schedule-dialog" id="schedule-dialog">
        <div class="dialog-head">
            <div><p class="eyebrow">Scheduled order</p><h2 id="schedule-order-id"></h2></div>
            <button class="icon-button" type="button" id="close-schedule-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
        </div>
        <div class="schedule-dialog-body">
            <div class="schedule-customer"><span id="schedule-avatar"></span><div><strong id="schedule-customer"></strong><a id="schedule-phone"></a></div></div>
            <div class="schedule-detail-grid">
                <div><span class="material-symbols-rounded">event</span><small>Date and time</small><strong id="schedule-date"></strong></div>
                <div><span class="material-symbols-rounded">local_shipping</span><small>Fulfillment</small><strong id="schedule-fulfillment"></strong></div>
                <div><span class="material-symbols-rounded">payments</span><small>Total, method and status</small><strong id="schedule-payment"></strong></div>
                <div><span class="material-symbols-rounded">pending_actions</span><small>Current status</small><strong id="schedule-status"></strong></div>
            </div>
            <div class="schedule-items" id="schedule-items"></div>
            <div class="schedule-note"><small>Customer note</small><p id="schedule-note"></p></div>
        </div>
        <div class="dialog-actions"><a class="secondary-button" id="schedule-call" href=""><span class="material-symbols-rounded">call</span>Call customer</a><a class="primary-button" href="{{ route('admin.dashboard') }}#orders"><span class="material-symbols-rounded">receipt_long</span>Open orders</a></div>
    </dialog>

    <script>
        window.bellyssimoSchedule = {
            orders: @json($orders),
        };
    </script>
    <script src="{{ asset('js/admin-schedule.js') }}"></script>
</body>
</html>
