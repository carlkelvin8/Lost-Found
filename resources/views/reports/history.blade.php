@extends('layouts.app')

@section('title', 'Report History · Lost & Found')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 fw-bold mb-0">Report #{{ $reportId }}</h1>
      <div class="text-muted small">Status History</div>
    </div>
    <a href="{{ route('reports.show',$reportId) }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  <div class="glass">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Status Change</th>
            <th>Changed By</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
        @forelse($history as $h)
          <tr>
            <td>
              <div class="fw-semibold">{{ $h->changed_at }}</div>
            </td>

            <td>
              <span class="badge bg-secondary">
                {{ $h->old_status ?? '—' }}
              </span>
              <span class="mx-1 arrow">→</span>
              <span class="badge bg-primary">
                {{ $h->new_status }}
              </span>
            </td>

            <td>
              {{ $h->changed_by_user_id ?? 'System' }}
            </td>

            <td>
              {{ $h->note ?? '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted p-4">
              No status history found
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $history->links() }}
  </div>
@endsection
