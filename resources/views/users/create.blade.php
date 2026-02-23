@extends('layouts.app')

@section('title', 'Create User')

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
  <h1 class="h4 fw-bold mb-0">Create User</h1>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.store') }}" class="card shadow-sm">
  @csrf
  <div class="card-body p-4">
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

      <hr class="my-2" />

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

    <div class="mt-3">
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
    </div>
  </div>
</form>
@endsection
