@extends('layouts.app')

@section('title', 'My Profile · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/image-cropper.css') }}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* Reset profile styles */
.profile-page, .profile-header, .profile-breadcrumb, .profile-header-row,
.profile-grid, .profile-aside, .profile-card, .profile-avatar-wrap,
.profile-avatar, .profile-avatar-initial, .profile-avatar-overlay,
.profile-user-info, .profile-user-name, .profile-user-email, .profile-user-role,
.profile-avatar-upload, .profile-main, .profile-section, .profile-section-header,
.profile-section-icon, .profile-section-title, .profile-section-subtitle,
.profile-form-grid, .profile-field, .profile-field-full, .profile-label,
.profile-input, .profile-select, .profile-input-icon, .profile-help,
.profile-actions, .profile-action-info, .profile-action-btns,
.profile-btn, .profile-btn-ghost, .profile-btn-primary,
.profile-avatar-cropper, .cropper-preview-wrap,
.profile-empty-avatar {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

:root {
  --p-blue: #0041C7;
  --p-blue-dark: #0033A0;
  --p-blue-glow: rgba(0,65,199,0.12);
  --p-gray-50: #f8fafc;
  --p-gray-100: #f1f5f9;
  --p-gray-200: #e2e8f0;
  --p-gray-300: #cbd5e1;
  --p-gray-400: #94a3b8;
  --p-gray-500: #64748b;
  --p-gray-800: #1e293b;
  --p-gray-900: #0f172a;
  --p-green: #10b981;
  --p-radius: 16px;
  --p-radius-sm: 12px;
  --p-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
  --p-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
  --p-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.06), 0 4px 6px -4px rgba(0,0,0,0.04);
  --p-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── PAGE ── */
.profile-page {
  width: 100%;
  max-width: 100%;
  padding: 2rem 1.5rem;
  overflow-x: hidden;
  box-sizing: border-box;
}

/* ── HEADER ── */
.profile-header {
  margin-bottom: 2rem;
  animation: pFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes pFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: var(--p-gray-400);
  margin-bottom: 1rem;
}

.profile-breadcrumb a {
  color: var(--p-blue);
  text-decoration: none;
  transition: var(--p-transition);
}

.profile-breadcrumb a:hover { opacity: 0.7; }

.profile-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.profile-header-text h1 {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--p-gray-900);
  letter-spacing: -0.03em;
  margin: 0 0 0.25rem;
}

.profile-header-text p {
  font-size: 0.9375rem;
  color: var(--p-gray-400);
  margin: 0;
}

/* ── GRID ── */
.profile-grid {
  display: grid;
  grid-template-columns: minmax(280px, 320px) minmax(0, 1fr);
  gap: 1.5rem;
  align-items: start;
}

/* ── ASIDE (Avatar Card) ── */
.profile-aside {
  position: sticky;
  top: 100px;
}

.profile-card {
  background: white;
  border: 1px solid var(--p-gray-200);
  border-radius: var(--p-radius);
  overflow: hidden;
}

/* ── AVATAR ── */
.profile-avatar-wrap {
  position: relative;
  padding: 2rem 2rem 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: linear-gradient(180deg, rgba(0,65,199,0.03) 0%, transparent 100%);
}

.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid white;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  background: linear-gradient(135deg, var(--p-blue) 0%, #0D85D8 100%);
  position: relative;
  transition: var(--p-transition);
}

.profile-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 24px rgba(0,65,199,0.2);
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-avatar-initial {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
}

.profile-avatar-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: var(--p-transition);
  cursor: pointer;
}

.profile-avatar:hover .profile-avatar-overlay {
  opacity: 1;
}

.profile-avatar-overlay i {
  color: white;
  font-size: 1.5rem;
}

/* ── USER INFO ── */
.profile-user-info {
  text-align: center;
  padding: 0 1.5rem 1.5rem;
}

.profile-user-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--p-gray-900);
  margin-bottom: 0.25rem;
}

.profile-user-email {
  font-size: 0.8125rem;
  color: var(--p-gray-400);
  margin-bottom: 0.75rem;
  word-break: break-all;
}

