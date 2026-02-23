@extends('layouts.app')

@section('title', 'User Details')

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
      <div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 fw-bold mb-0">User #{{ $user->id }}</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.edit', $user->id) }}"><i class="bi bi-pencil"></i> Edit</a>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          @if(!empty($user->profile?->avatar_url))
            <img src="{{ asset($user->profile->avatar_url) }}" alt="Avatar"
                 class="rounded-circle" style="width:56px;height:56px;object-fit:cover">
          @else
            @php $initial = strtoupper(substr($user->email,0,1)); @endphp
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;background:#e0e7ff;color:#2563eb;font-weight:800;font-size:1.25rem">
              {{ $initial }}
            </div>
          @endif
          <div>
            <div class="text-muted small">Email</div>
            <div class="fw-semibold">{{ $user->email }}</div>
          </div>
        </div>

        <div class="text-muted small mt-3">Name</div>
        <div class="fw-semibold">{{ $user->profile?->full_name ?? '—' }}</div>

        <div class="text-muted small mt-3">Roles</div>
        <div>
          @php $names = $user->roles->pluck('name')->values(); @endphp
          @if($names->count())
            @foreach($names as $n)
              <span class="badge text-bg-secondary">{{ $n }}</span>
            @endforeach
          @else
            <span class="text-muted">—</span>
          @endif
        </div>

        <div class="text-muted small mt-3">Status</div>
        @if((int)$user->is_active===1)
          <span class="badge text-bg-success">Active</span>
        @else
          <span class="badge text-bg-danger">Disabled</span>
        @endif

        <div class="text-muted small mt-3">Reports created</div>
        <div class="fw-semibold">{{ $reportsCount }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Profile fields</div>
        <div class="mt-2">
          <div><span class="text-muted">User type:</span> <span class="fw-semibold">{{ $user->profile?->user_type ?? '—' }}</span></div>
          <div><span class="text-muted">Department ID:</span> <span class="fw-semibold">{{ $user->profile?->department_id ?? '—' }}</span></div>
          <div><span class="text-muted">School ID:</span> <span class="fw-semibold">{{ $user->profile?->school_id_number ?? '—' }}</span></div>
          <div><span class="text-muted">Contact:</span> <span class="fw-semibold">{{ $user->profile?->contact_no ?? '—' }}</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
