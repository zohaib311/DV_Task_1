@extends('academic.layout')
@section('title', 'Curriculum Course Plan')
@section('heading', $curriculum->exists ? 'Curriculum Course Plan' : 'Create Semester Curriculum')
@section('description', 'Review the course selection and assessment allocation before approving this version.')
@section('academic-content')
    @php($approved = $curriculum->status === 'approved')
    @if($approved)<p class="academic-note"><i class="bi bi-lock"></i> Approved {{ $curriculum->approved_at?->format('d M Y') }}. This version is read-only.</p>@endif
    <form method="POST" action="{{ $curriculum->exists ? route('academic.curricula.update', $curriculum) : route('academic.curricula.store') }}">
        @csrf @if($curriculum->exists) @method('PUT') @endif
        <fieldset @disabled($approved)>
            <div class="row g-3 mb-4">
                <div class="col-md-5"><label for="program_id" class="form-label">Program</label><select id="program_id" name="program_id" class="form-select" required><option value="">Select program</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected(old('program_id', $curriculum->program_id) == $program->id)>{{ $program->code }} · {{ $program->name }} ({{ $program->department->name }})</option>@endforeach</select></div>
                <div class="col-md-3"><label for="semester" class="form-label">Semester</label><select id="semester" name="semester" class="form-select" required>@foreach(range(1, 8) as $number)<option @selected(old('semester', $curriculum->semester) === 'Semester '.$number)>Semester {{ $number }}</option>@endforeach</select></div>
                <div class="col-md-4"><label for="version" class="form-label">Curriculum version</label><input id="version" name="version" class="form-control" value="{{ old('version', $curriculum->version) }}" placeholder="e.g. 2026 Intake" maxlength="60" required></div>
            </div>
            <h2>Course plan</h2><p class="text-muted small">Selected courses are required by default. Mark optional courses as electives. Assessment columns show Attendance / Midterm / Final.</p>
            @php($selected = old('course_ids', $curriculum->courses->pluck('course_id')->all()))
            @php($electives = old('elective_ids', $curriculum->courses->where('type', 'elective')->pluck('course_id')->all()))
            <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Include</th><th>Course</th><th>Credits</th><th>Assessment</th><th>Total</th><th>Elective</th></tr></thead><tbody>
                @foreach($approved ? $curriculum->courses : $courses as $course)
                    @php($courseId = $approved ? $course->course_id : $course->id)
                    @php($snapshot = $approved ? $course : $curriculum->courses->firstWhere('course_id', $courseId))
                    <tr><td><input class="academic-check" aria-label="Include {{ $course->course_name ?? $course->name }}" type="checkbox" name="course_ids[]" value="{{ $courseId }}" @checked(in_array($courseId, $selected))></td><td><strong>{{ $course->course_name ?? $course->name }}</strong><small>{{ $course->course_code ?? $course->code }}</small></td><td>{{ $snapshot?->credit_hours ?? $course->credit_hours }}</td><td>{{ $snapshot?->attendance_marks ?? $course->attendance_marks }} / {{ $snapshot?->mid_marks ?? $course->mid_marks }} / {{ $snapshot?->final_marks ?? $course->final_marks }}</td><td>{{ $snapshot?->total_marks ?? $course->total_marks }}</td><td><input class="academic-check" aria-label="{{ $course->course_name ?? $course->name }} is elective" type="checkbox" name="elective_ids[]" value="{{ $courseId }}" @checked(in_array($courseId, $electives))></td></tr>
                @endforeach
            </tbody></table></div>
            @if(!$approved)<p class="text-muted small">Saving a draft refreshes its selected courses from the catalog. Approval freezes that saved plan.</p>@endif
        </fieldset>
        <div class="academic-actions"><a class="btn btn-light" href="{{ route('academic.curricula.index') }}">Back</a>@if(!$approved)<button class="btn btn-primary">Save draft</button>@endif</div>
    </form>
    @if($curriculum->exists && !$approved)<form action="{{ route('academic.curricula.approve', $curriculum) }}" method="POST" class="academic-approval">@csrf<div><strong>Ready to approve?</strong><p>Approval locks the saved course plan. Save any form changes above first.</p></div><button class="btn btn-primary"><i class="bi bi-shield-check"></i> Approve saved curriculum</button></form>@endif
@endsection
