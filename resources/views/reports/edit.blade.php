@extends('layouts.app')

@section('title', 'Edit Report · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if(session('success'))
  <div class="admin-alert alert-success">
    <i class="bi bi-check-circle"></i> <div>{{ session('success') }}</div>
  </div>
@endif

@if($errors->any())
  <div class="admin-alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i> <div>Please fix the errors below</div>
  </div>
@endif

<div class="form-page-header">
  <div>
    <h1>Edit Report #{{ $report->id }}</h1>
    <div class="form-page-subtitle">Update report details</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('reports.show',$report->id) }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<form id="editForm" method="POST" action="{{ route('reports.update',$report->id) }}" class="form-section">
@csrf

<div class="form-section-header">
  <div class="form-section-icon"><i class="bi bi-pencil-square"></i></div>
  <div>
    <div class="form-section-title">Report Information</div>
    <div class="form-section-subtitle">Edit the report fields below</div>
  </div>
</div>

<div class="row g-3">
  @if($isStaff)
  <div class="col-md-12">
    <div class="p-3 bg-white border rounded">
      <label class="form-label text-primary fw-bold mb-2">Admin Status Override</label>
      <select class="form-select" name="status">
        @foreach(['pending', 'matched', 'claimed', 'returned', 'archived'] as $s)
          <option value="{{ $s }}" @selected(old('status',$report->status)==$s)>
            {{ ucfirst($s) }}
          </option>
        @endforeach
      </select>
      <div class="form-text small">
        Changing status manually may affect auto-matching behavior.
      </div>
    </div>
  </div>
  @endif

  <div class="col-md-4">
    <label class="form-label">Category</label>
    <select class="form-select" name="category_id">
      <option value="">—</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}" @selected(old('category_id',$report->category_id)==$c->id)>
          {{ $c->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Location</label>
    <select class="form-select" name="location_id">
      <option value="">—</option>
      @foreach($locations as $l)
        <option value="{{ $l->id }}" @selected(old('location_id',$report->location_id)==$l->id)>
          {{ $l->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Owner User ID</label>
    <input class="form-control" name="owner_user_id" type="number"
           value="{{ old('owner_user_id',$report->owner_user_id) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Item Name</label>
    <input class="form-control" name="item_name" value="{{ old('item_name',$report->item_name) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Brand / Model</label>
    <input class="form-control" name="brand_model" value="{{ old('brand_model',$report->brand_model) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Color</label>
    <input class="form-control" name="color" value="{{ old('color',$report->color) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Incident Date</label>
    <input type="date" class="form-control" name="incident_date"
           value="{{ old('incident_date',$report->incident_date) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Incident Time</label>
    <input type="time" class="form-control" name="incident_time"
           value="{{ old('incident_time',$report->incident_time) }}">
  </div>

  <div class="col-12">
    <label class="form-label">Description</label>
    <textarea class="form-control" rows="4" name="item_description" required>{{ old('item_description',$report->item_description) }}</textarea>
  </div>

  <div class="col-12">
    <label class="form-label">Circumstances</label>
    <textarea class="form-control" rows="3" name="circumstances">{{ old('circumstances',$report->circumstances) }}</textarea>
  </div>

  <div class="col-12">
    <label class="form-label">Contact Override</label>
    <input class="form-control" name="contact_override" value="{{ old('contact_override',$report->contact_override) }}">
  </div>
</div>

<div class="form-actions">
  <button type="submit" class="btn btn-primary">
    <i class="bi bi-save"></i> Save Changes
  </button>
</div>
</form>

{{-- PHOTOS --}}
<div class="form-section">
  <div class="form-section-header">
    <div class="form-section-icon"><i class="bi bi-images"></i></div>
    <div>
      <div class="form-section-title">Photos</div>
      <div class="form-section-subtitle">Manage report photos</div>
    </div>
  </div>

  @if($report->photos->count())
    <div class="row g-3 mb-3">
      @foreach($report->photos as $p)
        <div class="col-6 col-md-3">
          <img src="{{ asset($p->photo_url) }}" class="img-fluid rounded" alt="Report photo" style="border:1px solid var(--border-default);">
        </div>
      @endforeach
    </div>
  @else
    <div class="text-muted mb-3">No photos uploaded</div>
  @endif

  <form method="POST" action="{{ route('reports.photos.store',$report->id) }}"
        enctype="multipart/form-data" class="row g-2 align-items-end">
    @csrf
    <div class="col-12">
      <label class="form-label">Upload New Photo</label>
      <div class="photo-upload-section">
        <div class="upload-options">
          <button type="button" class="btn btn-primary btn-camera" onclick="initCameraForEdit()">
            <i class="bi bi-camera-fill"></i> Take Photo
          </button>
          <span class="upload-divider">or</span>
          <label for="photoInputEdit" class="btn btn-outline-primary">
            <i class="bi bi-upload"></i> Choose File
          </label>
          <input id="photoInputEdit" class="form-control d-none" type="file" name="photo" accept="image/*" required onchange="previewSinglePhoto(this)" />
        </div>
        <div id="singlePhotoPreview" class="mt-3"></div>
      </div>
    </div>
    <div class="col-12">
      <button class="btn btn-primary w-100">
        <i class="bi bi-upload"></i> Upload Photo
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script src="{{ asset('js/camera-capture.js') }}"></script>
<script>
  function initCameraForEdit() {
    initCamera({
      maxPhotos: 1,
      targetInput: 'input[name="photo"]'
    });
  }

  function previewSinglePhoto(input) {
    const container = document.getElementById('singlePhotoPreview');
    container.innerHTML = '';
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        container.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
@endpush
@endsection
