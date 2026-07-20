@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $initial = strtoupper(substr($user->email,0,1));
@endphp

<!-- Welcome Header -->
<div class="dashboard-header">
  <div class="dashboard-welcome">
    <div class="dashboard-avatar">
      @if($user->profile && $user->profile->avatar_url)
        <img src="{{ asset($user->profile->avatar_url) }}" alt="{{ $user->profile->full_name ?? 'User' }}">
      @else
        <span class="dashboard-avatar-initial">{{ $initial }}</span>
      @endif
    </div>
    <div class="dashboard-welcome-text">
      <h1 class="dashboard-title">Welcome back, {{ $user->profile?->full_name ?? 'User' }}</h1>
      <p class="dashboard-subtitle">Here's what's happening with your lost and found items</p>
    </div>
  </div>
  <div class="dashboard-actions">
    <a class="btn btn-primary" href="{{ route('reports.create') }}">
      <i class="bi bi-plus-lg"></i> New Report
    </a>
  </div>
</div>

<!-- Stats Cards -->
<div class="dashboard-stats">
  <div class="stat-card">
    <div class="stat-icon stat-icon-primary">
      <i class="bi bi-file-earmark-text-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">My Reports</div>
      <div class="stat-value">{{ $stats['my_reports'] }}</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon stat-icon-success">
      <i class="bi bi-person-check-fill"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">My Claims</div>
      <div class="stat-value">{{ $stats['my_claims'] }}</div>
    </div>
  </div>

  @if($isStaff)
    <div class="stat-card">
      <div class="stat-icon stat-icon-warning">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div class="stat-content">
        <div class="stat-label">Pending Reports</div>
        <div class="stat-value">{{ $stats['pending_reports'] }}</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon stat-icon-info">
        <i class="bi bi-diagram-2-fill"></i>
      </div>
      <div class="stat-content">
        <div class="stat-label">Suggested Matches</div>
        <div class="stat-value">{{ $stats['suggested_matches'] }}</div>
      </div>
    </div>
  @endif
</div>

<!-- Quick Actions -->
<div class="dashboard-section">
  <h2 class="section-heading">Quick Actions</h2>
  <div class="quick-actions-grid">
    <a href="{{ route('reports.create') }}" class="quick-action-card">
      <div class="quick-action-icon">
        <i class="bi bi-plus-circle-fill"></i>
      </div>
      <div class="quick-action-title">New Report</div>
      <div class="quick-action-desc">Submit lost/found item</div>
    </a>

    <a href="{{ route('reports.index') }}" class="quick-action-card">
      <div class="quick-action-icon">
        <i class="bi bi-inbox-fill"></i>
      </div>
      <div class="quick-action-title">View Reports</div>
      <div class="quick-action-desc">Browse all reports</div>
    </a>

    <a href="{{ route('claims.index') }}" class="quick-action-card">
      <div class="quick-action-icon">
        <i class="bi bi-person-check-fill"></i>
      </div>
      <div class="quick-action-title">My Claims</div>
      <div class="quick-action-desc">Track your claims</div>
    </a>

    <a href="{{ route('notifications.index') }}" class="quick-action-card">
      <div class="quick-action-icon">
        <i class="bi bi-bell-fill"></i>
      </div>
      <div class="quick-action-title">Notifications</div>
      <div class="quick-action-desc">View updates</div>
    </a>
  </div>
</div>

