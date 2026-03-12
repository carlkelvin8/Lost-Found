@extends('layouts.app')

@section('title', 'Gallery · NAAP Lost & Found')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 fw-bold mb-1">Lost & Found Gallery</h1>
    <p class="text-muted mb-0">Browse items reported in the campus</p>
  </div>
  
  {{-- Filter by Type --}}
  <div class="d-flex gap-2">
    <a href="{{ route('gallery.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
    <a href="{{ route('gallery.index', ['type' => 'lost']) }}" class="btn btn-sm {{ request('type') === 'lost' ? 'btn-primary' : 'btn-outline-primary' }}">Lost</a>
    <a href="{{ route('gallery.index', ['type' => 'found']) }}" class="btn btn-sm {{ request('type') === 'found' ? 'btn-primary' : 'btn-outline-primary' }}">Found</a>
  </div>
</div>

<div class="row g-4">
  @forelse($reports as $report)
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card h-100 shadow-sm border-0 overflow-hidden">
        {{-- Image --}}
        <div class="ratio ratio-1x1 bg-light position-relative">
          @if($report->photos->isNotEmpty())
            <img src="{{ $report->photos->first()->photo_url }}" 
                 class="object-fit-cover w-100 h-100" 
                 alt="{{ $report->item_name }}"
                 style="cursor: pointer;"
                 onclick="viewImage(this.src)">
          @else
            <div class="d-flex align-items-center justify-content-center text-muted">
              <i class="bi bi-image fs-1 opacity-25"></i>
            </div>
          @endif
          
          <div class="position-absolute top-0 end-0 p-2">
            <span class="badge {{ $report->report_type === 'lost' ? 'bg-danger' : 'bg-success' }}">
              {{ ucfirst($report->report_type) }}
            </span>
          </div>
        </div>
        
        {{-- Basic Info --}}
        <div class="card-body p-3">
            <h5 class="card-title h6 mb-1 text-truncate">{{ $report->item_name ?? 'Unnamed Item' }}</h5>
            <div class="small text-muted mb-2">
                <i class="bi bi-calendar"></i> {{ $report->created_at->format('M d, Y') }}
            </div>
            
            {{-- If Found, allow Claim --}}
            @if($report->report_type === 'found')
                <a href="{{ route('claims.create', $report->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-hand-index-thumb"></i> Claim This
                </a>
            @endif
        </div>
      </div>
    </div>
  @empty
    <div class="col-12 text-center py-5">
      <div class="text-muted fs-5">No items found matching your criteria</div>
    </div>
  @endforelse
</div>

<div class="mt-4">
    {{ $reports->links() }}
</div>

{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-body p-0 text-center position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
        <img src="" id="modalImage" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
    function viewImage(src) {
        document.getElementById('modalImage').src = src;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
</script>
@endpush
@endsection
