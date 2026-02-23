<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends WebBaseController
{
    /* =========================
     * LIST
     * ========================= */
    public function index(Request $request)
    {
        // admin ONLY
        $this->requireAnyRole(['admin']);

        $q = trim((string) $request->query('q', ''));

        $roles = Role::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('roles.index', compact('roles', 'q'));
    }

    /* =========================
     * CREATE
     * ========================= */
    public function create(Request $request)
    {
        $this->requireAnyRole(['admin']);
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $this->requireAnyRole(['admin']);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $role = Role::create($data);

            $this->audit(
                $request,
                'roles.create',
                'roles',
                $role->id,
                $data
            );
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    /* =========================
     * EDIT
     * ========================= */
    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $role = Role::findOrFail($id);

        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $role, $data) {
            $role->update($data);

            $this->audit(
                $request,
                'roles.update',
                'roles',
                $role->id,
                $data
            );
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    /* =========================
     * DELETE
     * ========================= */
    public function destroy(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $role = Role::findOrFail($id);

        // Prevent deleting roles in use
        $attachedUsers = DB::table('user_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($attachedUsers > 0) {
            return redirect()
                ->route('roles.index')
                ->withErrors(['message' => 'Role is currently assigned to users and cannot be deleted']);
        }

        DB::transaction(function () use ($request, $role) {
            $roleId = $role->id;
            $role->delete();

            $this->audit(
                $request,
                'roles.delete',
                'roles',
                $roleId
            );
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
