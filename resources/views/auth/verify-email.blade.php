<!doctype html>
<html lang="en">
<head>
    <title>Verify Email &middot; NAAP Lost & Found</title>
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
                <h2>Verify your email</h2>
                <p>Almost there! Check your inbox.</p>
            </div>

            <div style="
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
                background: rgba(0, 65, 199, 0.08);
                color: #0041C7;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.5rem;
            ">
                <i class="bi bi-envelope-check"></i>
            </div>

            <p style="
                color: #64748b;
                font-size: 0.9375rem;
                line-height: 1.7;
                margin-bottom: 2rem;
                text-align: center;
            ">
                Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
            </p>

            @if (session('success'))
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" id="verify-form">
                @csrf
                <button type="submit" class="auth-btn auth-btn-primary" id="verify-submit">
                    <span class="btn-text">
                        <i class="bi bi-send"></i>
                        Resend Verification Email
                    </span>
                    <span class="btn-spinner"></span>
                </button>
            </form>

            <div class="auth-divider">
                <span>Not you?</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-btn auth-btn-outline">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    var form = document.getElementById('verify-form');
    var submitBtn = document.getElementById('verify-submit');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }
})();
</script>
</body>
</html>
