@extends('layouts.app')

@section('title', 'My Profile · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/image-cropper.css') }}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<style>
  /* Page Container */
  .profile-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--space-2xl) var(--space-lg);
  }

  /* Page Header */
  .profile-page-header {
    margin-bottom: var(--space-2xl);
  }

  .profile-breadcrumb {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: var(--text-sm);
    color: var(--text-muted);
    margin-bottom: var(--space-md);
  }

  .profile-breadcrumb a {
    color: var(--accent-primary);
    text-decoration: none;
    transition: color var(--transition-fast);
  }

  .profile-breadcrumb a:hover {
    color: var(--accent-secondary);
  }

  .profile-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-lg);
  }

  .profile-header-text h1 {
    font-size: var(--text-4xl);
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: var(--space-xs);
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .profile-header-text p {
    color: var(--text-muted);
    font-size: var(--text-lg);
  }

  /* Main Grid Layout */
  .profile-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: var(--space-2xl);
    align-items: start;
  }

  /* Left Sidebar - Avatar Card */
  .profile-avatar-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-2xl);
    padding: var(--space-2xl);
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 100px;
    transition: all var(--transition-base);
  }

  .profile-avatar-card:hover {
    box-shadow: var(--shadow-xl);
    transform: translateY(-4px);
  }

  .avatar-section {
    text-align: center;
    margin-bottom: var(--space-xl);
  }

  .avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: var(--space-lg);
  }

  .avatar-large {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 6px solid var(--bg-secondary);
    box-shadow: 0 8px 32px rgba(0, 65, 199, 0.2), 0 4px 16px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
    position: relative;
    transition: all var(--transition-base);
  }

  .avatar-large:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 40px rgba(0, 65, 199, 0.3), 0 6px 20px rgba(0, 0, 0, 0.15);
  }

  .avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 5rem;
    font-weight: 700;
  }

  .avatar-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: 4px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  }

  .user-info {
    margin-bottom: var(--space-xl);
  }

  .user-name {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-xs);
  }

  .user-email {
    color: var(--text-muted);
    font-size: var(--text-sm);
    margin-bottom: var(--space-md);
    word-break: break-all;
  }

  .user-type-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    padding: var(--space-sm) var(--space-lg);
    background: var(--accent-primary);
    color: white;
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 12px rgba(0, 65, 199, 0.3);
  }

  .avatar-upload-btn {
    width: 100%;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    background: var(--accent-primary);
    color: white;
    border: none;
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-base);
    box-shadow: 0 4px 12px rgba(0, 65, 199, 0.3);
    position: relative;
    overflow: hidden;
  }

  .avatar-upload-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
  }

  .avatar-upload-btn:hover::before {
    width: 300px;
    height: 300px;
  }

  .avatar-upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 65, 199, 0.4);
  }

  .avatar-upload-btn span {
    position: relative;
    z-index: 1;
  }

  .avatar-upload-btn i {
    position: relative;
    z-index: 1;
    font-size: 1.25rem;
  }

  .avatar-preview-section {
    margin-top: var(--space-lg);
    padding-top: var(--space-lg);
    border-top: 1px solid var(--border-default);
  }

  .preview-label {
    font-size: var(--text-xs);
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: var(--space-md);
    text-align: center;
  }

  #avatarPreview {
    display: none;
    text-align: center;
  }

  .avatar-preview-img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--border-default);
    box-shadow: var(--shadow-md);
  }

  /* Right Content - Form Sections */
  .profile-form-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-xl);
  }

  .form-section {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-2xl);
    padding: var(--space-2xl);
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-base);
  }

  .form-section:hover {
    box-shadow: var(--shadow-lg);
    border-color: var(--border-accent);
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-lg);
    border-bottom: 2px solid var(--border-default);
  }

  .section-icon {
    width: 56px;
    height: 56px;
    background: var(--accent-primary);
    color: white;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    box-shadow: 0 4px 12px rgba(0, 65, 199, 0.3);
  }

  .section-title {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.02em;
  }

  .section-subtitle {
    font-size: var(--text-sm);
    color: var(--text-muted);
    margin-top: var(--space-xs);
  }

  /* Form Controls */
  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-lg);
    margin-bottom: var(--space-lg);
  }

  .form-row:last-child {
    margin-bottom: 0;
  }

  .form-group-full {
    grid-column: 1 / -1;
  }

  .form-label {
    display: block;
    font-size: var(--text-sm);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-md);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .form-control {
    width: 100%;
    height: 56px;
    padding: 0 var(--space-lg);
    background: var(--bg-secondary);
    border: 2px solid var(--border-default);
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    color: var(--text-primary);
    transition: all var(--transition-base);
  }

  .form-control:hover {
    border-color: var(--border-strong);
    background: white;
  }

  .form-control:focus {
    border-color: var(--accent-primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 65, 199, 0.08);
    outline: none;
    transform: translateY(-1px);
  }

  .form-control:disabled {
    background: var(--bg-tertiary);
    color: var(--text-disabled);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .form-text {
    font-size: var(--text-xs);
    color: var(--text-muted);
    margin-top: var(--space-sm);
    display: flex;
    align-items: center;
    gap: var(--space-xs);
  }

  /* Action Bar */
  .profile-actions {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-2xl);
    padding: var(--space-xl) var(--space-2xl);
    box-shadow: var(--shadow-lg);
    position: sticky;
    bottom: var(--space-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-lg);
    margin-top: var(--space-2xl);
  }

  .action-info {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    color: var(--text-muted);
    font-size: var(--text-sm);
  }

  .action-info i {
    font-size: 1.5rem;
    color: var(--accent-primary);
  }

  .action-buttons {
    display: flex;
    gap: var(--space-md);
  }

  .btn {
    height: 56px;
    padding: 0 var(--space-2xl);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    font-size: var(--text-base);
    font-weight: 600;
    border-radius: var(--radius-lg);
    border: none;
    cursor: pointer;
    transition: all var(--transition-base);
    text-decoration: none;
    position: relative;
    overflow: hidden;
  }

  .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
  }

  .btn:hover::before {
    width: 300px;
    height: 300px;
  }

  .btn span, .btn i {
    position: relative;
    z-index: 1;
  }

  .btn-outline-secondary {
    background: white;
    border: 2px solid var(--border-default);
    color: var(--text-secondary);
  }

  .btn-outline-secondary:hover {
    border-color: var(--border-strong);
    background: var(--bg-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }

  .btn-primary {
    background: var(--accent-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 65, 199, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 65, 199, 0.4);
  }

  /* Responsive */
  @media (max-width: 992px) {
    .profile-grid {
      grid-template-columns: 1fr;
      gap: var(--space-xl);
    }

    .profile-avatar-card {
      position: relative;
      top: 0;
    }

    .form-row {
      grid-template-columns: 1fr;
    }

    .profile-actions {
      flex-direction: column;
      text-align: center;
    }

    .action-buttons {
      width: 100%;
      flex-direction: column;
    }

    .btn {
      width: 100%;
    }
  }

  @media (max-width: 576px) {
    .profile-page {
      padding: var(--space-lg) var(--space-md);
    }

    .profile-header-content {
      flex-direction: column;
      align-items: flex-start;
    }

    .profile-header-text h1 {
      font-size: var(--text-3xl);
    }

    .avatar-large {
      width: 160px;
      height: 160px;
    }

    .form-section {
      padding: var(--space-xl);
    }
  }
</style>
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
              {{ $profile?->user_type ?? 'Student' }}
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
                     value="{{ strtoupper($profile?->user_type ?? 'Student') }}" 
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
@endpush
@endsection
