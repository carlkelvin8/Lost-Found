@extends('layouts.app')

@section('title', 'Matches')

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
  <div>
    <h1 class="h4 fw-bold mb-0">Matches</h1>
    <div class="text-muted small">Suggested / Confirmed / Rejected</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form class="card shadow-sm mb-3" method="GET" action="{{ route('matches.index') }}">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label mb-1">Status</label>
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
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-striped align-middle mb-0">
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
            <td><span class="badge text-bg-primary">{{ $m->score }}</span></td>
            <td>{{ $m->method }}</td>
            <td><span class="badge text-bg-secondary">{{ $m->status }}</span></td>
            <td class="text-end">
              @if(($status ?? '')==='suggested')
                <form class="d-inline" method="POST" action="{{ route('matches.confirm', $m->id) }}" onsubmit="return confirm('Confirm this match?');">
                  @csrf
                  <button class="btn btn-sm btn-outline-success" type="submit"><i class="bi bi-check2"></i></button>
                </form>

                <form class="d-inline" method="POST" action="{{ route('matches.reject', $m->id) }}" onsubmit="return confirm('Reject this match?');">
                  @csrf
                  <input type="hidden" name="note" value="" />
                  <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-x-lg"></i></button>
                </form>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted p-4">No matches</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $matches->links() }}</div>

<div class="card shadow-sm mt-4">
  <div class="card-body">
    <h2 class="h6 fw-bold">Manual Match (Suggested)</h2>
    <form method="POST" action="{{ route('matches.manual') }}" class="row g-2 align-items-end">
      @csrf
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Lost report id</label>
        <input class="form-control" type="number" name="lost_report_id" required />
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Found report id</label>
        <input class="form-control" type="number" name="found_report_id" required />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label mb-1">Score</label>
        <input class="form-control" type="number" step="0.01" name="score" required />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label mb-1">Method</label>
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
