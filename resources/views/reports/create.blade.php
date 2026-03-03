@extends('layouts.app')

@section('title', 'Create Report · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/camera-capture.css') }}" rel="stylesheet">
<style>
  .page-header-section {
    margin-bottom: var(--space-xl);
  }

  .page-header-section h1 {
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin-bottom: var(--space-sm);
  }

  .page-subtitle {
    color: var(--text-muted);
    font-size: var(--text-base);
    line-height: 1.6;
  }

  .form-section {
    background: var(--bg-primary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    margin-bottom: var(--space-lg);
  }

  .form-section-header {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-lg);
    border-bottom: 1px solid var(--border-default);
  }

  .form-section-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    font-size: 1.5rem;
    color: var(--text-primary);
  }

  .form-section-title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
  }

  .form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: var(--space-md);
    padding-top: var(--space-xl);
    margin-top: var(--space-lg);
  }
</style>
@endpush

@section('content')
<div class="page-header-section">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1>Create Report</h1>
      <p class="page-subtitle">Submit a lost or found item report with complete details for faster matching.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
  @csrf

  <!-- Section: Type & Context -->
  <div class="form-section">
    <div class="form-section-header">
      <div class="form-section-icon">
        <i class="bi bi-signpost-2"></i>
      </div>
      <div class="form-section-title">Type &amp; Context</div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-4">
        <label class="form-label">Report type</label>
        @php $rt = old('report_type', 'lost'); @endphp
        <select class="form-select" name="report_type" required>
          <option value="lost" @selected($rt==='lost')>Lost</option>
          <option value="found" @selected($rt==='found')>Found</option>
        </select>
        <div class="form-text">Choose "Lost" if you lost it, "Found" if you discovered it.</div>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
          <option value="">Select category</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected((string)old('category_id')===(string)$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Location</label>
        <select class="form-select" name="location_id">
          <option value="">Select location</option>
          @foreach($locations as $l)
            <option value="{{ $l->id }}" @selected((string)old('location_id')===(string)$l->id)>{{ $l->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Incident date</label>
        <input class="form-control" type="date" name="incident_date" value="{{ old('incident_date') }}" />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Incident time</label>
        <input class="form-control" type="time" name="incident_time" value="{{ old('incident_time') }}" />
      </div>
    </div>
  </div>

  <!-- Section: Item Details -->
  <div class="form-section">
    <div class="form-section-header">
      <div class="form-section-icon">
        <i class="bi bi-box-seam"></i>
      </div>
      <div class="form-section-title">Item Details</div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Item name</label>
        <input class="form-control" name="item_name" value="{{ old('item_name') }}" placeholder="e.g., iPhone, Wallet, Umbrella" />
        <div class="form-text">Short label for quick scanning.</div>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Brand / Model</label>
        <input class="form-control" name="brand_model" value="{{ old('brand_model') }}" placeholder="e.g., Apple iPhone 11, Jansport" />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Color</label>
        <input class="form-control" name="color" value="{{ old('color') }}" placeholder="e.g., Black, Blue, Red" />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Contact override (optional)</label>
        <input class="form-control" name="contact_override" value="{{ old('contact_override') }}" placeholder="e.g., 09xx..., email, FB link" />
        <div class="form-text">If you want a separate contact detail for this report.</div>
      </div>
    </div>
  </div>

  <!-- Section: Description & Evidence -->
  <div class="form-section">
    <div class="form-section-header">
      <div class="form-section-icon">
        <i class="bi bi-card-text"></i>
      </div>
      <div class="form-section-title">Description &amp; Photos</div>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" rows="4" name="item_description" required
          placeholder="Describe key identifiers (stickers, scratches, contents, serial/unique marks)...">{{ old('item_description') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Circumstances (optional)</label>
        <textarea class="form-control" rows="3" name="circumstances"
          placeholder="What happened? Any details that can help verify ownership?">{{ old('circumstances') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Photos (optional, multiple)</label>
        <div class="photo-upload-section">
          <div class="upload-options">
            <button type="button" class="btn btn-primary btn-camera" onclick="initCamera()">
              <i class="bi bi-camera-fill"></i> Take Photo
            </button>
            <span class="upload-divider">or</span>
            <label for="photoInput" class="btn btn-outline-primary">
              <i class="bi bi-upload"></i> Choose Files
            </label>
            <input id="photoInput" class="form-control d-none" type="file" name="photos[]" multiple accept="image/*" onchange="previewPhotos(this)" />
          </div>
          <div class="form-text mt-2">
            <i class="bi bi-info-circle"></i> Upload clear photos for better matching (front/back/details). Max 5 photos.
          </div>
          <div id="photoPreviewContainer" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">
      <i class="bi bi-x-circle"></i> Cancel
    </a>
    <button class="btn btn-primary" type="submit">
      <i class="bi bi-save"></i> Create Report
    </button>
  </div>
</form>

@push('styles')
<style>
  .photo-upload-section {
    background: var(--bg-secondary);
    border: 2px dashed var(--border-default);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    text-align: center;
    transition: all var(--transition-base);
  }

  .photo-upload-section:hover {
    border-color: var(--accent-primary);
    background: var(--bg-hover);
  }

  .upload-options {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-md);
    flex-wrap: wrap;
  }

  .btn-camera {
    min-width: 160px;
    height: 56px;
    font-size: var(--text-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
  }

  .upload-divider {
    color: var(--text-muted);
    font-weight: 500;
    padding: 0 var(--space-sm);
  }

  #photoPreviewContainer {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: var(--space-md);
  }

  .photo-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 2px solid var(--border-default);
    background: var(--bg-tertiary);
    transition: all var(--transition-base);
  }

  .photo-preview-item:hover {
    border-color: var(--accent-primary);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
  }

  .photo-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .photo-number {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: var(--accent-primary);
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 700;
    box-shadow: var(--shadow-md);
  }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/camera-capture.js') }}"></script>
<script>
  function previewPhotos(input) {
    const container = document.getElementById('photoPreviewContainer');
    container.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
      Array.from(input.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const div = document.createElement('div');
          div.className = 'photo-preview-item fade-in';
          div.innerHTML = `
            <img src="${e.target.result}" alt="Photo ${index + 1}">
            <span class="photo-number">${index + 1}</span>
          `;
          container.appendChild(div);
        };
        reader.readAsDataURL(file);
      });
    }
  }
</script>
@endpush
@endsection
