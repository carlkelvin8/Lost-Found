@extends('layouts.app')

@section('title', 'Users')

@section('content')
@if (session('success'))
  <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Please fix the errors below</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif
      @php
  $roleFilter = $role ?? '';
  $activeFilter = $active ?? '';
@endphp

  
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0">Users</h1>
    <div class="text-muted small">Manage accounts</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-primary" href="{{ route('users.create') }}"><i class="bi bi-plus-circle"></i> New</a>
  </div>
</div>

<form class="card shadow-sm mb-3" method="GET" action="{{ route('users.index') }}">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label mb-1">Search (email or name)</label>
        <input class="form-control" name="q" value="{{ $q ?? '' }}" />
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Role</label>
        <select class="form-select" name="role">
          <option value="">Any</option>
          @foreach($roles as $r)
            <option value="{{ $r->name }}" @selected($roleFilter===$r->name)>{{ $r->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Active</label>
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
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-striped align-middle mb-0">
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
                  <span class="badge text-bg-secondary">{{ $n }}</span>
                @endforeach
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @if((int)$u->is_active===1)
                <span class="badge text-bg-success">Active</span>
              @else
                <span class="badge text-bg-danger">Disabled</span>
              @endif
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.show', $u->id) }}"><i class="bi bi-eye"></i></a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.edit', $u->id) }}"><i class="bi bi-pencil"></i></a>
              <form class="d-inline" method="POST" action="{{ route('users.destroy', $u->id) }}">
                @csrf
                <button 
                  class="btn btn-sm btn-outline-danger" 
                  type="submit"
                  data-confirm="Are you sure you want to delete this user? This action cannot be undone."
                  data-confirm-text="Delete User"
                  data-confirm-danger="true"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted p-4">No users</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $users->links() }}
</div>
@endsection
