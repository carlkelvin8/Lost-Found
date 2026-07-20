@extends('layouts.app')

@section('title', 'User Details')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
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

<div class="form-page-header">
  <div>
    <h1>User #{{ $user->id }}</h1>
    <div class="form-page-subtitle">Account details and activity</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="{{ route('users.edit', $user->id) }}"><i class="bi bi-pencil"></i> Edit</a>
    <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="detail-card">
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
          <div class="detail-label">Email</div>
          <div class="detail-value">{{ $user->email }}</div>
        </div>
      </div>

      <div class="detail-label">Name</div>
      <div class="detail-value">{{ $user->profile?->full_name ?? '—' }}</div>

      <div class="detail-label mt-3">Roles</div>
      <div>
        @php $names = $user->roles->pluck('name')->values(); @endphp
        @if($names->count())
          @foreach($names as $n)
            <span class="admin-badge admin-badge-secondary">{{ $n }}</span>
          @endforeach
        @else
          <span class="text-muted">—</span>
        @endif
      </div>

      <div class="detail-label mt-3">Status</div>
      <div>
        @if((int)$user->is_active===1)
          <span class="admin-badge admin-badge-success">Active</span>
        @else
          <span class="admin-badge admin-badge-danger">Disabled</span>
        @endif
      </div>

      <div class="detail-label mt-3">Reports created</div>
      <div class="detail-value">{{ $reportsCount }}</div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="detail-card">
      <div class="detail-card-title"><i class="bi bi-person-badge"></i> Profile Fields</div>
      <div class="detail-label mt-2">User type</div>
      <div class="detail-value">{{ $user->profile?->user_type ?? '—' }}</div>
      <div class="detail-label mt-2">Department ID</div>
      <div class="detail-value">{{ $user->profile?->department_id ?? '—' }}</div>
      <div class="detail-label mt-2">School ID</div>
      <div class="detail-value">{{ $user->profile?->school_id_number ?? '—' }}</div>
      <div class="detail-label mt-2">Contact</div>
      <div class="detail-value">{{ $user->profile?->contact_no ?? '—' }}</div>
    </div>
  </div>
</div>

<!-- Claims Section -->
<div class="mt-4">
  <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:var(--space-lg);">Claims</h2>
  @if($claims->count() > 0)
    <div class="admin-table-card">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Report</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Reviewed</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($claims as $claim)
              <tr>
                <td>#{{ $claim->id }}</td>
                <td>
                  <a href="{{ route('reports.show', $claim->report_id) }}" class="text-decoration-none">
                    Report #{{ $claim->report_id }}
                  </a>
                </td>
                <td>
                  @php
                    $badgeMap = ['pending' => 'admin-badge-warning', 'approved' => 'admin-badge-success', 'rejected' => 'admin-badge-danger', 'cancelled' => 'admin-badge-secondary'];
                    $bc = $badgeMap[$claim->status] ?? 'admin-badge-secondary';
                  @endphp
                  <span class="admin-badge {{ $bc }}">{{ ucfirst($claim->status) }}</span>
                </td>
                <td>{{ $claim->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $claim->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                  <div class="admin-btn-group">
                    <a href="{{ route('claims.show', $claim->id) }}" class="admin-action-btn">
                      <i class="bi bi-eye"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @else
    <div class="admin-empty-state" style="background:white;border:1px solid var(--border-default);border-radius:12px;padding:2rem;">No claims submitted by this user</div>
  @endif
</div>
@endsection
