<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthWebController extends WebBaseController
{
    /* =========================
     * SHOW FORMS
     * ========================= */
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /* =========================
     * REGISTER (STUDENT DEFAULT)
     * ========================= */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($request, $data) {

            // ✅ CORRECT: save to `password`
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => 1,
                'email_verified_at' => null,
                'last_login_at' => null,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'department_id' => $data['department_name'] ?? null, // Stores department name (ICS, ILAS, INET)
                'contact_no' => $data['contact_no'] ?? null,
                'user_type' => $data['user_type'],
                'address' => $data['address'] ?? null,
            ]);

            // ASSIGN ROLE BASED ON SELECTION
            $roleMap = [
                'admin' => 'admin',
                'faculty' => 'osa', // Treat faculty as staff (osa)
                'student' => 'student',
                'visitor' => 'visitor',
            ];
            
            $roleName = $roleMap[$data['user_type']] ?? 'student';
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            $user->roles()->syncWithoutDetaching([$role->id]);

            $this->audit($request, 'auth.register', 'users', $user->id, [
                'role' => $roleName,
                'user_type' => $data['user_type']
            ]);

            return $user;
        });

        // Send OTP email verification
        $this->sendOtp($user);

        // Auto-login the user
        Auth::login($user);

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Account created! Please verify your email with the OTP code sent to your inbox.');
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

        if (!$user) {
            return back()->withErrors(['message' => 'Invalid credentials'])->withInput();
        }

        if ((int) $user->is_active !== 1) {
            return back()->withErrors(['message' => 'Account disabled'])->withInput();
        }

        // ✅ THIS WILL NOW WORK
        if (!Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ], true)) {
            return back()->withErrors(['message' => 'Invalid credentials'])->withInput();
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        $this->audit($request, 'auth.login', 'users', $user->id);

        // If email not verified, redirect to verification
        if (!$user->hasVerifiedEmail()) {
            // Send OTP if needed
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
        // Invalidate any existing OTPs
        EmailOtp::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP (expires in 10 minutes)
        EmailOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send email
        $userName = $user->profile?->full_name ?? 'User';
        Mail::to($user->email)->send(new OtpVerificationMail($otp, $userName));
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
                'exists:users,email',
            ],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->sendPasswordResetNotification(
                app('auth.password.broker')->createToken($user)
            );
        }

        return back()->with('success', 'If that email exists, we sent a password reset link.');
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
