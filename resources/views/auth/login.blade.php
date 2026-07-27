<!doctype html>
<html lang="en">
<head>
    <title>Sign In · NAAP Lost & Found</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="{{ asset('image.png') }}" sizes="192x192" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #0041C7;
            --blue-dark: #0033A0;
            --blue-light: #3ACBEB;
            --blue-glow: rgba(0,65,199,0.15);
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --red: #ef4444;
            --red-bg: #fef2f2;
            --red-border: #fecaca;
            --green: #22c55e;
            --green-bg: #f0fdf4;
            --green-border: #bbf7d0;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            display: flex;
            align-items: stretch;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 52%;
            background: linear-gradient(145deg, #002060 0%, #0041C7 40%, #0D85D8 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 3rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(to top, rgba(0,0,0,0.15), transparent);
            pointer-events: none;
        }

        /* Animated orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.2;
            pointer-events: none;
        }
        .orb-1 { width: 500px; height: 500px; background: var(--blue-light); top: -180px; right: -120px; animation: orbDrift 14s ease-in-out infinite; }
        .orb-2 { width: 400px; height: 400px; background: #fff; bottom: -120px; left: -100px; animation: orbDrift 18s ease-in-out infinite reverse; }
        .orb-3 { width: 250px; height: 250px; background: #818cf8; top: 50%; left: 50%; transform: translate(-50%,-50%); animation: orbDrift 10s ease-in-out infinite 2s; }

        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -25px) scale(1.05); }
            66% { transform: translate(-15px, 15px) scale(0.95); }
        }

        .brand-wrap {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 440px;
        }

        .brand-logo-box {
            width: 128px;
            height: 128px;
            background: white;
            border-radius: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem;
            box-shadow:
                0 24px 60px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.1),
                inset 0 1px 0 rgba(255,255,255,0.4);
            overflow: hidden;
            animation: logoFloat 5s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%,100% { transform: translateY(0); box-shadow: 0 24px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1); }
            50% { transform: translateY(-10px); box-shadow: 0 32px 70px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1); }
        }

        .brand-logo-box img {
            width: 88%;
            height: 88%;
            object-fit: contain;
            padding: 10px;
        }

        .brand-wrap h1 {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 16px rgba(0,0,0,0.25);
        }

        .brand-wrap .tagline {
            font-size: 1rem;
            font-weight: 600;
            opacity: 0.92;
            margin-bottom: 0.375rem;
        }

        .brand-wrap .subtitle {
            font-size: 0.875rem;
            opacity: 0.6;
            line-height: 1.6;
        }

        /* Feature pills */
        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 2.75rem;
        }

        .feature-pill {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 0.875rem 1.25rem;
            backdrop-filter: blur(12px);
            text-align: left;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: pillSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .feature-pill:nth-child(1) { animation-delay: 0.1s; }
        .feature-pill:nth-child(2) { animation-delay: 0.2s; }
        .feature-pill:nth-child(3) { animation-delay: 0.3s; }

        @keyframes pillSlideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .feature-pill:hover {
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.2);
            transform: translateX(4px);
        }

        .pill-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            transition: background 0.3s;
        }

        .feature-pill:hover .pill-icon {
            background: rgba(255,255,255,0.25);
        }

        .pill-text { font-size: 0.875rem; color: rgba(255,255,255,0.85); font-weight: 500; }
        .pill-text strong { display: block; font-weight: 700; color: white; font-size: 0.9375rem; margin-bottom: 1px; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            overflow-y: auto;
            background: white;
            position: relative;
        }

        /* Subtle dot pattern */
        .panel-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, var(--gray-200) 1px, transparent 1px);
            background-size: 24px 24px;
            opacity: 0.4;
            pointer-events: none;
        }

        .form-wrap {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: formFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        @keyframes formFadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mobile branding */
        .mobile-brand {
            display: none;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .mobile-brand-logo {
            width: 44px;
            height: 44px;
            background: var(--blue);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mobile-brand-logo img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .mobile-brand-text {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.3;
        }

        .mobile-brand-text span {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-400);
        }

        .form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--blue);
            margin-bottom: 1rem;
            background: var(--blue-glow);
            padding: 0.375rem 0.875rem;
            border-radius: var(--radius-full, 9999px);
        }

        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            font-size: 0.9375rem;
            color: var(--gray-400);
            margin-bottom: 2rem;
        }

        /* Alerts */
        .form-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.125rem;
            border-radius: 14px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
            animation: alertSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes alertSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-alert-success {
            background: var(--green-bg);
            border-color: var(--green-border);
            color: #166534;
        }

        .form-alert-danger {
            background: var(--red-bg);
            border-color: var(--red-border);
            color: #991b1b;
        }

        .form-alert i {
            font-size: 1.125rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Field */
        .field { margin-bottom: 1.25rem; }

        .field-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.2s;
        }

        .field-input {
            width: 100%;
            height: 52px;
            padding: 0 1rem 0 2.875rem;
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            font-size: 0.9375rem;
            color: var(--gray-800);
            background: var(--gray-50);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .field-input:hover {
            border-color: var(--gray-300);
            background: white;
        }

        .field-input:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 4px var(--blue-glow);
        }

        .field-input:focus + .field-icon,
        .field-input-wrap:focus-within .field-icon {
            color: var(--blue);
        }

        .field-input.error {
            border-color: var(--red);
            background: var(--red-bg);
        }

        .field-input.error:focus {
            box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
        }

        .field-input::placeholder { color: #b0bac9; }

        /* Password toggle */
        .pwd-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            padding: 6px;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.2s;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pwd-toggle:hover {
            color: var(--gray-600);
            background: var(--gray-100);
        }

        .pwd-toggle:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
        }

        /* Remember me row */
        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.5rem;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            user-select: none;
        }

        .remember-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue);
            cursor: pointer;
            border-radius: 4px;
        }

        .remember-check span {
            font-size: 0.8125rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .forgot-link {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
            transition: all 0.2s;
            padding: 2px 4px;
            border-radius: 4px;
            margin: -2px -4px;
        }

        .forgot-link:hover {
            opacity: 0.8;
            background: var(--blue-glow);
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            height: 54px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            margin-top: 0.75rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            letter-spacing: -0.01em;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(0,65,199,0.35), 0 4px 12px rgba(0,65,199,0.2);
        }

        .btn-submit:hover::before { opacity: 1; }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(0,65,199,0.2);
        }

        .btn-submit:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
        }

        .btn-submit .btn-text { position: relative; z-index: 1; }
        .btn-submit .btn-icon { position: relative; z-index: 1; transition: transform 0.25s; }
        .btn-submit:hover .btn-icon { transform: translateX(3px); }

        /* Loading state */
        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.85;
        }

        .btn-submit.loading .btn-text,
        .btn-submit.loading .btn-icon { opacity: 0; }

        .btn-submit.loading::after {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: btnSpin 0.7s linear infinite;
        }

        @keyframes btnSpin {
            to { transform: rotate(360deg); }
        }

        /* Divider */
        .or-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.75rem 0;
            color: var(--gray-400);
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }

        /* Register button */
        .btn-outline {
            width: 100%;
            height: 54px;
            background: white;
            color: var(--gray-800);
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-outline:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: rgba(0,65,199,0.03);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,65,199,0.08);
        }

        .btn-outline:active { transform: translateY(0); }

        .btn-outline:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
        }

        /* Security badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        .security-badge i { color: var(--green); font-size: 0.875rem; }

        /* Footer */
        .form-foot {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-200);
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            body { overflow-y: auto; }

            .panel-left { display: none; }

            .panel-right {
                padding: 2rem 1.5rem;
                min-height: 100vh;
            }

            .mobile-brand { display: flex; }

            .form-title { font-size: 1.75rem; }
        }

        @media (max-width: 480px) {
            .panel-right { padding: 1.5rem 1.25rem; }
            .form-title { font-size: 1.5rem; }
            .field-input { height: 48px; font-size: 0.875rem; }
            .btn-submit, .btn-outline { height: 48px; }
        }

        /* ── Focus visible for keyboard nav ── */
        :focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="panel-left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="brand-wrap">
        <div class="brand-logo-box">
            <img src="{{ asset('image.png') }}" alt="NAAP Logo">
        </div>
        <h1>National Aviation Academy<br>of the Philippines</h1>
        <p class="tagline">Lost and Found Management System</p>
        <p class="subtitle">Piccio Garden, Villamor, Pasay City</p>

        <div class="feature-pills">
            <div class="feature-pill">
                <div class="pill-icon"><i class="bi bi-search-heart"></i></div>
                <div class="pill-text">
                    <strong>Smart Matching</strong>
                    AI-powered lost & found matching
                </div>
            </div>
            <div class="feature-pill">
                <div class="pill-icon"><i class="bi bi-bell"></i></div>
                <div class="pill-text">
                    <strong>Real-time Notifications</strong>
                    Instant alerts when items are found
                </div>
            </div>
            <div class="feature-pill">
                <div class="pill-icon"><i class="bi bi-shield-check"></i></div>
                <div class="pill-text">
                    <strong>Secure Claims</strong>
                    Verified ownership process
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
    <div class="form-wrap">

        <!-- Mobile branding (hidden on desktop) -->
        <div class="mobile-brand">
            <div class="mobile-brand-logo">
                <img src="{{ asset('image.png') }}" alt="NAAP">
            </div>
            <div class="mobile-brand-text">
                NAAP Lost & Found
                <span>Management System</span>
            </div>
        </div>

        <div class="form-eyebrow">
            <i class="bi bi-person-fill"></i> Welcome back
        </div>
        <h1 class="form-title">Sign in to<br>your account</h1>
        <p class="form-subtitle">Enter your credentials to continue</p>

        @if(session('success'))
            <div class="form-alert form-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="form-alert form-alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    @if($errors->has('email'))
                        {{ $errors->first('email') }}
                    @else
                        Invalid email or password. Please try again.
                    @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="field">
                <label class="field-label" for="email">Email address</label>
                <div class="field-input-wrap">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="field-input {{ $errors->has('email') ? 'error' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="you@naap.edu.ph"
                        autocomplete="email"
                        autofocus
                        required
                    >
                    <i class="bi bi-envelope field-icon"></i>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <div class="field-input-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="field-input"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        style="padding-right: 3rem;"
                    >
                    <i class="bi bi-lock field-icon"></i>
                    <button type="button" class="pwd-toggle" id="pwdToggle" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="pwdIcon"></i>
                    </button>
                </div>
                <div class="field-row">
                    <label class="remember-check">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-text">Sign In</span>
                <i class="bi bi-arrow-right btn-icon"></i>
            </button>
        </form>

        <div class="security-badge">
            <i class="bi bi-shield-lock-fill"></i>
            Secured with SSL encryption
        </div>

        <div class="or-divider">Don't have an account?</div>

        <a href="{{ route('register') }}" class="btn-outline">
            <i class="bi bi-person-plus"></i>
            Create an account
        </a>

        <p class="form-foot">
            &copy; {{ date('Y') }} NAAP Lost &amp; Found &mdash; All rights reserved.
        </p>
    </div>
</div>

<script>
// Password toggle
const pwdToggle = document.getElementById('pwdToggle');
const pwdInput  = document.getElementById('password');
const pwdIcon   = document.getElementById('pwdIcon');

pwdToggle.addEventListener('click', () => {
    const show = pwdInput.type === 'password';
    pwdInput.type = show ? 'text' : 'password';
    pwdIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    pwdInput.focus();
});

// Form submit loading state
const form = document.getElementById('loginForm');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', function() {
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});

// Re-enable on back button (bfcache)
window.addEventListener('pageshow', function() {
    submitBtn.classList.remove('loading');
    submitBtn.disabled = false;
});

// Remove error class on input
document.querySelectorAll('.field-input.error').forEach(function(input) {
    input.addEventListener('input', function() {
        this.classList.remove('error');
    });
});
</script>
</body>
</html>
