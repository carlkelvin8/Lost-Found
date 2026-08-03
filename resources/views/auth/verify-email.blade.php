<!doctype html>
<html lang="en">
<head>
    <title>Verify Email · NAAP Lost & Found</title>
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
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .verify-card {
            width: 100%;
            max-width: 460px;
            background: white;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.04);
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--blue-glow);
            color: var(--blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
        }

        .verify-title {
            font-size: 1.625rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .verify-subtitle {
            color: var(--gray-400);
            font-size: 0.9375rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .verify-subtitle strong {
            color: var(--gray-600);
        }

        /* OTP Input */
        .otp-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .otp-input {
            width: 52px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            background: var(--gray-50);
            color: var(--gray-900);
            transition: all 0.2s;
            outline: none;
            font-family: 'SF Mono', Consolas, monospace;
        }

        .otp-input:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 4px var(--blue-glow);
        }

        .otp-input.filled {
            border-color: var(--blue);
            background: white;
        }

        .otp-input.is-error {
            border-color: var(--red);
            background: var(--red-bg);
        }

        /* Hidden real input */
        .otp-hidden { position: absolute; opacity: 0; width: 0; height: 0; }

        /* Alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            font-size: 0.8125rem;
            margin-bottom: 1.5rem;
            text-align: left;
            border: 1px solid;
            animation: alertSlide 0.3s ease;
        }

        @keyframes alertSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success { background: var(--green-bg); border-color: var(--green-border); color: #166534; }
        .alert-danger { background: var(--red-bg); border-color: var(--red-border); color: #991b1b; }
        .alert i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        /* Buttons */
        .btn-verify {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.25s;
            margin-bottom: 1rem;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(0,65,199,0.3);
        }

        .btn-verify:active { transform: translateY(0); }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-resend {
            background: none;
            border: 2px solid var(--gray-200);
            color: var(--gray-600);
            width: 100%;
            height: 48px;
            border-radius: 14px;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-resend:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: rgba(0,65,199,0.03);
        }

        .btn-resend:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .timer {
            font-size: 0.75rem;
            color: var(--gray-400);
            margin-top: 1rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--gray-400);
            font-size: 0.8125rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }

        .btn-logout {
            background: none;
            border: none;
            color: var(--gray-400);
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            transition: color 0.2s;
            font-family: inherit;
        }

        .btn-logout:hover { color: var(--red); }

        @media (max-width: 520px) {
            body { padding: 1rem; }
            .verify-card { padding: 2rem 1.5rem; border-radius: 20px; }
            .otp-input { width: 44px; height: 52px; font-size: 1.25rem; }
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-icon">
            <i class="bi bi-envelope-check"></i>
        </div>

        <h1 class="verify-title">Check your email</h1>
        <p class="verify-subtitle">
            We sent a 6-digit verification code to<br>
            <strong>{{ auth()->user()->email }}</strong>
        </p>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    @foreach ($errors->all() as $e)
                        {{ $e }}
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('verification.verify') }}" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="otpHidden" value="">

            <div class="otp-inputs" id="otpContainer">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="0">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
            </div>

            <button type="submit" class="btn-verify" id="verifyBtn" disabled>
                <i class="bi bi-shield-check"></i>
                Verify Email
            </button>
        </form>

        <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
            @csrf
            <button type="submit" class="btn-resend" id="resendBtn">
                <i class="bi bi-arrow-clockwise"></i>
                Resend Code
            </button>
        </form>

        <p class="timer" id="timerText">Code expires in 10 minutes</p>

        <div class="divider">Not you?</div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </button>
        </form>
    </div>

    <script>
    (function() {
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('otpHidden');
        const verifyBtn = document.getElementById('verifyBtn');
        const form = document.getElementById('otpForm');

        function updateHidden() {
            let otp = '';
            inputs.forEach(input => { otp += input.value; });
            hiddenInput.value = otp;
            verifyBtn.disabled = otp.length !== 6;
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const val = this.value.replace(/[^0-9]/g, '');
                this.value = val.slice(0, 1);

                if (val && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                this.classList.toggle('filled', !!this.value);
                updateHidden();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                    updateHidden();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                
                pasted.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('filled');
                    }
                });

                if (pasted.length > 0) {
                    const focusIndex = Math.min(pasted.length, inputs.length - 1);
                    inputs[focusIndex].focus();
                }

                updateHidden();
            });

            input.addEventListener('focus', function() {
                this.select();
            });
        });

        // Auto-focus first input
        inputs[0].focus();

        // Form submit
        form.addEventListener('submit', function() {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';
        });

        // Resend cooldown
        const resendBtn = document.getElementById('resendBtn');
        const resendForm = document.getElementById('resendForm');
        let cooldown = 0;

        resendForm.addEventListener('submit', function() {
            cooldown = 60;
            resendBtn.disabled = true;
            updateCooldown();
        });

        function updateCooldown() {
            if (cooldown > 0) {
                resendBtn.innerHTML = '<i class="bi bi-clock"></i> Resend in ' + cooldown + 's';
                cooldown--;
                setTimeout(updateCooldown, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Resend Code';
            }
        }
    })();
    </script>
</body>
</html>
