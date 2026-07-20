@extends('layouts.app')

@section('title', 'Reports · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/reports.css') }}" rel="stylesheet" />
@endpush

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
@endphp

{{-- HEADER --}}
<div class="reports-header">
  <div class="reports-header-content">
    <h1>Reports</h1>
    <p class="reports-header-subtitle">
      @if($isStaff) 
        Manage all lost and found reports
      @else 
        View and manage your reports
      @endif
    </p>
  </div>
  <a class="btn btn-primary" href="{{ route('reports.create') }}">
    <i class="bi bi-plus-lg"></i> New Report
  </a>
</div>

{{-- QUICK STATS --}}
@php
  $lostCount = \App\Models\ItemReport::where('report_type', 'lost')->count();
  $foundCount = \App\Models\ItemReport::where('report_type', 'found')->count();
  $pendingCount = \App\Models\ItemReport::where('status', 'pending')->count();
  $matchedCount = \App\Models\ItemReport::where('status', 'matched')->count();
  $claimedCount = \App\Models\ItemReport::where('status', 'claimed')->count();
@endphp

<div class="quick-stats">
  <div class="stat-item">
    <div class="stat-icon stat-icon-lost">
      <i class="bi bi-exclamation-circle-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Lost Items</div>
      <div class="stat-value">{{ $lostCount }}</div>
    </div>
  </div>

  <div class="stat-item">
    <div class="stat-icon stat-icon-found">
      <i class="bi bi-check-circle-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Found Items</div>
      <div class="stat-value">{{ $foundCount }}</div>
    </div>
  </div>

  <div class="stat-item">
    <div class="stat-icon stat-icon-pending">
      <i class="bi bi-clock-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Pending</div>
      <div class="stat-value">{{ $pendingCount }}</div>
    </div>
  </div>

  <div class="stat-item">
    <div class="stat-icon stat-icon-matched">
      <i class="bi bi-link-45deg"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Matched</div>
      <div class="stat-value">{{ $matchedCount }}</div>
    </div>
  </div>

  <div class="stat-item">
    <div class="stat-icon stat-icon-claimed">
      <i class="bi bi-hand-thumbs-up-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Claimed</div>
      <div class="stat-value">{{ $claimedCount }}</div>
    </div>
  </div>
</div>

