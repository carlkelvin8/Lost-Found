<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends WebBaseController
{
    private const PRIVILEGED_ROLES = ['admin', 'osa'];

    /**
     * One account type decides BOTH the profile type (user_type) and the
     * permission role(s). The four self-serve types get the same limited
     * access; OSA and Admin are the only privileged accounts.
     */
    private const ACCOUNT_TYPES = [
        'student' => ['user_type' => 'student', 'roles' => ['student']],
        'faculty' => ['user_type' => 'faculty', 'roles' => ['faculty']],
        'staff'   => ['user_type' => 'staff',   'roles' => ['staff']],
        'guest'   => ['user_type' => 'guest',   'roles' => ['guest']],
        'admin_osa' => ['user_type' => 'admin', 'roles' => ['admin', 'osa']],
    ];

    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $q = trim((string) $request->query('q', ''));
        $active = $request->query('active', '');

        $escapedQ = str_replace(['%', '_'], ['\\%', '\\_'], $q);

        $users = User::query()
            ->when($q !== '', function ($qq) use ($escapedQ) {
                $qq->where('email', 'like', "%{$escapedQ}%")
                    ->orWhereHas('profile', fn($p) => $p->where('full_name', 'like', "%{$escapedQ}%"));
            })
            ->when($active !== '', fn($qq) => $qq->where('is_active', (int) $active))
            ->with(['profile','roles'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users','q','active'));
    }

    public function create(Request $request)
    {
        $this->requireAnyRole(['admin']);
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->requireAnyRole(['admin']);

        $data = $request->validate([
            'email' => ['required','email','max:190', Rule::unique('users','email')],
            'password' => ['required','string','min:8','max:255','confirmed'],
            'is_active' => ['nullable','boolean'],

            'full_name' => ['required','string','max:190'],
            'user_type' => ['required', Rule::in(array_keys(self::ACCOUNT_TYPES))],
            'school_id_number' => ['nullable','string','max:60'],
            'contact_no' => ['nullable','string','max:40'],
        ]);

        $account = self::ACCOUNT_TYPES[$data['user_type']];
        $data['user_type'] = $account['user_type'];
        $requestedRoles = $account['roles'];
        $this->enforceRoleAssignmentPolicy($requestedRoles);

        DB::transaction(function () use ($request, $data, $requestedRoles) {
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => array_key_exists('is_active', $data) ? (int) (bool) $data['is_active'] : 1,
                // Admin-created accounts are pre-verified so they can login immediately without OTP
                'email_verified_at' => now(),
                'last_login_at' => null,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'user_type' => $data['user_type'],
                'department_id' => null,
                'contact_no' => $data['contact_no'] ?? null,
            ]);

            $roleIds = Role::whereIn('name', $requestedRoles)->pluck('id')->all();
            $user->roles()->sync($roleIds);

            $this->audit($request, 'users.create', 'users', $user->id, ['email' => $user->email, 'roles' => $requestedRoles]);
        });

        return redirect()->route('users.index')->with('success', 'Created');
    }

    /* =========================
     * ADMIN ACCOUNT (Staff Tools)
     * ========================= */
    public function createAdmin(Request $request)
    {
        $this->requireAnyRole(['admin']);

        return view('users.create-admin');
    }

    /**
     * Creates an admin account that still has to verify its e-mail address,
     * so a leaked/typed-wrong address can never be used to sign in.
     */
    public function storeAdmin(Request $request)
    {
        $this->requireAnyRole(['admin']);

        $data = $request->validate([
            'email' => ['required','email','max:190', Rule::unique('users','email')],
            'password' => ['required','string','min:8','max:255','confirmed'],

            'full_name' => ['required','string','max:190'],
            'contact_no' => ['nullable','string','max:40'],
        ]);

        // Resolve the role first so the transaction only does account work.
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'System Administrator']
        );

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => 1,
                // Deliberately unverified: the OTP e-mail is the verification step
                'email_verified_at' => null,
                'last_login_at' => null,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'user_type' => 'admin',
                'department_id' => null,
                'contact_no' => $data['contact_no'] ?? null,
            ]);

            return $user;
        });

        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->audit($request, 'users.create_admin', 'users', $user->id, [
            'email' => $user->email,
            'requires_email_verification' => true,
        ]);

        app(\App\Services\EmailOtpService::class)->send($user);

        return redirect()->route('users.index')->with(
            'success',
            "Admin account created. A verification code was sent to {$user->email} — they must verify before their first login."
        );
    }

    public function show(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);
        $reportsCount = ItemReport::where('reporter_user_id', $id)->count();
        $claims = $user->claims()->with(['report'])->orderByDesc('id')->get();

        return view('users.show', compact('user','reportsCount','claims'));
    }

    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);

        if (!$this->hasAnyRole(['admin']) && $user->hasAnyRole(['admin'])) {
            abort(403);
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);
        $actor = $this->user();
        $actorIsAdmin = $this->hasAnyRole(['admin']);

        if (!$actorIsAdmin && $user->hasAnyRole(['admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required','email','max:190', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8','max:255','confirmed'],
            'is_active' => ['nullable','boolean'],

            'full_name' => ['required','string','max:190'],
            'user_type' => ['required', Rule::in(array_keys(self::ACCOUNT_TYPES))],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'school_id_number' => ['nullable','string','max:60'],
            'contact_no' => ['nullable','string','max:40'],
        ]);

        $accountType = $data['user_type'];
        $account = self::ACCOUNT_TYPES[$accountType];
        $data['user_type'] = $account['user_type'];
        $requestedRoles = $account['roles'];

        // Only an admin may give or take away privileged access (OSA / Admin)
        if ($accountType === 'admin_osa' && !$actorIsAdmin) {
            abort(403, 'Only admin can assign admin/OSA access');
        }

        $this->enforceRoleAssignmentPolicy($requestedRoles, $user);

        // Prevent self-deactivation (avoid lockout)
        if ((int) $actor->id === (int) $user->id && array_key_exists('is_active', $data) && (int) (bool) $data['is_active'] === 0) {
            return redirect()->route('users.edit', $id)->withErrors(['message' => 'You cannot deactivate your own account']);
        }

        // Prevent admin from removing own admin access (avoid orphaning system)
        if ((int) $actor->id === (int) $user->id && $actorIsAdmin && !in_array('admin', $requestedRoles, true)) {
            $otherAdmins = User::where('id', '!=', $actor->id)->whereHas('roles', fn($q) => $q->where('name', 'admin'))->exists();
            if (!$otherAdmins) {
                return redirect()->route('users.edit', $id)->withErrors(['message' => 'Cannot remove your own admin access — no other admin exists']);
            }
        }

        DB::transaction(function () use ($request, $user, $data, $requestedRoles) {
            $update = ['email' => $data['email']];

            if (array_key_exists('is_active', $data)) {
                $update['is_active'] = (int) (bool) $data['is_active'];
            }
            if (!empty($data['password'] ?? null)) {
                $update['password'] = Hash::make($data['password']);
            }

            $user->update($update);

            $profileData = [
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'user_type' => $data['user_type'],
                'department_id' => $data['department_id'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $profileData['user_id'] = $user->id;
                UserProfile::create($profileData);
            }

            $roleIds = Role::whereIn('name', $requestedRoles)->pluck('id')->all();
            $user->roles()->sync($roleIds);

            $this->audit($request, 'users.update', 'users', $user->id, ['email' => $user->email, 'roles' => $requestedRoles]);
        });

        return redirect()->route('users.edit', $id)->with('success', 'Updated');
    }

    public function destroy(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        if ((int) optional($this->user())->id === $id) {
            return redirect()->route('users.index')->withErrors(['message' => 'Cannot delete current user']);
        }

        // Hard FK restrictions: item_reports.reporter_user_id and claims.claimant_user_id are RESTRICT
        $hasReports = ItemReport::where('reporter_user_id', $id)->exists();
        $hasClaims = DB::table('claims')->where('claimant_user_id', $id)->exists();

        if ($hasReports || $hasClaims) {
            return redirect()->route('users.index')->withErrors(['message' => 'User is referenced and cannot be deleted']);
        }

        $user = User::findOrFail($id);
        $email = $user->email;

        // Prevent deleting last admin
        if ($user->hasAnyRole(['admin'])) {
            $otherAdmins = User::where('id', '!=', $id)->whereHas('roles', fn($q) => $q->where('name', 'admin'))->exists();
            if (!$otherAdmins) {
                return redirect()->route('users.index')->withErrors(['message' => 'Cannot delete the last admin account']);
            }
        }

        $user->delete();

        $this->audit($request, 'users.delete', 'users', $id, ['email' => $email]);

        return redirect()->route('users.index')->with('success', 'Deleted');
    }

    /**
     * Toggle active/inactive status - admin only, with backend guards.
     */
    public function toggleActive(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $target = User::with('roles')->findOrFail($id);
        $actor = $this->user();

        if ((int) $actor->id === (int) $target->id) {
            return redirect()->route('users.index')->withErrors(['message' => 'You cannot change your own status']);
        }

        // Prevent disabling last admin
        if ((int) $target->is_active === 1 && $target->hasAnyRole(['admin'])) {
            $otherActiveAdmins = User::where('id', '!=', $target->id)
                ->where('is_active', 1)
                ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->exists();
            if (!$otherActiveAdmins) {
                return redirect()->route('users.index')->withErrors(['message' => 'Cannot deactivate the last active admin']);
            }
        }

        $newStatus = (int) $target->is_active === 1 ? 0 : 1;
        $target->update(['is_active' => $newStatus]);

        $this->audit($request, 'users.toggle_active', 'users', $target->id, ['new_status' => $newStatus]);

        return redirect()->route('users.index')->with('success', $newStatus ? 'Account activated' : 'Account deactivated');
    }

    /**
     * Enforce privileged role assignment policy.
     * - Only admin can assign privileged roles (admin, osa)
     * - OSA and other roles cannot escalate to admin via API manipulation
     * - Validates roles exist (already validated via Rule::exists, but double-check)
     */
    private function enforceRoleAssignmentPolicy(array $requestedRoles, ?User $targetUser = null): void
    {
        if (empty($requestedRoles)) return;

        $actorIsAdmin = $this->hasAnyRole(['admin']);
        $privilegedRequested = array_intersect($requestedRoles, self::PRIVILEGED_ROLES);

        if (!empty($privilegedRequested) && !$actorIsAdmin) {
            abort(403, 'You are not authorized to assign privileged roles');
        }

        // Prevent OSA from assigning admin to anyone (including themselves)
        // Already covered above, but explicit for clarity
        if (in_array('admin', $requestedRoles, true) && !$actorIsAdmin) {
            abort(403, 'Only admin can assign admin role');
        }

        // Additional guard: non-admin trying to assign osa when target is being edited
        // OSA users should not be able to create new OSA admins either (optional hardening)
        // Uncomment next block if strict: only admin can assign osa as well
        // if (in_array('osa', $requestedRoles, true) && !$actorIsAdmin) {
        //     abort(403, 'Only admin can assign OSA role');
        // }
    }
}
