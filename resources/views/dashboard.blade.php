@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $initial = strtoupper(substr($user->email,0,1));
  $hour = now()->hour;
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<!-- Welcome Header -->
<div class="dash-header">
  <div class="dash-welcome">
    <div class="dash-avatar">
      @if($user->profile && $user->profile->avatar_url)
        @php $avatarSrc = str_starts_with($user->profile->avatar_url, 'storage/') ? substr($user->profile->avatar_url, 8) : $user->profile->avatar_url; @endphp
        <img src="{{ asset($avatarSrc) }}" alt="{{ $user->profile->full_name ?? 'User' }}" style="width:56px;height:56px;object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
        <span class="dash-avatar-initial" style="display:none">{{ $initial }}</span>
      @else
        <span class="dash-avatar-initial">{{ $initial }}</span>
      @endif
    </div>
    <div class="dash-welcome-text">
      <h1 class="dash-greeting">{{ $greeting }}, {{ $user->profile?->full_name ?? 'User' }}</h1>
      <p class="dash-subtitle">Here's what's happening with your lost & found items</p>
    </div>
  </div>
  <a class="dash-btn-primary" href="{{ route('reports.create') }}">
    <i class="bi bi-plus-lg"></i>
    <span>New Report</span>
  </a>
</div>

<!-- Stats Cards -->
<div class="dash-stats">
  <div class="dash-stat">
    <div class="dash-stat-icon dash-stat-blue">
      <i class="bi bi-file-earmark-text"></i>
    </div>
    <div class="dash-stat-info">
      <span class="dash-stat-label">My Reports</span>
      <span class="dash-stat-value" data-count="{{ $stats['my_reports'] }}">{{ $stats['my_reports'] }}</span>
    </div>
  </div>

  <div class="dash-stat">
    <div class="dash-stat-icon dash-stat-green">
      <i class="bi bi-person-check"></i>
    </div>
    <div class="dash-stat-info">
      <span class="dash-stat-label">My Claims</span>
      <span class="dash-stat-value" data-count="{{ $stats['my_claims'] }}">{{ $stats['my_claims'] }}</span>
    </div>
  </div>

  @if($isStaff)
    <div class="dash-stat">
      <div class="dash-stat-icon dash-stat-amber">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div class="dash-stat-info">
        <span class="dash-stat-label">Pending</span>
        <span class="dash-stat-value" data-count="{{ $stats['pending_reports'] }}">{{ $stats['pending_reports'] }}</span>
      </div>
    </div>

    <div class="dash-stat">
      <div class="dash-stat-icon dash-stat-indigo">
        <i class="bi bi-diagram-2"></i>
      </div>
      <div class="dash-stat-info">
        <span class="dash-stat-label">Matches</span>
        <span class="dash-stat-value" data-count="{{ $stats['suggested_matches'] }}">{{ $stats['suggested_matches'] }}</span>
      </div>
    </div>
  @endif
</div>

<!-- Quick Actions -->
<div class="dash-section">
  <h2 class="dash-section-title">Quick Actions</h2>
  <div class="dash-actions">
    <a href="{{ route('reports.create') }}" class="dash-action">
      <div class="dash-action-icon dash-action-blue">
        <i class="bi bi-plus-circle"></i>
      </div>
      <div class="dash-action-text">
        <span class="dash-action-title">New Report</span>
        <span class="dash-action-desc">Submit lost/found item</span>
      </div>
      <i class="bi bi-arrow-right dash-action-arrow"></i>
    </a>

    <a href="{{ route('reports.index') }}" class="dash-action">
      <div class="dash-action-icon dash-action-violet">
        <i class="bi bi-inbox"></i>
      </div>
      <div class="dash-action-text">
        <span class="dash-action-title">View Reports</span>
        <span class="dash-action-desc">Browse all reports</span>
      </div>
      <i class="bi bi-arrow-right dash-action-arrow"></i>
    </a>

    <a href="{{ route('claims.index') }}" class="dash-action">
      <div class="dash-action-icon dash-action-emerald">
        <i class="bi bi-person-check"></i>
      </div>
      <div class="dash-action-text">
        <span class="dash-action-title">My Claims</span>
        <span class="dash-action-desc">Track your claims</span>
      </div>
      <i class="bi bi-arrow-right dash-action-arrow"></i>
    </a>

    <a href="{{ route('notifications.index') }}" class="dash-action">
      <div class="dash-action-icon dash-action-rose">
        <i class="bi bi-bell"></i>
      </div>
      <div class="dash-action-text">
        <span class="dash-action-title">Notifications</span>
        <span class="dash-action-desc">View updates</span>
      </div>
      <i class="bi bi-arrow-right dash-action-arrow"></i>
    </a>
  </div>
</div>

