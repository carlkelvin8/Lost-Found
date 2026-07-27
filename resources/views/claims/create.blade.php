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
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.cc-header, .cc-grid, .cc-card, .cc-card-header, .cc-card-title,
.cc-card-body, .cc-summary-row, .cc-summary-label, .cc-summary-desc,
.cc-alert, .cc-form-group, .cc-label, .cc-textarea, .cc-form-hint,
.cc-form-actions, .cc-btn {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── ALERTS ── */
.cc-alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.125rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
  animation: ccFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.cc-alert-success { background: rgba(16,185,129,0.08); color: #059669; border: 1px solid rgba(16,185,129,0.15); }
.cc-alert-danger { background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.15); }

/* ── HEADER ── */
.cc-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  animation: ccFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes ccFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.cc-welcome {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.cc-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: linear-gradient(135deg, #0041C7 0%, #0033A0 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.375rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,65,199,0.25);
}

.cc-welcome-text { flex: 1; }

.cc-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.02em;
  margin: 0 0 0.25rem;
  line-height: 1.2;
}

.cc-subtitle {
  font-size: 0.9375rem;
  color: #94a3b8;
  margin: 0;
}

.cc-btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.125rem;
  background: white;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.cc-btn-back:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
}

/* ── GRID ── */
.cc-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 1.5rem;
  animation: ccFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
}

/* ── CARD ── */
.cc-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
}

.cc-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.cc-card-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cc-card-title i { color: #94a3b8; }

.cc-card-body { padding: 1.25rem; }

/* ── SUMMARY ── */
.cc-summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.cc-summary-row:last-of-type { border-bottom: none; }

.cc-summary-label {
  font-size: 0.8125rem;
  color: #94a3b8;
  font-weight: 500;
}

.cc-summary-bold {
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
}

.cc-summary-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.55;
  margin-top: 0.75rem;
}

.cc-type-badge {
  display: inline-flex;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.6875rem;
  font-weight: 700;
}

.cc-type-lost { background: rgba(245,158,11,0.1); color: #b45309; }
.cc-type-found { background: rgba(16,185,129,0.1); color: #047857; }

/* ── FORM ── */
.cc-form-group { margin-bottom: 1.5rem; }

.cc-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.cc-textarea {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.9375rem;
  color: #334155;
  background: #f8fafc;
  resize: vertical;
  font-family: inherit;
  line-height: 1.6;
  transition: all 0.2s;
}

.cc-textarea:focus {
  outline: none;
  border-color: #0041C7;
  box-shadow: 0 0 0 3px rgba(0,65,199,0.1);
  background: white;
}

.cc-textarea::placeholder { color: #94a3b8; }

.cc-form-hint {
  font-size: 0.8125rem;
  color: #94a3b8;
  margin-top: 0.5rem;
}

/* ── ACTIONS ── */
.cc-form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
}

.cc-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-family: inherit;
  text-decoration: none;
}

.cc-btn-cancel {
  background: white;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.cc-btn-cancel:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
}

.cc-btn-submit {
  background: linear-gradient(135deg, #0041C7 0%, #0033A0 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0,65,199,0.2);
}

.cc-btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,65,199,0.3);
}

.cc-btn-submit:disabled {
  opacity: 0.6;
  pointer-events: none;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .cc-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .cc-welcome { width: 100%; }

  .cc-btn-back { width: 100%; justify-content: center; }

  .cc-grid { grid-template-columns: 1fr; }

  .cc-form-actions { flex-direction: column-reverse; }

  .cc-btn { width: 100%; justify-content: center; }
}
</style>
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
