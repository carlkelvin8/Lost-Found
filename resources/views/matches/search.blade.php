@extends('layouts.app')
@section('title', 'Manual Match Search')
@section('content')
<h1>Manual Match Search</h1>
<p>Search active reports, then use their IDs to suggest a pair. Existing matches are preserved.</p>
<a href="{{ route('matches.index') }}" class="btn btn-outline-secondary mb-3">Back to Matches</a>
@if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
<form method="GET" action="{{ route('matches.search') }}" class="row g-2 mb-3">
  <div class="col-md-4"><label for="q">Search details</label><input id="q" name="q" class="form-control" maxlength="190" value="{{ $filters['q'] ?? '' }}"></div>
  <div class="col-md-2"><label for="type">Report type</label><select id="type" name="type" class="form-select"><option value="">All</option>@foreach(['lost','found'] as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
  <div class="col-md-2"><label for="category_id">Category</label><select id="category_id" name="category_id" class="form-select"><option value="">All</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
  <div class="col-md-2"><label for="location_id">Location</label><select id="location_id" name="location_id" class="form-select"><option value="">All</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(($filters['location_id'] ?? '') == $location->id)>{{ $location->name }}</option>@endforeach</select></div>
  <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary" type="submit">Search</button></div>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Type</th><th>Item</th><th>Description</th><th>Category</th><th>Location</th></tr></thead><tbody>
@forelse($reports as $report)
<tr><td><a href="{{ route('reports.show', $report->id) }}">#{{ $report->id }}</a></td><td>{{ ucfirst($report->report_type) }}</td><td>{{ $report->item_name }}</td><td>{{ \Illuminate\Support\Str::limit($report->item_description, 140) }}</td><td>{{ $report->category?->name }}</td><td>{{ $report->location?->name }}</td></tr>
@empty <tr><td colspan="6">No active reports match your search.</td></tr> @endforelse
</tbody></table></div>
{{ $reports->links() }}
@endsection
