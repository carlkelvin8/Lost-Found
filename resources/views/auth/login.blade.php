<!doctype html>
<html lang="en">
<head>
    <title>Sign In · NAAP Lost & Found</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="{{ asset('storage/image.png') }}" sizes="192x192" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #0041C7;
            --blue-dark: #0033A0;
            --blue-light: #3ACBEB;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --red: #ef4444;
            --green: #22c55e;
        }

        html, body { height: 100%; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            display: flex;
            align-items: stretch;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 52%;
            background: linear-gradient(145deg, #0033A0 0%, #0041C7 45%, #0D85D8 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 3rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Mesh grid overlay */
        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Glow blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
        }
        .blob-1 { width: 500px; height: 500px; background: #3ACBEB; top: -150px; right: -100px; animation: blobMove 12s ease-in-out infinite; }
        .blob-2 { width: 400px; height: 400px; background: #ffffff; bottom: -100px; left: -80px; animation: blobMove 15s ease-in-out infinite reverse; }

        @keyframes blobMove {
            0%, 100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .brand-wrap {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 440px;
        }

        .brand-logo-box {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 24px 60px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.1);
            overflow: hidden;
            animation: logoFloat 4s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .brand-logo-box img {
            width: 90%;
            height: 90%;
            object-fit: contain;
            padding: 8px;
        }

        .brand-wrap h1 {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }

        .brand-wrap .tagline {
            font-size: 1rem;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .brand-wrap .subtitle {
            font-size: 0.875rem;
            opacity: 0.7;
            line-height: 1.6;
        }

        /* Feature pills */
        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 2.5rem;
        }

        .feature-pill {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 14px;
            padding: 0.875rem 1.25rem;
            backdrop-filter: blur(10px);
            text-align: left;
            transition: background 0.2s;
        }

        .feature-pill:hover { background: rgba(255,255,255,0.15); }

        .pill-icon {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .pill-text { font-size: 0.875rem; color: rgba(255,255,255,0.9); font-weight: 500; }
        .pill-text strong { display: block; font-weight: 700; color: white; font-size: 0.9375rem; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            overflow-y: auto;
            background: white;
        }

        .form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .form-eyebrow {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--blue);
            margin-bottom: 1.25rem;
        }

        .form-eyebrow span {
            width: 24px;
            height: 2px;
            background: var(--blue);
            display: inline-block;
            border-radius: 2px;
        }

        .form-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-800);
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            font-size: 0.9375rem;
            color: var(--gray-400);
            margin-bottom: 2.25rem;
        }

        /* Alerts */
        .form-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.125rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }

        .form-alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .form-alert-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .form-alert i { font-size: 1.125rem; flex-shrink: 0; margin-top: 1px; }

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
            transition: color 0.2s;
        }

        .field-input {
            width: 100%;
            height: 52px;
            padding: 0 1rem 0 2.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            color: var(--gray-800);
            background: var(--gray-50);
            transition: all 0.2s;
            outline: none;
        }

        .field-input:hover {
            border-color: #cbd5e1;
            background: white;
        }

        .field-input:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(0,65,199,0.08);
        }

        .field-input:focus + .field-icon,
        .field-input-wrap:focus-within .field-icon {
            color: var(--blue);
        }

        .field-input::placeholder { color: #bec8d5; }

        .pwd-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            padding: 4px;
            border-radius: 6px;
            font-size: 1rem;
            transition: color 0.2s;
            line-height: 1;
        }

        .pwd-toggle:hover { color: var(--gray-800); }

        /* Forgot link */
        .field-foot {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.375rem;
        }

        .forgot-link {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link:hover { opacity: 0.7; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            margin-top: 0.5rem;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            letter-spacing: -0.01em;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-submit:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,65,199,0.3);
        }

        .btn-submit:hover::after { opacity: 1; }
        .btn-submit:active { transform: translateY(0); box-shadow: none; }

        /* Divider */
        .or-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
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

        /* Register link button */
        .btn-outline {
            width: 100%;
            height: 52px;
            background: white;
            color: var(--gray-800);
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: rgba(0,65,199,0.04);
            transform: translateY(-1px);
        }

        /* Footer */
        .form-foot {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8125rem;
            color: var(--gray-400);
        }

        /* Responsive */
        @media (max-width: 900px) {
            .panel-left { display: none; }
            .panel-right { padding: 2rem 1.5rem; }
            body { overflow-y: auto; }
        }
    </style>
</head>
<body>

<!-- LEFT -->
<div class="panel-left">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="brand-wrap">
        <div class="brand-logo-box">
            <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo">
        </div>
        <h1>National Aviation Academy<br>of the Philippines</h1>
        <p class="tagline">Lost and Found Management System</p>
        <p class="subtitle">Piccio Garden, Villamor, Pasay City</p>

        <div class="feature-pills">
            <div class="feature-pill">
                <div class="pill-icon"><i class="bi bi-search"></i></div>
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

<!-- RIGHT -->
<div class="panel-right">
    <div class="form-wrap">

        <div class="form-eyebrow">
            <span></span> Welcome back
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
                <div>Invalid email or password. Please try again.</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label class="field-label" for="email">Email address</label>
                <div class="field-input-wrap">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="field-input"
                        value="{{ old('email') }}"
                        placeholder="you@naap.edu.ph"
                        autocomplete="email"
                        autofocus
                        required
                        style="padding-left: 2.75rem;"
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
                        style="padding-left: 2.75rem; padding-right: 3rem;"
                    >
                    <i class="bi bi-lock field-icon"></i>
                    <button type="button" class="pwd-toggle" id="pwdToggle">
                        <i class="bi bi-eye" id="pwdIcon"></i>
                    </button>
                </div>
                <div class="field-foot">
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In
            </button>
        </form>

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
const pwdToggle = document.getElementById('pwdToggle');
const pwdInput  = document.getElementById('password');
const pwdIcon   = document.getElementById('pwdIcon');

pwdToggle.addEventListener('click', () => {
    const show = pwdInput.type === 'password';
    pwdInput.type = show ? 'text' : 'password';
    pwdIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
</body>
</html>
