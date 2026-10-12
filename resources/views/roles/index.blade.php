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
        <div class="admin-page-subtitle">The system has exactly five roles — each maps to an account type</div>
    </div>
</div>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ ucfirst($row->name) }}</td>
                        <td class="text-muted">{{ $row->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="admin-empty-state">No roles found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection