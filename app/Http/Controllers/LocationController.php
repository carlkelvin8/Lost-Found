<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $q = trim((string) $request->query('q', ''));
        $locations = Location::query()
            ->when($q !== '', fn($qq) => $qq->where('name','like',"%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('locations.index', compact('locations','q'));
    }

    public function create(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $data = $request->validate([
            'name' => ['required','string','max:190', Rule::unique('locations','name')],
            'details' => ['nullable','string','max:255'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
        ]);

        $row = Location::create($data);
        $this->audit($request, 'locations.create', 'locations', $row->id, $data);

        return redirect()->route('locations.index')->with('success', 'Created');
    }

    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);
        $location = Location::findOrFail($id);
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $location = Location::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:190', Rule::unique('locations','name')->ignore($location->id)],
            'details' => ['nullable','string','max:255'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
        ]);

        $location->update($data);
        $this->audit($request, 'locations.update', 'locations', $location->id, $data);

        return redirect()->route('locations.index')->with('success', 'Updated');
    }

    public function destroy(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $location = Location::findOrFail($id);
        $impacted = ItemReport::where('location_id', $id)->count();

        $location->delete();
        $this->audit($request, 'locations.delete', 'locations', $id, ['impacted_reports' => $impacted]);

        return redirect()->route('locations.index')->with('success', 'Deleted');
    }
}
