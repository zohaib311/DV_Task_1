@extends('academic.layout')
@section('title', 'Course Offering')
@section('heading', $offering->exists ? 'Course Offering' : 'Create Course Offering')
@section('description', 'Confirm the teaching assignment and approved assessment scheme before activation.')
@section('academic-content')
    @php($locked = $offering->exists && ($offering->status !== 'planned' || $offering->enrollmentCourses()->exists()))
    <p class="academic-note"><i class="bi {{ $locked ? 'bi-lock' : 'bi-info-circle' }}"></i> {{ $locked ? 'This offering is active or has enrolled students. Its course scheme and teacher assignments are preserved.' : 'Save as planned to review assignments. Activate when ready to accept enrollments; activation locks this offering.' }}</p>
    <form method="POST" action="{{ $offering->exists ? route('academic.offerings.update', $offering) : route('academic.offerings.store') }}" id="offeringForm">
        @csrf @if($offering->exists) @method('PUT') @endif
        <fieldset @disabled($locked)>
            <div class="row g-3 mb-4">
                <div class="col-md-6"><label for="academic_term_id" class="form-label">Academic term</label><select id="academic_term_id" name="academic_term_id" class="form-select" required><option value="">Select term</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(old('academic_term_id', $offering->academic_term_id) == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }} ({{ ucfirst($term->status) }})</option>@endforeach</select></div>
                <div class="col-md-6"><label for="section_id" class="form-label">Department / Section</label><select id="section_id" name="section_id" class="form-select" required><option value="">Select section</option>@foreach($sections as $section)<option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" @selected(old('section_id', $offering->section_id) == $section->id)>{{ $section->department->name }} / {{ $section->name }}</option>@endforeach</select></div>
                <div class="col-md-9"><label for="curriculum_course_id" class="form-label">Approved curriculum course</label><select id="curriculum_course_id" name="curriculum_course_id" class="form-select" required><option value="">Select section first</option>@foreach($curriculumCourses as $course)<option value="{{ $course->id }}" data-department-id="{{ $course->curriculum->department_id }}" data-scheme="{{ $course->credit_hours }} credits · Attendance {{ $course->attendance_marks }} / Midterm {{ $course->mid_marks }} / Final {{ $course->final_marks }} · Total {{ $course->total_marks }}" @selected(old('curriculum_course_id', $offering->curriculum_course_id) == $course->id)>{{ $course->course_code }} — {{ $course->course_name }} · {{ $course->curriculum->semester }} · {{ $course->curriculum->version }}</option>@endforeach</select></div>
                <div class="col-md-3"><label for="status" class="form-label">Status</label><select id="status" name="status" class="form-select">@foreach($locked ? [$offering->status] : ['planned', 'active'] as $status)<option value="{{ $status }}" @selected(old('status', $offering->status ?? 'planned') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
            </div>
            <p class="academic-note" id="offeringScheme" aria-live="polite">Choose a curriculum course to preview the assessment scheme.</p>
            <h2>Assigned teachers</h2><p class="text-muted small">Select one or more teachers with linked login accounts. Their roles and permissions are managed in Access Control.</p>
            <div class="academic-teacher-choices">@forelse($teachers as $teacher)<label><input type="checkbox" class="academic-check" name="teacher_ids[]" value="{{ $teacher->id }}" @checked(in_array($teacher->id, old('teacher_ids', $offering->teachers->pluck('id')->all())))>{{ $teacher->name }}</label>@empty<p class="text-muted">No teachers with login accounts are available. Link teacher profiles to User accounts first.</p>@endforelse</div>
        </fieldset>
        <div class="academic-actions"><a href="{{ route('academic.offerings.index') }}" class="btn btn-light">Back</a>@if(!$locked)<button class="btn btn-primary">Save offering</button>@endif</div>
    </form>
@endsection
@section('scripts')<script src="{{ asset('js/academic/offering-form.js') }}"></script>@endsection
