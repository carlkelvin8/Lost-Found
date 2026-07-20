<!doctype html>
<html lang="en">
<head>
    <title>Forgot Password &middot; NAAP Lost & Found</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="{{ asset('storage/image.png') }}" sizes="192x192" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet" />
</head>

<body>
<div class="auth-layout">
    <!-- Left Side - Brand -->
    <div class="auth-brand-side">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        <div class="decorative-circle circle-3"></div>

        <div class="brand-content">
            <div class="brand-logo">
                <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo">
            </div>
            <h1>National Aviation Academy<br>of the Philippines</h1>
            <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem;">Lost and Found Management System</p>
            <p style="font-size: 0.9375rem; opacity: 0.85;">Piccio Garden, Villamor, Pasay City, Philippines</p>
        </div>
    </div>

    <!-- Right Side - Form -->
    <div class="auth-form-side">
        <!-- Mobile condensed header -->
        <div class="auth-mobile-header">
            <div class="mobile-logo">
                <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo">
            </div>
            <div class="mobile-text">
                <span class="mobile-title">NAAP Lost & Found</span>
                <span class="mobile-subtitle">National Aviation Academy of the Philippines</span>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>Forgot password?</h2>
                <p>No problem. We'll send you a reset link.</p>
            </div>

            @if (session('success'))
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert-danger" id="forgot-errors">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgot-form" novalidate>
                @csrf

                <div class="auth-form-group">
                    <label class="auth-form-label" for="forgot-email">Email address</label>
                    <div class="auth-input-wrapper">
                        <i class="bi bi-envelope input-icon"></i>
                        <input
                            id="forgot-email"
                            type="email"
                            name="email"
                            class="auth-input"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <p style="color: #64748b; font-size: 0.9375rem; margin-bottom: 1.5rem; line-height: 1.6;">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <button type="submit" class="auth-btn auth-btn-primary" id="forgot-submit">
                    <span class="btn-text">
                        <i class="bi bi-send"></i>
                        Send Reset Link
                    </span>
                    <span class="btn-spinner"></span>
                </button>
            </form>

            <div class="auth-divider">
                <span>Remember your password?</span>
            </div>

            <a href="{{ route('login') }}" class="auth-btn auth-btn-outline">
                <i class="bi bi-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    var form = document.getElementById('forgot-form');
    var submitBtn = document.getElementById('forgot-submit');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }

    var errorAlert = document.getElementById('forgot-errors');
    if (errorAlert) {
        errorAlert.classList.add('auth-shake');
    }
})();
</script>
</body>
</html>
