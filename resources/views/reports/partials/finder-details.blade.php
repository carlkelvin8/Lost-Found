@if($isStaff)
<div id="studentFinderSection" class="form-section mt-3">
  <div class="form-section-header"><div class="form-section-title">Finder / Student Surrender Details</div></div>
  <p class="form-text">If a student found the item and turned it over to Admin/OSA, record the student's details here. You remain the account submitting the report.</p>
  <input id="studentSurrenderDefault" type="hidden" name="surrendered_by_student" value="0">
  <div class="form-check mb-3">
    <input id="studentSurrenderCheckbox" class="form-check-input" type="checkbox" name="surrendered_by_student" value="1" @checked(old('surrendered_by_student', isset($report) ? $report->surrendered_by_student : false))>
    <label class="form-check-label" for="studentSurrenderCheckbox">Item surrendered by a student</label>
  </div>
  <div id="studentFinderFields" class="row g-3">
    <div class="col-md-6">
      <label for="finder_name" class="form-label">Finder's full name <span class="text-danger">*</span></label>
      <input id="finder_name" class="form-control" name="finder_name" maxlength="190" value="{{ old('finder_name', $report->finder_name ?? '') }}">
      @error('finder_name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label for="finder_student_id" class="form-label">Finder's student ID <span class="text-danger">*</span></label>
      <input id="finder_student_id" class="form-control" name="finder_student_id" maxlength="60" value="{{ old('finder_student_id', $report->finder_student_id ?? '') }}">
      @error('finder_student_id') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>
</div>
@endif
