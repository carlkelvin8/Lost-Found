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
      <span class="dash-stat-value">{{ $stats['my_reports'] }}</span>
    </div>
  </div>

  <div class="dash-stat">
    <div class="dash-stat-icon dash-stat-green">
      <i class="bi bi-person-check"></i>
    </div>
    <div class="dash-stat-info">
      <span class="dash-stat-label">My Claims</span>
      <span class="dash-stat-value">{{ $stats['my_claims'] }}</span>
    </div>
  </div>

  @if($isStaff)
    <div class="dash-stat">
      <div class="dash-stat-icon dash-stat-amber">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div class="dash-stat-info">
        <span class="dash-stat-label">Pending</span>
        <span class="dash-stat-value">{{ $stats['pending_reports'] }}</span>
      </div>
    </div>

    <div class="dash-stat">
      <div class="dash-stat-icon dash-stat-indigo">
        <i class="bi bi-diagram-2"></i>
      </div>
      <div class="dash-stat-info">
        <span class="dash-stat-label">Matches</span>
        <span class="dash-stat-value">{{ $stats['suggested_matches'] }}</span>
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
          <span class="dash-analytics-value">{{ $stats['total_users'] ?? 0 }}</span>
        </div>
      </div>

      <div class="dash-analytics-card">
        <div class="dash-analytics-icon dash-stat-violet">
          <i class="bi bi-files"></i>
        </div>
        <div class="dash-analytics-info">
          <span class="dash-analytics-label">Total Reports</span>
          <span class="dash-analytics-value">{{ $total }}</span>
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
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --dash-blue: #0041C7;
  --dash-blue-light: #3ACBEB;
  --dash-blue-glow: rgba(0,65,199,0.12);
  --dash-gray-50: #f8fafc;
  --dash-gray-100: #f1f5f9;
  --dash-gray-200: #e2e8f0;
  --dash-gray-300: #cbd5e1;
  --dash-gray-400: #94a3b8;
  --dash-gray-500: #64748b;
  --dash-gray-600: #475569;
  --dash-gray-800: #1e293b;
  --dash-gray-900: #0f172a;
  --dash-green: #10b981;
  --dash-amber: #f59e0b;
  --dash-red: #ef4444;
  --dash-violet: #8b5cf6;
  --dash-indigo: #6366f1;
  --dash-emerald: #10b981;
  --dash-rose: #f43f5e;
  --dash-radius: 16px;
  --dash-radius-sm: 12px;
  --dash-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
  --dash-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
  --dash-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.06), 0 4px 6px -4px rgba(0,0,0,0.04);
  --dash-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Reset */
.dash-header, .dash-stats, .dash-section, .dash-grid, .dash-analytics,
.dash-analytics-summary, .dash-charts, .dash-chart-card, .dash-chart-content,
.dash-chart-bars, .dash-chart-legend, .dash-list, .dash-list-item,
.dash-card, .dash-card-header, .dash-card-body, .dash-card-title,
.dash-stat, .dash-stat-icon, .dash-stat-info, .dash-stat-label, .dash-stat-value,
.dash-actions, .dash-action, .dash-action-icon, .dash-action-text,
.dash-action-title, .dash-action-desc, .dash-action-arrow,
.dash-guide, .dash-guide-step, .dash-guide-num, .dash-guide-info,
.dash-guide-title, .dash-guide-desc,
.dash-empty, .dash-empty-icon, .dash-empty-title, .dash-empty-desc,
.dash-list-dot, .dash-list-content, .dash-list-header, .dash-list-title,
.dash-list-meta, .dash-list-time,
.dash-badge, .dash-card-link,
.dash-bar-row, .dash-bar-label, .dash-bar-track, .dash-bar-fill, .dash-bar-value,
.dash-legend-item, .dash-legend-dot, .dash-legend-label, .dash-legend-value {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── HEADER ── */
.dash-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  animation: dashFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes dashFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.dash-welcome {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.dash-avatar {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(135deg, var(--dash-blue) 0%, #0D85D8 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,65,199,0.2);
  overflow: hidden;
}

.dash-avatar img {
  width: 56px;
  height: 56px;
  object-fit: cover;
  display: block;
}

.dash-avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.dash-welcome-text { flex: 1; }

.dash-greeting {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dash-gray-900);
  letter-spacing: -0.02em;
  margin: 0 0 0.25rem;
  line-height: 1.2;
}

.dash-subtitle {
  font-size: 0.9375rem;
  color: var(--dash-gray-400);
  margin: 0;
}

.dash-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: linear-gradient(135deg, var(--dash-blue) 0%, #0033A0 100%);
  color: white;
  border-radius: var(--dash-radius-sm);
  font-size: 0.9375rem;
  font-weight: 600;
  text-decoration: none;
  transition: var(--dash-transition);
  box-shadow: 0 4px 12px rgba(0,65,199,0.2);
}

.dash-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,65,199,0.3);
  color: white;
}

