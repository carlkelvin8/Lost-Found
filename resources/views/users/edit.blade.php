@extends('layouts.app')

@section('title', 'Edit User')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<style>
  .pw-wrap{position:relative}
  .pw-wrap .form-control{padding-right:2.75rem}
  .pw-toggle{position:absolute;right:.55rem;top:50%;transform:translateY(-50%);border:none;background:var(--bg-tertiary);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer}
  .pw-toggle:hover{background:var(--border-default);color:var(--text-primary)}
  .type-card{display:flex;gap:.75rem;flex-wrap:wrap}
  .type-option{flex:1 1 140px;min-width:140px;border:1.5px solid var(--border-default);border-radius:12px;padding:.85rem .75rem;cursor:pointer;transition:all .18s;background:white}
  .type-option:has(input:checked){border-color:var(--brand);background:rgba(0,65,199,.06)}
  .type-option-title{font-weight:700;font-size:.875rem;display:flex;align-items:center;gap:.4rem}
  .type-option-desc{font-size:.72rem;color:var(--text-muted);margin-top:.2rem}
</style>
@endpush

@section('content')
@if (session('success'))
  <div class="admin-alert alert-success" role="alert"><i class="bi bi-check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="admin-alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i><div>{{ $errors->first() }}</div></div>
@endif

@php
  $roleNames = $user->roles->pluck('name')->toArray();
  if (in_array('admin', $roleNames, true)) {
    $type = 'admin_osa';
  } elseif (in_array('osa', $roleNames, true)) {
    $type = 'admin_osa';
  } else {
    $type = (string) ($user->profile?->user_type ?? 'student');
    if ($type === 'visitor' || $type === '') $type = 'guest';
  }
  $ut = old('user_type', $type);
@endphp

<div class="form-page-header">
  <div>
    <h1>Edit User #{{ $user->id }}</h1>
    <div class="form-page-subtitle">Update account — role is single source of truth</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.update', $user->id) }}" class="form-card">
  @csrf
  <div class="form-card-body" style="display:flex;flex-direction:column;gap:1.35rem">
    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon"><i class="bi bi-shield-lock"></i></div>
        <div><div class="form-section-title">Account</div><div class="form-section-subtitle">Email, status & password</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-7">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" required />
          @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-5">
          <label class="form-label">Status</label>
          @php $act = (string) old('is_active', (string)$user->is_active); @endphp
          <select class="form-select" name="is_active">
            <option value="1" @selected($act==='1')>Active</option>
            <option value="0" @selected($act==='0')>Disabled</option>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">New password <span class="text-muted" style="font-weight:400">(leave blank to keep)</span></label>
          <div class="pw-wrap">
            <input class="form-control" type="password" name="password" id="pw1" placeholder="Min. 8 chars" />
            <button type="button" class="pw-toggle" data-target="pw1"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Confirm new password</label>
          <div class="pw-wrap">
            <input class="form-control" type="password" name="password_confirmation" id="pw2" placeholder="Re-type" />
            <button type="button" class="pw-toggle" data-target="pw2"><i class="bi bi-eye"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon" style="background:rgba(16,185,129,.1);color:#059669"><i class="bi bi-person-badge"></i></div>
        <div><div class="form-section-title">Profile</div><div class="form-section-subtitle">Personal details</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Full name <span class="text-danger">*</span></label>
          <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $user->profile?->full_name) }}" required />
          @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">School ID</label>
          <input class="form-control" name="school_id_number" value="{{ old('school_id_number', $user->profile?->school_id_number) }}" />
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Contact no</label>
          <input class="form-control" name="contact_no" value="{{ old('contact_no', $user->profile?->contact_no) }}" />
        </div>
      </div>
    </div>

    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-shield-check"></i></div>
        <div><div class="form-section-title">Account type &amp; access</div><div class="form-section-subtitle">Only one setting — there is no separate "Role" option anymore. This single choice decides the profile type and what the account can do.</div></div>
      </div>
      <label class="form-label">Account type <span class="text-danger">*</span></label>
      <div class="type-card">
        <label class="type-option"><input type="radio" name="user_type" value="student" @checked($ut==='student')> <span class="type-option-title"><i class="bi bi-mortarboard"></i> Student</span><div class="type-option-desc">Learner account</div></label>
        <label class="type-option"><input type="radio" name="user_type" value="faculty" @checked($ut==='faculty')> <span class="type-option-title"><i class="bi bi-person-workspace"></i> Faculty</span><div class="type-option-desc">Teaching staff</div></label>
        <label class="type-option"><input type="radio" name="user_type" value="staff" @checked($ut==='staff')> <span class="type-option-title"><i class="bi bi-briefcase"></i> Staff</span><div class="type-option-desc">Office staff</div></label>
        <label class="type-option"><input type="radio" name="user_type" value="guest" @checked($ut==='guest')> <span class="type-option-title"><i class="bi bi-person"></i> Guest</span><div class="type-option-desc">Non-NAAP visitor</div></label>
        <label class="type-option"><input type="radio" name="user_type" value="admin_osa" @checked($ut==='admin_osa')> <span class="type-option-title"><i class="bi bi-shield-lock"></i> Admin/OSA</span><div class="type-option-desc">Full system and Staff Tools access</div></label>
      </div>
      <div id="accessPreview" class="form-text" style="margin-top:.6rem"></div>
    </div>

    <div class="form-actions">
      <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Cancel</a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save changes</button>
    </div>
  </div>
</form>

@push('scripts')
<script>
const accessHints = {
  student: 'Student — standard access. Same functions as Faculty, Staff and Guest (reports, claims, bookmarks). No Staff Tools.',
  faculty: 'Faculty — standard access. Same functions as Student, Staff and Guest (reports, claims, bookmarks). No Staff Tools.',
  staff: 'Staff — standard access. Same functions as Student, Faculty and Guest (reports, claims, bookmarks). No Staff Tools.',
  guest: 'Guest — standard access. Same functions as Student, Faculty and Staff (reports, claims, bookmarks). No Staff Tools.',
  admin_osa: 'Admin/OSA — full system and Staff Tools access.',
};
function updateAccessPreview(){
  const v = document.querySelector('input[name="user_type"]:checked');
  document.getElementById('accessPreview').textContent = v ? accessHints[v.value] : '';
}
document.querySelectorAll('input[name="user_type"]').forEach(r=>r.addEventListener('change', updateAccessPreview));
updateAccessPreview();

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
