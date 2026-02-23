<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $q = trim((string) $request->query('q', ''));
        $role = trim((string) $request->query('role', ''));
        $active = $request->query('active', '');

        $users = User::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('email', 'like', "%{$q}%")
                    ->orWhereHas('profile', fn($p) => $p->where('full_name', 'like', "%{$q}%"));
            })
            ->when($active !== '', fn($qq) => $qq->where('is_active', (int) $active))
            ->when($role !== '', fn($qq) => $qq->whereHas('roles', fn($r) => $r->where('name', $role)))
            ->with(['profile','roles'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users','q','role','active','roles'));
    }

    public function create(Request $request)
    {
        $this->requireAnyRole(['admin']);
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->requireAnyRole(['admin']);

        $data = $request->validate([
            'email' => ['required','email','max:190', Rule::unique('users','email')],
            'password' => ['required','string','min:8','max:255','confirmed'],
            'is_active' => ['nullable','boolean'],

            'full_name' => ['required','string','max:190'],
            'user_type' => ['required', Rule::in(['student','faculty','staff','visitor'])],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'school_id_number' => ['nullable','string','max:60'],
            'contact_no' => ['nullable','string','max:40'],

            'roles' => ['nullable','array'],
            'roles.*' => ['string','max:50'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'is_active' => array_key_exists('is_active', $data) ? (int) (bool) $data['is_active'] : 1,
                'email_verified_at' => null,
                'last_login_at' => null,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'user_type' => $data['user_type'],
                'department_id' => $data['department_id'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
            ]);

            $roleNames = $data['roles'] ?? [];
            if (!empty($roleNames)) {
                $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->all();
                $user->roles()->sync($roleIds);
            }

            $this->audit($request, 'users.create', 'users', $user->id, ['email' => $user->email, 'roles' => $roleNames]);
        });

        return redirect()->route('users.index')->with('success', 'Created');
    }

    public function show(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);
        $reportsCount = ItemReport::where('reporter_user_id', $id)->count();

        return view('users.show', compact('user','reportsCount'));
    }

    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $user = User::with(['profile','roles'])->findOrFail($id);

        $data = $request->validate([
            'email' => ['required','email','max:190', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8','max:255','confirmed'],
            'is_active' => ['nullable','boolean'],

            'full_name' => ['required','string','max:190'],
            'user_type' => ['required', Rule::in(['student','faculty','staff','visitor'])],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'school_id_number' => ['nullable','string','max:60'],
            'contact_no' => ['nullable','string','max:40'],

            'roles' => ['nullable','array'],
            'roles.*' => ['string','max:50'],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            $update = ['email' => $data['email']];

            if (array_key_exists('is_active', $data)) {
                $update['is_active'] = (int) (bool) $data['is_active'];
            }
            if (!empty($data['password'] ?? null)) {
                $update['password_hash'] = Hash::make($data['password']);
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

            if (array_key_exists('roles', $data)) {
                $roleIds = Role::whereIn('name', $data['roles'] ?? [])->pluck('id')->all();
                $user->roles()->sync($roleIds);
            }

            $this->audit($request, 'users.update', 'users', $user->id, ['email' => $user->email]);
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

        $user->delete();

        $this->audit($request, 'users.delete', 'users', $id, ['email' => $email]);

        return redirect()->route('users.index')->with('success', 'Deleted');
    }
}
