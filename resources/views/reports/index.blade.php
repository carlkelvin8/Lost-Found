@extends('layouts.app')

@section('title', 'Reports · NAAP Lost & Found')

@push('styles')
<style>
  /* ============================================
     SENIOR-LEVEL MODERN REPORTS PAGE
     ============================================ */

  /* Compact Page Header */
  .reports-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
    flex-wrap: wrap;
    position: relative;
  }

  .reports-header::before {
    content: '';
    position: absolute;
    bottom: -0.5rem;
    left: 0;
    width: 60px;
    height: 3px;
    background: #0041C7;
    border-radius: var(--radius-full);
  }

  .reports-header-content h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
    line-height: 1.1;
    letter-spacing: -0.03em;
  }

  .reports-header-subtitle {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: 400;
  }

  /* Quick Stats Bar */
  .quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
    margin-bottom: var(--space-xl);
  }

  .stat-item {
    background: white;
    border: 1px solid var(--border-default);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all var(--transition-fast);
  }

  .stat-item:hover {
    border-color: #0041C7;
    box-shadow: 0 4px 12px rgba(0, 65, 199, 0.08);
  }

  .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
  }

  .stat-icon-lost {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  .stat-icon-found {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  .stat-icon-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
  }

  .stat-icon-matched {
    background: rgba(13, 133, 216, 0.1);
    color: #0D85D8;
  }

  .stat-icon-claimed {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
  }

  .stat-content {
    flex: 1;
    min-width: 0;
  }

  .stat-label {
    font-size: 0.6875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 0.125rem;
  }

  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
  }

  /* Responsive Refinements */
  @media (max-width: 1200px) {
    .filter-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .reports-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .reports-header-content h1 {
      font-size: 1.75rem;
    }

    .reports-grid {
      grid-template-columns: 1fr;
    }

    .filter-grid {
      grid-template-columns: 1fr;
    }

    .filter-actions {
      width: 100%;
      flex-direction: column;
    }

    .filter-actions .btn {
      width: 100%;
    }

    .quick-stats {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 480px) {
    .report-card-actions {
      flex-direction: column;
    }

    .report-action-btn {
      width: 100%;
    }

    .quick-stats {
      grid-template-columns: 1fr;
    }
  }


  /* Compact Filter Section */
  .reports-filters {
    background: white;
    border: 1px solid var(--border-default);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: var(--space-xl);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all var(--transition-base);
  }

  .reports-filters:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  }

  .filter-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .filter-field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
  }

  .filter-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .filter-search {
    grid-column: 1 / -1;
  }

  .filter-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-subtle);
  }

  /* Compact Form Controls */
  .reports-filters .form-select,
  .reports-filters .form-control {
    height: 38px;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all var(--transition-fast);
    background: white;
    padding: 0.5rem 0.75rem;
  }

  .reports-filters .form-select:hover,
  .reports-filters .form-control:hover {
    border-color: var(--border-strong);
  }

  .reports-filters .form-select:focus,
  .reports-filters .form-control:focus {
    border-color: #0041C7;
    box-shadow: 0 0 0 3px rgba(0, 65, 199, 0.08);
  }

  /* Optimized Reports Grid */
  .reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.25rem;
    margin-bottom: var(--space-2xl);
  }

  /* Compact Report Card */
  .report-card {
    background: white;
    border: 1px solid var(--border-default);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
  }

  .report-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #0041C7;
    opacity: 0;
    transition: opacity var(--transition-base);
  }

  .report-card:hover {
    box-shadow: 0 8px 24px rgba(0, 65, 199, 0.12);
    transform: translateY(-4px);
    border-color: rgba(0, 65, 199, 0.3);
  }

  .report-card:hover::before {
    opacity: 1;
  }

  /* Compact Image Container */
  .report-image {
    width: 100%;
    height: 200px;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
  }

  .report-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.02) 100%);
    pointer-events: none;
  }

  .report-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .report-card:hover .report-image img {
    transform: scale(1.08);
  }

  .report-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 3.5rem;
    opacity: 0.2;
  }

  /* Compact Type Badge */
  .report-type-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    backdrop-filter: blur(12px);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 2;
  }

  .report-type-lost {
    background: rgba(245, 158, 11, 0.95);
    color: white;
  }

  .report-type-found {
    background: rgba(16, 185, 129, 0.95);
    color: white;
  }

  /* Compact Card Body */
  .report-card-body {
    padding: 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .report-card-title {
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
    letter-spacing: -0.01em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
  }

  .report-card-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.5;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
  }

  /* Compact Meta Grid */
  .report-card-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.625rem;
    padding: 0.875rem 0;
    border-top: 1px solid var(--border-subtle);
    border-bottom: 1px solid var(--border-subtle);
  }

  .report-meta-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-size: 0.8125rem;
    color: var(--text-secondary);
  }

  .report-meta-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(0, 65, 199, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0041C7;
    font-size: 0.875rem;
    flex-shrink: 0;
    transition: all var(--transition-fast);
  }

  .report-card:hover .report-meta-icon {
    background: rgba(0, 65, 199, 0.12);
    transform: scale(1.05);
  }

  .report-meta-content {
    flex: 1;
    min-width: 0;
  }

  .report-meta-label {
    font-size: 0.625rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 0.0625rem;
  }

  .report-meta-value {
    font-size: 0.8125rem;
    color: var(--text-primary);
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Compact Status Badge */
  .report-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: capitalize;
    letter-spacing: 0.02em;
  }

  .status-pending {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.08) 100%);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.2);
  }

  .status-matched {
    background: linear-gradient(135deg, rgba(13, 133, 216, 0.12) 0%, rgba(13, 133, 216, 0.08) 100%);
    color: #0D85D8;
    border: 1px solid rgba(13, 133, 216, 0.2);
  }

  .status-claimed {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.08) 100%);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
  }

  .status-returned {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.12) 0%, rgba(34, 197, 94, 0.08) 100%);
    color: #16a34a;
    border: 1px solid rgba(34, 197, 94, 0.2);
  }

  .status-archived {
    background: linear-gradient(135deg, rgba(115, 115, 115, 0.12) 0%, rgba(115, 115, 115, 0.08) 100%);
    color: #525252;
    border: 1px solid rgba(115, 115, 115, 0.2);
  }

  /* Compact Action Buttons */
  .report-card-actions {
    display: flex;
    gap: 0.5rem;
  }

  .report-action-btn {
    flex: 1;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0 1rem;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    background: white;
    text-decoration: none;
    transition: all var(--transition-fast);
    position: relative;
    overflow: hidden;
  }

  .report-action-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--bg-hover);
    opacity: 0;
    transition: opacity var(--transition-fast);
  }

  .report-action-btn:hover::before {
    opacity: 1;
  }

  .report-action-btn:hover {
    color: var(--text-primary);
    border-color: var(--border-strong);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
  }

  .report-action-btn span,
  .report-action-btn i {
    position: relative;
    z-index: 1;
  }

  .report-action-btn-primary {
    background: #0041C7;
    color: white;
    border-color: transparent;
    font-weight: 600;
  }

  .report-action-btn-primary::before {
    background: linear-gradient(135deg, #1CA3DE 0%, #0160C9 100%);
  }

  .report-action-btn-primary:hover {
    color: white;
    box-shadow: 0 8px 20px rgba(0, 65, 199, 0.3);
    transform: translateY(-2px);
  }

  .report-action-btn i {
    font-size: 0.9375rem;
  }

  /* Premium Empty State */
  .empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border: 2px dashed var(--border-default);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
  }

  .empty-state::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(58, 203, 235, 0.03) 0%, transparent 70%);
    animation: emptyStateRotate 20s linear infinite;
  }

  @keyframes emptyStateRotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  .empty-state-icon {
    font-size: 5rem;
    color: var(--text-muted);
    opacity: 0.2;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
  }

  .empty-state-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    position: relative;
    z-index: 1;
  }

  .empty-state-text {
    color: var(--text-muted);
    font-size: 1.0625rem;
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    z-index: 1;
  }

  /* Compact Results Count */
  .results-count {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding: 0.75rem 0;
  }

  .results-count-text {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }

  .results-count-number {
    font-weight: 700;
    color: #0041C7;
  }

  .view-toggle {
    display: flex;
    gap: 0.375rem;
    background: white;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    padding: 0.25rem;
  }

  .view-toggle-btn {
    padding: 0.375rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    border-radius: 6px;
    cursor: pointer;
    transition: all var(--transition-fast);
    font-size: 0.875rem;
  }

  .view-toggle-btn.active {
    background: #0041C7;
    color: white;
  }

  .view-toggle-btn:hover:not(.active) {
    background: var(--bg-hover);
    color: var(--text-primary);
  }

  /* Responsive Refinements */
  @media (max-width: 1200px) {
    .filter-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .reports-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .reports-header-content h1 {
      font-size: 2rem;
    }

    .reports-grid {
      grid-template-columns: 1fr;
    }

    .filter-grid {
      grid-template-columns: 1fr;
    }

    .filter-actions {
      width: 100%;
      flex-direction: column;
    }

    .filter-actions .btn {
      width: 100%;
    }

    .report-card-meta {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 480px) {
    .report-card-actions {
      flex-direction: column;
    }

    .report-action-btn {
      width: 100%;
    }
  }
</style>
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
