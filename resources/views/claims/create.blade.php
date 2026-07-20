@extends('layouts.app')

@section('title', 'Create Claim')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
  <div class="admin-alert alert-success" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="admin-alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <div>{{ $errors->first() }}</div>
  </div>
@endif

<div class="form-page-header">
  <div>
    <h1>Submit Claim</h1>
    <div class="form-page-subtitle">For Report #{{ $report->id }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('reports.show', $report->id) }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="detail-card mb-3">
  <div class="detail-card-title"><i class="bi bi-box-seam"></i> Report Summary</div>
  <div class="detail-label">Item</div>
  <div class="detail-value">{{ $report->item_name ?? '—' }}</div>
  <div class="detail-label mt-2">Description</div>
  <div class="detail-value">{{ \Illuminate\Support\Str::limit($report->item_description, 140) }}</div>
</div>

<form method="POST" action="{{ route('claims.store') }}" class="form-card">
  @csrf
  <input type="hidden" name="report_id" value="{{ $report->id }}" />
  <div class="form-card-body">
    <div class="mb-3">
      <label class="form-label">Proof / Explanation</label>
      <textarea class="form-control" name="proof_text" rows="6" required>{{ old('proof_text') }}</textarea>
      <div class="form-text">Minimum 20 characters</div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Submit</button>
    </div>
  </div>
</form>
@endsection
