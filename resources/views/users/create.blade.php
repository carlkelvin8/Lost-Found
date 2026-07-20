@extends('layouts.app')

@section('title', 'Create User')

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
    <h1>Create User</h1>
    <div class="form-page-subtitle">Add a new user account</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.store') }}" class="form-card">
  @csrf
  <div class="form-card-body">
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required />
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Active</label>
        <select class="form-select" name="is_active">
          <option value="1" @selected(old('is_active','1')==='1')>Active</option>
          <option value="0" @selected(old('is_active')==='0')>Disabled</option>
        </select>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required />
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Confirm password</label>
        <input class="form-control" type="password" name="password_confirmation" required />
      </div>
    </div>

    <hr class="detail-divider my-3" />

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Full name</label>
        <input class="form-control" name="full_name" value="{{ old('full_name') }}" required />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">User type</label>
        @php $ut = old('user_type', 'student'); @endphp
        <select class="form-select" name="user_type" required>
          <option value="student" @selected($ut==='student')>Student</option>
          <option value="faculty" @selected($ut==='faculty')>Faculty</option>
          <option value="staff" @selected($ut==='staff')>Staff</option>
          <option value="visitor" @selected($ut==='visitor')>Visitor</option>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Department ID</label>
        <input class="form-control" type="number" name="department_id" value="{{ old('department_id') }}" />
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">School ID number</label>
        <input class="form-control" name="school_id_number" value="{{ old('school_id_number') }}" />
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Contact no</label>
        <input class="form-control" name="contact_no" value="{{ old('contact_no') }}" />
      </div>

      <div class="col-12">
        <label class="form-label">Roles</label>
        <div class="d-flex flex-wrap gap-2">
          @foreach($roles as $r)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->name }}" id="role_{{ $r->id }}">
              <label class="form-check-label" for="role_{{ $r->id }}">{{ $r->name }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
    </div>
  </div>
</form>
@endsection