<!-- Content Grid -->
<div class="dash-grid">
  <!-- Recent Reports -->
  <div class="dash-card">
    <div class="dash-card-header">
      <h3 class="dash-card-title">
        <i class="bi bi-clock-history"></i> Recent Reports
      </h3>
      <a href="{{ route('reports.index') }}" class="dash-card-link">View all <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="dash-card-body">
      @if($recentReports->isEmpty())
        <div class="dash-empty">
          <div class="dash-empty-icon">
            <i class="bi bi-inbox"></i>
          </div>
          <p class="dash-empty-title">No reports yet</p>
          <p class="dash-empty-desc">Create your first report to get started</p>
        </div>
      @else
        <div class="dash-list">
          @foreach($recentReports as $report)
            <a href="{{ route('reports.show', $report->id) }}" class="dash-list-item">
              <div class="dash-list-dot {{ $report->report_type === 'lost' ? 'dash-dot-amber' : 'dash-dot-green' }}"></div>
              <div class="dash-list-content">
                <div class="dash-list-header">
                  <span class="dash-list-title">{{ Str::limit($report->item_name, 35) }}</span>
                  <span class="dash-badge {{ $report->report_type === 'lost' ? 'dash-badge-amber' : 'dash-badge-green' }}">
                    {{ ucfirst($report->report_type) }}
                  </span>
                </div>
                <div class="dash-list-meta">
                  <span><i class="bi bi-tag"></i> {{ $report->category->name ?? 'N/A' }}</span>
                  <span><i class="bi bi-geo-alt"></i> {{ $report->location->name ?? 'N/A' }}</span>
                </div>
              </div>
              <span class="dash-list-time">{{ $report->created_at->diffForHumans() }}</span>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  @if($isStaff)
    <!-- Recent Activity -->
    <div class="dash-card">
      <div class="dash-card-header">
        <h3 class="dash-card-title">
          <i class="bi bi-activity"></i> Recent Activity
        </h3>
        <a href="{{ route('reports.index') }}" class="dash-card-link">View all <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="dash-card-body">
        @if($recentActivity->isEmpty())
          <div class="dash-empty">
            <div class="dash-empty-icon">
              <i class="bi bi-activity"></i>
            </div>
            <p class="dash-empty-title">No recent activity</p>
            <p class="dash-empty-desc">Activity will appear here</p>
          </div>
        @else
          <div class="dash-list">
            @foreach($recentActivity as $activity)
              <a href="{{ route('reports.show', $activity->id) }}" class="dash-list-item">
                <div class="dash-list-dot {{ $activity->report_type === 'lost' ? 'dash-dot-amber' : 'dash-dot-green' }}"></div>
                <div class="dash-list-content">
                  <div class="dash-list-header">
                    <span class="dash-list-title">{{ Str::limit($activity->item_name, 30) }}</span>
                    <span class="dash-badge {{ $activity->report_type === 'lost' ? 'dash-badge-amber' : 'dash-badge-green' }}">
                      {{ ucfirst($activity->report_type) }}
                    </span>
                  </div>
                  <div class="dash-list-meta">
                    <span><i class="bi bi-person"></i> {{ $activity->reporter->email ?? 'Unknown' }}</span>
                  </div>
                </div>
                <span class="dash-list-time">{{ $activity->created_at->diffForHumans() }}</span>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  @else
    <!-- Getting Started Guide -->
    <div class="dash-card">
      <div class="dash-card-header">
        <h3 class="dash-card-title">
          <i class="bi bi-lightbulb"></i> Getting Started
        </h3>
      </div>
      <div class="dash-card-body">
        <div class="dash-guide">
          <div class="dash-guide-step">
            <div class="dash-guide-num">1</div>
            <div class="dash-guide-info">
              <span class="dash-guide-title">Report Lost or Found Items</span>
              <span class="dash-guide-desc">Submit a report when you lose or find an item</span>
            </div>
          </div>
          <div class="dash-guide-step">
            <div class="dash-guide-num">2</div>
            <div class="dash-guide-info">
              <span class="dash-guide-title">AI-Powered Matching</span>
              <span class="dash-guide-desc">System automatically matches lost & found items</span>
            </div>
          </div>
          <div class="dash-guide-step">
            <div class="dash-guide-num">3</div>
            <div class="dash-guide-info">
              <span class="dash-guide-title">Claim Your Items</span>
              <span class="dash-guide-desc">Get notified when matches are found</span>
            </div>
          </div>
          <div class="dash-guide-step">
            <div class="dash-guide-num">4</div>
            <div class="dash-guide-info">
              <span class="dash-guide-title">Track Progress</span>
              <span class="dash-guide-desc">Monitor status of your reports in real-time</span>
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