<!-- Recent Activity Section -->
<div class="dashboard-grid">
  <div class="dashboard-card">
    <div class="dashboard-card-header">
      <h3 class="dashboard-card-title">
        <i class="bi bi-clock-history"></i> My Recent Reports
      </h3>
    </div>
    <div class="dashboard-card-body">
      @if($recentReports->isEmpty())
        <div class="empty-state-simple">
          <i class="bi bi-inbox"></i>
          <p>No reports yet</p>
        </div>
      @else
        <div class="activity-list">
          @foreach($recentReports as $report)
            <a href="{{ route('reports.show', $report->id) }}" class="activity-list-item">
              <div class="activity-list-content">
                <div class="activity-list-badges">
                  <span class="badge badge-{{ $report->report_type === 'lost' ? 'warning' : 'success' }}">
                    {{ ucfirst($report->report_type) }}
                  </span>
                  <span class="badge badge-outline">{{ $report->status }}</span>
                </div>
                <div class="activity-list-title">{{ Str::limit($report->item_name, 40) }}</div>
                <div class="activity-list-meta">
                  <span><i class="bi bi-tag"></i> {{ $report->category->name ?? 'N/A' }}</span>
                  <span><i class="bi bi-geo-alt"></i> {{ $report->location->name ?? 'N/A' }}</span>
                </div>
              </div>
              <div class="activity-list-time">
                {{ $report->created_at->diffForHumans() }}
              </div>
            </a>
          @endforeach
        </div>
        <div class="dashboard-card-footer">
          <a href="{{ route('reports.index') }}" class="btn-link">
            View All Reports <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      @endif
    </div>
  </div>

  @if($isStaff)
    <div class="dashboard-card">
      <div class="dashboard-card-header">
        <h3 class="dashboard-card-title">
          <i class="bi bi-activity"></i> Recent Activity
        </h3>
      </div>
      <div class="dashboard-card-body">
        @if($recentActivity->isEmpty())
          <div class="empty-state-simple">
            <i class="bi bi-activity"></i>
            <p>No recent activity</p>
          </div>
        @else
          <div class="activity-list">
            @foreach($recentActivity as $activity)
              <a href="{{ route('reports.show', $activity->id) }}" class="activity-list-item">
                <div class="activity-list-content">
                  <div class="activity-list-badges">
                    <span class="badge badge-{{ $activity->report_type === 'lost' ? 'warning' : 'success' }}">
                      {{ ucfirst($activity->report_type) }}
                    </span>
                    <span class="badge badge-outline">{{ $activity->status }}</span>
                  </div>
                  <div class="activity-list-title">{{ Str::limit($activity->item_name, 35) }}</div>
                  <div class="activity-list-meta">
                    <span><i class="bi bi-person"></i> {{ $activity->reporter->email ?? 'Unknown' }}</span>
                  </div>
                </div>
                <div class="activity-list-time">
                  {{ $activity->created_at->diffForHumans() }}
                </div>
              </a>
            @endforeach
          </div>
          <div class="dashboard-card-footer">
            <a href="{{ route('reports.index') }}" class="btn-link">
              View All Activity <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        @endif
      </div>
    </div>
  @else
    <div class="dashboard-card">
      <div class="dashboard-card-header">
        <h3 class="dashboard-card-title">
          <i class="bi bi-lightbulb"></i> Getting Started
        </h3>
      </div>
      <div class="dashboard-card-body">
        <div class="guide-steps">
          <div class="guide-step">
            <div class="guide-step-number">1</div>
            <div class="guide-step-content">
              <div class="guide-step-title">Report Lost or Found Items</div>
              <div class="guide-step-desc">Submit a report when you lose or find an item on campus</div>
            </div>
          </div>
          <div class="guide-step">
            <div class="guide-step-number">2</div>
            <div class="guide-step-content">
              <div class="guide-step-title">AI-Powered Matching</div>
              <div class="guide-step-desc">Our system automatically matches lost and found items using AI</div>
            </div>
          </div>
          <div class="guide-step">
            <div class="guide-step-number">3</div>
            <div class="guide-step-content">
              <div class="guide-step-title">Claim Your Items</div>
              <div class="guide-step-desc">Get notified when matches are found and claim your items</div>
            </div>
          </div>
          <div class="guide-step">
            <div class="guide-step-number">4</div>
            <div class="guide-step-content">
              <div class="guide-step-title">Track Progress</div>
              <div class="guide-step-desc">Monitor the status of your reports and claims in real-time</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
 
@if($isStaff)
@php
  $total = (int) ($stats['total_reports'] ?? 0);
  $rs = $stats['report_status'] ?? [];
  $rt = $stats['report_type'] ?? [];
  $ms = $stats['match_status'] ?? [];
  $pct = function ($n, $d) { $d = max(1,(int)$d); return (int) round(($n/$d)*100); };

  $lostCnt = (int) ($rt['lost'] ?? 0);
  $foundCnt = (int) ($rt['found'] ?? 0);
  $sumType = max(1, $lostCnt + $foundCnt);
  $radius = 52;
  $circ = 2 * pi() * $radius;
  $lostLen = (int) round($circ * $lostCnt / $sumType);
  $foundLen = (int) round($circ * $foundCnt / $sumType);

  $sugCnt = (int) ($ms['suggested'] ?? 0);
  $conCnt = (int) ($ms['confirmed'] ?? 0);
  $rejCnt = (int) ($ms['rejected'] ?? 0);
  $sumMatch = max(1, $sugCnt + $conCnt + $rejCnt);
  $sugLen = (int) round($circ * $sugCnt / $sumMatch);
  $conLen = (int) round($circ * $conCnt / $sumMatch);
  $rejLen = (int) round($circ * $rejCnt / $sumMatch);

  $maxStatus = max(1, (int) max($rs['pending'] ?? 0, $rs['matched'] ?? 0, $rs['claimed'] ?? 0, $rs['returned'] ?? 0, $rs['archived'] ?? 0));
  $barMax = 240;
@endphp

