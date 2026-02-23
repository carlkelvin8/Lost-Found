<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $q = trim((string) $request->query('q', ''));
        $departments = Department::query()
            ->when($q !== '', fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('departments.index', compact('departments','q'));
    }

    public function create(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $data = $request->validate([
            'name' => ['required','string','max:150', Rule::unique('departments','name')],
        ]);

        $row = Department::create($data);
        $this->audit($request, 'departments.create', 'departments', $row->id, $data);

        return redirect()->route('departments.index')->with('success', 'Created');
    }

    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);
        $department = Department::findOrFail($id);
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $department = Department::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:150', Rule::unique('departments','name')->ignore($department->id)],
        ]);

        $department->update($data);
        $this->audit($request, 'departments.update', 'departments', $department->id, $data);

        return redirect()->route('departments.index')->with('success', 'Updated');
    }

    public function destroy(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $department = Department::findOrFail($id);
        $impacted = UserProfile::where('department_id', $id)->count();

        $department->delete();
        $this->audit($request, 'departments.delete', 'departments', $id, ['impacted_profiles' => $impacted]);

        return redirect()->route('departments.index')->with('success', 'Deleted');
    }
}
