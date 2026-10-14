@extends('layouts.app')

@section('title', 'Successful Claimants')

@push('styles')
<link href="{{ asset('css/claims.css') }}" rel="stylesheet" />
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
<style>
  .sc-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.85rem;margin-bottom:1rem}
  .sc-stat{background:white;border:1px solid var(--border-default);border-radius:12px;padding:1rem 1.1rem;display:flex;align-items:center;gap:.85rem;box-shadow:0 1px 3px rgba(0,0,0,.04)}
  .sc-stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
  .sc-stat-info{display:flex;flex-direction:column}
  .sc-stat-label{font-size:.68rem;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);font-weight:700}
  .sc-stat-value{font-size:1.35rem;font-weight:800;color:var(--text-primary);line-height:1}
  .sc-table-card{border-radius:14px;overflow:hidden}
  .sc-filter-card{border-radius:14px}
  .sc-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border-default)}
  .sc-initial{width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#e0e7ff;color:#2563eb;font-weight:800;font-size:.75rem;flex-shrink:0}
  @media(max-width:768px){ .sc-stats{grid-template-columns:1fr 1fr} }
</style>
@endpush

@section('content')
@php
  $totalSuccessful = $successful->total();
  $todayCount = $successful->getCollection()->filter(fn($c)=> $c->reviewed_at && $c->reviewed_at->isToday())->count();
  // For display only, not global count
  $isStaff = $isStaff ?? false;
@endphp

<!-- Header -->
<div class="cl-header" style="margin-bottom:1rem">
  <div class="cl-welcome">
    <div class="cl-icon-wrap" style="background:rgba(16,185,129,.12);color:#059669">
      <i class="bi bi-trophy-fill"></i>
    </div>
    <div class="cl-welcome-text">
      <h1 class="cl-title">Successful Claimants</h1>
      <p class="cl-subtitle">
        @if($isStaff)
          Approved claims ready for release — showing completed recoveries only
        @else
          Hall of successful recoveries — mga naibalik na gamit sa mga tunay na may-ari
        @endif
      </p>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="{{ route('claims.index') }}"><i class="bi bi-arrow-left"></i> {{ $isStaff ? 'All Claims' : 'My Claims' }}</a>
    <a class="btn btn-primary" href="{{ route('reports.index') }}"><i class="bi bi-box-seam"></i> Reports</a>
  </div>
</div>

<!-- Stats -->
<div class="sc-stats">
  <div class="sc-stat">
    <div class="sc-stat-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="bi bi-patch-check-fill"></i></div>
    <div class="sc-stat-info"><span class="sc-stat-label">Total Successful</span><span class="sc-stat-value">{{ $totalSuccessful }}</span></div>
  </div>
  <div class="sc-stat">
    <div class="sc-stat-icon" style="background:rgba(59,130,246,.1);color:#2563eb"><i class="bi bi-calendar-check"></i></div>
    <div class="sc-stat-info"><span class="sc-stat-label">Showing</span><span class="sc-stat-value">{{ $successful->count() }}</span></div>
  </div>
  <div class="sc-stat">
    <div class="sc-stat-icon" style="background:rgba(245,158,11,.12);color:#d97706"><i class="bi bi-people"></i></div>
    <div class="sc-stat-info"><span class="sc-stat-label">Unique Claimants</span><span class="sc-stat-value">{{ $successful->getCollection()->pluck('claimant_user_id')->unique()->count() }}</span></div>
  </div>
  <div class="sc-stat">
    <div class="sc-stat-icon" style="background:rgba(139,92,246,.12);color:#7c3aed"><i class="bi bi-shield-check"></i></div>
    <div class="sc-stat-info"><span class="sc-stat-label">Verified By Staff</span><span class="sc-stat-value">{{ $successful->getCollection()->whereNotNull('reviewed_by_user_id')->count() }}</span></div>
  </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('claims.successful') }}" class="admin-filter-card sc-filter-card">
  <div class="row g-3">
    <div class="{{ $isStaff ? 'col-12 col-md-4' : 'col-12 col-md-6' }}">
      <label class="form-label"><i class="bi bi-search"></i> Search</label>
      <input class="form-control" name="q" value="{{ $q }}" placeholder="Name, email, item, #ID" />
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label"><i class="bi bi-tag"></i> Category</label>
      <select class="form-select" name="category_id">
        <option value="">All Categories</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected((string)$categoryId===(string)$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    @if($isStaff)
    <div class="col-6 col-md-2">
      <label class="form-label"><i class="bi bi-person-badge"></i> Approved By</label>
      <select class="form-select" name="approved_by">
        <option value="">Any Staff</option>
        @foreach($approvers as $ap)
          <option value="{{ $ap->id }}" @selected((string)$approvedBy===(string)$ap->id)>{{ $ap->profile?->full_name ?? $ap->email }}</option>
        @endforeach
      </select>
    </div>
    @endif
    <div class="col-6 {{ $isStaff ? 'col-md-2' : 'col-md-2' }}">
      <label class="form-label"><i class="bi bi-calendar"></i> From</label>
      <input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}" />
    </div>
    <div class="col-6 {{ $isStaff ? 'col-md-2' : 'col-md-2' }}">
      <label class="form-label"><i class="bi bi-calendar-event"></i> To</label>
      <input class="form-control" type="date" name="date_to" value="{{ $dateTo }}" />
    </div>
  </div>
  <div class="d-flex gap-2 mt-3 justify-content-end">
    <a href="{{ route('claims.successful') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i> Clear</a>
    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Apply Filters</button>
  </div>
