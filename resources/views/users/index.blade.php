@extends('layouts.app')

@section('title', 'Users')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
  <div class="admin-alert alert-success" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="admin-alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <div>{{ $errors->first() }}</div>
  </div>
@endif

@php
  $roleFilter = $role ?? '';
  $activeFilter = $active ?? '';
@endphp

<div class="admin-page-header">
  <div>
    <h1>Users</h1>
    <div class="admin-page-subtitle">Manage accounts</div>
  </div>
  <a class="btn btn-primary" href="{{ route('users.create') }}"><i class="bi bi-plus-circle"></i> New</a>
</div>

<form class="admin-filter-card" method="GET" action="{{ route('users.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Search (email or name)</label>
      <input class="form-control" name="q" value="{{ $q ?? '' }}" />
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Role</label>
      <select class="form-select" name="role">
        <option value="">Any</option>
        @foreach($roles as $r)
          <option value="{{ $r->name }}" @selected($roleFilter===$r->name)>{{ $r->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Active</label>
      <select class="form-select" name="active">
        <option value="">Any</option>
        <option value="1" @selected((string)$activeFilter==='1')>Active</option>
        <option value="0" @selected((string)$activeFilter==='0')>Disabled</option>
      </select>
    </div>
    <div class="col-12 col-md-2 text-md-end">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
    </div>
  </div>
</form>

<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Avatar</th>
          <th>ID</th>
          <th>Email</th>
          <th>Name</th>
          <th>Roles</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>
              @if(!empty($u->profile?->avatar_url))
                <img src="{{ asset($u->profile->avatar_url) }}" alt="Avatar"
                     class="rounded-circle" style="width:36px;height:36px;object-fit:cover">
              @else
                @php $initial = strtoupper(substr($u->email,0,1)); @endphp
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:#e0e7ff;color:#2563eb;font-weight:700">
                  {{ $initial }}
                </div>
              @endif
            </td>
            <td>{{ $u->id }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->profile?->full_name ?? '—' }}</td>
            <td>
              @php $names = $u->roles->pluck('name')->values(); @endphp
              @if($names->count())
                @foreach($names as $n)
                  <span class="admin-badge admin-badge-secondary">{{ $n }}</span>
                @endforeach
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @if((int)$u->is_active===1)
                <span class="admin-badge admin-badge-success">Active</span>
              @else
                <span class="admin-badge admin-badge-danger">Disabled</span>
              @endif
            </td>
            <td>
              <div class="admin-btn-group">
                <a class="admin-action-btn" href="{{ route('users.show', $u->id) }}"><i class="bi bi-eye"></i></a>
                <a class="admin-action-btn" href="{{ route('users.edit', $u->id) }}"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="{{ route('users.destroy', $u->id) }}">
                  @csrf
                  <button class="admin-action-btn admin-action-btn-danger" type="submit"
                    data-confirm="Are you sure you want to delete this user? This action cannot be undone."
                    data-confirm-text="Delete User"
                    data-confirm-danger="true">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="admin-empty-state">No users</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="admin-pagination">
  {{ $users->links() }}
</div>
@endsection
