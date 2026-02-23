@extends('layouts.app')

@section('title', 'Create Department')

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
  <h1 class="h4 fw-bold mb-0">Create Department</h1>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('departments.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('departments.store') }}" class="card shadow-sm">
  @csrf
  <div class="card-body p-4">
    
<div class="mb-3">
  <label class="form-label">Name</label>
  <input class="form-control" name="name" value="{{ old('name') }}" required />
</div>

    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
  </div>
</form>
@endsection
