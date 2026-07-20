@extends('layouts.app')

@section('title', 'Report History · NAAP Lost & Found')

@push('styles')
<link href="{{ asset('css/form.css') }}" rel="stylesheet" />
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="form-page-header">
  <div>
    <h1>Report #{{ $reportId }}</h1>
    <div class="form-page-subtitle">Status History</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('reports.show',$reportId) }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="admin-table-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
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
            <span class="admin-badge admin-badge-secondary">
              {{ $h->old_status ?? '—' }}
            </span>
            <span style="margin:0 0.5rem;color:var(--text-muted)">→</span>
            <span class="admin-badge admin-badge-info">
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
          <td colspan="4" class="admin-empty-state">No status history found</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
</div>

<div class="admin-pagination">
  {{ $history->links() }}
</div>
@endsection
