<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\EmailOtp;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthWebController extends WebBaseController
{
    /**
     * Self-registration maps a profile type to exactly one system role.
     * None of these are privileged — Student, Faculty, Staff and Guest all
     * get the same interior; privileged roles (admin, osa) are only ever
     * granted by an admin from Staff Tools → Users.
     */
    private const USER_TYPE_ROLES = [
        'admin'   => 'admin',
        'student' => 'student',
        'faculty' => 'faculty',
        'staff'   => 'staff',
        'guest'   => 'guest',
    ];

    /* =========================
     * SHOW FORMS
     * ========================= */
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        // Self-heal: a fresh deployment may have an empty departments table,
        // which left students with no option to pick (and registration failed
        // because a department is required). Ensure the defaults exist.
        foreach (\App\Models\Department::DEFAULT_NAMES as $name) {
            \App\Models\Department::firstOrCreate(['name' => $name]);
        }

        $departments = \App\Models\Department::orderBy('name')->get();

        return view('auth.register', compact('departments'));
    }

    /* =========================
     * REGISTER (STUDENT DEFAULT)
     * ========================= */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // Resolve the role BEFORE opening the transaction. Creating the role
        // row inside the transaction meant any roles-table hiccup rolled the
        // whole registration back, so the account never existed and the user
        // could not log in (this is what broke Guest registration).
        $roleName = self::USER_TYPE_ROLES[$data['user_type']] ?? 'student';
        $role = $this->resolveRole($roleName);

        $user = DB::transaction(function () use ($data) {

            // ✅ CORRECT: save to `password`
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => 1,
                'email_verified_at' => null,
                'last_login_at' => null,
            ]);

            // Resolve department_id from department_name (students only)
            $departmentId = null;
            if (!empty($data['department_name'])) {
                $dept = \App\Models\Department::where('name', $data['department_name'])->first();
                $departmentId = $dept?->id;
            }

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'department_id' => $departmentId,
                'contact_no' => $data['contact_no'] ?? null,
                'user_type' => $data['user_type'],
                'address' => $data['address'] ?? null,
            ]);

            return $user;
        });

        // Attach the role outside the transaction: the account must survive
        // even if role bookkeeping fails (it is logged instead of thrown).
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        } else {
            \Illuminate\Support\Facades\Log::error('role_assignment_failed', [
                'user_id' => $user->id,
                'role'    => $roleName,
            ]);
        }

        $this->audit($request, 'auth.register', 'users', $user->id, [
            'role' => $roleName,
            'user_type' => $data['user_type']
        ]);

        // Send OTP email verification (never fatal — login resends it)
        $this->sendOtp($user);

        // Auto-login the user
        Auth::login($user);

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Account created! Please verify your email with the OTP code sent to your inbox.');
    }

    /**
     * Find (or lazily create) a role without letting a failure bubble up
     * into the caller — registration/login must never hard-fail on this.
     */
    private function resolveRole(string $roleName): ?Role
    {
        try {
            return Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => ucfirst($roleName) . ' User']
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('role_resolve_failed: ' . $e->getMessage(), [
                'role' => $roleName,
            ]);

            return null;
        }
    }

    /* =========================
     * LOGIN (ADMIN + USER)
     * ========================= */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:190',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user && (int) $user->is_active !== 1) {
            return back()->withErrors(['message' => 'Invalid credentials'])->withInput();
        }

        if (!Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ], true)) {
            return back()->withErrors(['message' => 'Invalid credentials'])->withInput();
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        $this->audit($request, 'auth.login', 'users', $user->id);

        // If email not verified, redirect to verification
        if (!$user->hasVerifiedEmail()) {
            $hasValidOtp = EmailOtp::where('user_id', $user->id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->exists();

            if (!$hasValidOtp) {
                $this->sendOtp($user);
            }

            return redirect()->route('verification.notice');
        }

        // ADMIN / OSA CHECK
        $isAdmin = $user->roles()
            ->whereIn('name', ['admin', 'osa'])
            ->exists();

        return redirect()
            ->route('dashboard')
            ->with('success', $isAdmin ? 'Welcome, Admin' : 'Logged in');
    }

    /* =========================
     * LOGOUT
     * ========================= */
    public function logout(Request $request)
    {
        $u = $this->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($u) {
            $this->audit($request, 'auth.logout', 'users', $u->id);
        }

        return redirect()->route('login')->with('success', 'Logged out');
    }

    /* =========================
     * EMAIL VERIFICATION (OTP-BASED)
     * ========================= */
    public function showVerifyEmail()
    {
        $user = Auth::user();
        
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $otpRecord = EmailOtp::where('user_id', $user->id)
            ->where('otp', $data['otp'])
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code. Please request a new one.']);
        }

        // Mark OTP as used
        $otpRecord->update(['used' => true]);

        // Mark email as verified
        $user->markEmailAsVerified();

        $this->audit($request, 'auth.email_verified', 'users', $user->id);

        return redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $this->sendOtp($user);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Generate and send OTP to user's email
     */
    private function sendOtp(User $user): void
    {
        app(\App\Services\EmailOtpService::class)->send($user);
    }

    /* =========================
     * PASSWORD RESET
     * ========================= */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:190',
            ],
        ]);

        $mailer = config('mail.default');
        $isLocalLog = $mailer === 'log' && app()->environment('local');

        try {
            // Use Laravel's password broker - handles token generation, throttling, expiry, and notification
            // This reuses existing auth.php passwords.users config and password_reset_tokens table
            $status = Password::sendResetLink($request->only('email'));

            // Log broker status for debugging without exposing enumeration to user
            if ($status === Password::RESET_LINK_SENT) {
                \Illuminate\Support\Facades\Log::info('Password reset link sent', ['email' => $request->input('email'), 'mailer' => $mailer]);
            } elseif ($status === Password::RESET_THROTTLED) {
                \Illuminate\Support\Facades\Log::info('Password reset throttled', ['email' => $request->input('email')]);
            } else {
                \Illuminate\Support\Facades\Log::info('Password reset attempt', ['email' => $request->input('email'), 'status' => $status, 'mailer' => $mailer]);
            }

            // Senior dev: detect misconfigured mailer in production
            if ($mailer === 'log' && app()->environment('production')) {
                \Illuminate\Support\Facades\Log::warning('MAIL_MAILER=log in production — email will not reach inbox. Set MAIL_MAILER=smtp with Hostinger/Gmail credentials.');
            }
            if ($mailer === 'smtp' && empty(config('mail.mailers.smtp.host'))) {
                \Illuminate\Support\Facades\Log::error('SMTP host not configured');
            }
        } catch (\Throwable $e) {
            // Do not expose SMTP / mail errors to user, but log root cause for storage/logs/laravel.log
            \Illuminate\Support\Facades\Log::error('Password reset mail failed', [
                'email' => $request->input('email'),
                'mailer' => $mailer,
                'error' => $e->getMessage(),
            ]);
            // In local with log mailer, mail failures should not block token creation — token already stored
        }

        // Always return generic success to prevent email enumeration (security)
        // For local dev with log mailer, add helper hint so tester can find link without inbox
        $message = 'If that email exists, we sent a password reset link.';
        if ($isLocalLog) {
            $message .= ' (Local DEV: MAIL_MAILER=log — check storage/logs/laravel.log or http://127.0.0.1:8000/dev/last-email for link)';
        }
        return back()->with('success', $message);
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'exists:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully!')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
