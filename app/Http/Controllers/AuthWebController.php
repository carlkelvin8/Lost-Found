<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'full_name' => ['required', 'string', 'max:190'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'school_id_number' => ['nullable', 'string', 'max:60'],
            'contact_no' => ['nullable', 'string', 'max:40'],
        ]);

        DB::transaction(function () use ($request, $data) {

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
                'department_id' => $data['department_id'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
            ]);

            // DEFAULT ROLE = STUDENT
            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
            }

            $this->audit($request, 'auth.register', 'users', $user->id, [
                'role' => 'student'
            ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Account created successfully. You may now log in.');
    }

    /* =========================
     * LOGIN (ADMIN + USER)
     * ========================= */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
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
}
