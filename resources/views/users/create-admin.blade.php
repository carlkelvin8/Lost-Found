@extends('layouts.app')

@section('title', 'Create Admin')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<style>
  .pw-wrap{position:relative}
  .pw-wrap .form-control{padding-right:2.75rem}
  .pw-toggle{position:absolute;right:.55rem;top:50%;transform:translateY(-50%);border:none;background:var(--bg-tertiary);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;transition:all .15s}
  .pw-toggle:hover{background:var(--border-default);color:var(--text-primary)}
  .admin-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.45rem .9rem;border-radius:9999px;background:rgba(0,65,199,.08);color:#0041C7;font-size:.8125rem;font-weight:600}
</style>
@endpush

@section('content')
@if (session('success'))
  <div class="admin-alert alert-success" role="alert"><i class="bi bi-check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="admin-alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i><div>{{ $errors->first() }}</div></div>
@endif

<div class="form-page-header">
  <div>
    <h1>Create Admin</h1>
    <div class="form-page-subtitle">Create an Admin/OSA account — the user must verify their email before they can log in</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.storeAdmin') }}" class="form-card" id="createAdminForm">
  @csrf
  <div class="form-card-body" style="display:flex;flex-direction:column;gap:1.35rem">
    <div class="admin-badge">
      <i class="bi bi-shield-lock"></i> This account gets full Admin/OSA access — no account type selection needed.
    </div>

    {{-- Account --}}
    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon"><i class="bi bi-shield-lock"></i></div>
        <div><div class="form-section-title">Account credentials</div><div class="form-section-subtitle">Login details & status</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-7">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="admin@naap.edu.ph" required />
          @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-5">
          <label class="form-label">Status</label>
          <select class="form-select" name="is_active">
            <option value="1" @selected(old('is_active','1')==='1')>Active</option>
            <option value="0" @selected(old('is_active')==='0')>Disabled</option>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <div class="pw-wrap">
            <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" id="pw1" placeholder="Min. 8 characters" required />
            <button type="button" class="pw-toggle" data-target="pw1" aria-label="Toggle password"><i class="bi bi-eye"></i></button>
          </div>
          @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Confirm password <span class="text-danger">*</span></label>
          <div class="pw-wrap">
            <input class="form-control" type="password" name="password_confirmation" id="pw2" placeholder="Re-type password" required />
            <button type="button" class="pw-toggle" data-target="pw2" aria-label="Toggle password"><i class="bi bi-eye"></i></button>
          </div>
        </div>
      </div>
    </div>

    {{-- Profile --}}
    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon" style="background:rgba(16,185,129,.1);color:#059669"><i class="bi bi-person-badge"></i></div>
        <div><div class="form-section-title">Profile</div><div class="form-section-subtitle">Displayed name & contact</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Full name <span class="text-danger">*</span></label>
          <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" placeholder="Juan Dela Cruz" required />
          @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Contact no</label>
          <input class="form-control" name="contact_no" value="{{ old('contact_no') }}" placeholder="0912 345 6789" />
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Cancel</a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Create admin account</button>
    </div>
  </div>
</form>

@push('scripts')
<script>
document.querySelectorAll('.pw-toggle').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const input=document.getElementById(btn.dataset.target);
    const icon=btn.querySelector('i');
    const isPw=input.type==='password';
    input.type=isPw?'text':'password';
    icon.className=isPw?'bi bi-eye-slash':'bi bi-eye';
  });
});
</script>
@endpush
@endsection