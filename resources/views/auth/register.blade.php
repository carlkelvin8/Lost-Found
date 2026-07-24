<!doctype html>
<html lang="en">
<head>
    <title>Create Account · NAAP Lost & Found</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="{{ asset('storage/image.png') }}" sizes="192x192" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #0041C7;
            --blue-dark: #0033A0;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
        }

        html, body { height: 100%; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: white;
            display: flex;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(145deg, #0033A0 0%, #0041C7 50%, #0D85D8 100%);
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
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 36px 36px;
        }

        .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.2; pointer-events: none; }
        .blob-1 { width: 400px; height: 400px; background: #3ACBEB; top: -120px; right: -80px; animation: blobMove 12s ease-in-out infinite; }
        .blob-2 { width: 300px; height: 300px; background: white; bottom: -80px; left: -60px; animation: blobMove 16s ease-in-out infinite reverse; }

        @keyframes blobMove {
            0%,100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(20px,-20px) scale(1.08); }
        }

        .brand-wrap {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .brand-logo-box {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: logoFloat 4s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .brand-logo-box img { width: 88%; height: 88%; object-fit: contain; padding: 6px; }

        .brand-wrap h1 {
            font-size: 1.375rem;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin-bottom: 0.625rem;
        }

        .brand-wrap .tagline { font-size: 0.9rem; font-weight: 600; opacity: 0.9; margin-bottom: 0.375rem; }
        .brand-wrap .loc { font-size: 0.8125rem; opacity: 0.65; }

        .steps {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            margin-top: 2rem;
            width: 100%;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            text-align: left;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            flex-shrink: 0;
            color: white;
        }

        .step-label { font-size: 0.8125rem; color: rgba(255,255,255,0.85); font-weight: 500; }
        .step-label strong { display: block; font-weight: 700; color: white; font-size: 0.875rem; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            background: white;
            overflow-y: auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .form-wrap {
            width: 100%;
            max-width: 560px;
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
            margin-bottom: 1rem;
        }

        .form-eyebrow span { width: 24px; height: 2px; background: var(--blue); display: inline-block; border-radius: 2px; }

        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-800);
            letter-spacing: -0.04em;
            line-height: 1.2;
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
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: rgba(0,65,199,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 0.875rem;
        }

        .section-head-label { font-size: 0.875rem; font-weight: 700; color: var(--gray-800); }

        /* Alert */
        .form-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.125rem;
            border-radius: 12px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }

        .form-alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .form-alert-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
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
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 0.9375rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .field-input,
        .field-select {
            width: 100%;
            height: 48px;
            padding: 0 0.875rem 0 2.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 11px;
            font-size: 0.9rem;
            color: var(--gray-800);
            background: var(--gray-50);
            transition: all 0.2s;
            outline: none;
            appearance: none;
        }

        .field-input:hover, .field-select:hover { border-color: var(--gray-300); background: white; }

        .field-input:focus, .field-select:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(0,65,199,0.08);
        }

        .field-input::placeholder { color: #bec8d5; }

        .field-input-wrap:focus-within .field-icon { color: var(--blue); }

        /* Select arrow */
        .select-wrap { position: relative; }
        .select-wrap .select-arrow {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            font-size: 0.75rem;
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
            padding: 4px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: color 0.2s;
            line-height: 1;
        }
        .pwd-toggle:hover { color: var(--gray-800); }

        /* Strength meter */
        .strength-bar {
            display: none;
            gap: 4px;
            margin-top: 6px;
            align-items: center;
        }
        .strength-bar.show { display: flex; }
        .strength-seg {
            flex: 1;
            height: 3px;
            background: var(--gray-200);
            border-radius: 3px;
            transition: background 0.3s;
        }
        .strength-seg.weak   { background: #ef4444; }
        .strength-seg.fair   { background: #f59e0b; }
        .strength-seg.good   { background: #3b82f6; }
        .strength-seg.strong { background: #22c55e; }
        .strength-txt { font-size: 0.75rem; color: var(--gray-400); min-width: 60px; text-align: right; }

        /* Submit */
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
            margin-top: 1.5rem;
            transition: all 0.2s;
            letter-spacing: -0.01em;
        }

        .btn-submit:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,65,199,0.28);
        }

        .btn-submit:active { transform: translateY(0); box-shadow: none; }

        /* Divider */
        .or-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0;
            color: var(--gray-400);
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }

        /* Outline button */
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
        }

        .form-foot { text-align: center; margin-top: 1.75rem; font-size: 0.8125rem; color: var(--gray-400); }

        /* Error state */
        .field-input.is-error, .field-select.is-error { border-color: #ef4444; }
        .field-error { font-size: 0.775rem; color: #ef4444; margin-top: 0.3rem; display: block; }

        @media (max-width: 960px) {
            .panel-left { display: none; }
            .panel-right { padding: 2rem 1.25rem; }
        }

        @media (max-width: 520px) {
            .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="panel-left">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="brand-wrap">
        <div class="brand-logo-box">
            <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo">
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

        <div class="form-eyebrow">
            <span></span> Get started
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

        <form method="POST" action="{{ route('register.post') }}" novalidate>
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
                        <input id="password" type="password" name="password" class="field-input @error('password') is-error @enderror" placeholder="Min. 8 characters" style="padding-right:2.5rem;" required>
                        <button type="button" class="pwd-toggle" data-for="password"><i class="bi bi-eye"></i></button>
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
                        <input id="password_confirmation" type="password" name="password_confirmation" class="field-input" placeholder="Re-enter password" style="padding-right:2.5rem;" required>
                        <button type="button" class="pwd-toggle" data-for="password_confirmation"><i class="bi bi-eye"></i></button>
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
                    <select id="user_type" name="user_type" class="field-select" required>
                        <option value="" disabled selected>Select user type</option>
                        <option value="student" {{ old('user_type')=='student' ? 'selected' : '' }}>Student</option>
                        <option value="faculty" {{ old('user_type')=='faculty' ? 'selected' : '' }}>Faculty / Staff</option>
                    </select>
                    <i class="bi bi-chevron-down select-arrow"></i>
                </div>
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

            <button type="submit" class="btn-submit">
                <i class="bi bi-person-check"></i>
                Create Account
            </button>
        </form>

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
    });
});

// Password strength
const pwdInput   = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');
const segs       = [document.getElementById('s1'), document.getElementById('s2'), document.getElementById('s3'), document.getElementById('s4')];
const strengthTxt = document.getElementById('strength-txt');

const levels = [
    { label: 'Weak',        cls: 'weak',   fill: 1 },
    { label: 'Fair',        cls: 'fair',   fill: 2 },
    { label: 'Good',        cls: 'good',   fill: 3 },
    { label: 'Strong',      cls: 'strong', fill: 4 },
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
</script>
</body>
</html>
