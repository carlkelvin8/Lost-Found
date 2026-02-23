@extends('layouts.app')

@section('title', 'Categories')

@section('content')
{{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success d-flex gap-2">
            <i class="bi bi-check-circle"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> Please fix the errors below
        </div>
    @endif
  
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold mb-0">Categories</h1>
            <div class="text-muted small">Manage categories</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-primary" href="{{ route('categories.create') }}">
                <i class="bi bi-plus-circle"></i> New
            </a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard') }}">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Search --}}
    <form class="card shadow-sm mb-3" method="GET">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label mb-1">Search</label>
                    <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Type keyword..." />
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route('categories.index') }}">Reset</a>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->name }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('categories.edit', $row->id) }}">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form class="d-inline" method="POST"
                                  action="{{ route('categories.destroy', $row->id) }}"
                                  onsubmit="return confirm('Delete this record?');">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted p-4">No records</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $categories->links() }}
    </div>
@endsection
