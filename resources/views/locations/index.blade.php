@extends('layouts.app')

@section('title', 'Locations')

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
    <h1>Locations</h1>
    <div class="admin-page-subtitle">Manage locations</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="{{ route('locations.create') }}"><i class="bi bi-plus-circle"></i> New</a>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Dashboard</a>
  </div>
</div>

<form class="admin-filter-card" method="GET" action="{{ route('locations.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-6">
      <label class="form-label">Search</label>
      <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Type keyword..." />
    </div>
    <div class="col-12 col-md-6 text-md-end">
      <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
      <a class="btn btn-outline-secondary" href="{{ route('locations.index') }}">Reset</a>
    </div>
  </div>
</form>

<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Details</th>
          <th>Lat</th>
          <th>Lng</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($locations as $row)
          <tr>
            <td>{{ $row->id }}</td>
            <td>{{ $row->name }}</td>
            <td>{{ $row->details ?? '' }}</td>
            <td>{{ $row->latitude ?? '' }}</td>
            <td>{{ $row->longitude ?? '' }}</td>
            <td>
              <div class="admin-btn-group">
                <a class="admin-action-btn" href="{{ route('locations.edit', $row->id) }}"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="{{ route('locations.destroy', $row->id) }}" onsubmit="return confirm('Delete this record?');">
                  @csrf
                  <button class="admin-action-btn admin-action-btn-danger" type="submit"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="admin-empty-state">No records</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="admin-pagination">
  {{ $locations->links() }}
</div>
@endsection