.profile-user-role {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.875rem;
  background: var(--p-blue-glow);
  color: var(--p-blue);
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ── AVATAR UPLOAD ── */
.profile-avatar-upload {
  padding: 1.25rem;
  border-top: 1px solid var(--p-gray-100);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.profile-btn-upload {
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, var(--p-blue) 0%, var(--p-blue-dark) 100%);
  color: white;
  border: none;
  border-radius: var(--p-radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: var(--p-transition);
  position: relative;
  overflow: hidden;
}

.profile-btn-upload::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 60%);
  opacity: 0;
  transition: opacity 0.25s;
}

.profile-btn-upload:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,65,199,0.3);
}

.profile-btn-upload:hover::before { opacity: 1; }

.profile-btn-upload i { position: relative; z-index: 1; }
.profile-btn-upload span { position: relative; z-index: 1; }

.profile-upload-hint {
  font-size: 0.75rem;
  color: var(--p-gray-400);
}

/* ── AVATAR PREVIEW ── */
.profile-cropper {
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--p-gray-100);
}

.profile-cropper-label {
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--p-gray-400);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.75rem;
  text-align: center;
}

.cropper-preview-wrap {
  display: none;
  text-align: center;
}

.cropper-preview-wrap img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--p-gray-200);
  box-shadow: var(--p-shadow);
}

/* ── MAIN CONTENT ── */
.profile-main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ── SECTION ── */
.profile-section {
  background: white;
  border: 1px solid var(--p-gray-200);
  border-radius: var(--p-radius);
  overflow: visible;
  animation: pFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.profile-section:nth-child(1) { animation-delay: 0.05s; }
.profile-section:nth-child(2) { animation-delay: 0.1s; }

.profile-section-header {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--p-gray-100);
}

.profile-section-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: var(--p-blue-glow);
  color: var(--p-blue);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.profile-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--p-gray-900);
}

.profile-section-subtitle {
  font-size: 0.8125rem;
  color: var(--p-gray-400);
  margin-top: 0.125rem;
}

/* ── FORM GRID ── */
.profile-form-grid {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.profile-field-full { grid-column: 1 / -1; }

.profile-field {
  min-width: 0;
}

.profile-label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--p-gray-800);
  margin-bottom: 0.5rem;
}

.profile-input-wrap {
  position: relative;
}

.profile-input,
.profile-select {
  width: 100%;
  height: 48px;
  padding: 0 1rem;
  border: 2px solid var(--p-gray-200);
  border-radius: var(--p-radius-sm);
  font-size: 0.9rem;
  font-family: inherit;
  color: var(--p-gray-800);
  background: var(--p-gray-50);
  transition: var(--p-transition);
  outline: none;
}

.profile-input:hover,
.profile-select:hover {
  border-color: var(--p-gray-300);
  background: white;
}

.profile-input:focus,
.profile-select:focus {
  border-color: var(--p-blue);
  background: white;
  box-shadow: 0 0 0 4px var(--p-blue-glow);
}

.profile-input:disabled,
.profile-select:disabled {
  background: var(--p-gray-100);
  color: var(--p-gray-400);
  cursor: not-allowed;
  opacity: 0.7;
}

.profile-input::placeholder { color: var(--p-gray-300); }

.profile-input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--p-gray-400);
  font-size: 0.9375rem;
  pointer-events: none;
  transition: color 0.2s;
}

.profile-input-wrap:focus-within .profile-input-icon {
  color: var(--p-blue);
}

.profile-input-has-icon { padding-left: 2.5rem !important; }

.profile-help {
  font-size: 0.75rem;
  color: var(--p-gray-400);
  margin-top: 0.375rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.profile-help i { font-size: 0.6875rem; }

/* Select */
.profile-select-wrap {
  position: relative;
}

.profile-select {
  appearance: none;
  padding-right: 2.5rem;
}

.profile-select-arrow {
  position: absolute;
  right: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--p-gray-400);
  pointer-events: none;
  font-size: 0.75rem;
}

/* ── ACTION BAR ── */
.profile-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: white;
  border: 1px solid var(--p-gray-200);
  border-radius: var(--p-radius);
  position: sticky;
  bottom: 1rem;
  box-shadow: var(--p-shadow-md);
  animation: pFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
  z-index: 10;
}

.profile-action-info {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.8125rem;
  color: var(--p-gray-400);
}

.profile-action-info i {
  color: var(--p-green);
  font-size: 1rem;
}

.profile-action-btns {
  display: flex;
  gap: 0.75rem;
}

