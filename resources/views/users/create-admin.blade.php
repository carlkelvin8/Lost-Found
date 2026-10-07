@extends('layouts.app')

@section('title', 'Create Admin Account')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<style>
  .pw-wrap{position:relative}
  .pw-wrap .form-control{padding-right:2.75rem}
  .pw-toggle{position:absolute;right:.55rem;top:50%;transform:translateY(-50%);border:none;background:var(--bg-tertiary);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;transition:all .15s}
  .pw-toggle:hover{background:var(--border-default);color:var(--text-primary)}
  .verify-note{display:flex;gap:.75rem;align-items:flex-start;border:1.5px solid rgba(0,65,199,.25);background:rgba(0,65,199,.05);border-radius:12px;padding:.9rem 1rem;font-size:.8125rem;color:var(--text-secondary);line-height:1.5}
  .verify-note i{color:var(--brand);font-size:1.05rem;margin-top:.1rem}
  .verify-note strong{color:var(--text-primary)}
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
    <h1>Create Admin Account</h1>
    <div class="form-page-subtitle">Staff Tools — new admins must verify their e-mail before they can sign in</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.storeAdmin') }}" class="form-card">
  @csrf
  <div class="form-card-body" style="display:flex;flex-direction:column;gap:1.35rem">
    <div class="verify-note">
      <i class="bi bi-envelope-check-fill"></i>
      <div>
        <strong>E-mail verification is required.</strong><br>
        The account is created <em>unverified</em>. A 6-digit code is sent to the address below and
        the admin must enter it on first login before the dashboard unlocks.
      </div>
    </div>

    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon"><i class="bi bi-shield-lock"></i></div>
        <div><div class="form-section-title">Account credentials</div><div class="form-section-subtitle">Login details</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Full name <span class="text-danger">*</span></label>
          <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" placeholder="Juan Dela Cruz" required />
          @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label">E-mail <span class="text-danger">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="admin@naap.edu.ph" required />
          @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon" style="background:rgba(16,185,129,.1);color:#059669"><i class="bi bi-person-badge"></i></div>
        <div><div class="form-section-title">Profile</div><div class="form-section-subtitle">Optional contact details</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Contact no</label>
          <input class="form-control" name="contact_no" value="{{ old('contact_no') }}" placeholder="0912 345 6789" />
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Cancel</a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-person-plus"></i> Create admin &amp; send verification e-mail</button>
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
