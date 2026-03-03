<!doctype html>
<html lang="en">
<head>
    <title>Verify Email · NAAP Lost & Found</title>
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
            max-width: 480px;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 1rem;
        }

        p {
            color: #737373;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
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
            font-size: 0.875rem;
            text-align: left;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #059669;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.25rem;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="verify-icon">
                    <i class="bi bi-envelope-check"></i>
                </div>

                <h1>Verify Your Email</h1>
                
                <p>
                    Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
                </p>

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
