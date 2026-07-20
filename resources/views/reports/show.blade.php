@extends('layouts.app')

@section('title', 'Report #{{ $report->id }} · NAAP Lost & Found')

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
  <div class="d-flex gap-2">
    @if($isStaff || $isOwner)
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

  {{-- PHOTOS --}}
  <div class="detail-card mt-3">
    <div class="detail-card-title"><i class="bi bi-images"></i> Photos</div>

    @if($report->photos->count())
      <div class="detail-photo-grid">
        @foreach($report->photos as $p)
          <img src="{{ asset($p->photo_url) }}" alt="Report photo">
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
  @if($report->report_type === 'found')
    <div class="detail-card mt-3">
      <div class="detail-card-title"><i class="bi bi-person-check"></i> Claim Item</div>
      <a class="btn btn-primary w-100" href="{{ route('claims.create',$report->id) }}">
        Submit Claim
      </a>
    </div>
  @endif

  {{-- MATCHES --}}
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
@endsection
