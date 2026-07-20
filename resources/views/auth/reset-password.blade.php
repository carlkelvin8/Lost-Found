<!doctype html>
<html lang="en">
<head>
    <title>Reset Password &middot; NAAP Lost & Found</title>
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
                <h2>Reset password</h2>
                <p>Enter your new password below</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert auth-alert-danger" id="reset-errors">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" id="reset-form" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="auth-form-group">
                    <label class="auth-form-label" for="reset-password">New Password</label>
                    <div class="auth-input-wrapper">
                        <i class="bi bi-lock input-icon"></i>
                        <input
                            id="reset-password"
                            type="password"
                            name="password"
                            class="auth-input"
                            placeholder="Min. 8 characters"
                            autocomplete="new-password"
                            required
                            autofocus
                            style="padding-right: 3rem;"
                        >
                        <button type="button" class="password-toggle" data-target="reset-password" aria-label="Toggle password visibility">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div style="color: #64748b; font-size: 0.875rem; margin-top: 0.375rem;">Minimum 8 characters</div>
                </div>

                <div class="auth-form-group">
                    <label class="auth-form-label" for="reset-password-confirm">Confirm Password</label>
                    <div class="auth-input-wrapper">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input
                            id="reset-password-confirm"
                            type="password"
                            name="password_confirmation"
                            class="auth-input"
                            placeholder="Re-enter new password"
                            autocomplete="new-password"
                            required
                            style="padding-right: 3rem;"
                        >
                        <button type="button" class="password-toggle" data-target="reset-password-confirm" aria-label="Toggle password visibility">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-btn auth-btn-primary" id="reset-submit">
                    <span class="btn-text">
                        <i class="bi bi-check-circle"></i>
                        Reset Password
                    </span>
                    <span class="btn-spinner"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            var icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    var form = document.getElementById('reset-form');
    var submitBtn = document.getElementById('reset-submit');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }

    var errorAlert = document.getElementById('reset-errors');
    if (errorAlert) {
        errorAlert.classList.add('auth-shake');
    }
})();
</script>
</body>
</html>
