@extends('layouts.app')

@section('title', 'My Profile · Lost & Found')

@push('styles')
<style>
  .profile-header-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    margin-bottom: var(--space-lg);
  }

  .profile-avatar-section {
    display: flex;
    align-items: center;
    gap: var(--space-lg);
    padding-bottom: var(--space-lg);
    margin-bottom: var(--space-lg);
    border-bottom: 1px solid var(--border-default);
  }

  .profile-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--text-primary);
    color: var(--bg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    overflow: hidden;
  }

  .profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .profile-meta h2 {
    font-size: var(--text-2xl);
    font-weight: 700;
    margin-bottom: 0.25rem;
  }

  .profile-meta .email {
    color: var(--text-muted);
    font-size: var(--text-sm);
  }

  .form-section {
    background: var(--bg-primary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    margin-bottom: var(--space-lg);
  }

  .form-actions-sticky {
    position: sticky;
    bottom: 0;
    background: var(--bg-primary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: var(--space-lg) var(--space-xl);
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-lg);
    margin-top: var(--space-xl);
  }

  .form-actions-sticky .btn {
    min-width: 160px;
  }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 fw-bold mb-0">My Profile</h1>
    <p class="text-muted mb-0">Manage your personal information and preferences</p>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
  @csrf

  <!-- Profile Header -->
  <div class="profile-header-card">
    <div class="profile-avatar-section">
      <div class="profile-avatar-large">
        @if(!empty($profile?->avatar_url))
          <img src="{{ asset($profile->avatar_url) }}" alt="Avatar">
        @else
          {{ strtoupper(substr($u->email,0,1)) }}
        @endif
      </div>
      <div class="profile-meta">
        <h2>{{ $profile?->full_name ?? 'User' }}</h2>
        <div class="email">{{ $u->email }}</div>
        <span class="badge bg-secondary mt-2 text-uppercase">
          {{ $profile?->user_type ?? 'Student' }}
        </span>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Profile Photo</label>
        <input class="form-control" type="file" name="avatar" accept="image/*">
        @if(!empty($profile?->avatar_url))
          <div class="form-text">
            <a href="{{ asset($profile->avatar_url) }}" target="_blank">View current photo</a>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Personal Information -->
  <div class="form-section">
    <h3 class="h5 fw-bold mb-4">Personal Information</h3>
    
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="full_name"
               value="{{ old('full_name',$profile?->full_name) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">User Type</label>
        <input class="form-control" value="{{ strtoupper($profile?->user_type ?? 'Student') }}" disabled>
      </div>

      <div class="col-md-6">
        <label class="form-label">School ID Number</label>
        <input class="form-control" name="school_id_number"
               value="{{ old('school_id_number',$profile?->school_id_number) }}">
      </div>

      <div class="col-md-6">
        <label class="form-label">Department ID</label>
        <input class="form-control" type="number" name="department_id"
               value="{{ old('department_id',$profile?->department_id) }}">
      </div>
    </div>
  </div>

  <!-- Contact Information -->
  <div class="form-section">
    <h3 class="h5 fw-bold mb-4">Contact Information</h3>
    
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input class="form-control" name="contact_no"
               value="{{ old('contact_no',$profile?->contact_no) }}"
               placeholder="e.g., 09123456789">
      </div>

      <div class="col-md-6">
        <label class="form-label">Email Address</label>
        <input class="form-control" value="{{ $u->email }}" disabled>
        <div class="form-text">Email cannot be changed</div>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="form-actions-sticky">
    <div class="text-muted small">
      <i class="bi bi-info-circle"></i> Changes will be saved to your profile
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle"></i> Cancel
      </a>
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-save"></i> Save Changes
      </button>
    </div>
  </div>
</form>
@endsection
