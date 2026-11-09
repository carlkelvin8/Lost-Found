@php
  $selectedCategory = old('category_id', $report->category_id ?? '');
  $selectedLocation = old('location_id', $report->location_id ?? '');
@endphp
<div class="col-12 col-md-4">
  <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
  <select id="category_id" class="form-select" name="category_id" data-searchable required aria-describedby="categoryHelp">
    <option value="">Select category</option>
    @foreach($categories as $category)
      <option value="{{ $category->id }}" @selected((string)$selectedCategory === (string)$category->id)>{{ $category->name }}</option>
    @endforeach
  </select>
  <div id="categoryHelp" class="form-text">Search existing categories. Ask staff to add a missing category.</div>
  @error('category_id') <div class="text-danger">{{ $message }}</div> @enderror
</div>
<div class="col-12 col-md-4">
  <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
  <select id="location_id" class="form-select" name="location_id" data-searchable aria-describedby="locationHelp">
    <option value="">Select location or enter details below</option>
    @foreach($locations as $location)
      <option value="{{ $location->id }}" @selected((string)$selectedLocation === (string)$location->id)>{{ $location->name }}</option>
    @endforeach
  </select>
  <div id="locationHelp" class="form-text">Choose a known place, enter a specific room, or both.</div>
  @error('location_id') <div class="text-danger">{{ $message }}</div> @enderror
</div>
<div class="col-12 col-md-4">
  <label for="location_detail" class="form-label">Building / room / specific location</label>
  <input id="location_detail" class="form-control" name="location_detail" maxlength="190" minlength="2"
         value="{{ old('location_detail', $report->location_detail ?? '') }}" placeholder="e.g., Building A Room 301" aria-describedby="locationDetailHelp">
  <div id="locationDetailHelp" class="form-text">Required if no existing location is selected. Saved with this report.</div>
  @error('location_detail') <div class="text-danger">{{ $message }}</div> @enderror
</div>
