<!doctype html>
<html lang="en">
<head>
    <title>Forgot Password · NAAP Lost & Found</title>
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

        .brand-icon {
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
            font-size: 0.9375rem;
            margin: 0;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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
            padding: 0 2rem;
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
            width: 100%;
        }

        .btn-primary {
            background: #000000;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #262626;
            color: #ffffff;
        }

        .btn-outline {
            background: transparent;
            color: #000000;
            border: 1px solid rgba(0, 0, 0, 0.2);
            margin-top: 0.75rem;
        }

        .btn-outline:hover {
            background: #f5f5f5;
            border-color: #000000;
            color: #000000;
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

        .help-text {
            color: #737373;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Brand -->
            <div class="auth-brand">
                <div class="brand-icon">
                    <i class="bi bi-key"></i>
                </div>
                <h1>Forgot Password?</h1>
                <p>No problem. We'll send you a reset link.</p>
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
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- Card -->
            <div class="auth-card">
                <div class="help-text">
                    Enter your email address and we'll send you a link to reset your password.
                </div>

                <form method="POST" action="{{ route('password.email') }}">
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

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Reset Link
                    </button>
                </form>

                <a href="{{ route('login') }}" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
