@extends('layouts.app')

@section('title', 'Roles · Admin')

@section('content')
{{-- ALERTS --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                {{ $errors->first() }}
            </div>
        @endif

  
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h5 fw-bold mb-0">Roles</h1>
                <small class="text-muted">System role management</small>
            </div>
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> New Role
            </a>
        </div>

        {{-- FILTER --}}
        <form class="card mb-3 shadow-sm" method="GET">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input class="form-control"
                               name="q"
                               value="{{ $q ?? '' }}"
                               placeholder="Search role name or description">
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-end" style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td class="fw-semibold">{{ $row->name }}</td>
                                <td class="text-muted">{{ $row->description }}</td>
                                <td class="text-end">
                                    <a href="{{ route('roles.edit', $row->id) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('roles.destroy', $row->id) }}"
                                          class="d-inline"
                                          data-confirm="Are you sure you want to delete this role? This action cannot be undone." data-confirm-text="Delete Role" data-confirm-danger="true">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted p-4">
                                    No roles found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $roles->links() }}
        </div>
@endsection
