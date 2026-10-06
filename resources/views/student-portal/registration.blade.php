@extends('student-portal.layout')
@section('heading', 'Course Registration')
@section('student-content')
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(!$enrollment)
        <div class="academic-note">You need an active semester enrollment before selecting elective or backlog courses.</div>
    @else
        <div class="teaching-scheme mb-4">
            <strong>{{ $enrollment->semester }} · {{ $enrollment->term?->name }}</strong>
            <span>Registered: {{ number_format($registeredCredits, 1) }} credits</span>
            <span>Maximum: {{ number_format($maximumCredits, 1) }} credits</span>
        </div>
        <p class="academic-note">Select any available course from your semester plan, including required, elective, or failed-course repeats, without exceeding the maximum load.</p>
        <form method="POST" action="{{ route('student.registration.store') }}" id="studentCourseRegistration" data-current="{{ $registeredCredits }}" data-maximum="{{ $maximumCredits }}">
            @csrf
            <div class="table-responsive"><table class="table academic-table align-middle">
                <thead><tr><th>Select</th><th>Course</th><th>Type</th><th>Credits</th><th>Teacher</th></tr></thead>
                <tbody>@forelse($offerings as $offering)
                    @php($currentPlanCourse = $offering->curriculumCourse->semester_curriculum_id === $enrollment->semester_curriculum_id)
                    <tr><td><input class="form-check-input registration-course" type="checkbox" name="offering_ids[]" value="{{ $offering->id }}" data-credits="{{ $offering->credit_hours }}" @checked(in_array($offering->id, old('offering_ids', [])))></td><td>{{ $offering->course_name }}<small>{{ $offering->course_code }}</small></td><td>{{ $currentPlanCourse ? ucfirst($offering->curriculumCourse->type) : 'Backlog repeat' }}</td><td>{{ $offering->credit_hours }}</td><td>{{ $offering->teachers->pluck('name')->join(', ') }}</td></tr>
                @empty<tr><td colspan="5" class="academic-empty">No elective or backlog offerings are currently available for your section and term.</td></tr>@endforelse</tbody>
            </table></div>
            @if($offerings->isNotEmpty())<p id="registrationLoad" class="text-muted" aria-live="polite"></p><div class="academic-actions"><button class="btn btn-primary" id="registerCourses" disabled>Register selected courses</button></div>@endif
        </form>
    @endif
@endsection
@section('scripts')
    <script src="{{ asset('js/student-portal/course-registration.js') }}"></script>
@endsection
