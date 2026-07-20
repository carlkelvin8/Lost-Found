@extends('layouts.app')

@section('title', 'Departments')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
    <div class="admin-alert alert-success">
        <i class="bi bi-check-circle"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="admin-alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        <div>Please fix the errors below</div>
    </div>
@endif

<div class="admin-page-header">
    <div>
        <h1>Departments</h1>
        <div class="admin-page-subtitle">Manage departments</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-primary" href="{{ route('departments.create') }}">
            <i class="bi bi-plus-circle"></i> New
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>
</div>

<form class="admin-filter-card" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Type keyword..." />
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
            <a class="btn btn-outline-secondary" href="{{ route('departments.index') }}">Reset</a>
        </div>
    </div>
</form>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($departments as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>
                        <div class="admin-btn-group">
                            <a class="admin-action-btn" href="{{ route('departments.edit', $row->id) }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form class="d-inline" method="POST"
                                  action="{{ route('departments.destroy', $row->id) }}"
                                  onsubmit="return confirm('Delete this record?');">
                                @csrf
                                <button class="admin-action-btn admin-action-btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="admin-empty-state">No records</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-pagination">
    {{ $departments->links() }}
</div>
@endsection
