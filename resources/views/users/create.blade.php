@extends('layouts.app')

@section('title', 'Create User')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<style>
  .role-pill{position:relative;display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1rem;border:1.5px solid var(--border-default);border-radius:9999px;font-size:.8125rem;font-weight:600;cursor:pointer;transition:all .18s;background:white;color:var(--text-secondary);user-select:none}
  .role-pill:has(input:checked){background:var(--brand);border-color:var(--brand);color:white;box-shadow:0 2px 10px rgba(0,65,199,.18)}
  .role-pill input{position:absolute;opacity:0;pointer-events:none}
  .role-pill i{font-size:.95rem}
  .pw-wrap{position:relative}
  .pw-wrap .form-control{padding-right:2.75rem}
  .pw-toggle{position:absolute;right:.55rem;top:50%;transform:translateY(-50%);border:none;background:var(--bg-tertiary);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;transition:all .15s}
  .pw-toggle:hover{background:var(--border-default);color:var(--text-primary)}
  .type-card{display:flex;gap:.75rem;flex-wrap:wrap}
  .type-option{flex:1 1 140px;min-width:140px;border:1.5px solid var(--border-default);border-radius:12px;padding:.85rem .75rem;cursor:pointer;transition:all .18s;background:white}
  .type-option:has(input:checked){border-color:var(--brand);background:rgba(0,65,199,.06);box-shadow:0 2px 8px rgba(0,65,199,.08)}
  .type-option input{accent-color:var(--brand)}
  .type-option-title{font-weight:700;font-size:.875rem;color:var(--text-primary);display:flex;align-items:center;gap:.4rem}
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

<div class="form-page-header">
  <div>
    <h1>Create User</h1>
    <div class="form-page-subtitle">Add a new account — one account type sets both profile and access</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('users.store') }}" class="form-card" id="createUserForm">
  @csrf
  <div class="form-card-body" style="display:flex;flex-direction:column;gap:1.35rem">
    {{-- Account --}}
    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon"><i class="bi bi-shield-lock"></i></div>
        <div><div class="form-section-title">Account credentials</div><div class="form-section-subtitle">Login details & status</div></div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-7">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="user@naap.edu.ph" required />
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
          <label class="form-label">School ID</label>
          <input class="form-control" name="school_id_number" value="{{ old('school_id_number') }}" placeholder="2026-XXXX" />
        </div>
        <div class="col-12">
          <label class="form-label">Contact no</label>
          <input class="form-control" name="contact_no" value="{{ old('contact_no') }}" placeholder="0912 345 6789" />
        </div>
      </div>
    </div>

    {{-- Account type (single control: profile type + access) --}}
    <div>
      <div class="form-section-header" style="margin-bottom:.85rem">
        <div class="form-section-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-shield-check"></i></div>
        <div><div class="form-section-title">Account type &amp; access</div><div class="form-section-subtitle">Only one setting — there is no separate "Role" option anymore. This single choice decides the profile type and what the account can do.</div></div>
      </div>

      @php $ut = old('user_type','student'); @endphp
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
      <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Create account</button>
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
