@extends('layouts.app')

@section('title', 'Edit Role')

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
    <h1>Edit Role</h1>
    <div class="form-page-subtitle">Update role details</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('roles.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('roles.update', $role->id) }}" class="form-card">
  @csrf
  <div class="form-card-body">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input class="form-control" name="name" value="{{ old('name', $role->name) }}" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input class="form-control" name="description" value="{{ old('description', $role->description) }}" />
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
    </div>
  </div>
</form>
@endsection