</form>

<!-- Table -->
<div class="admin-table-card sc-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Claimant</th>
          @if($isStaff)<th>Contact</th>@endif
          <th>Item & Reference</th>
          <th>Category</th>
          <th>Claimed On</th>
          <th>Completed</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($successful as $claim)
          @php
            $claimantName = $claim->claimant?->profile?->full_name ?? $claim->claimant?->email ?? 'Unknown';
            $itemName = $claim->report?->item_name ?? '—';
            $categoryName = $claim->report?->category?->name ?? '—';
          @endphp
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                @if($claim->claimant?->profile?->avatar_url)
                  @php $av = uploaded_image_url($claim->claimant->profile->avatar_url); @endphp
                  <img src="{{ $av }}" class="sc-avatar" alt="">
                @else
                  <span class="sc-initial">{{ strtoupper(substr($claimantName,0,1)) }}</span>
                @endif
                <div>
                  <div style="font-weight:700;font-size:.875rem;line-height:1">{{ $claimantName }}</div>
                  <div class="text-muted" style="font-size:.68rem">User #{{ $claim->claimant_user_id }}</div>
                </div>
              </div>
            </td>
            @if($isStaff)
            <td>
              <div style="font-size:.8125rem">{{ $claim->claimant?->email ?? '—' }}</div>
              @if($claim->claimant?->profile?->contact_no)
                <div class="text-muted" style="font-size:.7rem"><i class="bi bi-telephone"></i> {{ $claim->claimant->profile->contact_no }}</div>
              @endif
            </td>
            @endif
            <td>
              <div style="font-weight:700;font-size:.875rem">{{ \Illuminate\Support\Str::limit($itemName,26) }}</div>
              <div class="text-muted" style="font-size:.68rem">Claim #{{ $claim->id }} · <a href="{{ route('reports.show',$claim->report_id) }}" class="text-decoration-none">Report #{{ $claim->report_id }}</a> · {{ $claim->report?->location?->name ?? '—' }}</div>
            </td>
            <td><span class="admin-badge admin-badge-secondary">{{ $categoryName }}</span></td>
            <td style="white-space:nowrap">
              <div style="font-size:.8125rem;font-weight:600">{{ $claim->created_at?->format('M d, Y') }}</div>
              <div class="text-muted" style="font-size:.68rem">{{ $claim->created_at?->format('h:i A') }}</div>
            </td>
            <td style="white-space:nowrap">
              <div style="font-size:.8125rem;font-weight:600;color:#059669">{{ $claim->reviewed_at?->format('M d, Y') ?? '—' }}</div>
              <div class="text-muted" style="font-size:.68rem">{{ $claim->reviewed_at?->format('h:i A') ?? '' }}</div>
            </td>
            <td>
              <span class="admin-badge admin-badge-success"><i class="bi bi-check-circle-fill"></i> Approved</span>
              @if($claim->reviewer)
                <div class="text-muted" style="font-size:.62rem;margin-top:2px"><i class="bi bi-person-check"></i> {{ $claim->reviewer?->profile?->full_name ?? $claim->reviewer->email }}</div>
              @endif
            </td>
            <td>
              <div class="admin-btn-group">
                <a class="admin-action-btn" href="{{ route('claims.show', $claim->id) }}" title="View Details"><i class="bi bi-eye"></i></a>
                <a class="admin-action-btn" href="{{ route('reports.show', $claim->report_id) }}" title="View Item"><i class="bi bi-box-seam"></i></a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $isStaff ? 8 : 7 }}">
              <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;color:#94a3b8"><i class="bi bi-trophy" style="font-size:1.4rem"></i></div>
                <div style="font-weight:700;color:var(--text-primary)">No successful claimants yet</div>
                <div class="text-muted" style="font-size:.8125rem">Only approved claims appear here — pending, rejected and cancelled are excluded.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($successful->hasPages())
  <div class="admin-pagination mt-3">{{ $successful->links() }}</div>
@endif
@endsection
