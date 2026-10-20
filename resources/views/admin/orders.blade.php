<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard | Toño's Bellyssimo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}?v={{ filemtime(public_path('css/admin-orders.css')) }}">
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
                <a href="#overview" data-section-link="overview"><span class="material-symbols-rounded">space_dashboard</span>Overview</a>
                <a href="#orders" data-section-link="orders"><span class="material-symbols-rounded">receipt_long</span>Orders <b id="nav-order-count">0</b></a>
                <a href="{{ route('admin.schedule') }}"><span class="material-symbols-rounded">calendar_month</span>Roasting schedule</a>
                <a href="#customers" data-section-link="customers"><span class="material-symbols-rounded">group</span>Customers</a>
                <p>Manage</p>
                <a href="#reports" data-section-link="reports"><span class="material-symbols-rounded">monitoring</span>Reports</a>
                <a href="{{ route('admin.notifications.edit') }}"><span class="material-symbols-rounded">notifications_active</span>Email alerts</a>
                <a href="{{ route('admin.password.edit') }}"><span class="material-symbols-rounded">lock</span>Change password</a>
            </nav>
        </div>

        <div class="sidebar-foot">
            <div class="demo-note">
                <span class="material-symbols-rounded">sync</span>
                <div><strong>Live order feed</strong><small>Customer bookings sync here.</small></div>
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
                <strong>{{ now('Asia/Manila')->format('l, F j') }}</strong>
                <span><i></i> Kitchen is accepting orders</span>
            </div>
            <div class="topbar-actions">
                <button class="icon-button" aria-label="Notifications"><span class="material-symbols-rounded">notifications</span><i></i></button>
                <a class="store-link" href="{{ route('home') }}"><span class="material-symbols-rounded">storefront</span>View storefront</a>
            </div>
        </header>

        <div class="page-wrap">
            <section class="admin-section" data-admin-section="overview" id="overview">
                <div class="page-heading">
                    <div>
                        <p class="eyebrow">Owner overview</p>
                        <h1>What needs your attention today</h1>
                        <p>Orders, roasting commitments, revenue, customers, and inventory at a glance.</p>
                    </div>
                    <a class="primary-button" href="#orders" data-section-link="orders"><span class="material-symbols-rounded">receipt_long</span>Review orders</a>
                </div>

                <div class="metrics" aria-label="Business overview">
                    <article class="metric-card metric-accent"><div><span class="material-symbols-rounded">pending_actions</span><small>Needs response</small></div><strong>{{ $dashboard['pending'] }}</strong><p>Pending confirmation</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">event_upcoming</span><small>Next 7 days</small></div><strong>{{ $dashboard['upcoming_confirmed'] }}</strong><p>Confirmed orders</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">inventory_2</span><small>Inventory alert</small></div><strong>{{ $dashboard['low_stock_count'] }}</strong><p>Low-stock items</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">payments</span><small>This month</small></div><strong>₱{{ number_format($dashboard['month_revenue']) }}</strong><p>Completed &amp; paid revenue</p></article>
                </div>

                <div class="dashboard-grid overview-insights">
                    <article class="dashboard-card chart-card">
                        <div class="card-title"><div><p class="eyebrow">Revenue trend</p><h2>Completed &amp; paid</h2></div><strong>₱{{ number_format($dashboard['total_revenue']) }}</strong></div>
                        <div class="bar-chart" aria-label="Six-month revenue chart">
                            @foreach ($dashboard['monthly'] as $month)
                                <div class="bar-column"><span class="bar-value">₱{{ number_format($month['revenue'] / 1000, 1) }}k</span><i style="height: {{ $month['height'] }}%"></i><small>{{ $month['label'] }}</small></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="dashboard-card">
                        <div class="card-title"><div><p class="eyebrow">Order pipeline</p><h2>Order progress</h2></div><span class="material-symbols-rounded card-icon">donut_large</span></div>
                        <div class="status-breakdown">
                            @foreach ($dashboard['status_counts'] as $status => $count)
                                <div><span><i data-status="{{ $status }}"></i>{{ $status }}</span><strong>{{ $count }}</strong></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="dashboard-card attention-card">
                        <div class="card-title"><div><p class="eyebrow">Attention queue</p><h2>Owner actions</h2></div><span class="material-symbols-rounded card-icon">notification_important</span></div>
                        <a href="#orders" data-section-link="orders"><span class="material-symbols-rounded">pending_actions</span><div><strong>{{ $dashboard['pending'] }} pending requests</strong><small>Confirm or decline newly submitted orders.</small></div></a>
                        <a href="#orders" data-section-link="orders"><span class="material-symbols-rounded">history</span><div><strong>{{ $dashboard['overdue_pending'] }} overdue requests</strong><small>Pending orders whose preferred date has passed.</small></div></a>
                        <a href="#inventory" data-section-link="inventory"><span class="material-symbols-rounded">inventory</span><div><strong>{{ $dashboard['low_stock_count'] }} low-stock items</strong><small>Restock items at or below their alert level.</small></div></a>
                    </article>
                </div>
            </section>

            <section class="admin-section" data-admin-section="orders" id="orders" hidden>
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Order command center</p>
                    <h1>Track every Bellyssimo order</h1>
                    <p>Keep bookings, roasting progress, payments, and hand-offs in one clear view.</p>
                </div>
                <div class="heading-actions">
                    <button class="secondary-button" id="export-button"><span class="material-symbols-rounded">download</span>Export CSV</button>
                    <button class="primary-button" id="new-order-button"><span class="material-symbols-rounded">add</span>New order</button>
                </div>
            </section>

            <section class="metrics" aria-label="Order summary">
                <article class="metric-card metric-accent">
                    <div><span class="material-symbols-rounded">pending_actions</span><small>Needs attention</small></div>
                    <strong id="metric-attention">0</strong>
                    <p>Pending confirmation</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">verified</span><small>Scheduled work</small></div>
                    <strong id="metric-confirmed">0</strong>
                    <p>Confirmed</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">cancel</span><small>Not proceeding</small></div>
                    <strong id="metric-not-proceeding">0</strong>
                    <p>Declined or cancelled</p>
                </article>
                <article class="metric-card">
                    <div><span class="material-symbols-rounded">payments</span><small>Payments received</small></div>
                    <strong id="metric-sales">₱0</strong>
                    <p>Down payments and settled orders</p>
                </article>
            </section>

            <section class="order-workspace">
                <div class="order-list-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Orders</h2>
                            <p id="result-count">0 records</p>
                        </div>
                        <div class="view-toggle" aria-label="View options">
                            <button class="active" aria-label="List view"><span class="material-symbols-rounded">view_list</span></button>
                            <a href="{{ route('admin.schedule') }}" aria-label="Calendar view"><span class="material-symbols-rounded">calendar_view_month</span></a>
                        </div>
                    </div>

                    <div class="toolbar">
                        <label class="search-box">
                            <span class="material-symbols-rounded">search</span>
                            <input id="order-search" type="search" placeholder="Search name, order ID, phone, or email" autocomplete="off">
                        </label>
                        <label class="date-filter">
                            <span class="material-symbols-rounded">event</span>
                            <select id="date-filter" aria-label="Filter by schedule">
                                <option value="all">All schedules</option>
                                <option value="today">Today</option>
                                <option value="upcoming">Upcoming</option>
                            </select>
                        </label>
                    </div>

                    <div class="status-tabs" id="status-tabs" role="tablist" aria-label="Filter orders by status">
                        <button class="active" data-status="all" role="tab">All <span>0</span></button>
                        <button data-status="Pending confirmation" role="tab">Pending <span>0</span></button>
                        <button data-status="Confirmed" role="tab">Confirmed <span>0</span></button>
                        <button data-status="Completed" role="tab">Completed <span>0</span></button>
                        <button data-status="Declined" role="tab">Declined <span>0</span></button>
                        <button data-status="Cancelled" role="tab">Cancelled <span>0</span></button>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Order</th><th>Customer</th><th>Schedule</th><th>Total</th><th>Status</th><th><span class="sr-only">Open</span></th></tr>
                            </thead>
                            <tbody id="orders-body"></tbody>
                        </table>
                        <div class="empty-state" id="empty-state" hidden>
                            <span class="material-symbols-rounded">search_off</span>
                            <h3>No matching orders</h3>
                            <p>Try a different search or status filter.</p>
                        </div>
                    </div>
                </div>

                <aside class="inspector" id="order-inspector" aria-label="Selected order details">
                    <div class="inspector-empty" id="inspector-empty">
                        <span class="material-symbols-rounded">touch_app</span>
                        <h2>Select an order</h2>
                        <p>Choose a row to review the full booking and update its progress.</p>
                    </div>
                    <div id="inspector-content" hidden>
                        <div class="inspector-head">
                            <div>
                                <span class="status-badge" id="inspector-status"></span>
                                <h2 id="inspector-id"></h2>
                                <p id="inspector-created"></p>
                            </div>
                            <button class="icon-button" id="close-inspector" aria-label="Close details"><span class="material-symbols-rounded">close</span></button>
                        </div>

                        <div class="customer-summary">
                            <span class="customer-avatar" id="customer-avatar"></span>
                            <div><strong id="customer-name"></strong><a id="customer-phone" href=""></a><a id="customer-email" href=""></a></div>
                            <a class="icon-button" id="call-customer" aria-label="Call customer"><span class="material-symbols-rounded">call</span></a>
                        </div>

                        <div class="detail-section">
                            <h3>Order details</h3>
                            <div class="product-lines" id="order-items"></div>
                            <div class="order-grand-total"><span>Order total</span><strong id="order-total"></strong></div>
                        </div>

                        <dl class="detail-grid">
                            <div><dt><span class="material-symbols-rounded">calendar_month</span>Schedule</dt><dd id="order-schedule"></dd></div>
                            <div><dt><span class="material-symbols-rounded">local_shipping</span>Fulfillment</dt><dd id="order-fulfillment"></dd></div>
                            <div><dt><span class="material-symbols-rounded">payments</span>Payment</dt><dd id="order-payment"></dd></div>
                            <div><dt><span class="material-symbols-rounded">sticky_note_2</span>Customer note</dt><dd id="order-note"></dd></div>
                        </dl>

                        <div class="rider-draft-panel" id="rider-draft-panel" hidden>
                            <div class="rider-draft-heading">
                                <div><span class="material-symbols-rounded">two_wheeler</span><div><strong>Rider hand-off</strong><small>Edit the draft if needed, then copy and paste it to your rider.</small></div></div>
                                <span class="draft-type" id="rider-draft-type">Delivery</span>
                            </div>
                            <label for="rider-draft" class="sr-only">Pickup and delivery details for rider</label>
                            <textarea id="rider-draft" rows="12" spellcheck="true"></textarea>
                            <button class="primary-button" id="copy-rider-draft" type="button"><span class="material-symbols-rounded">content_copy</span>Copy rider details</button>
                        </div>

                        <div class="payment-panel">
                            <h3>Down payment</h3>
                            <div class="payment-summary">
                                <div><span>Amount received</span><strong id="payment-amount-paid"></strong></div>
                                <div class="balance" id="payment-balance-row" hidden><span>Remaining balance</span><strong id="payment-balance"></strong></div>
                            </div>
                            <form id="payment-form">
                                <label for="amount-paid-input">Down payment amount</label>
                                <span class="currency-input"><b>₱</b><input id="amount-paid-input" name="amount_paid" type="number" min="0" step="1" inputmode="numeric" required></span>
                                <small id="payment-help">Enter the total amount received so far. It will be included in sales.</small>
                                <button class="primary-button" type="submit">Save payment</button>
                            </form>
                        </div>

                        <div class="status-control">
                            <label for="status-select">Update progress</label>
                            <select id="status-select">
                                <option>Pending confirmation</option>
                                <option>Confirmed</option>
                                <option>Completed</option>
                                <option>Declined</option>
                                <option>Cancelled</option>
                            </select>
                            <button class="primary-button" id="save-status">Save status</button>
                        </div>
                        <div class="order-edit-zone">
                            <div><strong>Booking details</strong><span>Update the customer, schedule, fulfillment, payment method, or notes.</span></div>
                            <button class="secondary-button" id="edit-order" type="button"><span class="material-symbols-rounded">edit</span>Edit order</button>
                        </div>
                        <div class="order-danger-zone">
                            <div><strong>Delete order</strong><span>Permanently remove this booking from the dashboard and reports.</span></div>
                            <button class="danger-button" id="delete-order" type="button"><span class="material-symbols-rounded">delete</span>Delete</button>
                        </div>
                    </div>
                </aside>
            </section>
            </section>

            <section class="admin-section" data-admin-section="customers" id="customers" hidden>
                <div class="page-heading">
                    <div><p class="eyebrow">Customer directory</p><h1>Customers for future reference</h1><p>Every submitted booking automatically creates or refreshes a customer record.</p></div>
                    <div class="summary-pill"><span class="material-symbols-rounded">group</span><strong id="customer-count">{{ $customers->count() }}</strong> customers</div>
                </div>
                <div class="data-panel">
                    <div class="panel-heading"><div><h2>Customer records</h2><p>Name, contact details, order history, and completed revenue.</p></div></div>
                    <div class="table-wrap customer-table">
                        <table>
                            <thead><tr><th>Customer</th><th>Phone</th><th>Email address</th><th>Orders</th><th>Completed</th><th>Lifetime value</th><th>Last order</th><th><span class="sr-only">Actions</span></th></tr></thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td><strong>{{ $customer['name'] }}</strong></td>
                                        <td><a class="table-link" href="tel:{{ preg_replace('/\D+/', '', $customer['phone']) }}">{{ $customer['phone'] }}</a></td>
                                        <td>
                                            @if ($customer['email'])
                                                <a class="table-link" href="mailto:{{ $customer['email'] }}">{{ $customer['email'] }}</a>
                                            @else
                                                <span class="cell-note">Not provided</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer['orders'] }}</td>
                                        <td>{{ $customer['completed_orders'] }}</td>
                                        <td class="amount">₱{{ number_format($customer['lifetime_value']) }}</td>
                                        <td>{{ $customer['last_order'] }}</td>
                                        <td><button class="row-action customer-delete" type="button" data-customer-delete="{{ $customer['database_id'] }}" data-customer-name="{{ $customer['name'] }}"><span class="material-symbols-rounded">delete</span>Delete</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8"><div class="empty-state"><span class="material-symbols-rounded">group_off</span><h3>No customers yet</h3><p>Customer records appear here after the first booking.</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="admin-section" data-admin-section="inventory" id="inventory" hidden>
                <div class="page-heading">
                    <div><p class="eyebrow">Inventory control</p><h1>Track kitchen items and supplies</h1><p>Add items, current quantities, alert levels, units, and optional unit costs.</p></div>
                    <button class="primary-button" id="new-inventory-button"><span class="material-symbols-rounded">add</span>Add inventory item</button>
                </div>
                <div class="metrics inventory-metrics">
                    <article class="metric-card"><div><span class="material-symbols-rounded">inventory_2</span><small>Tracked</small></div><strong>{{ $inventoryItems->count() }}</strong><p>Inventory items</p></article>
                    <article class="metric-card {{ $dashboard['low_stock_count'] > 0 ? 'metric-alert' : '' }}"><div><span class="material-symbols-rounded">warning</span><small>Needs attention</small></div><strong>{{ $dashboard['low_stock_count'] }}</strong><p>At or below alert level</p></article>
                </div>
                <div class="data-panel">
                    <div class="panel-heading"><div><h2>Current stock</h2><p>Quantities are manually maintained by the admin.</p></div></div>
                    <div class="table-wrap inventory-table">
                        <table>
                            <thead><tr><th>Item</th><th>Category</th><th>On hand</th><th>Alert at</th><th>Unit cost</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($inventoryItems as $item)
                                    @php($isLow = (float) $item->quantity <= (float) $item->low_stock_threshold)
                                    <tr>
                                        <td><strong>{{ $item->name }}</strong>@if ($item->notes)<span class="cell-note">{{ $item->notes }}</span>@endif</td>
                                        <td>{{ $item->category }}</td>
                                        <td class="amount">{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}</td>
                                        <td>{{ number_format((float) $item->low_stock_threshold, 2) }} {{ $item->unit }}</td>
                                        <td>{{ $item->unit_cost !== null ? '₱'.number_format((float) $item->unit_cost, 2) : '—' }}</td>
                                        <td><span class="stock-badge {{ $isLow ? 'low' : 'good' }}">{{ $isLow ? 'Low stock' : 'In stock' }}</span></td>
                                        <td><button class="row-action" data-inventory-edit="{{ $item->id }}"><span class="material-symbols-rounded">edit</span>Edit</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7"><div class="empty-state"><span class="material-symbols-rounded">inventory_2</span><h3>No inventory items</h3><p>Add your first ingredient, packaging item, or kitchen supply.</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="admin-section" data-admin-section="reports" id="reports" hidden>
                <div class="page-heading">
                    <div><p class="eyebrow">Business reports</p><h1>Revenue and order performance</h1><p>Revenue includes only orders that are both Completed and Paid.</p></div>
                    <a class="secondary-button" href="#orders" data-section-link="orders"><span class="material-symbols-rounded">receipt_long</span>View order details</a>
                </div>
                <div class="metrics">
                    <article class="metric-card metric-accent"><div><span class="material-symbols-rounded">payments</span><small>All time</small></div><strong>₱{{ number_format($dashboard['total_revenue']) }}</strong><p>Completed revenue</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">calendar_month</span><small>This month</small></div><strong>₱{{ number_format($dashboard['month_revenue']) }}</strong><p>Completed revenue</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">task_alt</span><small>Fulfilled</small></div><strong>{{ $dashboard['completed'] }}</strong><p>Completed orders</p></article>
                    <article class="metric-card"><div><span class="material-symbols-rounded">receipt</span><small>Average</small></div><strong>₱{{ number_format($dashboard['average_order']) }}</strong><p>Completed order value</p></article>
                </div>
                <div class="dashboard-grid reports-grid">
                    <article class="dashboard-card chart-card wide-card">
                        <div class="card-title"><div><p class="eyebrow">Six-month view</p><h2>Revenue from completed orders</h2></div><span class="report-note">Cash + GCash</span></div>
                        <div class="bar-chart report-chart">
                            @foreach ($dashboard['monthly'] as $month)
                                <div class="bar-column"><span class="bar-value">₱{{ number_format($month['revenue'] / 1000, 1) }}k</span><i style="height: {{ $month['height'] }}%"></i><small>{{ $month['label'] }} · {{ $month['orders'] }}</small></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="dashboard-card">
                        <div class="card-title"><div><p class="eyebrow">Package demand</p><h2>Bellies ordered by size</h2></div><span class="material-symbols-rounded card-icon">restaurant</span></div>
                        <div class="horizontal-chart">
                            @foreach ($dashboard['package_mix'] as $package)
                                <div><span>{{ $package['weight'] }}</span><i><b style="width: {{ $package['width'] }}%"></b></i><strong>{{ $package['count'] }}</strong></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="dashboard-card">
                        <div class="card-title"><div><p class="eyebrow">Order outcomes</p><h2>Current status totals</h2></div><strong>{{ $dashboard['total_orders'] }}</strong></div>
                        <div class="status-breakdown">
                            @foreach ($dashboard['status_counts'] as $status => $count)
                                <div><span><i data-status="{{ $status }}"></i>{{ $status }}</span><strong>{{ $count }}</strong></div>
                            @endforeach
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </main>

    <dialog id="new-order-dialog">
        <form id="new-order-form" method="dialog">
            <div class="dialog-head">
                <div><p class="eyebrow">Manual booking</p><h2>Add a new order</h2></div>
                <button class="icon-button" type="button" id="close-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div class="form-grid">
                <label class="full">Customer name<input name="customer" required placeholder="e.g. Ana Santos"></label>
                <label>Contact number<span class="admin-phone-field"><b>+63</b><input name="phone" required inputmode="numeric" pattern="9[0-9]{9}" maxlength="10" placeholder="917 123 4567"></span></label>
                <label>Email address <span>(optional)</span><input name="email" type="email" maxlength="254" placeholder="customer@example.com"></label>
                <label>Schedule<input name="schedule" required type="datetime-local"></label>
                <label>Package<select name="package"><option value="barkadahan">Barkadahan · 3 kg · ₱2,000</option><option value="salo-salo">Salo-Salo · 4 kg · ₱2,550</option><option value="handaan">Handaan · 5 kg · ₱2,950</option></select></label>
                <label>Flavor<select name="flavor"><option value="Regular">Regular</option><option value="Spicy">Spicy (+₱50)</option></select></label>
                <label>Fulfillment<select name="fulfillment" id="manual-fulfillment"><option value="Pickup">Pickup at Perayra Village</option><option value="Delivery">Delivery — fee to confirm</option></select></label>
                <label>Payment method<select name="payment_method"><option>Cash</option><option>GCash</option></select></label>
                <label>Payment status<select name="payment"><option>Unpaid</option><option>Deposit received</option><option>Paid</option></select></label>
                <label class="full" id="manual-delivery-location" hidden>Delivery location<input name="delivery_location" placeholder="Barangay, landmark, or full address"></label>
                <label class="full">Customer note<textarea name="note" rows="3" placeholder="Optional preparation or hand-off notes"></textarea></label>
            </div>
            <div class="dialog-actions"><button class="secondary-button" type="button" id="cancel-dialog">Cancel</button><button class="primary-button" type="submit">Add order</button></div>
        </form>
    </dialog>

    <dialog id="edit-order-dialog">
        <form id="edit-order-form" method="dialog">
            <div class="dialog-head">
                <div><p class="eyebrow">Booking details</p><h2>Edit customer order</h2></div>
                <button class="icon-button" type="button" id="close-edit-order-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div class="form-grid">
                <label class="full">Customer name<input name="customer" required maxlength="100"></label>
                <label>Contact number<span class="admin-phone-field"><b>+63</b><input name="phone" required inputmode="numeric" pattern="9[0-9]{9}" maxlength="10"></span></label>
                <label>Email address <span>(optional)</span><input name="email" type="email" maxlength="254"></label>
                <label>Schedule<input name="schedule" required type="datetime-local"></label>
                <label>Fulfillment<select name="fulfillment" id="edit-fulfillment"><option value="Pickup">Pickup at Perayra Village</option><option value="Delivery">Delivery — fee to confirm</option></select></label>
                <label>Payment method<select name="payment_method"><option>Cash</option><option>GCash</option></select></label>
                <label class="full" id="edit-delivery-location" hidden>Delivery location<input name="delivery_location" maxlength="300" placeholder="Barangay, landmark, or full address"></label>
                <label class="full">Customer note<textarea name="note" rows="3" maxlength="300" placeholder="Optional preparation or hand-off notes"></textarea></label>
                <p class="form-note full">Package items, total, payment amount, and progress are managed separately and will not change.</p>
            </div>
            <div class="dialog-actions"><button class="secondary-button" type="button" id="cancel-edit-order">Cancel</button><button class="primary-button" type="submit">Save changes</button></div>
        </form>
    </dialog>

    <dialog id="inventory-dialog">
        <form id="inventory-form" method="dialog">
            <div class="dialog-head">
                <div><p class="eyebrow">Inventory record</p><h2 id="inventory-dialog-title">Add inventory item</h2></div>
                <button class="icon-button" type="button" id="close-inventory-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
            </div>
            <input type="hidden" name="id">
            <div class="form-grid">
                <label class="full">Item name<input name="name" required maxlength="100" placeholder="e.g. Pork belly"></label>
                <label>Category<input name="category" required maxlength="60" placeholder="Ingredient, packaging, supply"></label>
                <label>Unit<input name="unit" required maxlength="30" placeholder="kg, piece, bottle"></label>
                <label>Current quantity<input name="quantity" required min="0" step="0.01" type="number" value="0"></label>
                <label>Low-stock alert<input name="low_stock_threshold" required min="0" step="0.01" type="number" value="0"></label>
                <label>Unit cost <span>(optional)</span><input name="unit_cost" min="0" step="0.01" type="number" placeholder="0.00"></label>
                <label class="full">Notes <span>(optional)</span><textarea name="notes" rows="3" maxlength="300" placeholder="Supplier, storage, or restocking note"></textarea></label>
            </div>
            <div class="dialog-actions"><button class="secondary-button" type="button" id="cancel-inventory-dialog">Cancel</button><button class="primary-button" type="submit">Save item</button></div>
        </form>
    </dialog>

    <dialog class="confirm-dialog" id="delete-order-dialog">
        <div class="dialog-head">
            <div><p class="eyebrow">Permanent action</p><h2>Delete this order?</h2></div>
            <button class="icon-button" type="button" id="close-delete-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
        </div>
        <div class="delete-dialog-copy">
            <span class="material-symbols-rounded">warning</span>
            <div><strong id="delete-order-reference"></strong><p>This booking will be permanently removed from orders, the roasting schedule, and reports. The saved customer contact will remain available.</p></div>
        </div>
        <div class="dialog-actions"><button class="secondary-button" type="button" id="cancel-delete-order">Keep order</button><button class="danger-button confirm-delete" type="button" id="confirm-delete-order"><span class="material-symbols-rounded">delete_forever</span>Delete permanently</button></div>
    </dialog>

    <dialog class="confirm-dialog" id="delete-customer-dialog">
        <div class="dialog-head">
            <div><p class="eyebrow">Permanent action</p><h2>Delete customer details?</h2></div>
            <button class="icon-button" type="button" id="close-delete-customer-dialog" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
        </div>
        <div class="delete-dialog-copy">
            <span class="material-symbols-rounded">person_remove</span>
            <div><strong id="delete-customer-name"></strong><p>The saved contact profile will be permanently removed. Existing orders and their customer details will stay in order history.</p></div>
        </div>
        <div class="dialog-actions"><button class="secondary-button" type="button" id="cancel-delete-customer">Keep customer</button><button class="danger-button confirm-delete" type="button" id="confirm-delete-customer"><span class="material-symbols-rounded">delete_forever</span>Delete details</button></div>
    </dialog>

    <div class="toast" id="toast" role="status" aria-live="polite"><span class="material-symbols-rounded">check_circle</span><span id="toast-message">Saved</span></div>

    <script>
        window.bellyssimoAdmin = {
            orders: @json($orders),
            inventoryItems: @json($inventoryItems),
            storeUrl: @json(route('admin.orders.store')),
            updateUrlTemplate: @json(route('admin.orders.update', ['order' => '__ORDER__'])),
            statusUrlTemplate: @json(route('admin.orders.status', ['order' => '__ORDER__'])),
            paymentUrlTemplate: @json(route('admin.orders.payment', ['order' => '__ORDER__'])),
            deleteUrlTemplate: @json(route('admin.orders.destroy', ['order' => '__ORDER__'])),
            customerDeleteUrlTemplate: @json(route('admin.customers.destroy', ['customer' => '__CUSTOMER__'])),
            inventoryStoreUrl: @json(route('admin.inventory.store')),
            inventoryUpdateUrlTemplate: @json(route('admin.inventory.update', ['inventoryItem' => '__ITEM__'])),
        };
    </script>
    <script src="{{ asset('js/admin-orders.js') }}?v={{ filemtime(public_path('js/admin-orders.js')) }}"></script>
</body>
</html>
