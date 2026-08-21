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
<link href="{{ asset('css/claims.css') }}" rel="stylesheet" />
@endpush
