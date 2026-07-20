@extends('layouts.app')

@section('title', 'Claims')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
{{-- Header --}}
<div class="admin-page-header">
  <div>
    <h1>Claims</h1>
    <div class="admin-page-subtitle">{{ $isStaff ? 'All claims' : 'My claims' }}</div>
  </div>
  <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('claims.index') }}" class="admin-filter-card">
  <div class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Any</option>
        @foreach(['pending','approved','rejected','cancelled'] as $s)
          <option value="{{ $s }}" @selected($status === $s)>
            {{ ucfirst($s) }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-8 text-end">
      <button class="btn btn-outline-primary">
        <i class="bi bi-filter"></i> Apply
      </button>
    </div>
  </div>
</form>

{{-- Table --}}
<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Report</th>
          <th>Status</th>
          <th>Claimant</th>
          <th>Reviewed</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($claims as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>
              <a href="{{ route('reports.show', $c->report_id) }}">
                #{{ $c->report_id }}
              </a>
            </td>
            <td>
              @php
                $badgeClass = match($c->status) {
                  'approved' => 'admin-badge-success',
                  'rejected' => 'admin-badge-danger',
                  'cancelled' => 'admin-badge-secondary',
                  default => 'admin-badge-warning',
                };
              @endphp
              <span class="admin-badge {{ $badgeClass }}">
                {{ ucfirst($c->status) }}
              </span>
            </td>
            <td>{{ $c->claimant->name ?? '—' }}</td>
            <td>{{ $c->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</td>
            <td>
              <div class="admin-btn-group">
                <a href="{{ route('claims.show', $c->id) }}" class="admin-action-btn">
                  <i class="bi bi-eye"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="admin-empty-state">No claims found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="admin-pagination">
  {{ $claims->links() }}
</div>
@endsection