{{-- FILTERS --}}
<form class="reports-filters" method="GET">
  <div class="filter-grid">
    <div class="filter-field">
      <label class="filter-label">Type</label>
      <select class="form-select" name="type">
        <option value="">All Types</option>
        <option value="lost" @selected(($type ?? '')==='lost')>Lost</option>
        <option value="found" @selected(($type ?? '')==='found')>Found</option>
      </select>
    </div>

    <div class="filter-field">
      <label class="filter-label">Status</label>
      <select class="form-select" name="status">
        <option value="">All Statuses</option>
        @foreach(['pending','matched','claimed','returned','archived'] as $s)
          <option value="{{ $s }}" @selected(($status ?? '')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>

    <div class="filter-field">
      <label class="filter-label">Category</label>
      <select class="form-select" name="category_id">
        <option value="">All Categories</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected((string)$categoryId===(string)$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="filter-field">
      <label class="filter-label">Location</label>
      <select class="form-select" name="location_id">
        <option value="">All Locations</option>
        @foreach($locations as $l)
          <option value="{{ $l->id }}" @selected((string)$locationId===(string)$l->id)>{{ $l->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="filter-field filter-search">
      <label class="filter-label">Search</label>
      <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Search by item name, description...">
    </div>
  </div>

  <div class="filter-actions">
    @if(request()->hasAny(['type', 'status', 'category_id', 'location_id', 'q']))
      <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="bi bi-x-lg"></i> Clear
      </a>
    @endif
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-search"></i> Apply Filters
    </button>
  </div>
</form>

{{-- RESULTS COUNT --}}
@if(!$reports->isEmpty())
<div class="results-count">
  <div class="results-count-text">
    Showing <span class="results-count-number">{{ $reports->count() }}</span> of 
    <span class="results-count-number">{{ $reports->total() }}</span> reports
  </div>
  <div class="view-toggle">
    <button class="view-toggle-btn active" type="button">
      <i class="bi bi-grid-3x3-gap-fill"></i>
    </button>
    <button class="view-toggle-btn" type="button">
      <i class="bi bi-list-ul"></i>
    </button>
  </div>
</div>
@endif

{{-- CARDS GRID --}}
@if($reports->isEmpty())
  <div class="empty-state">
    <div class="empty-state-icon">
      <i class="bi bi-inbox"></i>
    </div>
    <h3 class="empty-state-title">No reports found</h3>
    <p class="empty-state-text">
      @if(request()->hasAny(['type', 'status', 'category_id', 'location_id', 'q']))
        Try adjusting your filters to find what you're looking for
      @else
        Get started by creating your first report
      @endif
    </p>
    @if(!request()->hasAny(['type', 'status', 'category_id', 'location_id', 'q']))
      <a href="{{ route('reports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create First Report
      </a>
    @else
      <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="bi bi-x-lg"></i> Clear Filters
      </a>
    @endif
  </div>
@else
  <div class="reports-grid">
    @foreach($reports as $r)
      <div class="report-card">
        {{-- Image --}}
        <div class="report-image">
          @php
            $firstPhoto = $r->photos->first();
          @endphp
          
          @if($firstPhoto && $firstPhoto->photo_url)
            <img src="{{ asset($firstPhoto->photo_url) }}" alt="{{ $r->item_name }}">
          @else
            <div class="report-image-placeholder">
              <i class="bi bi-image"></i>
            </div>
          @endif
          
          {{-- Type Badge --}}
          <div class="report-type-badge {{ $r->report_type === 'lost' ? 'report-type-lost' : 'report-type-found' }}">
            <i class="bi bi-{{ $r->report_type === 'lost' ? 'exclamation-circle-fill' : 'check-circle-fill' }}"></i>
            {{ ucfirst($r->report_type) }}
          </div>
        </div>

        {{-- Body --}}
        <div class="report-card-body">
          <h3 class="report-card-title">{{ $r->item_name ?? 'Untitled Item' }}</h3>
          
          <p class="report-card-description">
            {{ $r->item_description ?? 'No description provided' }}
          </p>

          {{-- Meta Information --}}
          <div class="report-card-meta">
            <div class="report-meta-item">
              <div class="report-meta-icon">
                <i class="bi bi-tag-fill"></i>
              </div>
              <div class="report-meta-content">
                <div class="report-meta-label">Category</div>
                <div class="report-meta-value">{{ $r->category?->name ?? 'Uncategorized' }}</div>
              </div>
            </div>

            <div class="report-meta-item">
              <div class="report-meta-icon">
                <i class="bi bi-geo-alt-fill"></i>
              </div>
              <div class="report-meta-content">
                <div class="report-meta-label">Location</div>
                <div class="report-meta-value">{{ $r->location?->name ?? 'Unknown' }}</div>
              </div>
            </div>

            <div class="report-meta-item">
              <div class="report-meta-icon">
                <i class="bi bi-calendar-fill"></i>
              </div>
              <div class="report-meta-content">
                <div class="report-meta-label">Date</div>
                <div class="report-meta-value">
                  {{ $r->incident_date ? \Carbon\Carbon::parse($r->incident_date)->format('M d, Y') : 'Not specified' }}
                </div>
              </div>
            </div>

            <div class="report-meta-item">
              <div class="report-meta-icon">
                <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
              </div>
              <div class="report-meta-content">
                <div class="report-meta-label">Status</div>
                <div class="report-meta-value">
                  <span class="report-status-badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="report-card-actions">
            <a href="{{ route('reports.show', $r->id) }}" class="report-action-btn report-action-btn-primary">
              <i class="bi bi-eye-fill"></i>
              <span>View</span>
            </a>
            <a href="{{ route('reports.edit', $r->id) }}" class="report-action-btn">
              <i class="bi bi-pencil-fill"></i>
              <span>Edit</span>
            </a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  <div class="mt-3">
    {{ $reports->links() }}
  </div>
@endif

@endsection
