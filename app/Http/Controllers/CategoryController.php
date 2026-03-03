<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\ItemReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $q = trim((string) $request->query('q', ''));
        $categories = Category::query()
            ->when($q !== '', fn($qq) => $qq->where('name','like',"%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories','q'));
    }

    public function create(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);
        return view('categories.create');
    }

    public function store(CategoryRequest $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $data = $request->validated();

        $row = Category::create($data);
        $this->audit($request, 'categories.create', 'categories', $row->id, $data);

        return redirect()->route('categories.index')->with('success', 'Created');
    }

    public function edit(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);
        $category = Category::findOrFail($id);
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $category = Category::findOrFail($id);

        $data = $request->validated();

        $category->update($data);
        $this->audit($request, 'categories.update', 'categories', $category->id, $data);

        return redirect()->route('categories.index')->with('success', 'Updated');
    }

    public function destroy(Request $request, int $id)
    {
        $this->requireAnyRole(['admin']);

        $category = Category::findOrFail($id);
        $impacted = ItemReport::where('category_id', $id)->count();

        $category->delete();
        $this->audit($request, 'categories.delete', 'categories', $id, ['impacted_reports' => $impacted]);

        return redirect()->route('categories.index')->with('success', 'Deleted');
    }
}
