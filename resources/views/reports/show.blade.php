@extends('layouts.app')

@section('title', 'Report #{{ $report->id }} · Lost & Found')

@section('content')
{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h1 class="h4 fw-bold mb-1">Report #{{ $report->id }}</h1>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <span class="badge badge-status text-bg-{{ $statusColor }}">{{ strtoupper($report->status) }}</span>
      <span class="badge text-bg-{{ $report->report_type==='lost'?'warning':'info' }}">{{ strtoupper($report->report_type) }}</span>
      <span class="text-muted small">System-managed status</span>
    </div>
  </div>

  <div class="d-flex gap-2">
    @if($isStaff || $isOwner)
      <a class="btn btn-sm btn-outline-primary" href="{{ route('reports.edit',$report->id) }}">
        <i class="bi bi-pencil"></i> Edit
      </a>
    @endif

    <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.index') }}">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<div class="row g-3">

{{-- LEFT --}}
<div class="col-lg-7">

  {{-- DETAILS --}}
  <div class="glass-card p-3">
    <div class="section-title"><i class="bi bi-box-seam"></i> Item Details</div>

    <div class="row g-2">
      <div class="col-md-6">
        <div class="meta-label">Item</div>
        <div class="meta-value">{{ $report->item_name ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="meta-label">Category</div>
        <div class="meta-value">{{ $report->category?->name ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="meta-label">Brand / Model</div>
        <div class="meta-value">{{ $report->brand_model ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="meta-label">Color</div>
        <div class="meta-value">{{ $report->color ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="meta-label">Incident Date</div>
        <div class="meta-value">{{ $report->incident_date ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="meta-label">Location</div>
        <div class="meta-value">{{ $report->location?->name ?? '—' }}</div>
      </div>
    </div>

    <hr>

    <div class="meta-label">Description</div>
    <div>{{ $report->item_description }}</div>

    @if($report->circumstances)
      <div class="meta-label mt-3">Circumstances</div>
      <div>{{ $report->circumstances }}</div>
    @endif

    @if($report->contact_override)
      <div class="meta-label mt-3">Contact</div>
      <div>{{ $report->contact_override }}</div>
    @endif
  </div>

  {{-- PHOTOS --}}
  <div class="glass-card p-3 mt-3">
    <div class="section-title"><i class="bi bi-images"></i> Photos</div>

    @if($report->photos->count())
      <div class="row g-2">
        @foreach($report->photos as $p)
          <div class="col-6 col-md-3">
          <img src="{{ asset($p->photo_url) }}" class="img-fluid rounded">

          </div>
        @endforeach
      </div>
    @else
      <div class="text-muted">No photos uploaded</div>
    @endif
  </div>

  {{-- AI ANALYSIS (STAFF ONLY) --}}
  @if($isStaff && !empty($report->ai_analysis))
    <div class="glass-card p-3 mt-3 border-info">
      <div class="section-title text-info"><i class="bi bi-cpu"></i> AI Analysis</div>
      
      @php $ai = $report->ai_analysis; @endphp

      <div class="row g-2">
        <div class="col-md-6">
          <div class="meta-label">Detected Color</div>
          <div class="meta-value">{{ $ai['color'] ?? '—' }}</div>
        </div>
        <div class="col-md-6">
          <div class="meta-label">Detected Brand</div>
          <div class="meta-value">{{ $ai['brand'] ?? '—' }}</div>
        </div>
        <div class="col-12">
          <div class="meta-label">Keywords</div>
          <div>
            @foreach($ai['keywords'] ?? [] as $k)
              <span class="badge bg-secondary opacity-75">{{ $k }}</span>
            @endforeach
          </div>
        </div>
        @if(!empty($ai['distinct_features']))
        <div class="col-12">
          <div class="meta-label">Distinct Features</div>
          <div class="small text-muted">{{ $ai['distinct_features'] }}</div>
        </div>
        @endif
      </div>
    </div>
  @endif

</div>

{{-- RIGHT --}}
<div class="col-lg-5">

  {{-- STATUS --}}
  <div class="glass-card p-3">
    <div class="section-title"><i class="bi bi-activity"></i> Status</div>

    <p class="mb-2">
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
    <div class="glass-card p-3 mt-3">
      <div class="section-title"><i class="bi bi-person-check"></i> Claim Item</div>
      <a class="btn btn-primary w-100" href="{{ route('claims.create',$report->id) }}">
        Submit Claim
      </a>
    </div>
  @endif

  {{-- MATCHES --}}
  <div class="glass-card p-3 mt-3">
    <div class="section-title"><i class="bi bi-link-45deg"></i> Matches</div>

    @forelse($matches as $m)
      @php
        $other = $m->lost_report_id == $report->id ? $m->found_report_id : $m->lost_report_id;
      @endphp

      <a
        class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 text-decoration-none"
        href="{{ route('reports.show',$other) }}"
      >
        <div>
          <div class="fw-semibold">Report #{{ $other }}</div>
          <div class="text-muted small">Score {{ $m->score }}</div>
        </div>
        <span class="badge text-bg-primary">{{ $m->method }}</span>
      </a>
    @empty
      <div class="text-muted">No matches yet</div>
    @endforelse
  </div>

  {{-- CLAIMS LIST --}}
  @if($claims->count() && ($isStaff || $isOwner))
  <div class="glass-card p-3 mt-3">
    <div class="section-title"><i class="bi bi-people"></i> Claims History</div>
    @foreach($claims as $c)
      <div class="border rounded p-2 mb-2 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold text-dark">Claim #{{ $c->id }}</span>
            <span class="badge bg-secondary">{{ ucfirst($c->status) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('claims.show', $c->id) }}" class="small text-decoration-none">View Details</a>
            <small class="text-muted">{{ \Carbon\Carbon::parse($c->created_at)->diffForHumans() }}</small>
        </div>
      </div>
    @endforeach
  </div>
  @endif

</div>
</div>
@endsection
