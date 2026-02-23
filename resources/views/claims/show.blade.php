@extends('layouts.app')

@section('title', 'Claim Details')

@section('content')
@if (session('success'))
  <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Please fix the errors below</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif
      @php
  $isPending = ($claim->status === 'pending');
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0">Claim #{{ $claim->id }}</h1>
    <div class="text-muted small">
      Status: <span class="badge text-bg-secondary">{{ $claim->status }}</span> · Report: <a href="{{ route('reports.show', $report->id) }}">#{{ $report->id }}</a>
    </div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('claims.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Proof text</div>
        <div class="mt-1">{{ $claim->proof_text }}</div>
      </div>
    </div>

    <div class="card shadow-sm mt-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <h2 class="h6 fw-bold mb-0">Documents</h2>
          @if($isPending)
            <form method="POST" action="{{ route('claim_docs.store', $claim->id) }}" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
              @csrf
              <input class="form-control form-control-sm" type="file" name="file" required />
              <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-upload"></i></button>
            </form>
          @endif
        </div>

        @if(count($documents))
          <div class="list-group mt-2">
            @foreach($documents as $d)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="overflow-hidden me-3">
                  <div class="fw-semibold text-truncate" title="{{ $d->file_type }}">{{ $d->file_type ?? 'Document' }}</div>
                  <a href="{{ $d->file_url }}" target="_blank" class="small text-decoration-none">
                    <i class="bi bi-box-arrow-up-right"></i> Open File
                  </a>
                </div>
                @if($isPending)
                  <form method="POST" action="{{ route('claim_docs.destroy', $d->id) }}" onsubmit="return confirm('Delete this document?');">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                  </form>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div class="text-muted mt-2">No documents</div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 fw-bold">Actions</h2>

        @if($isStaff && $isPending)
          <form method="POST" action="{{ route('claims.approve', $claim->id) }}" onsubmit="return confirm('Approve claim?');" class="mb-2">
            @csrf
            <button class="btn btn-success w-100" type="submit"><i class="bi bi-check2-circle"></i> Approve</button>
          </form>

          <form method="POST" action="{{ route('claims.reject', $claim->id) }}" onsubmit="return confirm('Reject claim?');" class="mb-2">
            @csrf
            <input class="form-control mb-2" name="note" placeholder="note (optional)" />
            <button class="btn btn-danger w-100" type="submit"><i class="bi bi-x-circle"></i> Reject</button>
          </form>
        @endif

        @if($isOwner && $isPending)
          <form method="POST" action="{{ route('claims.cancel', $claim->id) }}" onsubmit="return confirm('Cancel claim?');">
            @csrf
            <button class="btn btn-outline-secondary w-100" type="submit"><i class="bi bi-slash-circle"></i> Cancel</button>
          </form>
        @endif

        @if(!$isPending)
          <div class="text-muted">Claim already reviewed.</div>
        @endif
      </div>
    </div>

    <div class="card shadow-sm mt-3">
      <div class="card-body">
        <h2 class="h6 fw-bold">Report Summary</h2>
        <div><span class="text-muted">Type:</span> <span class="fw-semibold">{{ strtoupper($report->report_type) }}</span></div>
        <div><span class="text-muted">Item:</span> <span class="fw-semibold">{{ $report->item_name ?? '—' }}</span></div>
        <div class="text-muted small mt-2">{{ \Illuminate\Support\Str::limit($report->item_description, 120) }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
