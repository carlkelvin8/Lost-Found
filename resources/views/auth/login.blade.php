<!doctype html>
<html lang="en">
<head>
    <title>Login · Lost & Found</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('css/white-black-theme.css') }}" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow-x: hidden;
        }

        body {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            font-family: var(--font-sans);
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-brand-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: #000000;
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .auth-brand h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .auth-brand p {
            color: #737373;
            font-size: 0.875rem;
            margin: 0;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-card-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .auth-card-header p {
            color: #737373;
            font-size: 0.875rem;
            margin: 0;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #000000;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-floating-icon {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .form-floating-icon .form-control {
            height: 48px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 150ms ease;
            width: 100%;
        }

        .form-floating-icon .form-control:focus {
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
            outline: none;
        }

        .form-floating-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #737373;
            font-size: 1.125rem;
            pointer-events: none;
            z-index: 1;
        }

        .form-floating-icon .form-label {
            margin-bottom: 0.5rem;
        }

        .btn {
            height: 48px;
            padding: 0 1.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 150ms ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: #000000;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #404040;
            color: #ffffff;
        }

        .btn-outline-secondary {
            background: transparent;
            color: #000000;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }

        .btn-outline-secondary:hover {
            background: #f5f5f5;
            border-color: #000000;
            color: #000000;
        }

        .w-100 {
            width: 100%;
        }

        .auth-divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
        }

        .auth-divider span {
            position: relative;
            background: #ffffff;
            padding: 0 1rem;
            color: #737373;
            font-size: 0.875rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: #737373;
            font-size: 0.8125rem;
        }

        .auth-footer p {
            margin: 0;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #059669;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .alert i {
            font-size: 1.25rem;
        }

        @media (max-width: 576px) {
            .auth-wrapper {
                padding: 1.5rem 1rem;
            }

            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-brand h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <!-- Brand -->
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <h1>Lost & Found</h1>
            <p>Campus Lost and Found Management System</p>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>Invalid email or password</div>
            </div>
        @endif

        <!-- Card -->
        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Welcome back</h2>
                <p>Sign in to your account to continue</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating-icon">
                    <label class="form-label">Email address</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    <i class="bi bi-envelope"></i>
                </div>

                <div class="form-floating-icon">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                    <i class="bi bi-lock"></i>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>New to Lost & Found?</span>
            </div>

            <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-person-plus"></i> Create an account
            </a>
        </div>

        <div class="auth-footer">
            <p>© 2026 Lost & Found System. All rights reserved.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
