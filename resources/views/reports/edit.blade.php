@extends('layouts.app')

@section('title', 'Edit Report · Lost & Found')

@section('content')
{{-- FLASH --}}
@if(session('success'))
  <div class="alert alert-success d-flex gap-2">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <strong><i class="bi bi-exclamation-triangle"></i> Fix the errors:</strong>
    <ul class="mb-0 mt-1">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 fw-bold mb-0">Edit Report #{{ $report->id }}</h1>
  <a class="btn btn-outline-secondary btn-sm" href="{{ route('reports.show',$report->id) }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

{{-- FORM --}}
<form id="editForm" method="POST" action="{{ route('reports.update',$report->id) }}" class="glass-card p-4 mb-4">
@csrf

<div class="section-title"><i class="bi bi-pencil-square"></i> Report Information</div>

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

<div class="sticky-actions mt-3">
  <button type="button" class="btn btn-primary px-4" onclick="confirmSave()">
    <i class="bi bi-save"></i> Save Changes
  </button>
</div>
</form>

{{-- PHOTOS --}}
<div class="glass-card p-4">
  <div class="section-title"><i class="bi bi-images"></i> Photos</div>

  @if($report->photos->count())
    <div class="row g-3 mb-3">
      @foreach($report->photos as $p)
        <div class="col-6 col-md-3">
          <div class="photo-card">
            {{-- photo_url is already full URL --}}
            <img src="{{ asset($p->photo_url) }}" class="report-photo">
            <div class="photo-actions">
              <button type="button"
                class="btn btn-sm btn-danger w-100"
                onclick="confirmDelete({{ $p->id }})">
                <i class="bi bi-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="text-muted mb-3">No photos uploaded</div>
  @endif

  <form method="POST" action="{{ route('reports.photos.store',$report->id) }}"
        enctype="multipart/form-data" class="row g-2 align-items-end">
    @csrf
    <div class="col-md-8">
      <label class="form-label">Upload New Photo</label>
      <input class="form-control" type="file" name="photo" required>
    </div>
    <div class="col-md-4">
      <button class="btn btn-outline-primary w-100">
        <i class="bi bi-upload"></i> Upload
      </button>
    </div>
  </form>
</div>
@endsection
