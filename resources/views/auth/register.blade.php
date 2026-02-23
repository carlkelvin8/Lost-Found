<!doctype html>
<html lang="en">
<head>
    <title>Register · Lost & Found</title>
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

        html {
            height: 100%;
        }

        body {
            min-height: 100%;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .register-header {
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

        .register-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .register-header p {
            color: #737373;
            font-size: 0.9375rem;
            margin: 0;
        }

        .register-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #059669;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert ul {
            margin: 0.5rem 0 0 1.25rem;
            padding: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .label-optional {
            font-weight: 400;
            color: #737373;
            font-size: 0.8125rem;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 150ms ease;
            background: #ffffff;
        }

        .form-control::placeholder {
            color: #a3a3a3;
        }

        .form-control:focus {
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 0.8125rem;
            margin-top: 0.375rem;
            display: block;
        }

        .form-text {
            color: #737373;
            font-size: 0.8125rem;
            margin-top: 0.375rem;
            display: block;
        }

        .section-divider {
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            font-size: 1.25rem;
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
        }

        .btn-outline:hover {
            background: #f5f5f5;
            border-color: #000000;
            color: #000000;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
        }

        .divider span {
            position: relative;
            background: #ffffff;
            padding: 0 1rem;
            color: #737373;
            font-size: 0.875rem;
        }

        .register-footer {
            text-align: center;
            margin-top: 2rem;
            color: #737373;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1.5rem 1rem;
            }

            .register-card {
                padding: 2rem 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .register-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="brand-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <h1>Create your account</h1>
            <p>Join the Lost & Found system</p>
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
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Please fix the following errors:</strong>
                </div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card -->
        <div class="register-card">
            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <!-- Account Information -->
                <div class="form-grid">
                    <div>
                        <label class="form-label">Full name</label>
                        <input 
                            class="form-control @error('full_name') is-invalid @enderror" 
                            name="full_name" 
                            value="{{ old('full_name') }}" 
                            placeholder="Enter your full name"
                            required
                        >
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Email address</label>
                        <input 
                            class="form-control @error('email') is-invalid @enderror" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            placeholder="your.email@example.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Password</label>
                        <input 
                            class="form-control @error('password') is-invalid @enderror" 
                            type="password" 
                            name="password" 
                            placeholder="Create a strong password"
                            required
                        >
                        <div class="form-text">Minimum 8 characters</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Confirm password</label>
                        <input 
                            class="form-control" 
                            type="password" 
                            name="password_confirmation" 
                            placeholder="Re-enter your password"
                            required
                        >
                    </div>
                </div>

                <!-- Optional Information -->
                <div class="section-divider">
                    <div class="section-title">
                        <i class="bi bi-info-circle"></i>
                        <span>Additional Information <span class="label-optional">(Optional)</span></span>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-label">School ID number</label>
                        <input 
                            class="form-control @error('school_id_number') is-invalid @enderror" 
                            name="school_id_number" 
                            value="{{ old('school_id_number') }}"
                            placeholder="e.g., 2024-12345"
                        >
                        @error('school_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Department ID</label>
                        <input 
                            class="form-control @error('department_id') is-invalid @enderror" 
                            type="number" 
                            name="department_id" 
                            value="{{ old('department_id') }}"
                            placeholder="Enter department number"
                        >
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Contact number</label>
                        <input 
                            class="form-control @error('contact_no') is-invalid @enderror" 
                            name="contact_no" 
                            value="{{ old('contact_no') }}"
                            placeholder="e.g., 09123456789"
                        >
                        @error('contact_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button class="btn btn-primary" type="submit" style="margin-top: 1.5rem;">
                    <i class="bi bi-person-check"></i> Create Account
                </button>
            </form>

            <div class="divider">
                <span>Already have an account?</span>
            </div>

            <a href="{{ route('login') }}" class="btn btn-outline">
                <i class="bi bi-box-arrow-in-right"></i> Sign in instead
            </a>
        </div>

        <div class="register-footer">
            <p>© 2026 Lost & Found System. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
