@extends('layouts.app')

@section('title', 'Locations')

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
    <h1 class="h4 fw-bold mb-0">Locations</h1>
    <div class="text-muted small">Manage locations</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-primary" href="{{ route('locations.create') }}"><i class="bi bi-plus-circle"></i> New</a>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Dashboard</a>
  </div>
</div>

<form class="card shadow-sm mb-3" method="GET" action="{{ route('locations.index') }}">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-6">
        <label class="form-label mb-1">Search</label>
        <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Type keyword..." />
      </div>
      <div class="col-12 col-md-6 text-md-end">
        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('locations.index') }}">Reset</a>
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
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('locations.edit', $row->id) }}"><i class="bi bi-pencil"></i></a>
              <form class="d-inline" method="POST" action="{{ route('locations.destroy', $row->id) }}" onsubmit="return confirm('Delete this record?');">
                @csrf
                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted p-4">No records</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $locations->links() }}
</div>
@endsection
