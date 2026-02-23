@extends('layouts.app')

@section('title', 'Edit User')

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
      @php
  $roleNames = $user->roles->pluck('name')->toArray();
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 fw-bold mb-0">Edit User #{{ $user->id }}</h1>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.update', $user->id) }}" class="card shadow-sm">
  @csrf
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required />
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label">Active</label>
        <select class="form-select" name="is_active">
          @php $act = (string) old('is_active', (string)$user->is_active); @endphp
          <option value="1" @selected($act==='1')>Active</option>
          <option value="0" @selected($act==='0')>Disabled</option>
        </select>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">New password (optional)</label>
        <input class="form-control" type="password" name="password" />
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Confirm new password</label>
        <input class="form-control" type="password" name="password_confirmation" />
      </div>

      <hr class="my-2" />

      <div class="col-12 col-md-6">
        <label class="form-label">Full name</label>
        <input class="form-control" name="full_name" value="{{ old('full_name', $user->profile?->full_name) }}" required />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">User type</label>
        @php $ut = old('user_type', $user->profile?->user_type ?? 'student'); @endphp
        <select class="form-select" name="user_type" required>
          <option value="student" @selected($ut==='student')>Student</option>
          <option value="faculty" @selected($ut==='faculty')>Faculty</option>
          <option value="staff" @selected($ut==='staff')>Staff</option>
          <option value="visitor" @selected($ut==='visitor')>Visitor</option>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Department ID</label>
        <input class="form-control" type="number" name="department_id" value="{{ old('department_id', $user->profile?->department_id) }}" />
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">School ID number</label>
        <input class="form-control" name="school_id_number" value="{{ old('school_id_number', $user->profile?->school_id_number) }}" />
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Contact no</label>
        <input class="form-control" name="contact_no" value="{{ old('contact_no', $user->profile?->contact_no) }}" />
      </div>

      <div class="col-12">
        <label class="form-label">Roles</label>
        <div class="d-flex flex-wrap gap-2">
          @foreach($roles as $r)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->name }}"
                     id="role_{{ $r->id }}" @checked(in_array($r->name, old('roles', $roleNames), true))>
              <label class="form-check-label" for="role_{{ $r->id }}">{{ $r->name }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save changes</button>
    </div>
  </div>
</form>
@endsection