.profile-btn {
  height: 44px;
  padding: 0 1.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: var(--p-radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: var(--p-transition);
  text-decoration: none;
  border: none;
}

.profile-btn-ghost {
  background: var(--p-gray-100);
  color: var(--p-gray-500);
  border: 1px solid var(--p-gray-200);
}

.profile-btn-ghost:hover {
  background: var(--p-gray-200);
  color: var(--p-gray-800);
}

.profile-btn-primary {
  background: linear-gradient(135deg, var(--p-blue) 0%, var(--p-blue-dark) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0,65,199,0.2);
}

.profile-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,65,199,0.3);
  color: white;
}

.profile-btn-primary:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(0,65,199,0.2);
}

/* Loading */
.profile-btn-primary.loading {
  pointer-events: none;
  opacity: 0.85;
}
.profile-btn-primary.loading span,
.profile-btn-primary.loading i { opacity: 0; }
.profile-btn-primary.loading::after {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: pSpin 0.7s linear infinite;
}
@keyframes pSpin { to { transform: rotate(360deg); } }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
  .profile-grid {
    grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
  }
}

@media (max-width: 900px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }

  .profile-aside { position: relative; top: 0; }

  .profile-form-grid {
    grid-template-columns: 1fr;
  }

  .profile-actions {
    flex-direction: column;
    text-align: center;
  }

  .profile-action-btns { width: 100%; }

  .profile-btn { flex: 1; }
}

@media (max-width: 560px) {
  .profile-page { padding: 1.25rem 1rem; }

  .profile-header-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .profile-form-grid {
    grid-template-columns: 1fr;
    padding: 1.25rem;
  }

  .profile-avatar { width: 100px; height: 100px; }

  .profile-user-name { font-size: 1.125rem; }

  .profile-actions {
    position: static;
    box-shadow: none;
    border: none;
    background: transparent;
    padding: 1rem 0;
  }
}
</style>
@endpush

@section('content')
@php
  $roleNames = $u->roles()->pluck('name')->toArray();
  $isAdmin = in_array('admin', $roleNames) || in_array('osa', $roleNames);
  $displayRole = $isAdmin ? 'Admin' : ($profile?->user_type ?? 'Student');
  $initial = strtoupper(substr($u->email, 0, 1));
@endphp

