@extends('layouts.app')

@section('title', 'Claims')

@section('content')
{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0">Claims</h1>
    <small class="text-muted">
      {{ $isStaff ? 'All claims' : 'My claims' }}
    </small>
  </div>
  <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

  
  {{-- Filter --}}
  <form method="GET" action="{{ route('claims.index') }}" class="card shadow-sm mb-3">
    <div class="card-body row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">Any</option>
          @foreach(['pending','approved','rejected','cancelled'] as $s)
            <option value="{{ $s }}" @selected($status === $s)>
              {{ ucfirst($s) }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-8 text-end">
        <button class="btn btn-outline-primary">
          <i class="bi bi-filter"></i> Apply
        </button>
      </div>
    </div>
  </form>

  {{-- Table --}}
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Report</th>
            <th>Status</th>
            <th>Claimant</th>
            <th>Reviewed</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($claims as $c)
            <tr>
              <td>{{ $c->id }}</td>
              <td>
                <a href="{{ route('reports.show', $c->report_id) }}">
                  #{{ $c->report_id }}
                </a>
              </td>
              <td>
                <span class="badge bg-secondary">
                  {{ ucfirst($c->status) }}
                </span>
              </td>
              <td>{{ $c->claimant->name ?? '—' }}</td>
              <td>{{ $c->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('claims.show', $c->id) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                No claims found
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $claims->links() }}
  </div>
@endsection
