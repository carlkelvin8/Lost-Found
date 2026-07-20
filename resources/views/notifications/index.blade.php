@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
  <div class="admin-alert alert-success" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="admin-alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <div>{{ $errors->first() }}</div>
  </div>
@endif

<div class="admin-page-header">
  <div>
    <h1>Notifications</h1>
    <div class="admin-page-subtitle">Your inbox</div>
  </div>
  <form method="POST" action="{{ route('notifications.read_all') }}">
    @csrf
    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-check2-all"></i> Mark all read</button>
  </form>
</div>

<form class="admin-filter-card" method="GET" action="{{ route('notifications.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Show</label>
      <select class="form-select" name="unread">
        <option value="0" @selected((int)$onlyUnread===0)>All</option>
        <option value="1" @selected((int)$onlyUnread===1)>Unread only</option>
      </select>
    </div>
    <div class="col-12 col-md-8 text-md-end">
      <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter"></i> Apply</button>
    </div>
  </div>
</form>

<div class="admin-table-card">
  @forelse($notifications as $n)
    <div class="admin-notif-item">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2">
          <div class="admin-notif-title">{{ $n->title }}</div>
          @if($n->read_at === null)
            <span class="admin-badge admin-badge-warning">Unread</span>
          @endif
        </div>
        <div class="admin-notif-meta">{{ $n->notif_type }} · {{ $n->created_at }}</div>
        <div class="admin-notif-body">{{ $n->body }}</div>
      </div>
      <div class="admin-notif-actions">
        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
          @csrf
          <button class="btn" type="submit" title="Mark as read"><i class="bi bi-check2"></i></button>
        </form>
        <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" onsubmit="return confirm('Delete notification?');">
          @csrf
          <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
  @empty
    <div class="admin-empty-state">No notifications</div>
  @endforelse
</div>

<div class="admin-pagination">
  {{ $notifications->links() }}
</div>
@endsection