<div class="profile-page">

  <!-- Header -->
  <div class="profile-header">
    <div class="profile-breadcrumb">
      <a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a>
      <i class="bi bi-chevron-right"></i>
      <span>My Profile</span>
    </div>
    <div class="profile-header-row">
      <div class="profile-header-text">
        <h1>My Profile</h1>
        <p>Manage your personal information and preferences</p>
      </div>
      <a class="profile-btn profile-btn-ghost" href="{{ route('dashboard') }}">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
    @csrf

    <div class="profile-grid">

      <!-- Left - Avatar Card -->
      <div class="profile-aside">
        <div class="profile-card">
          <div class="profile-avatar-wrap">
            <label for="avatarInput" class="profile-avatar" title="Click to change photo">
              <div id="currentAvatarDisplay">
                @if(!empty($profile?->avatar_url))
                  @php $avatarSrc = str_starts_with($profile->avatar_url, 'storage/') ? substr($profile->avatar_url, 8) : $profile->avatar_url; @endphp
                  <img src="{{ asset($avatarSrc) }}" alt="Avatar" onerror="this.parentElement.innerHTML='<div class=\'profile-avatar-initial\'>{{ $initial }}</div>'">
                @else
                  <div class="profile-avatar-initial">{{ $initial }}</div>
                @endif
              </div>
              <div class="profile-avatar-overlay">
                <i class="bi bi-camera-fill"></i>
              </div>
            </label>
            <input id="avatarInput" class="d-none" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="handleAvatarChange(this)">
          </div>

          <div class="profile-user-info">
            <div class="profile-user-name">{{ $profile?->full_name ?? 'User' }}</div>
            <div class="profile-user-email">{{ $u->email }}</div>
            <span class="profile-user-role">
              <i class="bi bi-person-badge"></i> {{ ucfirst($displayRole) }}
            </span>
          </div>

          <div class="profile-avatar-upload">
            <label for="avatarInput" class="profile-btn-upload">
              <i class="bi bi-camera-fill"></i>
              <span>Change Photo</span>
            </label>
            <span class="profile-upload-hint">JPG, PNG or WebP. Max 4MB.</span>
          </div>

          <div class="profile-cropper">
            <div class="profile-cropper-label">New Photo Preview</div>
            <div id="avatarPreview" class="cropper-preview-wrap"></div>
          </div>
        </div>
      </div>

      <!-- Right - Form -->
      <div class="profile-main">

        <!-- Personal Information -->
        <div class="profile-section">
          <div class="profile-section-header">
            <div class="profile-section-icon"><i class="bi bi-person"></i></div>
            <div>
              <div class="profile-section-title">Personal Information</div>
              <div class="profile-section-subtitle">Update your personal details</div>
            </div>
          </div>

          <div class="profile-form-grid">
            <div class="profile-field">
              <label class="profile-label">Full Name</label>
              <div class="profile-input-wrap">
                <i class="bi bi-person profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" name="full_name"
                       value="{{ old('full_name', $profile?->full_name) }}"
                       placeholder="Enter your full name" required>
              </div>
            </div>

            <div class="profile-field">
              <label class="profile-label">User Type</label>
              <div class="profile-input-wrap">
                <i class="bi bi-shield-lock profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" value="{{ strtoupper($displayRole) }}" disabled>
              </div>
              <div class="profile-help"><i class="bi bi-lock"></i> Cannot be changed</div>
            </div>

            <div class="profile-field">
              <label class="profile-label">School ID</label>
              <div class="profile-input-wrap">
                <i class="bi bi-credit-card profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" name="school_id_number"
                       value="{{ old('school_id_number', $profile?->school_id_number) }}"
                       placeholder="e.g., 2024-12345">
              </div>
            </div>

            <div class="profile-field">
              <label class="profile-label">Department</label>
              <div class="profile-input-wrap">
                <i class="bi bi-building profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" name="department_id"
                       value="{{ old('department_id', $profile?->department_id) }}"
                       placeholder="Enter department ID">
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="profile-section" style="margin-bottom: 2rem;">
          <div class="profile-section-header">
            <div class="profile-section-icon"><i class="bi bi-telephone"></i></div>
            <div>
              <div class="profile-section-title">Contact Information</div>
              <div class="profile-section-subtitle">How can we reach you?</div>
            </div>
          </div>

          <div class="profile-form-grid">
            <div class="profile-field">
              <label class="profile-label">Contact Number</label>
              <div class="profile-input-wrap">
                <i class="bi bi-phone profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" name="contact_no"
                       value="{{ old('contact_no', $profile?->contact_no) }}"
                       placeholder="e.g., 09123456789">
              </div>
            </div>

            <div class="profile-field">
              <label class="profile-label">Email Address</label>
              <div class="profile-input-wrap">
                <i class="bi bi-envelope profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" value="{{ $u->email }}" disabled>
              </div>
              <div class="profile-help"><i class="bi bi-lock"></i> Cannot be changed</div>
            </div>

            <div class="profile-field profile-field-full">
              <label class="profile-label">Address</label>
              <div class="profile-input-wrap">
                <i class="bi bi-geo-alt profile-input-icon"></i>
                <input class="profile-input profile-input-has-icon" name="address"
                       value="{{ old('address', $profile?->address) }}"
                       placeholder="Enter your full address">
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Action Bar -->
    <div class="profile-actions">
      <div class="profile-action-info">
        <i class="bi bi-shield-check"></i>
        <span>Your changes will be saved securely</span>
      </div>
      <div class="profile-action-btns">
        <a href="{{ route('dashboard') }}" class="profile-btn profile-btn-ghost">
          <i class="bi bi-x-circle"></i> Cancel
        </a>
        <button class="profile-btn profile-btn-primary" type="submit" id="saveBtn">
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
  if (file.size > 4 * 1024 * 1024) {
    alert('File size must be less than 4MB');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    // Update main avatar display
    const display = document.getElementById('currentAvatarDisplay');
    display.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';

    // Update preview
    const preview = document.getElementById('avatarPreview');
    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
    preview.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

// Form submit loading state
document.getElementById('profileForm').addEventListener('submit', function() {
  const btn = document.getElementById('saveBtn');
  btn.classList.add('loading');
  btn.disabled = true;
});

// Re-enable on back button
window.addEventListener('pageshow', function() {
  const btn = document.getElementById('saveBtn');
  btn.classList.remove('loading');
  btn.disabled = false;
});
</script>
@endpush
@endsection
