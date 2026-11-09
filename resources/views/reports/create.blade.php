@extends('layouts.app')

@section('title', 'Create Report · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/reports.css') }}" rel="stylesheet" />
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<link href="{{ asset('css/camera-capture.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="form-page-header">
  <div>
    <h1>Create Report</h1>
    <div class="form-page-subtitle">Submit a lost or found item report with complete details for faster matching.</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<form id="reportCreateForm" method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
  <div id="reportSubmitFeedback" class="alert alert-danger d-none" role="alert" tabindex="-1"></div>

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

      @include('reports.partials.location-category')

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
        <label for="item_name" class="form-label">Item name <span class="text-danger">*</span></label>
        <input class="form-control" id="item_name" name="item_name" required minlength="2" maxlength="190" value="{{ old('item_name') }}" placeholder="e.g., iPhone, Wallet, Umbrella" />
        <div class="form-text">Short label for quick scanning.</div>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Brand / Model (optional)</label>
        <input class="form-control" name="brand_model" value="{{ old('brand_model') }}" placeholder="e.g., Apple iPhone 11, Jansport" />
      </div>

      <div class="col-12 col-md-6">
        <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
        <input class="form-control" id="color" name="color" required maxlength="60" value="{{ old('color') }}" placeholder="e.g., Black, Blue, Red" />
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Contact override (optional)</label>
        <input class="form-control" name="contact_override" value="{{ old('contact_override') }}" placeholder="e.g., 09xx..., email, FB link" />
        <div class="form-text">If you want a separate contact detail for this report.</div>
        <div id="reporterNameGroup" class="mt-2 {{ $rt === 'found' ? '' : 'd-none' }}">
          <label for="reporter_name" class="form-label">{{ $isStaff ? "Finder's name" : 'Reporter name' }} <span id="reporterNameRequired" class="text-danger d-none">*</span></label>
          <input id="reporter_name" class="form-control" name="reporter_name" maxlength="190" value="{{ old('reporter_name') }}" @required($rt === 'found') @disabled($rt !== 'found')>
          <div class="form-text">Required for every Found report.</div>
        </div>
      </div>
    </div>
  </div>

  @include('reports.partials.finder-details')

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
        <label for="item_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" rows="4" id="item_description" name="item_description" required minlength="10" maxlength="5000"
          placeholder="Describe key identifiers (stickers, scratches, contents, serial/unique marks)...">{{ old('item_description') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Circumstances (optional)</label>
        <textarea class="form-control" rows="3" name="circumstances"
          placeholder="What happened? Any details that can help verify ownership?">{{ old('circumstances') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Photos (optional, multiple)</label>
        <div class="photo-upload-section drag-drop-zone" id="dragDropZone">
          <div class="drag-drop-icon">
            <i class="bi bi-cloud-arrow-up"></i>
          </div>
          <div class="drag-drop-text">Drag & drop photos here</div>
          <div class="drag-drop-hint">or <strong>browse files</strong></div>
          <div class="upload-options" style="margin-top:1rem">
            {{-- Camera capture using modal --}}
            <button type="button" class="btn btn-primary btn-camera" id="takePhotoBtn">
              <i class="bi bi-camera-fill"></i> Take Photo
            </button>
            <span class="upload-divider">or</span>
            <label for="photoInput" class="btn btn-outline-primary">
              <i class="bi bi-upload"></i> Choose Files
            </label>
            <input id="photoInput" class="d-none" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif" />
          </div>
          <div class="form-text mt-2">
            <i class="bi bi-info-circle"></i> Upload clear photos for better matching (front/back/details). Max 5 photos. Photos are optimized before upload. HEIC/HEIF conversion depends on your browser.
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
<script src="{{ asset('js/report-fields.js') }}"></script>
<script src="{{ asset('js/photo-optimizer.js') }}"></script>
<script src="{{ asset('js/camera-capture.js') }}"></script>
<script src="{{ asset('js/report-create.js') }}"></script>
<script src="{{ asset('js/finder-details.js') }}"></script>
<script src="{{ asset('js/photo-selection.js') }}"></script>
@endpush
@endsection
