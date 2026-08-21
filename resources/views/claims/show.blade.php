@extends('layouts.app')

@section('title', 'Claim Details')

@section('content')
@php
  $roleNames = auth()->check() ? auth()->user()->roles()->pluck('name')->toArray() : [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $isPending = ($claim->status === 'pending');

  $statusConfig = match($claim->status) {
    'approved' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#059669', 'icon' => 'bi-check-circle-fill', 'label' => 'Approved'],
    'rejected' => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#dc2626', 'icon' => 'bi-x-circle-fill', 'label' => 'Rejected'],
    'cancelled' => ['bg' => 'rgba(148,163,184,0.1)', 'color' => '#64748b', 'icon' => 'bi-slash-circle', 'label' => 'Cancelled'],
    default => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#d97706', 'icon' => 'bi-hourglass-split', 'label' => 'Pending'],
  };
@endphp

@if (session('success'))
  <div class="cs-alert cs-alert-success">
    <i class="bi bi-check-circle"></i>
    <span>{{ session('success') }}</span>
  </div>
@endif

@if ($errors->any())
  <div class="cs-alert cs-alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
@endif

<!-- Header -->
<div class="cs-header">
  <div class="cs-welcome">
    <div class="cs-icon-wrap" style="background: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['color'] }}">
      <i class="bi {{ $statusConfig['icon'] }}"></i>
    </div>
    <div class="cs-welcome-text">
      <h1 class="cs-title">Claim #{{ $claim->id }}</h1>
      <p class="cs-subtitle">
        <span class="cs-badge" style="background: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['color'] }}">{{ $statusConfig['label'] }}</span>
        <span class="cs-sep">·</span>
        <a href="{{ route('reports.show', $report->id) }}" class="cs-link">Report #{{ $report->id }}</a>
      </p>
    </div>
  </div>
  <a class="cs-btn-back" href="{{ route('claims.index') }}">
    <i class="bi bi-arrow-left"></i>
    <span>Back</span>
  </a>
</div>

<div class="cs-grid">
  <!-- Left Column -->
  <div class="cs-col-main">
    <!-- Proof Text -->
    <div class="cs-card">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-chat-left-text"></i> Proof / Explanation</h3>
      </div>
      <div class="cs-card-body">
        <p class="cs-proof-text">{{ $claim->proof_text }}</p>
      </div>
    </div>

    <!-- Documents -->
    <div class="cs-card">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-paperclip"></i> Documents</h3>
        @if($isPending)
          <form method="POST" action="{{ route('claim_docs.store', $claim->id) }}" enctype="multipart/form-data" class="cs-doc-form">
            @csrf
            <input type="file" name="file" required class="cs-file-input" id="docFileInput">
            <label for="docFileInput" class="cs-file-label">
              <i class="bi bi-plus-lg"></i> Add
            </label>
            <button type="submit" class="cs-file-submit"><i class="bi bi-upload"></i></button>
          </form>
        @endif
      </div>
      <div class="cs-card-body">
        @if(count($documents))
          <div class="cs-doc-list">
            @foreach($documents as $d)
              <div class="cs-doc-item">
                <div class="cs-doc-icon">
                  <i class="bi bi-file-earmark"></i>
                </div>
                <div class="cs-doc-info">
                  <span class="cs-doc-name">{{ $d->file_type ?? 'Document' }}</span>
                  <a href="{{ $d->file_url }}" target="_blank" class="cs-doc-link">
                    <i class="bi bi-box-arrow-up-right"></i> Open
                  </a>
                </div>
                @if($isPending)
                  <form method="POST" action="{{ route('claim_docs.destroy', $d->id) }}" onsubmit="return confirm('Delete this document?');" class="cs-doc-delete">
                    @csrf
                    <button type="submit" class="cs-doc-delete-btn"><i class="bi bi-trash3"></i></button>
                  </form>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div class="cs-doc-empty">
            <i class="bi bi-inbox"></i>
            <span>No documents uploaded</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="cs-col-side">
    <!-- Actions -->
    <div class="cs-card">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-lightning"></i> Actions</h3>
      </div>
      <div class="cs-card-body">
        @if($isStaff && $isPending)
          <form method="POST" action="{{ route('claims.approve', $claim->id) }}" onsubmit="return confirm('Approve claim?');" class="cs-action-form">
            @csrf
            <button type="submit" class="cs-btn cs-btn-success">
              <i class="bi bi-check2-circle"></i> Approve Claim
            </button>
          </form>

          <form method="POST" action="{{ route('claims.reject', $claim->id) }}" onsubmit="return confirm('Reject claim?');" class="cs-action-form">
            @csrf
            <input type="text" name="note" placeholder="Reason (optional)" class="cs-input">
            <button type="submit" class="cs-btn cs-btn-danger">
              <i class="bi bi-x-circle"></i> Reject Claim
            </button>
          </form>
        @endif

        @if($claim->claimant_user_id === auth()->id() && $isPending)
          <form method="POST" action="{{ route('claims.cancel', $claim->id) }}" onsubmit="return confirm('Cancel claim?');" class="cs-action-form">
            @csrf
            <button type="submit" class="cs-btn cs-btn-outline">
              <i class="bi bi-slash-circle"></i> Cancel Claim
            </button>
          </form>
        @endif

        @if(!$isPending)
          <div class="cs-action-done">
            <i class="bi bi-check-circle"></i>
            <span>Claim already reviewed</span>
          </div>
        @endif
      </div>
    </div>

    <!-- Report Summary -->
    <div class="cs-card">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-box-seam"></i> Report Summary</h3>
      </div>
      <div class="cs-card-body">
        <div class="cs-summary-row">
          <span class="cs-summary-label">Type</span>
          <span class="cs-summary-value">
            <span class="cs-type-badge {{ $report->report_type === 'lost' ? 'cs-type-lost' : 'cs-type-found' }}">
              {{ strtoupper($report->report_type) }}
            </span>
          </span>
        </div>
        <div class="cs-summary-row">
          <span class="cs-summary-label">Item</span>
          <span class="cs-summary-value cs-summary-bold">{{ $report->item_name ?? '—' }}</span>
        </div>
        <div class="cs-summary-desc">{{ \Illuminate\Support\Str::limit($report->item_description, 140) }}</div>
      </div>
    </div>

    <!-- Claimant Info -->
    @if($claim->claimant)
    <div class="cs-card">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-person"></i> Claimant</h3>
      </div>
      <div class="cs-card-body">
        <div class="cs-claimant">
          <div class="cs-claimant-avatar">
            @if($claim->claimant->profile && $claim->claimant->profile->avatar_url)
              @php $avatarSrc = str_starts_with($claim->claimant->profile->avatar_url, 'storage/') ? substr($claim->claimant->profile->avatar_url, 8) : $claim->claimant->profile->avatar_url; @endphp
              <img src="{{ asset($avatarSrc) }}" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
              <span style="display:none">{{ strtoupper(substr($claim->claimant->email, 0, 1)) }}</span>
            @else
              {{ strtoupper(substr($claim->claimant->email, 0, 1)) }}
            @endif
          </div>
          <div class="cs-claimant-info">
            <span class="cs-claimant-name">{{ $claim->claimant->profile->full_name ?? 'Unknown' }}</span>
            <span class="cs-claimant-email">{{ $claim->claimant->email }}</span>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- AI Match Analysis -->
    @if($isStaff && $matchScore !== null)
    <div class="cs-card cs-card-ai">
      <div class="cs-card-header">
        <h3 class="cs-card-title"><i class="bi bi-cpu"></i> AI Match Analysis</h3>
      </div>
      <div class="cs-card-body">
        <div class="cs-score">
          <div class="cs-score-header">
            <span class="cs-score-label">Match Score</span>
            <span class="cs-score-value {{ $matchScore >= 70 ? 'cs-score-high' : ($matchScore >= 45 ? 'cs-score-mid' : 'cs-score-low') }}">{{ $matchScore }}%</span>
          </div>
          <div class="cs-score-track">
            <div class="cs-score-fill {{ $matchScore >= 70 ? 'cs-score-fill-high' : ($matchScore >= 45 ? 'cs-score-fill-mid' : 'cs-score-fill-low') }}" style="width: {{ $matchScore }}%"></div>
          </div>
        </div>

        <div class="cs-ai-rec cs-rec-{{ $matchRecommendation }}">
          @if($matchRecommendation === 'high')
            <i class="bi bi-check-circle-fill"></i>
            <div>
              <span class="cs-rec-title">High Confidence</span>
              <span class="cs-rec-desc">AI suggests approving this claim. Details match strongly.</span>
            </div>
          @elseif($matchRecommendation === 'medium')
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
              <span class="cs-rec-title">Medium Confidence</span>
              <span class="cs-rec-desc">Some details match. Manual verification recommended.</span>
            </div>
          @else
            <i class="bi bi-x-circle-fill"></i>
            <div>
              <span class="cs-rec-title">Low Confidence</span>
              <span class="cs-rec-desc">AI suggests rejecting. Few matching details found.</span>
            </div>
          @endif
        </div>

        <div class="cs-ai-note">
          <i class="bi bi-info-circle"></i> Based on item name, color, location, category, brand, and date.
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/claims.css') }}" rel="stylesheet" />
@endpush
