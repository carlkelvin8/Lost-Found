@extends('layouts.app')

@section('title')
Report #{{ $report->id }} · NAAP Lost & Found
@endsection

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="form-page-header">
  <div>
    <h1>Report #{{ $report->id }}</h1>
    <div class="form-page-subtitle">
      <span class="detail-badge" style="background:{{ $report->report_type==='lost' ? 'rgba(245,158,11,0.12);color:#d97706;border:1px solid rgba(245,158,11,0.2)' : 'rgba(16,185,129,0.12);color:#059669;border:1px solid rgba(16,185,129,0.2)' }}">
        {{ strtoupper($report->report_type) }}
      </span>
      <span class="detail-badge" style="background:rgba(0,65,199,0.08);color:#0041C7;border:1px solid rgba(0,65,199,0.15)">
        {{ strtoupper($report->status) }}
      </span>
      System-managed status
    </div>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <button onclick="bookmarks.toggle({{ $report->id }}, 'Report #{{ $report->id }}')" 
        class="bookmark-btn {{ in_array($report->id, session('bookmarks', [])) ? 'bookmarked' : '' }}" 
        data-report-id="{{ $report->id }}" title="Bookmark — save this report so you can find it again from Reports → Bookmarks" aria-label="Bookmark this report">
      <i class="bi bi-bookmark"></i>
    </button>
    <button onclick="shareReport({{ $report->id }}, 'Report #{{ $report->id }}')" class="btn btn-outline-secondary btn-sm" title="Share — copy this report's link or open the share menu">
      <i class="bi bi-share"></i>
    </button>
    @if($isStaff)
      <a class="btn btn-outline-danger btn-sm" href="{{ route('reports.exportSinglePdf', $report->id) }}">
        <i class="bi bi-file-earmark-pdf"></i> PDF
      </a>
    @endif
    @if($isLocked)
      <span class="btn btn-sm btn-secondary" style="cursor:default" title="{{ $report->locked_at && $report->locked_by_user_id ? 'Locked by ' . ($lockerName ?? 'staff') . ' on ' . \Carbon\Carbon::parse($report->locked_at)->format('M j, Y \a\t g:i A') : 'This report is locked — details, photos and status cannot be changed' }}">
        <i class="bi bi-lock-fill"></i> Locked
      </span>
    @elseif($report->can_edit)
      <a class="btn btn-outline-primary" href="{{ route('reports.edit',$report->id) }}">
        <i class="bi bi-pencil"></i> Edit
      </a>
    @endif
    <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<div class="row g-3">