<!-- Analytics Section -->
<div class="dash-section">
  <h2 class="dash-section-title"><i class="bi bi-graph-up"></i> Analytics Overview</h2>
  
  <div class="dash-analytics">
    <!-- Summary Cards -->
    <div class="dash-analytics-summary">
      <div class="dash-analytics-card">
        <div class="dash-analytics-icon dash-stat-blue">
          <i class="bi bi-people"></i>
        </div>
        <div class="dash-analytics-info">
          <span class="dash-analytics-label">Total Users</span>
          <span class="dash-analytics-value" data-count="{{ $stats['total_users'] ?? 0 }}">{{ $stats['total_users'] ?? 0 }}</span>
        </div>
      </div>

      <div class="dash-analytics-card">
        <div class="dash-analytics-icon dash-stat-violet">
          <i class="bi bi-files"></i>
        </div>
        <div class="dash-analytics-info">
          <span class="dash-analytics-label">Total Reports</span>
          <span class="dash-analytics-value" data-count="{{ $total }}">{{ $total }}</span>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="dash-charts">
      <!-- Reports by Type -->
      <div class="dash-chart-card">
        <h4 class="dash-chart-title">Reports by Type</h4>
        <div class="dash-chart-content">
          <svg width="140" height="140" viewBox="0 0 120 120">
            <g transform="rotate(-90 60 60)">
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="var(--gray-100)" stroke-width="16"></circle>
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f59e0b" stroke-width="16"
                      stroke-dasharray="{{ $lostLen }} {{ $circ - $lostLen }}" stroke-dashoffset="0"
                      stroke-linecap="round" class="dash-chart-seg"></circle>
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#10b981" stroke-width="16"
                      stroke-dasharray="{{ $foundLen }} {{ $circ - $foundLen }}" stroke-dashoffset="-{{ $lostLen }}"
                      stroke-linecap="round" class="dash-chart-seg"></circle>
            </g>
            <text x="60" y="58" text-anchor="middle" font-size="11" fill="var(--gray-400)" font-weight="500">Total</text>
            <text x="60" y="78" text-anchor="middle" font-size="20" font-weight="700" fill="var(--gray-900)">{{ $sumType }}</text>
          </svg>
          <div class="dash-chart-legend">
            <div class="dash-legend-item">
              <span class="dash-legend-dot" style="background:#f59e0b"></span>
              <span class="dash-legend-label">Lost</span>
              <span class="dash-legend-value">{{ $lostCnt }}</span>
            </div>
            <div class="dash-legend-item">
              <span class="dash-legend-dot" style="background:#10b981"></span>
              <span class="dash-legend-label">Found</span>
              <span class="dash-legend-value">{{ $foundCnt }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Reports by Status -->
      <div class="dash-chart-card">
        <h4 class="dash-chart-title">Reports by Status</h4>
        <div class="dash-chart-bars">
          @php
            $labels = ['pending','matched','claimed','returned','archived'];
            $colors = ['pending' => '#f59e0b', 'matched' => '#3b82f6', 'claimed' => '#8b5cf6', 'returned' => '#10b981', 'archived' => '#94a3b8'];
            $y = 0;
          @endphp
          @foreach($labels as $s)
            @php
              $count = (int) ($rs[$s] ?? 0);
              $wpx = (int) round($barMax * $count / $maxStatus);
              $pctVal = $pct($count, $maxStatus);
            @endphp
            <div class="dash-bar-row">
              <span class="dash-bar-label">{{ ucfirst($s) }}</span>
              <div class="dash-bar-track">
                <div class="dash-bar-fill" style="width:{{ $pctVal }}%; background:{{ $colors[$s] }}"></div>
              </div>
              <span class="dash-bar-value">{{ $count }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Matches -->
      <div class="dash-chart-card">
        <h4 class="dash-chart-title">Matches</h4>
        <div class="dash-chart-content">
          <svg width="140" height="140" viewBox="0 0 120 120">
            <g transform="rotate(-90 60 60)">
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="var(--gray-100)" stroke-width="16"></circle>
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#f59e0b" stroke-width="16"
                      stroke-dasharray="{{ $sugLen }} {{ $circ - $sugLen }}" stroke-dashoffset="0"
                      stroke-linecap="round" class="dash-chart-seg"></circle>
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#10b981" stroke-width="16"
                      stroke-dasharray="{{ $conLen }} {{ $circ - $conLen }}" stroke-dashoffset="-{{ $sugLen }}"
                      stroke-linecap="round" class="dash-chart-seg"></circle>
              <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="#ef4444" stroke-width="16"
                      stroke-dasharray="{{ $rejLen }} {{ $circ - $rejLen }}" stroke-dashoffset="-{{ $sugLen + $conLen }}"
                      stroke-linecap="round" class="dash-chart-seg"></circle>
            </g>
            <text x="60" y="58" text-anchor="middle" font-size="11" fill="var(--gray-400)" font-weight="500">Total</text>
            <text x="60" y="78" text-anchor="middle" font-size="20" font-weight="700" fill="var(--gray-900)">{{ $sumMatch }}</text>
          </svg>
          <div class="dash-chart-legend">
            <div class="dash-legend-item">
              <span class="dash-legend-dot" style="background:#f59e0b"></span>
              <span class="dash-legend-label">Suggested</span>
              <span class="dash-legend-value">{{ $sugCnt }}</span>
            </div>
            <div class="dash-legend-item">
              <span class="dash-legend-dot" style="background:#10b981"></span>
              <span class="dash-legend-label">Confirmed</span>
              <span class="dash-legend-value">{{ $conCnt }}</span>
            </div>
            <div class="dash-legend-item">
              <span class="dash-legend-dot" style="background:#ef4444"></span>
              <span class="dash-legend-label">Rejected</span>
              <span class="dash-legend-value">{{ $rejCnt }}</span>
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
@endpush
