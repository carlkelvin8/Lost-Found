@extends('layouts.app')

@section('title', 'Claims')

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);

  $total = $claims->total();
  $pendingCount = $claims->filter(fn($c) => $c->status === 'pending')->count();
  $approvedCount = $claims->filter(fn($c) => $c->status === 'approved')->count();
  $rejectedCount = $claims->filter(fn($c) => $c->status === 'rejected')->count();
@endphp

<!-- Header -->
<div class="cl-header">
  <div class="cl-welcome">
    <div class="cl-icon-wrap">
      <i class="bi bi-person-check"></i>
    </div>
    <div class="cl-welcome-text">
      <h1 class="cl-title">{{ $isStaff ? 'All Claims' : 'My Claims' }}</h1>
      <p class="cl-subtitle">Review and manage claim requests</p>
    </div>
  </div>
  <a class="cl-btn-back" href="{{ route('dashboard') }}">
    <i class="bi bi-arrow-left"></i>
    <span>Back</span>
  </a>
</div>

<!-- Stats -->
<div class="cl-stats">
  <div class="cl-stat">
    <div class="cl-stat-icon cl-stat-blue">
      <i class="bi bi-files"></i>
    </div>
    <div class="cl-stat-info">
      <span class="cl-stat-label">Total</span>
      <span class="cl-stat-value">{{ $total }}</span>
    </div>
  </div>
  <div class="cl-stat">
    <div class="cl-stat-icon cl-stat-amber">
      <i class="bi bi-hourglass-split"></i>
    </div>
    <div class="cl-stat-info">
      <span class="cl-stat-label">Pending</span>
      <span class="cl-stat-value">{{ $claims->filter(fn($c) => $c->status === 'pending')->count() }}</span>
    </div>
  </div>
  <div class="cl-stat">
    <div class="cl-stat-icon cl-stat-green">
      <i class="bi bi-check-circle"></i>
    </div>
    <div class="cl-stat-info">
      <span class="cl-stat-label">Approved</span>
      <span class="cl-stat-value">{{ $claims->filter(fn($c) => $c->status === 'approved')->count() }}</span>
    </div>
  </div>
  <div class="cl-stat">
    <div class="cl-stat-icon cl-stat-red">
      <i class="bi bi-x-circle"></i>
    </div>
    <div class="cl-stat-info">
      <span class="cl-stat-label">Rejected</span>
      <span class="cl-stat-value">{{ $claims->filter(fn($c) => $c->status === 'rejected')->count() }}</span>
    </div>
  </div>
</div>

<!-- Filter -->
<form method="GET" action="{{ route('claims.index') }}" class="cl-filter">
  <div class="cl-filter-inner">
    <div class="cl-filter-group">
      <label class="cl-filter-label"><i class="bi bi-funnel"></i> Status</label>
      <select name="status" class="cl-filter-select">
        <option value="">All Claims</option>
        @foreach(['pending','approved','rejected','cancelled'] as $s)
          <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="cl-filter-btn">
      <i class="bi bi-arrow-right"></i> Apply
    </button>
  </div>
</form>

<!-- Claims List -->
<div class="cl-section">
  <div class="cl-list">
    @forelse($claims as $i => $c)
      <a href="{{ route('claims.show', $c->id) }}" class="cl-card" style="animation-delay: {{ $i * 0.04 }}s">
        <div class="cl-card-left">
          @php
            $statusConfig = match($c->status) {
              'approved' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#059669', 'icon' => 'bi-check-circle-fill'],
              'rejected' => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#dc2626', 'icon' => 'bi-x-circle-fill'],
              'cancelled' => ['bg' => 'rgba(148,163,184,0.1)', 'color' => '#64748b', 'icon' => 'bi-slash-circle'],
              default => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#d97706', 'icon' => 'bi-hourglass-split'],
            };
          @endphp
          <div class="cl-card-icon" style="background: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['color'] }}">
            <i class="bi {{ $statusConfig['icon'] }}"></i>
          </div>
          <div class="cl-card-info">
            <div class="cl-card-header">
              <span class="cl-card-title">Report #{{ $c->report_id }}</span>
              <span class="cl-badge" style="background: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['color'] }}">
                {{ ucfirst($c->status) }}
              </span>
            </div>
            <div class="cl-card-meta">
              @if($c->claimant)
                <span><i class="bi bi-person"></i> {{ $c->claimant->profile->full_name ?? $c->claimant->email ?? 'Unknown' }}</span>
              @endif
              <span><i class="bi bi-calendar3"></i> {{ $c->created_at->format('M d, Y') }}</span>
              @if($c->reviewed_at)
                <span><i class="bi bi-clock-history"></i> Reviewed {{ $c->reviewed_at->diffForHumans() }}</span>
              @endif
            </div>
          </div>
        </div>
        <i class="bi bi-chevron-right cl-card-arrow"></i>
      </a>
    @empty
      <div class="cl-empty">
        <div class="cl-empty-icon">
          <i class="bi bi-person-check"></i>
        </div>
        <p class="cl-empty-title">No claims found</p>
        <p class="cl-empty-desc">{{ $isStaff ? 'No claims match your current filter.' : 'You haven\'t submitted any claims yet.' }}</p>
      </div>
    @endforelse
  </div>
