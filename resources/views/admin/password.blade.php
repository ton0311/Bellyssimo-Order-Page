<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Change Password | Toño's Bellyssimo Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-password.css') }}">
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
                <a class="active" href="{{ route('admin.password.edit') }}" aria-current="page"><span class="material-symbols-rounded">lock</span>Change password</a>
            </nav>
        </div>
        <div class="sidebar-foot">
            <div class="demo-note"><span class="material-symbols-rounded">shield_lock</span><div><strong>Secure access</strong><small>Protect customer and business data.</small></div></div>
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
            <div class="topbar-copy"><strong>{{ now('Asia/Manila')->format('l, F j') }}</strong><span><i></i>Administrator security</span></div>
            <div class="topbar-actions"><a class="store-link" href="{{ route('admin.dashboard') }}"><span class="material-symbols-rounded">space_dashboard</span>Dashboard</a></div>
        </header>

        <div class="page-wrap password-page">
            <section class="page-heading password-heading">
                <div><p class="eyebrow">Account security</p><h1>Change your admin password</h1><p>Update the password used to access orders, customers, inventory, and reports.</p></div>
                <div class="security-chip"><span class="material-symbols-rounded">verified_user</span>Protected account</div>
            </section>

            <div class="password-layout">
                <article class="password-card">
                    <div class="password-card-head">
                        <span class="password-icon material-symbols-rounded">lock_reset</span>
                        <div><p class="eyebrow">Signed in as</p><h2>{{ $adminLogin }}</h2></div>
                    </div>

                    @if (session('status'))
                        <div class="password-alert success" role="status"><span class="material-symbols-rounded">check_circle</span><div><strong>Password updated</strong><small>{{ session('status') }}</small></div></div>
                    @endif

                    @if ($errors->any())
                        <div class="password-alert error" role="alert"><span class="material-symbols-rounded">error</span><div><strong>Check the form</strong><small>{{ $errors->first() }}</small></div></div>
                    @endif

                    <form method="POST" action="{{ route('admin.password.update') }}" id="password-form">
                        @csrf
                        @method('PATCH')
                        <label>Current password<span class="password-input"><span class="material-symbols-rounded">key</span><input name="current_password" type="password" autocomplete="current-password" required><button type="button" aria-label="Show current password"><span class="material-symbols-rounded">visibility</span></button></span></label>
                        <label>New password<span class="password-input"><span class="material-symbols-rounded">lock</span><input name="password" type="password" autocomplete="new-password" minlength="8" required><button type="button" aria-label="Show new password"><span class="material-symbols-rounded">visibility</span></button></span></label>
                        <label>Confirm new password<span class="password-input"><span class="material-symbols-rounded">lock</span><input name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required><button type="button" aria-label="Show password confirmation"><span class="material-symbols-rounded">visibility</span></button></span></label>
                        <div class="password-actions"><a class="secondary-button" href="{{ route('admin.dashboard') }}">Cancel</a><button class="primary-button" type="submit"><span class="material-symbols-rounded">save</span>Update password</button></div>
                    </form>
                </article>

                <aside class="password-guidance">
                    <div class="guidance-mark"><span class="material-symbols-rounded">security</span></div>
                    <p class="eyebrow">Password checklist</p>
                    <h2>Keep your admin account secure</h2>
                    <ul><li><span class="material-symbols-rounded">check_circle</span>Use at least 8 characters</li><li><span class="material-symbols-rounded">check_circle</span>Include both letters and numbers</li><li><span class="material-symbols-rounded">check_circle</span>Avoid names or common passwords</li><li><span class="material-symbols-rounded">check_circle</span>Use a password unique to Bellyssimo</li></ul>
                    <div class="session-note"><span class="material-symbols-rounded">devices</span><p><strong>Other sessions sign out</strong><small>Changing the password keeps this session active and requires every other device to sign in again.</small></p></div>
                </aside>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('.password-input button').forEach((button) => button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            button.querySelector('span').textContent = reveal ? 'visibility_off' : 'visibility';
        }));

        const sidebar = document.querySelector('#sidebar');
        const backdrop = document.querySelector('#sidebar-backdrop');
        const menuButton = document.querySelector('#menu-button');
        const closeSidebar = () => { sidebar.classList.remove('open'); backdrop.classList.remove('show'); menuButton.setAttribute('aria-expanded', 'false'); };
        menuButton.addEventListener('click', () => { const open = !sidebar.classList.contains('open'); sidebar.classList.toggle('open', open); backdrop.classList.toggle('show', open); menuButton.setAttribute('aria-expanded', String(open)); });
        backdrop.addEventListener('click', closeSidebar);
    </script>
</body>
</html>
