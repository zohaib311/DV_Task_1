@extends('academic.layout')
@section('title', 'Prepare Semester Teaching')
@section('heading', 'Prepare Semester Teaching')
@section('description', 'Create every class for one department, section, term, and semester plan in one controlled step.')
@section('academic-content')
    <div class="academic-note mb-4"><i class="bi bi-diagram-3"></i> Select the term, section, and approved semester plan. Assign a teacher to each course once; future student enrollments will receive the required courses automatically.</div>
    <form method="POST" action="{{ route('academic.teaching-setup.store') }}" id="semesterTeachingSetup">
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-md-4"><label class="form-label" for="academic_term_id">Teaching term</label><select class="form-select" name="academic_term_id" id="academic_term_id" required><option value="">Select active term</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(old('academic_term_id') == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="program_id">Program</label><select class="form-select" name="program_id" id="program_id" required><option value="">Select program</option>@foreach($programs as $program)<option value="{{ $program->id }}" data-department-id="{{ $program->department_id }}" @selected(old('program_id') == $program->id)>{{ $program->code }} · {{ $program->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="section_id">Department / section</label><select class="form-select" name="section_id" id="section_id" required><option value="">Select section</option>@foreach($sections as $section)<option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" @selected(old('section_id') == $section->id)>{{ $section->department->name }} / {{ $section->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="semester_curriculum_id">Approved semester plan</label><select class="form-select" name="semester_curriculum_id" id="semester_curriculum_id" required><option value="">Select program and section first</option>@foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" data-program-id="{{ $curriculum->program_id }}" @selected(old('semester_curriculum_id') == $curriculum->id)>{{ $curriculum->semester }} · {{ $curriculum->version }} · {{ $curriculum->program->code }}</option>@endforeach</select></div>
        </div>
        <section class="academic-form-section">
            <h2>Course-to-teacher assignments</h2>
            <p class="text-muted">These are teaching classes, not student-specific course selections. All required courses will be assigned automatically during enrollment.</p>
            <div class="table-responsive"><table class="table academic-table"><thead><tr><th>Course</th><th>Type</th><th>Credits</th><th>Assessment scheme</th><th>Assigned teacher</th></tr></thead><tbody id="teachingSetupCourses"><tr><td colspan="5" class="academic-empty">Select a department section and semester plan to load its courses.</td></tr></tbody></table></div>
        </section>
        <div class="academic-actions"><a href="{{ route('academic.offerings.index') }}" class="btn btn-light">Back</a><button type="submit" class="btn btn-primary" id="createTeachingSetup" disabled>Prepare semester classes</button></div>
    </form>
    <script type="application/json" id="teachingSetupData">@json($curriculaPayload)</script>
    <script type="application/json" id="teachingSetupTeachers">@json($teachersPayload)</script>
    <script type="application/json" id="teachingSetupExisting">@json($existingOfferingsPayload)</script>
@endsection
@section('scripts')<script src="{{ asset('js/academic/semester-teaching-setup.js') }}"></script>@endsection
