@extends('layouts.app')

@section('title', 'Create Claim')

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
      <div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 fw-bold mb-0">Submit Claim for Report #{{ $report->id }}</h1>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.show', $report->id) }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <div class="text-muted small">Item</div>
    <div class="fw-semibold">{{ $report->item_name ?? '—' }}</div>
    <div class="text-muted small mt-2">Description</div>
    <div>{{ \Illuminate\Support\Str::limit($report->item_description, 140) }}</div>
  </div>
</div>

<form method="POST" action="{{ route('claims.store') }}" class="card shadow-sm">
  @csrf
  <input type="hidden" name="report_id" value="{{ $report->id }}" />
  <div class="card-body p-4">
    <div class="mb-3">
      <label class="form-label">Proof / Explanation</label>
      <textarea class="form-control" name="proof_text" rows="6" required>{{ old('proof_text') }}</textarea>
      <div class="form-text">Minimum 20 characters</div>
    </div>
    <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Submit</button>
  </div>
</form>
@endsection