<div class="glass-card p-3 p-md-4 mt-4">
  <div class="section-title text-center mb-4">
    <i class="bi bi-graph-up"></i> Analytics Overview
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="card">
        <div class="card-body text-center">
          <div class="mini">Total Users</div>
          <div class="h3 fw-bold mb-2">{{ $stats['total_users'] ?? 0 }}</div>
          <span class="chip chip-primary"><i class="bi bi-people"></i> Users</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-3">
      <div class="card">
        <div class="card-body text-center">
          <div class="mini">Total Reports</div>
          <div class="h3 fw-bold mb-2">{{ $total }}</div>
          <span class="chip chip-primary"><i class="bi bi-files"></i> Reports</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="mini mb-2">Reports by Type</div>
          <div class="d-flex align-items-center justify-content-center gap-3">
            <svg width="140" height="140" viewBox="0 0 120 120">
              <g transform="rotate(-90 60 60)">
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f5f5f5" stroke-width="18"></circle>
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f59e0b" stroke-width="18"
                        stroke-dasharray="{{ $lostLen }} {{ $circ - $lostLen }}" stroke-dashoffset="0"
                        stroke-linecap="round"></circle>
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#10b981" stroke-width="18"
                        stroke-dasharray="{{ $foundLen }} {{ $circ - $foundLen }}" stroke-dashoffset="-{{ $lostLen }}"
                        stroke-linecap="round"></circle>
              </g>
              <text x="60" y="64" text-anchor="middle" font-size="14" fill="#737373">Total</text>
              <text x="60" y="84" text-anchor="middle" font-size="18" font-weight="700" fill="#000000">{{ $sumType }}</text>
            </svg>
            <div class="d-flex flex-column gap-2">
              <span class="chip chip-warning"><i class="bi bi-exclamation-circle"></i> Lost: {{ $lostCnt }}</span>
              <span class="chip chip-success"><i class="bi bi-check-circle"></i> Found: {{ $foundCnt }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="mini mb-2">Reports by Status</div>
          <svg width="100%" height="140" viewBox="0 0 300 140">
            @php
              $labels = ['pending','matched','claimed','returned','archived'];
              $y = 18;
            @endphp
            @foreach($labels as $s)
              @php
                $count = (int) ($rs[$s] ?? 0);
                $wpx = (int) round($barMax * $count / $maxStatus);
              @endphp
              <text x="0" y="{{ $y-6 }}" fill="#737373" font-size="10" style="text-transform:uppercase">{{ $s }}</text>
              <rect x="90" y="{{ $y-14 }}" width="{{ $barMax }}" height="12" rx="6" fill="#f5f5f5"></rect>
              <rect x="90" y="{{ $y-14 }}" width="{{ $wpx }}" height="12" rx="6" fill="#000000"></rect>
              <text x="{{ 90 + $barMax + 8 }}" y="{{ $y-3 }}" fill="#737373" font-size="11">{{ $count }}</text>
              @php $y += 26; @endphp
            @endforeach
          </svg>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="mini mb-2">Matches</div>
          <div class="d-flex align-items-center justify-content-center gap-3">
            <svg width="140" height="140" viewBox="0 0 120 120">
              <g transform="rotate(-90 60 60)">
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f5f5f5" stroke-width="18"></circle>
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f59e0b" stroke-width="18"
                        stroke-dasharray="{{ $sugLen }} {{ $circ - $sugLen }}" stroke-dashoffset="0" stroke-linecap="round"></circle>
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#10b981" stroke-width="18"
                        stroke-dasharray="{{ $conLen }} {{ $circ - $conLen }}" stroke-dashoffset="-{{ $sugLen }}" stroke-linecap="round"></circle>
                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#ef4444" stroke-width="18"
                        stroke-dasharray="{{ $rejLen }} {{ $circ - $rejLen }}" stroke-dashoffset="-{{ $sugLen + $conLen }}" stroke-linecap="round"></circle>
              </g>
              <text x="60" y="64" text-anchor="middle" font-size="14" fill="#737373">Total</text>
              <text x="60" y="84" text-anchor="middle" font-size="18" font-weight="700" fill="#000000">{{ $sumMatch }}</text>
            </svg>
            <div class="d-flex flex-column gap-2">
              <span class="chip chip-warning"><i class="bi bi-lightbulb"></i> Suggested: {{ $sugCnt }}</span>
              <span class="chip chip-success"><i class="bi bi-check2-circle"></i> Confirmed: {{ $conCnt }}</span>
              <span class="chip chip-danger"><i class="bi bi-x-circle"></i> Rejected: {{ $rejCnt }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endsection


@push('styles')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet" />
<style>
.analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); }
.analytics-card { background: white; border: 1px solid var(--border-default); border-radius: var(--radius-lg); overflow: hidden; }
.analytics-card-wide { grid-column: span 2; }
.analytics-card-body { padding: var(--space-xl); }
.analytics-label { font-size: var(--text-sm); color: var(--text-muted); margin-bottom: var(--space-md); font-weight: 600; }
.analytics-value { font-size: var(--text-4xl); font-weight: 700; color: var(--text-primary); margin-bottom: var(--space-md); line-height: 1; }
.chart-container { display: flex; align-items: center; justify-content: center; gap: var(--space-xl); }
.chart-legend { display: flex; flex-direction: column; gap: var(--space-sm); }
@media (max-width: 768px) { .analytics-card-wide { grid-column: span 1; } .chart-container { flex-direction: column; } }
</style>
@endpush
