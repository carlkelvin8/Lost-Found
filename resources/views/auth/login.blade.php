<!doctype html>
<html lang="en">
<head>
    <title>Login · NAAP Lost & Found</title>
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
            overflow: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
        }

        .auth-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        /* Left Side - Brand */
        .auth-brand-side {
            background: linear-gradient(135deg, #0041C7 0%, #0160C9 50%, #0D85D8 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        .auth-brand-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(58, 203, 235, 0.3) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }

        .auth-brand-side::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
            animation: float 25s ease-in-out infinite reverse;
        }

        /* Decorative circles */
        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            top: -150px;
            right: -150px;
            animation: pulse 8s ease-in-out infinite;
        }

        .circle-2 {
            width: 250px;
            height: 250px;
            bottom: -80px;
            left: -80px;
            animation: pulse 10s ease-in-out infinite 2s;
        }

        .circle-3 {
            width: 180px;
            height: 180px;
            top: 45%;
            left: -90px;
            animation: pulse 12s ease-in-out infinite 4s;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.4;
            }
            50% {
                transform: scale(1.15);
                opacity: 0.6;
            }
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-30px, -30px) rotate(5deg); }
        }

        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 520px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-logo {
            width: 140px;
            height: 140px;
            background: #ffffff;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem;
            box-shadow: 0 25px 70px rgba(0, 65, 199, 0.5), 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 12px;
        }

        .brand-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            letter-spacing: -0.03em;
            line-height: 1.15;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
        }

        .brand-content p {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.7;
            text-shadow: 0 1px 10px rgba(0, 0, 0, 0.1);
        }



        /* Right Side - Form */
        .auth-form-side {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow-y: auto;
            position: relative;
        }

        .auth-form-side::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(58, 203, 235, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
            animation: slideIn 0.6s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-header {
            margin-bottom: 3rem;
        }

        .form-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0041C7;
            margin-bottom: 0.75rem;
            letter-spacing: -0.04em;
        }

        .form-header p {
            color: #64748b;
            font-size: 1.0625rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            border: none;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #0041C7;
            margin-bottom: 0.625rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            height: 60px;
            padding: 0 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .form-control:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .form-control:focus {
            border-color: #0041C7;
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 65, 199, 0.08), 0 4px 12px rgba(0, 65, 199, 0.1);
            background: #ffffff;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .forgot-link {
            text-align: right;
            margin-top: 0.5rem;
        }

        .forgot-link a {
            color: #000000;
            font-size: 0.9375rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link a:hover {
            opacity: 0.7;
        }

        .btn {
            width: 100%;
            height: 60px;
            border: none;
            border-radius: 14px;
            font-size: 1.0625rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: #0041C7;
            color: #ffffff;
            margin-top: 0.75rem;
            box-shadow: 0 4px 16px rgba(0, 65, 199, 0.2);
        }

        .btn-primary:hover {
            background: #0160C9;
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 65, 199, 0.35);
        }

        .btn-primary:active {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 65, 199, 0.3);
        }

        .btn-outline {
            background: #ffffff;
            color: #0041C7;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .btn-outline:hover {
            border-color: #0041C7;
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 65, 199, 0.12);
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            position: relative;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 0 1rem;
            color: #0D85D8;
            font-size: 0.875rem;
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .auth-layout {
                grid-template-columns: 1fr;
            }

            .auth-brand-side {
                display: none;
            }

            .auth-form-side {
                background: #ffffff;
            }

            .form-container {
                max-width: 500px;
            }
        }

        @media (max-width: 576px) {
            .auth-form-side {
                padding: 2rem 1.5rem;
            }

            .form-header h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
<div class="auth-layout">
    <!-- Left Side - Brand -->
    <div class="auth-brand-side">
        <!-- Decorative circles -->
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        <div class="decorative-circle circle-3"></div>

        <div class="brand-content">
            <div class="brand-logo">
                <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo">
            </div>
            <h1>National Aviation Academy<br>of the Philippines</h1>
            <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Lost and Found Management System</p>
            <p style="font-size: 0.9375rem; opacity: 0.9;">Piccio Garden, Villamor, Pasay City, Philippines</p>
        </div>
    </div>

    <!-- Right Side - Form -->
    <div class="auth-form-side">
        <div class="form-container">
            <div class="form-header">
                <h2>Welcome back</h2>
                <p>Sign in to continue to your account</p>
            </div>

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

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
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
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                    <div class="forgot-link">
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

            <div class="divider">
                <span>Don't have an account?</span>
            </div>

            <a href="{{ route('register') }}" class="btn btn-outline">
                <i class="bi bi-person-plus"></i>
                Create account
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