/* ── STATS ── */
.dash-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.dash-stat {
  background: white;
  border: 1px solid var(--dash-gray-200);
  border-radius: var(--dash-radius);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: var(--dash-transition);
  animation: dashFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.dash-stat:nth-child(1) { animation-delay: 0.05s; }
.dash-stat:nth-child(2) { animation-delay: 0.1s; }
.dash-stat:nth-child(3) { animation-delay: 0.15s; }
.dash-stat:nth-child(4) { animation-delay: 0.2s; }

.dash-stat:hover {
  border-color: var(--dash-gray-300);
  box-shadow: var(--dash-shadow-md);
  transform: translateY(-2px);
}

.dash-stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.dash-stat-blue { background: rgba(0,65,199,0.08); color: var(--dash-blue); }
.dash-stat-green { background: rgba(16,185,129,0.08); color: var(--dash-green); }
.dash-stat-amber { background: rgba(245,158,11,0.08); color: var(--dash-amber); }
.dash-stat-indigo { background: rgba(99,102,241,0.08); color: var(--dash-indigo); }
.dash-stat-violet { background: rgba(139,92,246,0.08); color: var(--dash-violet); }

.dash-stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.dash-stat-label {
  font-size: 0.8125rem;
  color: var(--dash-gray-400);
  font-weight: 500;
}

.dash-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dash-gray-900);
  line-height: 1;
}

/* ── SECTION ── */
.dash-section {
  margin-bottom: 2rem;
}

.dash-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--dash-gray-900);
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dash-section-title i {
  color: var(--dash-blue);
}

/* ── QUICK ACTIONS ── */
.dash-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 0.75rem;
}

.dash-action {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: white;
  border: 1px solid var(--dash-gray-200);
  border-radius: var(--dash-radius-sm);
  text-decoration: none;
  transition: var(--dash-transition);
  animation: dashFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.dash-action:nth-child(1) { animation-delay: 0.05s; }
.dash-action:nth-child(2) { animation-delay: 0.1s; }
.dash-action:nth-child(3) { animation-delay: 0.15s; }
.dash-action:nth-child(4) { animation-delay: 0.2s; }

.dash-action:hover {
  border-color: var(--dash-blue);
  box-shadow: 0 4px 16px rgba(0,65,199,0.1);
  transform: translateY(-1px);
  color: inherit;
}

.dash-action-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
  transition: var(--dash-transition);
}

.dash-action:hover .dash-action-icon { transform: scale(1.1); }

.dash-action-blue { background: rgba(0,65,199,0.08); color: var(--dash-blue); }
.dash-action-violet { background: rgba(139,92,246,0.08); color: var(--dash-violet); }
.dash-action-emerald { background: rgba(16,185,129,0.08); color: var(--dash-emerald); }
.dash-action-rose { background: rgba(244,63,94,0.08); color: var(--dash-rose); }

.dash-action:hover .dash-action-blue { background: var(--dash-blue); color: white; }
.dash-action:hover .dash-action-violet { background: var(--dash-violet); color: white; }
.dash-action:hover .dash-action-emerald { background: var(--dash-emerald); color: white; }
.dash-action:hover .dash-action-rose { background: var(--dash-rose); color: white; }

.dash-action-text {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.dash-action-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--dash-gray-900);
}

.dash-action-desc {
  font-size: 0.8125rem;
  color: var(--dash-gray-400);
}

.dash-action-arrow {
  color: var(--dash-gray-300);
  font-size: 0.875rem;
  transition: var(--dash-transition);
}

.dash-action:hover .dash-action-arrow {
  color: var(--dash-blue);
  transform: translateX(3px);
}

/* ── GRID ── */
.dash-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1rem;
}

/* ── CARD ── */
.dash-card {
  background: white;
  border: 1px solid var(--dash-gray-200);
  border-radius: var(--dash-radius);
  overflow: hidden;
}

.dash-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--dash-gray-100);
}

.dash-card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--dash-gray-900);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dash-card-title i { color: var(--dash-gray-400); }

.dash-card-link {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--dash-blue);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: var(--dash-transition);
}

.dash-card-link:hover { gap: 0.5rem; }

.dash-card-body { padding: 0.75rem; }

/* ── LIST ── */
.dash-list { display: flex; flex-direction: column; gap: 2px; }

