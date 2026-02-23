@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
@if (session('success'))
  <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
    <i class="bi bi-check-circle"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Please fix the errors below</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif
      <div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 fw-bold mb-0">Notifications</h1>
    <div class="text-muted small">Your inbox</div>
  </div>
  <div class="d-flex gap-2">
    <form method="POST" action="{{ route('notifications.read_all') }}">
      @csrf
      <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2-all"></i> Mark all read</button>
    </form>
  </div>
</div>

<form class="card shadow-sm mb-3" method="GET" action="{{ route('notifications.index') }}">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label mb-1">Show</label>
        <select class="form-select" name="unread">
          <option value="0" @selected((int)$onlyUnread===0)>All</option>
          <option value="1" @selected((int)$onlyUnread===1)>Unread only</option>
        </select>
      </div>
      <div class="col-12 col-md-8 text-md-end">
        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter"></i> Apply</button>
      </div>
    </div>
  </div>
</form>

<div class="card shadow-sm">
  <div class="list-group list-group-flush">
    @forelse($notifications as $n)
      <div class="list-group-item">
        <div class="d-flex justify-content-between align-items-start gap-3">
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2">
              <div class="fw-semibold">{{ $n->title }}</div>
              @if($n->read_at === null)
                <span class="badge text-bg-warning">Unread</span>
              @endif
            </div>
            <div class="text-muted small">{{ $n->notif_type }} · {{ $n->created_at }}</div>
            <div class="mt-2">{{ $n->body }}</div>
          </div>
          <div class="d-flex flex-column gap-2">
            <form method="POST" action="{{ route('notifications.read', $n->id) }}">
              @csrf
              <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i></button>
            </form>
            <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" onsubmit="return confirm('Delete notification?');">
              @csrf
              <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="p-4 text-center text-muted">No notifications</div>
    @endforelse
  </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