{{-- LEFT --}}
<div class="col-lg-7">

  {{-- DETAILS --}}
  <div class="detail-card">
    <div class="detail-card-title"><i class="bi bi-box-seam"></i> Item Details</div>

    <div class="row g-2">
      <div class="col-md-6">
        <div class="detail-label">Item</div>
        <div class="detail-value">{{ $report->item_name ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="detail-label">Category</div>
        <div class="detail-value">{{ $report->category?->name ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="detail-label">Brand / Model</div>
        <div class="detail-value">{{ $report->brand_model ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="detail-label">Color</div>
        <div class="detail-value">{{ $report->color ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="detail-label">Incident Date</div>
        <div class="detail-value">{{ $report->incident_date ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="detail-label">Location</div>
        <div class="detail-value">{{ $report->location?->name ?? '—' }}</div>
      </div>
    </div>

    <hr class="detail-divider">

    <div class="detail-label">Description</div>
    <div class="detail-value mb-0">{{ $report->item_description }}</div>

    @if($report->circumstances)
      <div class="detail-label mt-3">Circumstances</div>
      <div class="detail-value">{{ $report->circumstances }}</div>
    @endif

    @if($report->contact_override)
      <div class="detail-label mt-3">Contact</div>
      <div class="detail-value">{{ $report->contact_override }}</div>
    @endif
  </div>

  {{-- STATUS TIMELINE --}}
  <div class="detail-card mt-3">
    <div class="detail-card-title"><i class="bi bi-diagram-3"></i> Status Progress</div>
    {!! createStatusTimeline($report->status) !!}
  </div>

  {{-- PHOTOS --}}
  <div class="detail-card mt-3">
    <div class="detail-card-title"><i class="bi bi-images"></i> Photos</div>

    @if($report->photos->count())
      <div class="detail-photo-grid">
        @foreach($report->photos as $p)
          <img src="{{ asset($p->photo_url) }}" alt="Report photo" loading="lazy" 
               style="cursor:pointer" 
               onclick="lightbox.open({{ $report->photos->map(fn($photo) => asset($photo->photo_url))->toJson() }}, {{ $loop->index }})">
        @endforeach
      </div>
    @else
      <div class="text-muted">No photos uploaded</div>
    @endif
  </div>

  {{-- AI ANALYSIS (STAFF ONLY) --}}
  @if($isStaff && !empty($report->ai_analysis))
    <div class="detail-card mt-3" style="border-color:rgba(13,133,216,0.3)">
      <div class="detail-card-title" style="color:#0D85D8"><i class="bi bi-cpu"></i> AI Analysis</div>
      
      @php $ai = $report->ai_analysis; @endphp

      <div class="row g-2">
        <div class="col-md-6">
          <div class="detail-label">Detected Color</div>
          <div class="detail-value">{{ $ai['color'] ?? '—' }}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">Detected Brand</div>
          <div class="detail-value">{{ $ai['brand'] ?? '—' }}</div>
        </div>
        <div class="col-12">
          <div class="detail-label">Keywords</div>
          <div>
            @foreach($ai['keywords'] ?? [] as $k)
              <span class="admin-badge admin-badge-secondary">{{ $k }}</span>
            @endforeach
          </div>
        </div>
        @if(!empty($ai['distinct_features']))
        <div class="col-12">
          <div class="detail-label">Distinct Features</div>
          <div class="text-muted small">{{ $ai['distinct_features'] }}</div>
        </div>
        @endif
      </div>
    </div>
  @endif


</div>

{{-- RIGHT --}}
<div class="col-lg-5">

  {{-- STATUS --}}
  <div class="detail-card">
    <div class="detail-card-title"><i class="bi bi-activity"></i> Status</div>

    <p class="text-muted small mb-3">
      Status is automatically updated by the system based on
      matching, claims, and verification.
    </p>

    @if($isStaff)
      <div class="d-flex gap-2 flex-wrap">

        @if($report->status === 'claimed')
          <form method="POST" action="{{ route('reports.markReturned',$report->id) }}">
            @csrf
            <button class="btn btn-sm btn-success">
              <i class="bi bi-check-circle"></i> Mark Returned
            </button>
          </form>
        @endif

        @if(in_array($report->status,['claimed','returned'],true))
          <form method="POST" action="{{ route('reports.archive',$report->id) }}">
            @csrf
            <button class="btn btn-sm btn-outline-dark">
              <i class="bi bi-archive"></i> Archive
            </button>
          </form>
        @endif

      </div>
    @endif
  </div>

  {{-- CLAIM --}}
  {{-- Only the LOST reporter matched to this FOUND report may claim it.
       The finder sees their own management actions instead. --}}
  @if($report->report_type === 'found' && $claimEligibility->allowed)
    <div class="detail-card mt-3">
      <div class="detail-card-title"><i class="bi bi-person-check"></i> Claim Item</div>
      <a class="btn btn-primary w-100" href="{{ route('claims.create',$report->id) }}">
        Submit Claim
      </a>
    </div>
  @endif

  {{-- CLAIM ACTION (students) --}}
  @if(!$isStaff && count($claimableFoundIds))
    <div class="detail-card mt-3">
      <div class="detail-card-title"><i class="bi bi-person-check"></i> Claim</div>
      @foreach($claimableFoundIds as $claimableFoundId)
        <a class="btn btn-primary w-100 mb-2" href="{{ route('claims.create',$claimableFoundId) }}">
          <i class="bi bi-person-check"></i> Submit Claim
        </a>
      @endforeach
    </div>
  @endif

  {{-- MATCHES (staff only) --}}
  @if($isStaff)
  <div class="detail-card mt-3">
    <div class="detail-card-title"><i class="bi bi-link-45deg"></i> Matches</div>

    @forelse($matches as $m)
      @php
        $other = $m->lost_report_id == $report->id ? $m->found_report_id : $m->lost_report_id;
      @endphp

      <a class="match-item" href="{{ route('reports.show',$other) }}">
        <div>
          <div class="fw-semibold">Report #{{ $other }}</div>
          <div class="text-muted small">Score {{ $m->score }}</div>
        </div>
        <span class="detail-badge" style="background:rgba(0,65,199,0.08);color:#0041C7;border:1px solid rgba(0,65,199,0.15)">{{ $m->method }}</span>
      </a>
    @empty
      <div class="text-muted">No matches yet</div>
    @endforelse
  </div>
  @endif

  {{-- CLAIMS LIST --}}
  @if($claims->count() && ($isStaff || $isOwner))
  <div class="detail-card mt-3">
    <div class="detail-card-title"><i class="bi bi-people"></i> Claims History</div>
    @foreach($claims as $c)
      <div class="match-item" style="cursor:default">
        <div>
          <div class="fw-semibold">Claim #{{ $c->id }}</div>
          <a href="{{ route('claims.show', $c->id) }}" class="small text-decoration-none">View Details</a>
        </div>
        <div class="text-end">
          <span class="detail-badge" style="background:rgba(115,115,115,0.12);color:#525252;border:1px solid rgba(115,115,115,0.2)">{{ ucfirst($c->status) }}</span>
          <div class="text-muted small mt-1">{{ \Carbon\Carbon::parse($c->created_at)->diffForHumans() }}</div>
        </div>
      </div>
    @endforeach
  </div>
  @endif

</div>
</div>

@if($report->status === 'returned')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => launchConfetti(), 500);
});
</script>
@endpush
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bookmarks
    if (window.bookmarks) {
        bookmarks.updateUI();
    }

    // Initialize breadcrumbs
    if (typeof generateBreadcrumbs === 'function') {
        generateBreadcrumbs();
    }
});
</script>
@endpush
@endsection
