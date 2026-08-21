@extends('layouts.app')

@section('title', 'Submit Claim')

@section('content')
@php
  $reportType = $report->report_type;
@endphp

@if (session('success'))
  <div class="cc-alert cc-alert-success">
    <i class="bi bi-check-circle"></i>
    <span>{{ session('success') }}</span>
  </div>
@endif

@if ($errors->any())
  <div class="cc-alert cc-alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
@endif

<!-- Header -->
<div class="cc-header">
  <div class="cc-welcome">
    <div class="cc-icon-wrap">
      <i class="bi bi-person-check"></i>
    </div>
    <div class="cc-welcome-text">
      <h1 class="cc-title">Submit Claim</h1>
      <p class="cc-subtitle">Provide proof of ownership for Report #{{ $report->id }}</p>
    </div>
  </div>
  <a class="cc-btn-back" href="{{ route('reports.show', $report->id) }}">
    <i class="bi bi-arrow-left"></i>
    <span>Back</span>
  </a>
</div>

<div class="cc-grid">
  <!-- Report Summary -->
  <div class="cc-card">
    <div class="cc-card-header">
      <h3 class="cc-card-title"><i class="bi bi-box-seam"></i> Report Summary</h3>
    </div>
    <div class="cc-card-body">
      <div class="cc-summary-row">
        <span class="cc-summary-label">Type</span>
        <span class="cc-type-badge {{ $reportType === 'lost' ? 'cc-type-lost' : 'cc-type-found' }}">
          {{ strtoupper($reportType) }}
        </span>
      </div>
      <div class="cc-summary-row">
        <span class="cc-summary-label">Item</span>
        <span class="cc-summary-bold">{{ $report->item_name ?? '—' }}</span>
      </div>
      <div class="cc-summary-desc">{{ \Illuminate\Support\Str::limit($report->item_description, 180) }}</div>
    </div>
  </div>

  <!-- Claim Form -->
  <div class="cc-card">
    <div class="cc-card-header">
      <h3 class="cc-card-title"><i class="bi bi-chat-left-text"></i> Your Proof</h3>
    </div>
    <div class="cc-card-body">
      <form method="POST" action="{{ route('claims.store') }}" id="claimForm">
        @csrf
        <input type="hidden" name="report_id" value="{{ $report->id }}">

        <div class="cc-form-group">
          <label class="cc-label">Explanation / Proof of Ownership</label>
          <textarea
            class="cc-textarea"
            name="proof_text"
            rows="7"
            required
            minlength="20"
            placeholder="Describe how you can prove this item is yours. Include details like unique identifiers, serial numbers, or where/when you lost the item..."
          >{{ old('proof_text') }}</textarea>
          <div class="cc-form-hint">
            <span id="charCount">0</span> / 20 minimum characters
          </div>
        </div>

        <div class="cc-form-actions">
          <a href="{{ route('reports.show', $report->id) }}" class="cc-btn cc-btn-cancel">
            Cancel
          </a>
          <button type="submit" class="cc-btn cc-btn-submit" id="submitBtn">
            <i class="bi bi-send"></i>
            <span>Submit Claim</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('css/claims.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const textarea = document.querySelector('.cc-textarea');
  const charCount = document.getElementById('charCount');
  const form = document.getElementById('claimForm');
  const submitBtn = document.getElementById('submitBtn');

  if (textarea && charCount) {
    function updateCount() {
      charCount.textContent = textarea.value.length;
    }
    textarea.addEventListener('input', updateCount);
    updateCount();
  }

  if (form && submitBtn) {
    form.addEventListener('submit', function() {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Submitting...</span>';
    });
  }
});
</script>
@endpush
