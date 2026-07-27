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
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.cs-header, .cs-grid, .cs-col-main, .cs-col-side, .cs-card, .cs-card-header,
.cs-card-title, .cs-card-body, .cs-badge, .cs-link, .cs-alert,
.cs-proof-text, .cs-doc-form, .cs-doc-list, .cs-doc-item, .cs-doc-icon,
.cs-doc-info, .cs-doc-name, .cs-doc-link, .cs-doc-empty,
.cs-action-form, .cs-btn, .cs-input, .cs-action-done,
.cs-summary-row, .cs-summary-label, .cs-summary-value, .cs-summary-desc,
.cs-claimant, .cs-claimant-avatar, .cs-claimant-info, .cs-claimant-name, .cs-claimant-email,
.cs-score, .cs-score-header, .cs-score-label, .cs-score-value,
.cs-score-track, .cs-score-fill, .cs-ai-rec, .cs-rec-title, .cs-rec-desc, .cs-ai-note,
.cs-type-badge, .cs-sep {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── ALERTS ── */
.cs-alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.125rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
  animation: csFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.cs-alert-success { background: rgba(16,185,129,0.08); color: #059669; border: 1px solid rgba(16,185,129,0.15); }
.cs-alert-danger { background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.15); }

/* ── HEADER ── */
.cs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  animation: csFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes csFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.cs-welcome {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.cs-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.375rem;
  flex-shrink: 0;
}

.cs-welcome-text { flex: 1; }

.cs-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.02em;
  margin: 0 0 0.25rem;
  line-height: 1.2;
}

.cs-subtitle {
  font-size: 0.9375rem;
  color: #94a3b8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cs-badge {
  display: inline-flex;
  padding: 0.2rem 0.625rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  text-transform: capitalize;
}

.cs-sep { color: #cbd5e1; }

.cs-link {
  color: #0041C7;
  text-decoration: none;
  font-weight: 600;
}

.cs-link:hover { text-decoration: underline; }

.cs-btn-back {
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

.cs-btn-back:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
}

/* ── GRID ── */
.cs-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 1.5rem;
  animation: csFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
}

/* ── CARD ── */
.cs-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
}

.cs-card + .cs-card { margin-top: 1rem; }

