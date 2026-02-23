@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $initial = strtoupper(substr($user->email,0,1));
@endphp

<div class="glass-card hero p-3 p-md-4 mb-3">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="avatar">{{ $initial }}</div>
      <div>
        <div class="h4 fw-bold mb-0">Dashboard</div>
        <div class="mini">
          Logged in as <span class="fw-semibold">{{ $user->email }}</span>
          @if($user->profile) · {{ $user->profile->full_name }} @endif
        </div>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-sm btn-primary" href="{{ route('reports.create') }}"><i class="bi bi-plus-circle"></i> New Report</a>
      <a class="btn btn-sm btn-ghost" href="{{ route('reports.index') }}"><i class="bi bi-inbox"></i> View Reports</a>
      @if($isStaff)
        <a class="btn btn-sm btn-ghost" href="{{ route('matches.index') }}"><i class="bi bi-diagram-2"></i> Matches</a>
        <a class="btn btn-sm btn-ghost" href="{{ route('users.index') }}"><i class="bi bi-people"></i> Users</a>
      @endif
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6 col-lg-3">
    <div class="metric-card">
      <div class="metric-top d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
          <div class="fw-semibold">My Reports</div>
        </div>
        <div class="mini">You</div>
      </div>
      <div class="metric-body">
        <div class="display-6 fw-bold">{{ $stats['my_reports'] }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-lg-3">
    <div class="metric-card">
      <div class="metric-top d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="icon"><i class="bi bi-person-check"></i></div>
          <div class="fw-semibold">My Claims</div>
        </div>
        <div class="mini">You</div>
      </div>
      <div class="metric-body">
        <div class="display-6 fw-bold">{{ $stats['my_claims'] }}</div>
      </div>
    </div>
  </div>

  @if($isStaff)
    <div class="col-12 col-md-6 col-lg-3">
      <div class="metric-card">
        <div class="metric-top d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="fw-semibold">Pending Reports</div>
          </div>
          <div class="mini">Staff</div>
        </div>
        <div class="metric-body">
          <div class="display-6 fw-bold">{{ $stats['pending_reports'] }}</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
      <div class="metric-card">
        <div class="metric-top d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="icon"><i class="bi bi-diagram-2"></i></div>
            <div class="fw-semibold">Suggested Matches</div>
          </div>
          <div class="mini">Staff</div>
        </div>
        <div class="metric-body">
          <div class="display-6 fw-bold">{{ $stats['suggested_matches'] }}</div>
        </div>
      </div>
    </div>
  @endif
</div>

{{-- Quick Actions --}}
<div class="row g-3 mt-2">
  <div class="col-12">
    <div class="section-title">
      <i class="bi bi-lightning-charge"></i> Quick Actions
    </div>
  </div>
  <div class="col-6 col-md-3">
    <a href="{{ route('reports.create') }}" class="quick-action text-decoration-none">
      <i class="bi bi-plus-circle-fill"></i>
      <div class="fw-bold text-dark">New Report</div>
      <div class="mini">Submit lost/found item</div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="{{ route('reports.index') }}" class="quick-action text-decoration-none">
      <i class="bi bi-inbox-fill"></i>
      <div class="fw-bold text-dark">View Reports</div>
      <div class="mini">Browse all reports</div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="{{ route('claims.index') }}" class="quick-action text-decoration-none">
      <i class="bi bi-person-check-fill"></i>
      <div class="fw-bold text-dark">My Claims</div>
      <div class="mini">Track your claims</div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="{{ route('notifications.index') }}" class="quick-action text-decoration-none">
      <i class="bi bi-bell-fill"></i>
      <div class="fw-bold text-dark">Notifications</div>
      <div class="mini">View updates</div>
    </a>
  </div>
</div>

{{-- Recent Activity Section --}}
<div class="row g-3 mt-3">
  <div class="col-12 col-lg-6">
    <div class="glass-card p-3">
      <div class="section-title">
        <i class="bi bi-clock-history"></i> My Recent Reports
      </div>
      @if($recentReports->isEmpty())
        <div class="text-center py-4">
          <i class="bi bi-inbox" style="font-size:3rem;color:var(--muted);opacity:.3"></i>
          <div class="mini mt-2">No reports yet</div>
          <a href="{{ route('reports.create') }}" class="btn btn-sm btn-primary mt-2">
            <i class="bi bi-plus-circle"></i> Create First Report
          </a>
        </div>
      @else
        <div class="d-flex flex-column gap-2">
          @foreach($recentReports as $report)
            <a href="{{ route('reports.show', $report->id) }}" class="activity-item text-decoration-none">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="chip chip-{{ $report->report_type === 'lost' ? 'warning' : 'success' }}">
                      <i class="bi bi-{{ $report->report_type === 'lost' ? 'exclamation-circle' : 'check-circle' }}"></i>
                      {{ ucfirst($report->report_type) }}
                    </span>
                    <span class="status-badge status-{{ $report->status }}">{{ $report->status }}</span>
                  </div>
                  <div class="fw-semibold text-dark">{{ Str::limit($report->item_name, 40) }}</div>
                  <div class="mini">
                    <i class="bi bi-tag"></i> {{ $report->category->name ?? 'N/A' }}
                    · <i class="bi bi-geo-alt"></i> {{ $report->location->name ?? 'N/A' }}
                  </div>
                </div>
                <div class="text-end">
                  <div class="mini">{{ $report->created_at->diffForHumans() }}</div>
                </div>
              </div>
            </a>
          @endforeach
        </div>
        <div class="text-center mt-3">
          <a href="{{ route('reports.index') }}" class="btn btn-sm btn-ghost">
            View All Reports <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      @endif
    </div>
  </div>

  @if($isStaff)
    <div class="col-12 col-lg-6">
      <div class="glass-card p-3">
        <div class="section-title">
          <i class="bi bi-activity"></i> Recent Activity
        </div>
        @if($recentActivity->isEmpty())
          <div class="text-center py-4">
            <i class="bi bi-activity" style="font-size:3rem;color:var(--muted);opacity:.3"></i>
            <div class="mini mt-2">No recent activity</div>
          </div>
        @else
          <div class="d-flex flex-column gap-2">
            @foreach($recentActivity as $activity)
              <a href="{{ route('reports.show', $activity->id) }}" class="activity-item text-decoration-none">
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="chip chip-{{ $activity->report_type === 'lost' ? 'warning' : 'success' }}">
                        <i class="bi bi-{{ $activity->report_type === 'lost' ? 'exclamation-circle' : 'check-circle' }}"></i>
                        {{ ucfirst($activity->report_type) }}
                      </span>
                      <span class="status-badge status-{{ $activity->status }}">{{ $activity->status }}</span>
                    </div>
                    <div class="fw-semibold text-dark">{{ Str::limit($activity->item_name, 35) }}</div>
                    <div class="mini">
                      <i class="bi bi-person"></i> {{ $activity->reporter->email ?? 'Unknown' }}
                      · {{ $activity->created_at->diffForHumans() }}
                    </div>
                  </div>
                </div>
              </a>
            @endforeach
          </div>
          <div class="text-center mt-3">
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-ghost">
              View All Activity <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        @endif
      </div>
    </div>
  @else
    <div class="col-12 col-lg-6">
      <div class="glass-card p-3">
        <div class="section-title">
          <i class="bi bi-info-circle"></i> Getting Started
        </div>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <div style="width:40px;height:40px;border-radius:8px;border:2px solid var(--text-primary);display:flex;align-items:center;justify-content:center;color:var(--text-primary);font-weight:700;background:transparent">1</div>
            </div>
            <div>
              <div class="fw-semibold">Report Lost or Found Items</div>
              <div class="mini">Submit a report when you lose or find an item on campus</div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <div style="width:40px;height:40px;border-radius:8px;border:2px solid var(--text-primary);display:flex;align-items:center;justify-content:center;color:var(--text-primary);font-weight:700;background:transparent">2</div>
            </div>
            <div>
              <div class="fw-semibold">AI-Powered Matching</div>
              <div class="mini">Our system automatically matches lost and found items using AI</div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <div style="width:40px;height:40px;border-radius:8px;border:2px solid var(--text-primary);display:flex;align-items:center;justify-content:center;color:var(--text-primary);font-weight:700;background:transparent">3</div>
            </div>
            <div>
              <div class="fw-semibold">Claim Your Items</div>
              <div class="mini">Get notified when matches are found and claim your items</div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <div style="width:40px;height:40px;border-radius:8px;border:2px solid var(--text-primary);display:flex;align-items:center;justify-content:center;color:var(--text-primary);font-weight:700;background:transparent">4</div>
            </div>
            <div>
              <div class="fw-semibold">Track Progress</div>
              <div class="mini">Monitor the status of your reports and claims in real-time</div>
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
