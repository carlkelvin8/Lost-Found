@extends('layouts.app')

@section('title', 'Activity Logs')

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
    <h1 class="h4 fw-bold mb-0">Activity Logs</h1>
    <div class="text-muted small">Audit trail</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form class="card shadow-sm mb-3" method="GET" action="{{ route('activity_logs.index') }}">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Action</label>
        <input class="form-control" name="action" value="{{ $action ?? '' }}" />
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Entity type</label>
        <input class="form-control" name="entity_type" value="{{ $entityType ?? '' }}" />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label mb-1">Entity id</label>
        <input class="form-control" name="entity_id" value="{{ $entityId ?? '' }}" />
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label mb-1">User id</label>
        <input class="form-control" name="user_id" value="{{ $userId ?? '' }}" />
      </div>
      <div class="col-12 col-md-2 text-md-end">
        <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
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
          <th>Created</th>
          <th>User</th>
          <th>Action</th>
          <th>Entity</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $l)
          <tr>
            <td>{{ $l->id }}</td>
            <td>{{ $l->created_at }}</td>
            <td>{{ $l->user_id ?? '—' }}</td>
            <td><span class="badge text-bg-secondary">{{ $l->action }}</span></td>
            <td>{{ $l->entity_type ?? '—' }} #{{ $l->entity_id ?? '—' }}</td>
            <td>{{ $l->ip_address ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted p-4">No logs</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $logs->links() }}</div>
@endsection
