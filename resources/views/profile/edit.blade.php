@extends('layouts.app')

@section('title', 'My Profile · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/image-cropper.css') }}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<link href="{{ asset('css/profile.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="profile-page">
  <!-- Page Header -->
  <div class="profile-page-header">
    <div class="profile-breadcrumb">
      <a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a>
      <i class="bi bi-chevron-right"></i>
      <span>My Profile</span>
    </div>
    <div class="profile-header-content">
      <div class="profile-header-text">
        <h1>My Profile</h1>
        <p>Manage your personal information and preferences</p>
      </div>
      <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">
        <i class="bi bi-arrow-left"></i>
        <span>Back</span>
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf

    <div class="profile-grid">
      <!-- Left Sidebar - Avatar Card -->
      <div class="profile-avatar-card">
        <div class="avatar-section">
          <div class="avatar-wrapper">
            <div class="avatar-large" id="currentAvatarDisplay">
              @if(!empty($profile?->avatar_url))
                <img src="{{ asset($profile->avatar_url) }}" alt="Avatar">
              @else
                <div class="avatar-placeholder">
                  {{ strtoupper(substr($u->email,0,1)) }}
                </div>
              @endif
            </div>
            <div class="avatar-badge">
              <i class="bi bi-check-lg"></i>
            </div>
          </div>

          <div class="user-info">
            <div class="user-name">{{ $profile?->full_name ?? 'User' }}</div>
            <div class="user-email">{{ $u->email }}</div>
            <span class="user-type-badge">
              <i class="bi bi-person-badge"></i>
              @php
                $roleNames = $u->roles()->pluck('name')->toArray();
                $isAdmin = in_array('admin', $roleNames) || in_array('osa', $roleNames);
                $displayRole = $isAdmin ? 'Admin' : ($profile?->user_type ?? 'Student');
              @endphp
              {{ ucfirst($displayRole) }}
            </span>
          </div>
        </div>

        <label for="avatarInput" class="avatar-upload-btn">
          <i class="bi bi-camera-fill"></i>
          <span>Change Photo</span>
        </label>
        <input id="avatarInput" class="d-none" type="file" name="avatar" accept="image/*" onchange="handleAvatarChange(this)">

        <div class="avatar-preview-section">
          <div class="preview-label">New Photo Preview</div>
          <div id="avatarPreview"></div>
        </div>
      </div>

      <!-- Right Content - Form Sections -->
      <div class="profile-form-content">
        <!-- Personal Information -->
        <div class="form-section">
          <div class="section-header">
            <div class="section-icon">
              <i class="bi bi-person-circle"></i>
            </div>
            <div>
              <div class="section-title">Personal Information</div>
              <div class="section-subtitle">Update your personal details</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="full_name"
                     value="{{ old('full_name',$profile?->full_name) }}" 
                     placeholder="Enter your full name"
                     required>
            </div>

            <div class="form-group">
              <label class="form-label">User Type</label>
              <input class="form-control" 
                     value="@php
                       $roleNames = $u->roles()->pluck('name')->toArray();
                       $isAdmin = in_array('admin', $roleNames) || in_array('osa', $roleNames);
                       echo $isAdmin ? 'ADMIN' : strtoupper($profile?->user_type ?? 'STUDENT');
                     @endphp" 
                     disabled>
              <div class="form-text">
                <i class="bi bi-lock"></i> User type cannot be changed
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">School ID Number</label>
              <input class="form-control" name="school_id_number"
                     value="{{ old('school_id_number',$profile?->school_id_number) }}"
                     placeholder="e.g., 2024-12345">
            </div>

            <div class="form-group">
              <label class="form-label">Department ID</label>
              <input class="form-control" type="number" name="department_id"
                     value="{{ old('department_id',$profile?->department_id) }}"
                     placeholder="Enter department ID">
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section">
          <div class="section-header">
            <div class="section-icon">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <div class="section-title">Contact Information</div>
              <div class="section-subtitle">How can we reach you?</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input class="form-control" name="contact_no"
                     value="{{ old('contact_no',$profile?->contact_no) }}"
                     placeholder="e.g., 09123456789">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input class="form-control" value="{{ $u->email }}" disabled>
              <div class="form-text">
                <i class="bi bi-lock"></i> Email cannot be changed
              </div>
            </div>
          </div>

          <div class="form-row form-group-full">
            <div class="form-group">
              <label class="form-label">Address</label>
              <input class="form-control" name="address"
                     value="{{ old('address',$profile?->address) }}"
                     placeholder="Enter your full address">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="profile-actions">
      <div class="action-info">
        <i class="bi bi-shield-check"></i>
        <span>Your changes will be saved securely</span>
      </div>
      <div class="action-buttons">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
          <i class="bi bi-x-circle"></i>
          <span>Cancel</span>
        </a>
        <button class="btn btn-primary" type="submit">
          <i class="bi bi-check-circle"></i>
          <span>Save Changes</span>
        </button>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script src="{{ asset('js/image-cropper.js') }}"></script>
<script>
  function handleAvatarChange(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
      const preview = document.getElementById('avatarPreview');
      preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="avatar-preview-img">`;
      preview.style.display = 'block';
    };

    reader.readAsDataURL(file);
  }
</script>
@endpush
@endsection