</div>

<!-- Pagination -->
@if($claims->hasPages())
  <div class="cl-pagination">
    {{ $claims->links() }}
  </div>
@endif
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.cl-header, .cl-stats, .cl-stat, .cl-stat-icon, .cl-stat-info,
.cl-filter, .cl-section, .cl-list, .cl-card, .cl-card-left,
.cl-card-icon, .cl-card-info, .cl-card-header, .cl-card-title,
.cl-card-meta, .cl-empty, .cl-empty-icon, .cl-empty-title,
.cl-empty-desc, .cl-pagination {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.cl-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  animation: clFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes clFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.cl-welcome {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.cl-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.375rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(16,185,129,0.25);
}

.cl-welcome-text { flex: 1; }

.cl-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.02em;
  margin: 0 0 0.25rem;
  line-height: 1.2;
}

.cl-subtitle {
  font-size: 0.9375rem;
  color: #94a3b8;
  margin: 0;
}

.cl-btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.125rem;
  background: white;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.cl-btn-back:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
}

/* ── STATS ── */
.cl-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.cl-stat {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.125rem;
  display: flex;
  align-items: center;
  gap: 0.875rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  animation: clFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.cl-stat:nth-child(1) { animation-delay: 0.05s; }
.cl-stat:nth-child(2) { animation-delay: 0.1s; }
.cl-stat:nth-child(3) { animation-delay: 0.15s; }
.cl-stat:nth-child(4) { animation-delay: 0.2s; }

.cl-stat:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
  transform: translateY(-2px);
}

.cl-stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.cl-stat-blue { background: rgba(0,65,199,0.08); color: #0041C7; }
.cl-stat-amber { background: rgba(245,158,11,0.08); color: #d97706; }
.cl-stat-green { background: rgba(16,185,129,0.08); color: #059669; }
.cl-stat-red { background: rgba(239,68,68,0.08); color: #dc2626; }

.cl-stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.cl-stat-label {
  font-size: 0.8125rem;
  color: #94a3b8;
  font-weight: 500;
}

.cl-stat-value {
  font-size: 1.375rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1;
}

/* ── FILTER ── */
.cl-filter {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.125rem 1.25rem;
  margin-bottom: 1.5rem;
  animation: clFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
}

.cl-filter-inner {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.cl-filter-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.cl-filter-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  white-space: nowrap;
}

.cl-filter-label i { color: #94a3b8; }

.cl-filter-select {
  flex: 1;
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #334155;
  background: #f8fafc;
  transition: all 0.2s;
  font-family: inherit;
  appearance: auto;
}

.cl-filter-select:focus {
  outline: none;
  border-color: #0041C7;
  box-shadow: 0 0 0 3px rgba(0,65,199,0.1);
}

.cl-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #0041C7 0%, #0033A0 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
  white-space: nowrap;
}

.cl-filter-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,65,199,0.25);
}

/* ── CLAIMS LIST ── */
.cl-list {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.cl-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  animation: clFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.cl-card:hover {
  border-color: #0041C7;
  box-shadow: 0 4px 16px rgba(0,65,199,0.08);
  transform: translateY(-1px);
}

.cl-card:hover .cl-card-arrow {
  color: #0041C7;
  transform: translateX(3px);
}

.cl-card-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.cl-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.cl-card-info {
  flex: 1;
  min-width: 0;
}

.cl-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.375rem;
}

.cl-card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #0f172a;
}

.cl-badge {
  display: inline-flex;
  padding: 0.2rem 0.625rem;
  border-radius: 6px;
  font-size: 0.6875rem;
  font-weight: 600;
  flex-shrink: 0;
  text-transform: capitalize;
}

.cl-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.8125rem;
  color: #94a3b8;
}

.cl-card-meta i { margin-right: 0.25rem; }

.cl-card-arrow {
  color: #cbd5e1;
  font-size: 0.875rem;
  transition: all 0.2s;
  flex-shrink: 0;
}

/* ── EMPTY STATE ── */
.cl-empty {
  text-align: center;
  padding: 3.5rem 1rem;
}

.cl-empty-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
}

.cl-empty-icon i {
  font-size: 1.75rem;
  color: #cbd5e1;
}

.cl-empty-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.375rem;
}

.cl-empty-desc {
  font-size: 0.875rem;
  color: #94a3b8;
  margin: 0;
}

/* ── PAGINATION ── */
.cl-pagination {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .cl-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .cl-welcome { width: 100%; }

  .cl-btn-back { width: 100%; justify-content: center; }

  .cl-stats { grid-template-columns: 1fr 1fr; }

  .cl-filter-inner { flex-direction: column; align-items: stretch; }

  .cl-filter-group { flex-direction: column; align-items: stretch; }

  .cl-card-meta { gap: 0.5rem; }
}

@media (max-width: 480px) {
  .cl-stats { grid-template-columns: 1fr; }

  .cl-card-header { flex-direction: column; align-items: flex-start; gap: 0.375rem; }
}
</style>
@endpush
