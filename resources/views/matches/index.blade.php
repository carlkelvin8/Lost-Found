@extends('layouts.app')

@section('title', 'Matches')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
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

<div class="admin-page-header">
  <div>
    <h1>Matches</h1>
    <div class="admin-page-subtitle">Suggested / Confirmed / Rejected</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form class="admin-filter-card" method="GET" action="{{ route('matches.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <option value="suggested" @selected(($status ?? '')==='suggested')>Suggested</option>
        <option value="confirmed" @selected(($status ?? '')==='confirmed')>Confirmed</option>
        <option value="rejected" @selected(($status ?? '')==='rejected')>Rejected</option>
      </select>
    </div>
    <div class="col-12 col-md-8 text-md-end">
      <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter"></i> Apply</button>
    </div>
  </div>
</form>

<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Lost</th>
          <th>Found</th>
          <th>Score</th>
          <th>Method</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($matches as $m)
          <tr>
            <td>{{ $m->id }}</td>
            <td><a href="{{ route('reports.show', $m->lost_report_id) }}">#{{ $m->lost_report_id }}</a></td>
            <td><a href="{{ route('reports.show', $m->found_report_id) }}">#{{ $m->found_report_id }}</a></td>
            <td>
              @php $scoreVal = (float) $m->score; @endphp
              @php
                $scoreClass = $scoreVal >= 70 ? 'admin-score-high' : ($scoreVal >= 45 ? 'admin-score-medium' : 'admin-score-low');
              @endphp
              <span class="admin-badge {{ $scoreClass }}">
                {{ $m->score }}%
              </span>
              <div class="text-muted" style="font-size:0.75rem;margin-top:0.25rem;">
                @if($scoreVal >= 70) High
                @elseif($scoreVal >= 45) Medium
                @else Low
                @endif
              </div>
            </td>
            <td>{{ $m->method }}</td>
            <td><span class="admin-badge admin-badge-secondary">{{ $m->status }}</span></td>
            <td>
              @if(($status ?? '')==='suggested')
                <div class="admin-btn-group">
                  <form class="d-inline" method="POST" action="{{ route('matches.confirm', $m->id) }}" onsubmit="return confirm('Confirm this match?');">
                    @csrf
                    <button class="admin-action-btn" style="color:#059669;border-color:rgba(16,185,129,0.3)" type="submit"><i class="bi bi-check2"></i></button>
                  </form>
                  <form class="d-inline" method="POST" action="{{ route('matches.reject', $m->id) }}" onsubmit="return confirm('Reject this match?');">
                    @csrf
                    <input type="hidden" name="note" value="" />
                    <button class="admin-action-btn admin-action-btn-danger" type="submit"><i class="bi bi-x-lg"></i></button>
                  </form>
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="admin-empty-state">No matches</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="admin-pagination">{{ $matches->links() }}</div>

<div class="admin-manual-card">
  <div class="card-body">
    <h6>Manual Match (Suggested)</h6>
    <form method="POST" action="{{ route('matches.manual') }}" class="row g-2 align-items-end">
      @csrf
      <div class="col-12 col-md-3">
        <label class="form-label">Lost report id</label>
        <input class="form-control" type="number" name="lost_report_id" required />
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Found report id</label>
        <input class="form-control" type="number" name="found_report_id" required />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label">Score</label>
        <input class="form-control" type="number" step="0.01" name="score" required />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label">Method</label>
        <select class="form-select" name="method" required>
          <option value="manual">manual</option>
          <option value="keyword">keyword</option>
          <option value="nlp">nlp</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-plus-circle"></i> Save</button>
      </div>
    </form>
  </div>
</div>
@endsection
