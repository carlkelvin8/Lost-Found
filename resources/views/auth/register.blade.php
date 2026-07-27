<!doctype html>
<html lang="en">
<head>
    <title>Create Account · NAAP Lost & Found</title>
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
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(145deg, #002060 0%, #0041C7 40%, #0D85D8 100%);
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            overflow: hidden;
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
        .orb-1 { width: 450px; height: 450px; background: var(--blue-light); top: -150px; right: -100px; animation: orbDrift 14s ease-in-out infinite; }
        .orb-2 { width: 350px; height: 350px; background: #fff; bottom: -100px; left: -80px; animation: orbDrift 18s ease-in-out infinite reverse; }
        .orb-3 { width: 200px; height: 200px; background: #818cf8; top: 50%; left: 50%; transform: translate(-50%,-50%); animation: orbDrift 10s ease-in-out infinite 2s; }

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
        }

        .brand-logo-box {
            width: 112px;
            height: 112px;
            background: white;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
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

        .brand-logo-box img { width: 88%; height: 88%; object-fit: contain; padding: 8px; }

        .brand-wrap h1 {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.03em;
            margin-bottom: 0.625rem;
            text-shadow: 0 2px 16px rgba(0,0,0,0.25);
        }

        .brand-wrap .tagline { font-size: 0.9375rem; font-weight: 600; opacity: 0.92; margin-bottom: 0.375rem; }
        .brand-wrap .loc { font-size: 0.8125rem; opacity: 0.6; }

        /* Steps */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            margin-top: 2.25rem;
            width: 100%;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 0.875rem 1rem;
            text-align: left;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: stepSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .step:nth-child(1) { animation-delay: 0.1s; }
        .step:nth-child(2) { animation-delay: 0.2s; }
        .step:nth-child(3) { animation-delay: 0.3s; }

        @keyframes stepSlideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .step:hover {
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.2);
            transform: translateX(4px);
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            flex-shrink: 0;
            color: white;
            transition: background 0.3s;
        }

        .step:hover .step-num { background: rgba(255,255,255,0.28); }

        .step-label { font-size: 0.8125rem; color: rgba(255,255,255,0.85); font-weight: 500; }
        .step-label strong { display: block; font-weight: 700; color: white; font-size: 0.875rem; margin-bottom: 1px; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            background: white;
            overflow-y: auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 2.5rem;
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
            max-width: 560px;
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
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
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
            border-radius: 9999px;
        }

        .form-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 0.375rem;
        }

        .form-subtitle { font-size: 0.9375rem; color: var(--gray-400); margin-bottom: 2rem; }

        /* Section header */
        .section-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.75rem 0 1.25rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .section-head-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(0,65,199,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 0.9375rem;
        }

        .section-head-label { font-size: 0.875rem; font-weight: 700; color: var(--gray-800); }

        /* Alert */
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

        .form-alert-success { background: var(--green-bg); border-color: var(--green-border); color: #166534; }
        .form-alert-danger  { background: var(--red-bg); border-color: var(--red-border); color: #991b1b; }
        .form-alert i { font-size: 1.125rem; flex-shrink: 0; margin-top: 1px; }
        .form-alert ul { padding-left: 1rem; margin: 0.25rem 0 0; }

        /* Grid row */
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Field */
        .field { margin-bottom: 1rem; }

        .field-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.4rem;
        }

        .field-input-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 0.9375rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.2s;
        }

        .field-input,
        .field-select {
            width: 100%;
            height: 50px;
            padding: 0 0.9375rem 0 2.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            font-size: 0.9rem;
            color: var(--gray-800);
            background: var(--gray-50);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            appearance: none;
        }

        .field-input:hover, .field-select:hover { border-color: var(--gray-300); background: white; }

        .field-input:focus, .field-select:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 4px var(--blue-glow);
        }

        .field-input::placeholder { color: #b0bac9; }

        .field-input-wrap:focus-within .field-icon { color: var(--blue); }

        /* Error state */
        .field-input.is-error, .field-select.is-error {
            border-color: var(--red);
            background: var(--red-bg);
        }

        .field-input.is-error:focus, .field-select.is-error:focus {
            box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
        }

        .field-error {
            font-size: 0.775rem;
            color: var(--red);
            margin-top: 0.35rem;
            display: block;
        }

        /* Select arrow */
        .select-wrap { position: relative; }
        .select-wrap .select-arrow {
            position: absolute;
            right: 0.9375rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            font-size: 0.8125rem;
        }

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
            font-size: 1rem;
            transition: all 0.2s;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pwd-toggle:hover { color: var(--gray-600); background: var(--gray-100); }

        .pwd-toggle:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
        }

        /* Password strength */
        .strength-bar {
            display: none;
            gap: 4px;
            margin-top: 8px;
            align-items: center;
        }
        .strength-bar.show { display: flex; }

        .strength-seg {
            flex: 1;
            height: 4px;
            background: var(--gray-200);
            border-radius: 4px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .strength-seg.weak   { background: #ef4444; }
        .strength-seg.fair   { background: #f59e0b; }
        .strength-seg.good   { background: #3b82f6; }
        .strength-seg.strong { background: #22c55e; }
        .strength-txt { font-size: 0.75rem; color: var(--gray-400); min-width: 60px; text-align: right; font-weight: 500; }

        /* Submit */
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
            margin-top: 1.5rem;
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
        .btn-submit:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(0,65,199,0.2); }

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
        @keyframes btnSpin { to { transform: rotate(360deg); } }

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
        .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }

        /* Outline button */
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

        .form-foot {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-200);
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        /* ── Responsive ── */
        @media (max-width: 960px) {
            .panel-left { display: none; }
            .panel-right { padding: 2rem 1.25rem; min-height: 100vh; }
            body { overflow-y: auto; }
            .mobile-brand { display: flex; }
            .form-title { font-size: 1.625rem; }
        }

        @media (max-width: 520px) {
            .field-row { grid-template-columns: 1fr; }
            .panel-right { padding: 1.5rem 1rem; }
            .form-title { font-size: 1.5rem; }
            .field-input, .field-select { height: 46px; font-size: 0.875rem; }
            .btn-submit, .btn-outline { height: 48px; }
        }

        :focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
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
        <p class="loc">Piccio Garden, Villamor, Pasay City</p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-label">
                    <strong>Create your account</strong>
                    Fill in your personal details
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-label">
                    <strong>Report lost items</strong>
                    Submit reports with photos
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-label">
                    <strong>Get matched &amp; claim</strong>
                    AI finds and notifies you
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
    <div class="form-wrap">

        <!-- Mobile branding -->
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
            <i class="bi bi-rocket-takeoff-fill"></i> Get started
        </div>
        <h1 class="form-title">Create your account</h1>
        <p class="form-subtitle">Fill in the details below to register</p>

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
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" novalidate id="registerForm">
            @csrf

            {{-- ── ACCOUNT INFO ── --}}
            <div class="section-head">
                <div class="section-head-icon"><i class="bi bi-person"></i></div>
                <span class="section-head-label">Account Information</span>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="full_name">Full name</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-person field-icon"></i>
                        <input id="full_name" type="text" name="full_name" class="field-input @error('full_name') is-error @enderror" value="{{ old('full_name') }}" placeholder="Juan Dela Cruz" required>
                    </div>
                    @error('full_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label class="field-label" for="email">Email address</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input id="email" type="email" name="email" class="field-input @error('email') is-error @enderror" value="{{ old('email') }}" placeholder="you@naap.edu.ph" required>
                    </div>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input id="password" type="password" name="password" class="field-input @error('password') is-error @enderror" placeholder="Min. 8 characters" style="padding-right:2.75rem;" required>
                        <button type="button" class="pwd-toggle" data-for="password" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="strength-bar" id="strength-bar">
                        <div class="strength-seg" id="s1"></div>
                        <div class="strength-seg" id="s2"></div>
                        <div class="strength-seg" id="s3"></div>
                        <div class="strength-seg" id="s4"></div>
                        <span class="strength-txt" id="strength-txt"></span>
                    </div>
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label class="field-label" for="password_confirmation">Confirm password</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-shield-lock field-icon"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="field-input" placeholder="Re-enter password" style="padding-right:2.75rem;" required>
                        <button type="button" class="pwd-toggle" data-for="password_confirmation" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>

            {{-- ── PERSONAL DETAILS ── --}}
            <div class="section-head">
                <div class="section-head-icon"><i class="bi bi-person-badge"></i></div>
                <span class="section-head-label">Personal Details</span>
            </div>

            <div class="field">
                <label class="field-label" for="user_type">I am a</label>
                <div class="field-input-wrap select-wrap">
                    <i class="bi bi-people field-icon"></i>
                    <select id="user_type" name="user_type" class="field-select @error('user_type') is-error @enderror" required>
                        <option value="" disabled selected>Select user type</option>
                        <option value="student" {{ old('user_type')=='student' ? 'selected' : '' }}>Student</option>
                        <option value="faculty" {{ old('user_type')=='faculty' ? 'selected' : '' }}>Faculty / Staff</option>
                    </select>
                    <i class="bi bi-chevron-down select-arrow"></i>
                </div>
                @error('user_type')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="school_id_number">School ID</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-credit-card field-icon"></i>
                        <input id="school_id_number" type="text" name="school_id_number" class="field-input" value="{{ old('school_id_number') }}" placeholder="2024-12345">
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="department_name">Department</label>
                    <div class="field-input-wrap select-wrap">
                        <i class="bi bi-building field-icon"></i>
                        <select id="department_name" name="department_name" class="field-select">
                            <option value="" disabled selected>Select department</option>
                            <option value="ICS"  {{ old('department_name')=='ICS'  ? 'selected' : '' }}>ICS</option>
                            <option value="ILAS" {{ old('department_name')=='ILAS' ? 'selected' : '' }}>ILAS</option>
                            <option value="INET" {{ old('department_name')=='INET' ? 'selected' : '' }}>INET</option>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="contact_no">Contact number</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-phone field-icon"></i>
                        <input id="contact_no" type="tel" name="contact_no" class="field-input" value="{{ old('contact_no') }}" placeholder="09123456789">
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="address">Address</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-geo-alt field-icon"></i>
                        <input id="address" type="text" name="address" class="field-input" value="{{ old('address') }}" placeholder="Your full address">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-text">Create Account</span>
                <i class="bi bi-check-circle btn-icon"></i>
            </button>
        </form>

        <div class="security-badge">
            <i class="bi bi-shield-lock-fill"></i>
            Your data is protected with SSL encryption
        </div>

        <div class="or-divider">Already have an account?</div>

        <a href="{{ route('login') }}" class="btn-outline">
            <i class="bi bi-box-arrow-in-right"></i>
            Sign in instead
        </a>

        <p class="form-foot">&copy; {{ date('Y') }} NAAP Lost &amp; Found &mdash; All rights reserved.</p>
    </div>
</div>

<script>
// Password toggle
document.querySelectorAll('.pwd-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.for);
        const icon  = btn.querySelector('i');
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        input.focus();
    });
});

// Password strength
const pwdInput    = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');
const segs        = [document.getElementById('s1'), document.getElementById('s2'), document.getElementById('s3'), document.getElementById('s4')];
const strengthTxt = document.getElementById('strength-txt');

const levels = [
    { label: 'Weak',   cls: 'weak',   fill: 1 },
    { label: 'Fair',   cls: 'fair',   fill: 2 },
    { label: 'Good',   cls: 'good',   fill: 3 },
    { label: 'Strong', cls: 'strong', fill: 4 },
];

pwdInput.addEventListener('input', () => {
    const v = pwdInput.value;
    if (!v) { strengthBar.classList.remove('show'); return; }
    strengthBar.classList.add('show');

    let score = 0;
    if (v.length >= 8)  score++;
    if (v.length >= 12) score++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
    if (/\d/.test(v))   score++;
    if (/[^a-zA-Z0-9]/.test(v)) score++;

    const lvl = score <= 1 ? 0 : score <= 2 ? 1 : score <= 3 ? 2 : 3;
    const { label, cls, fill } = levels[lvl];

    segs.forEach((s, i) => {
        s.className = 'strength-seg' + (i < fill ? ' ' + cls : '');
    });
    strengthTxt.textContent = label;
});

// Form submit loading state
const form = document.getElementById('registerForm');
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
document.querySelectorAll('.field-input.is-error, .field-select.is-error').forEach(function(input) {
    input.addEventListener('input', function() {
        this.classList.remove('is-error');
    });
});
</script>
</body>
</html>
