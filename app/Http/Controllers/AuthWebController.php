<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

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
                'department_id' => $data['department_name'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
                'user_type' => $data['user_type'], // Save the selected user type
                'address' => $data['address'] ?? null,
            ]);

            // ASSIGN ROLE BASED ON SELECTION
            $roleMap = [
                'admin' => 'admin',
                'faculty' => 'osa', // Treat faculty as staff (osa)
                'student' => 'student',
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

        // Send email verification notification (commented out for testing)
        // $user->sendEmailVerificationNotification();

        return redirect()
            ->route('login')
            ->with('success', 'Account created successfully. You can now log in.');
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
     * EMAIL VERIFICATION
     * ========================= */
    public function showVerifyEmail()
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->markEmailAsVerified();

        return redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
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
