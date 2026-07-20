@extends('layouts.app')

@section('title', 'Create Location')

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
    <h1>Create Location</h1>
    <div class="form-page-subtitle">Add a new location</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('locations.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('locations.store') }}" class="form-card">
  @csrf
  <div class="form-card-body">
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="{{ old('name') }}" required />
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Details</label>
        <input class="form-control" name="details" value="{{ old('details') }}" />
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Latitude</label>
        <input class="form-control" name="latitude" value="{{ old('latitude') }}" />
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Longitude</label>
        <input class="form-control" name="longitude" value="{{ old('longitude') }}" />
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
    </div>
  </div>
</form>
@endsection
