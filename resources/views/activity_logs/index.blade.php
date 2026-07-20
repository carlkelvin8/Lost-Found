@extends('layouts.app')

@section('title', 'Activity Logs')

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
    <h1>Activity Logs</h1>
    <div class="admin-page-subtitle">Audit trail</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form class="admin-filter-card" method="GET" action="{{ route('activity_logs.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-3">
      <label class="form-label">Action</label>
      <input class="form-control" name="action" value="{{ $action ?? '' }}" />
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Entity type</label>
      <input class="form-control" name="entity_type" value="{{ $entityType ?? '' }}" />
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Entity id</label>
      <input class="form-control" name="entity_id" value="{{ $entityId ?? '' }}" />
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">User id</label>
      <input class="form-control" name="user_id" value="{{ $userId ?? '' }}" />
    </div>
    <div class="col-12 col-md-2 text-md-end">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
    </div>
  </div>
</form>

<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
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
            <td><span class="admin-badge admin-badge-secondary">{{ $l->action }}</span></td>
            <td>{{ $l->entity_type ?? '—' }} #{{ $l->entity_id ?? '—' }}</td>
            <td>{{ $l->ip_address ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="admin-empty-state">No logs</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="admin-pagination">{{ $logs->links('pagination::simple-bootstrap-5') }}</div>
@endsection
