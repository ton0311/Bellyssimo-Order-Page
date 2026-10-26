<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Sign In | Toño's Bellyssimo</title>
    <x-admin-fonts />
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
</head>
<body>
    <header class="login-header">
        <a class="login-brand" href="{{ route('home') }}" aria-label="Toño's Bellyssimo home">
            <span class="login-brand-mark">
                <img alt="Toño's Bellyssimo logo" src="{{ asset('images/bellyssimo-logo.png') }}" width="96" height="96" decoding="async">
            </span>
            <span><strong>Toño's Bellyssimo</strong><small>Basta belly, diri mismo!</small></span>
        </a>
        <span class="staff-pill">Admin portal</span>
        <a class="public-link" href="{{ route('home') }}"><span class="material-symbols-rounded">arrow_back</span>Return to website</a>
    </header>

    <main class="login-main">
        <div class="glow glow-one"></div>
        <div class="glow glow-two"></div>
        <section class="login-layout" aria-labelledby="login-title">
            <div class="welcome-panel">
                <div class="city-pill"><span></span>Ozamiz City operations</div>
                <p class="eyebrow">Private staff access</p>
                <h1>Keep every feast<br><em>on schedule.</em></h1>
                <p>Review reservations, organize roasting schedules, update inventory, and track Bellyssimo performance from one secure workspace.</p>
                <div class="portal-features" aria-label="Admin portal features">
                    <span><i class="material-symbols-rounded">receipt_long</i>Orders</span>
                    <span><i class="material-symbols-rounded">calendar_month</i>Schedule</span>
                    <span><i class="material-symbols-rounded">inventory_2</i>Inventory</span>
                </div>
                <div class="photo-card">
                    <img alt="Toño's Bellyssimo lechon belly served on banana leaves" src="{{ asset('images/home-hero-filipino-v2-1600.jpg') }}" width="1600" height="900" fetchpriority="high" decoding="async">
                    <span>Beautifully crispy, dangerously juicy.</span>
                </div>
            </div>

            <div class="auth-card-wrap">
                <div class="secure-label"><span class="material-symbols-rounded">verified_user</span>Authorized personnel only</div>
                <article class="auth-card">
                    <div class="card-accent"></div>
                    <div class="card-body">
                        <div class="mini-logo">
                            <img alt="" src="{{ asset('images/bellyssimo-logo.png') }}" width="96" height="96" decoding="async">
                        </div>
                        <p class="card-kicker">Welcome back</p>
                        <h2 id="login-title">Sign in to your dashboard</h2>
                        <p class="card-copy">Use your approved administrator username and password.</p>

                        @if (session('status'))
                            <div class="notice success" role="status"><span class="material-symbols-rounded">check_circle</span>{{ session('status') }}</div>
                        @endif

                        @if (! $isConfigured)
                            <div class="notice setup" role="status"><span class="material-symbols-rounded">settings</span>Admin credentials still need to be configured in the website environment.</div>
                        @endif

                        @if ($errors->any())
                            <div class="notice error" role="alert"><span class="material-symbols-rounded">error</span>{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf
                            <label for="login">Admin username</label>
                            <div class="input-shell">
                                <span class="material-symbols-rounded">badge</span>
                                <input id="login" name="login" type="text" value="{{ old('login') }}" placeholder="Enter your admin username" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
                            </div>

                            <div class="password-label"><label for="password">Password</label><small>Secure access</small></div>
                            <div class="input-shell">
                                <span class="material-symbols-rounded">lock</span>
                                <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button class="password-toggle" id="password-toggle" type="button" aria-label="Show password" aria-pressed="false"><span class="material-symbols-rounded">visibility</span></button>
                            </div>

                            <button class="submit-button" type="submit" @disabled(! $isConfigured)>
                                <span>Sign in securely</span><span class="material-symbols-rounded">arrow_forward</span>
                            </button>
                        </form>

                        <div class="security-note"><span class="material-symbols-rounded">shield_lock</span><p><strong>Restricted admin portal</strong><small>Customer and business information is protected. Sign in only from a trusted device.</small></p></div>
                    </div>
                </article>
                <a class="mobile-public-link" href="{{ route('home') }}"><span class="material-symbols-rounded">arrow_back</span>Return to customer website</a>
            </div>
        </section>
    </main>

    <script>
        const password = document.getElementById('password');
        const toggle = document.getElementById('password-toggle');

        toggle?.addEventListener('click', () => {
            const reveal = password.type === 'password';
            password.type = reveal ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(reveal));
            toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            toggle.querySelector('span').textContent = reveal ? 'visibility_off' : 'visibility';
        });
    </script>
</body>
</html>
