@extends('layouts.app')

@section('title', 'Roles · Admin')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
    <div class="admin-alert alert-success">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="admin-alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        {{ $errors->first() }}
    </div>
@endif

<div class="admin-page-header">
    <div>
        <h1>Roles</h1>
        <div class="admin-page-subtitle">System role management</div>
    </div>
    <a href="{{ route('roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Role
    </a>
</div>

<form class="admin-filter-card" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input class="form-control"
                   name="q"
                   value="{{ $q ?? '' }}"
                   placeholder="Search role name or description">
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
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
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td class="fw-semibold">{{ $row->name }}</td>
                        <td class="text-muted">{{ $row->description }}</td>
                        <td>
                            <div class="admin-btn-group">
                                <a href="{{ route('roles.edit', $row->id) }}" class="admin-action-btn">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('roles.destroy', $row->id) }}"
                                      class="d-inline"
                                      data-confirm="Are you sure you want to delete this role? This action cannot be undone." data-confirm-text="Delete Role" data-confirm-danger="true">
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
                        <td colspan="4" class="admin-empty-state">No roles found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-pagination">
    {{ $roles->links() }}
</div>
@endsection
