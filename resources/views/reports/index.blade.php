@extends('layouts.app')

@section('title', 'Reports · Lost & Found')

@push('styles')
<style>
  .page-title {
    font-weight: 700;
    font-size: 2rem;
    letter-spacing: -0.02em;
    margin-bottom: 1.5rem;
  }

  .filter-section {
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
  }

  .action-btn {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: var(--transition-fast);
  }

  .table-container {
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }
</style>
@endpush

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
@endphp

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h4 page-title mb-0">Reports</h1>
    <div class="text-muted small">
      @if($isStaff) All reports @else My reports @endif
    </div>
  </div>
  <a class="btn btn-primary btn-sm" href="{{ route('reports.create') }}">
    <i class="bi bi-plus-circle"></i> New Report
  </a>
</div>
{{-- FILTERS --}}
<form class="glass-card p-3 mb-3" method="GET">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <div class="filter-label">Type</div>
      <select class="form-select form-select-sm" name="type">
        <option value="">Any</option>
        <option value="lost" @selected(($type ?? '')==='lost')>Lost</option>
        <option value="found" @selected(($type ?? '')==='found')>Found</option>
      </select>
    </div>

    <div class="col-md-3">
      <div class="filter-label">Status</div>
      <select class="form-select form-select-sm" name="status">
        <option value="">Any</option>
        @foreach(['pending','matched','claimed','returned','archived'] as $s)
          <option value="{{ $s }}" @selected(($status ?? '')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <div class="filter-label">Category</div>
      <select class="form-select form-select-sm" name="category_id">
        <option value="">Any</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected((string)$categoryId===(string)$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <div class="filter-label">Location</div>
      <select class="form-select form-select-sm" name="location_id">
        <option value="">Any</option>
        @foreach($locations as $l)
          <option value="{{ $l->id }}" @selected((string)$locationId===(string)$l->id)>{{ $l->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-9 mt-2">
      <div class="filter-label">Search</div>
      <input class="form-control form-control-sm" name="q" value="{{ $q ?? '' }}" placeholder="Search keywords…">
    </div>

    <div class="col-md-3 mt-2">
      <button class="btn btn-outline-primary btn-sm w-100">
        <i class="bi bi-search"></i> Apply Filters
      </button>
    </div>
  </div>
</form>

{{-- TABLE --}}
<div class="glass-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Item</th>
          <th>Status</th>
          <th>Date</th>
          <th>Location</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($reports as $r)
        <tr>
          <td class="fw-semibold">#{{ $r->id }}</td>
          <td>
            <span class="badge {{ $r->report_type==='lost' ? 'text-bg-warning' : 'text-bg-info' }}">
              {{ strtoupper($r->report_type) }}
            </span>
          </td>
          <td>
            <div class="fw-semibold">{{ $r->item_name ?? '—' }}</div>
            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($r->item_description, 55) }}</div>
          </td>
          <td>
            <span class="badge badge-status text-bg-secondary">{{ ucfirst($r->status) }}</span>
          </td>
          <td>{{ $r->incident_date ?? '—' }}</td>
          <td>{{ $r->location?->name ?? '—' }}</td>
          <td class="text-end">
            <a class="btn btn-outline-secondary btn-sm action-btn" href="{{ route('reports.show',$r->id) }}"><i class="bi bi-eye"></i></a>
            <a class="btn btn-outline-secondary btn-sm action-btn" href="{{ route('reports.edit',$r->id) }}"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-outline-secondary btn-sm action-btn" href="{{ route('reports.history',$r->id) }}"><i class="bi bi-clock-history"></i></a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted p-4">No reports found</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $reports->links() }}
</div>

@endsection