.cs-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.cs-card-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cs-card-title i { color: #94a3b8; }

.cs-card-body { padding: 1.25rem; }

/* ── PROOF TEXT ── */
.cs-proof-text {
  font-size: 0.9375rem;
  color: #334155;
  line-height: 1.65;
  margin: 0;
}

/* ── DOCUMENTS ── */
.cs-doc-form {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cs-file-input { display: none; }

.cs-file-label {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.4rem 0.75rem;
  background: rgba(0,65,199,0.06);
  color: #0041C7;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.cs-file-label:hover { background: rgba(0,65,199,0.12); }

.cs-file-submit {
  padding: 0.4rem 0.75rem;
  background: #0041C7;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.2s;
}

.cs-file-submit:hover { background: #0033A0; }

.cs-doc-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cs-doc-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 10px;
  transition: all 0.2s;
}

.cs-doc-item:hover { background: #f1f5f9; }

.cs-doc-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: rgba(0,65,199,0.06);
  color: #0041C7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.cs-doc-info { flex: 1; min-width: 0; }

.cs-doc-name {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
}

.cs-doc-link {
  font-size: 0.8125rem;
  color: #0041C7;
  text-decoration: none;
  font-weight: 500;
}

.cs-doc-link:hover { text-decoration: underline; }

.cs-doc-delete-btn {
  padding: 0.375rem;
  background: rgba(239,68,68,0.06);
  color: #dc2626;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.cs-doc-delete-btn:hover { background: rgba(239,68,68,0.12); }

.cs-doc-empty {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-size: 0.875rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.cs-doc-empty i { font-size: 1.5rem; color: #cbd5e1; }

/* ── ACTIONS ── */
.cs-action-form { margin-bottom: 0.75rem; }
.cs-action-form:last-child { margin-bottom: 0; }

.cs-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.cs-btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}
.cs-btn-success:hover { box-shadow: 0 4px 12px rgba(16,185,129,0.3); transform: translateY(-1px); }

.cs-btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}
.cs-btn-danger:hover { box-shadow: 0 4px 12px rgba(239,68,68,0.3); transform: translateY(-1px); }

.cs-btn-outline {
  background: white;
  color: #64748b;
  border: 1px solid #e2e8f0;
}
.cs-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; }

.cs-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  color: #334155;
  margin-bottom: 0.5rem;
  font-family: inherit;
  transition: all 0.2s;
}

.cs-input:focus { outline: none; border-color: #0041C7; box-shadow: 0 0 0 3px rgba(0,65,199,0.1); }

.cs-action-done {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem;
  background: #f8fafc;
  border-radius: 10px;
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 500;
}

/* ── SUMMARY ── */
.cs-summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.cs-summary-row:last-of-type { border-bottom: none; }

.cs-summary-label {
  font-size: 0.8125rem;
  color: #94a3b8;
  font-weight: 500;
}

.cs-summary-value {
  font-size: 0.875rem;
  color: #0f172a;
}

.cs-summary-bold { font-weight: 600; }

.cs-summary-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin-top: 0.5rem;
}

.cs-type-badge {
  display: inline-flex;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.6875rem;
  font-weight: 700;
}

.cs-type-lost { background: rgba(245,158,11,0.1); color: #b45309; }
.cs-type-found { background: rgba(16,185,129,0.1); color: #047857; }

/* ── CLAIMANT ── */
.cs-claimant {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.cs-claimant-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0041C7 0%, #0D85D8 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
  flex-shrink: 0;
  overflow: hidden;
}

.cs-claimant-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cs-claimant-info {
  display: flex;
  flex-direction: column;
}

.cs-claimant-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
}

.cs-claimant-email {
  font-size: 0.8125rem;
  color: #94a3b8;
}

/* ── AI MATCH ── */
.cs-card-ai { border-color: rgba(0,65,199,0.12); }

.cs-score { margin-bottom: 1rem; }

.cs-score-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.cs-score-label {
  font-size: 0.8125rem;
  color: #94a3b8;
  font-weight: 500;
}

.cs-score-value {
  font-size: 0.9375rem;
  font-weight: 700;
}

.cs-score-high { color: #059669; }
.cs-score-mid { color: #d97706; }
.cs-score-low { color: #dc2626; }

.cs-score-track {
  height: 8px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
}

.cs-score-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.cs-score-fill-high { background: #10b981; }
.cs-score-fill-mid { background: #f59e0b; }
.cs-score-fill-low { background: #ef4444; }

.cs-ai-rec {
  display: flex;
  gap: 0.75rem;
  padding: 0.875rem;
  border-radius: 10px;
  font-size: 0.8125rem;
}

.cs-rec-high { background: rgba(16,185,129,0.06); color: #059669; }
.cs-rec-medium { background: rgba(245,158,11,0.06); color: #d97706; }
.cs-rec-low { background: rgba(239,68,68,0.06); color: #dc2626; }

.cs-ai-rec i { font-size: 1rem; flex-shrink: 0; margin-top: 0.125rem; }

.cs-rec-title {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.cs-rec-desc {
  display: block;
  opacity: 0.8;
  line-height: 1.4;
}

.cs-ai-note {
  font-size: 0.8125rem;
  color: #94a3b8;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

/* ── RESPONSIVE ── */
@media (max-width: 960px) {
  .cs-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .cs-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .cs-welcome { width: 100%; }

  .cs-btn-back { width: 100%; justify-content: center; }

  .cs-subtitle { flex-wrap: wrap; }
}
</style>
@endpush