.dash-list-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: var(--dash-radius-sm);
  text-decoration: none;
  transition: var(--dash-transition);
}

.dash-list-item:hover {
  background: var(--dash-gray-50);
}

.dash-list-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.dash-dot-amber { background: var(--dash-amber); }
.dash-dot-green { background: var(--dash-green); }

.dash-list-content { flex: 1; min-width: 0; }

.dash-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.dash-list-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--dash-gray-900);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-badge {
  display: inline-flex;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.6875rem;
  font-weight: 600;
  flex-shrink: 0;
  text-transform: capitalize;
}

.dash-badge-amber { background: rgba(245,158,11,0.1); color: #b45309; }
.dash-badge-green { background: rgba(16,185,129,0.1); color: #047857; }

.dash-list-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: var(--dash-gray-400);
}

.dash-list-meta i { margin-right: 0.25rem; }

.dash-list-time {
  font-size: 0.75rem;
  color: var(--dash-gray-400);
  white-space: nowrap;
  flex-shrink: 0;
}

/* ── EMPTY STATE ── */
.dash-empty {
  text-align: center;
  padding: 2.5rem 1rem;
}

.dash-empty-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--dash-gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.dash-empty-icon i {
  font-size: 1.5rem;
  color: var(--dash-gray-300);
}

.dash-empty-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--dash-gray-800);
  margin: 0 0 0.25rem;
}

.dash-empty-desc {
  font-size: 0.8125rem;
  color: var(--dash-gray-400);
  margin: 0;
}

/* ── GUIDE ── */
.dash-guide {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.dash-guide-step {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-radius: var(--dash-radius-sm);
  transition: var(--dash-transition);
}

.dash-guide-step:hover {
  background: var(--dash-gray-50);
}

.dash-guide-num {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(0,65,199,0.08);
  color: var(--dash-blue);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 700;
  flex-shrink: 0;
}

.dash-guide-info { flex: 1; }

.dash-guide-title {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--dash-gray-900);
}

.dash-guide-desc {
  display: block;
  font-size: 0.8125rem;
  color: var(--dash-gray-400);
}

/* ── ANALYTICS ── */
.dash-analytics { display: flex; flex-direction: column; gap: 1rem; }

.dash-analytics-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.dash-analytics-card {
  background: white;
  border: 1px solid var(--dash-gray-200);
  border-radius: var(--dash-radius);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: var(--dash-transition);
}

.dash-analytics-card:hover {
  border-color: var(--dash-gray-300);
  box-shadow: var(--dash-shadow-md);
}

.dash-analytics-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.dash-analytics-info {
  display: flex;
  flex-direction: column;
}

.dash-analytics-label {
  font-size: 0.8125rem;
  color: var(--dash-gray-400);
  font-weight: 500;
}

.dash-analytics-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dash-gray-900);
  line-height: 1;
}

.dash-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.dash-chart-card {
  background: white;
  border: 1px solid var(--dash-gray-200);
  border-radius: var(--dash-radius);
  padding: 1.25rem;
}

.dash-chart-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--dash-gray-900);
  margin: 0 0 1rem;
}

.dash-chart-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
}

.dash-chart-legend {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.dash-legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
}

.dash-legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.dash-legend-label {
  color: var(--dash-gray-500);
  flex: 1;
}

.dash-legend-value {
  font-weight: 600;
  color: var(--dash-gray-900);
}

/* ── BAR CHART ── */
.dash-chart-bars {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dash-bar-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.dash-bar-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--dash-gray-500);
  width: 72px;
  text-transform: capitalize;
  flex-shrink: 0;
}

.dash-bar-track {
  flex: 1;
  height: 8px;
  background: var(--dash-gray-100);
  border-radius: 4px;
  overflow: hidden;
}

.dash-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-bar-value {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--dash-gray-900);
  width: 32px;
  text-align: right;
  flex-shrink: 0;
}

/* ── CHART ANIMATION ── */
.dash-chart-seg {
  animation: dashChartDraw 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes dashChartDraw {
  from { stroke-dasharray: 0 999; }
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .dash-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .dash-welcome { width: 100%; }

  .dash-btn-primary { width: 100%; justify-content: center; }

  .dash-grid { grid-template-columns: 1fr; }

  .dash-actions { grid-template-columns: 1fr 1fr; }

  .dash-chart-content { flex-direction: column; }
}

@media (max-width: 480px) {
  .dash-actions { grid-template-columns: 1fr; }

  .dash-stats { grid-template-columns: 1fr 1fr; }

  .dash-analytics-summary { grid-template-columns: 1fr; }

  .dash-charts { grid-template-columns: 1fr; }
}
</style>
@endpush
