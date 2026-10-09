@extends('student-portal.layout')
@section('heading', 'Course Registration')
@section('description', 'Build your active semester course load from prepared classes and eligible backlog repeats.')
@section('student-content')
    @if(!$enrollment)
        <div class="portal-empty-state portal-card"><i class="bi bi-journal-x" aria-hidden="true"></i><strong>No active semester enrollment</strong><span>You need an active semester enrollment before selecting elective or backlog courses.</span></div>
    @else
        @php($creditPercent = $maximumCredits > 0 ? min(100, ($registeredCredits / $maximumCredits) * 100) : 0)
        <div class="portal-credit-panel">
            <div class="portal-credit-item"><small>Active registration</small><strong>{{ $enrollment->semester }} · {{ $enrollment->term?->name }}</strong><div class="portal-credit-progress" aria-hidden="true"><span id="registrationCreditBar" style="width: {{ $creditPercent }}%"></span></div></div>
            <div class="portal-credit-item"><small>Currently registered</small><strong>{{ number_format($registeredCredits, 1) }} credits</strong></div>
            <div class="portal-credit-item"><small>Maximum load</small><strong>{{ number_format($maximumCredits, 1) }} credits</strong></div>
        </div>
        <p class="academic-note portal-note-with-icon"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Select available courses from your semester plan, including required, elective, or failed-course repeats, without exceeding the maximum load.</span></p>
        <form method="POST" action="{{ route('student.registration.store') }}" id="studentCourseRegistration" data-current="{{ $registeredCredits }}" data-maximum="{{ $maximumCredits }}">
            @csrf
            <div class="portal-section-heading"><div><h2>Available courses</h2><p>Select one or more courses to add to your active enrollment</p></div><span class="portal-badge is-info">{{ $offerings->count() }} available</span></div>
            <div class="table-responsive portal-table-card"><table class="table academic-table align-middle">
                <thead><tr><th>Select</th><th>Course</th><th>Type</th><th>Credits</th><th>Teacher</th></tr></thead>
                <tbody>@forelse($offerings as $offering)
                    @php($currentPlanCourse = $offering->curriculumCourse->semester_curriculum_id === $enrollment->semester_curriculum_id)
                    @php($registrationType = $currentPlanCourse ? ucfirst($offering->curriculumCourse->type) : 'Backlog repeat')
                    <tr class="portal-course-choice"><td class="portal-select-cell"><input id="offering-{{ $offering->id }}" class="form-check-input registration-course" type="checkbox" name="offering_ids[]" value="{{ $offering->id }}" data-credits="{{ $offering->credit_hours }}" @checked(in_array($offering->id, old('offering_ids', [])))></td><td class="portal-course-cell"><label for="offering-{{ $offering->id }}" class="mb-0 w-100"><strong>{{ $offering->course_name }}</strong><small>{{ $offering->course_code }}</small></label></td><td><span class="portal-badge {{ !$currentPlanCourse ? 'is-warning' : '' }}">{{ $registrationType }}</span></td><td>{{ number_format((float) $offering->credit_hours, 1) }}</td><td><i class="bi bi-person-video3 me-1 text-muted" aria-hidden="true"></i>{{ $offering->teachers->pluck('name')->join(', ') }}</td></tr>
                @empty<tr class="portal-empty-row"><td colspan="5"><div class="portal-empty-state"><i class="bi bi-check2-circle" aria-hidden="true"></i><strong>No courses waiting for registration</strong><span>No elective or backlog offerings are currently available for your section and term.</span></div></td></tr>@endforelse</tbody>
            </table></div>
            @if($offerings->isNotEmpty())<p id="registrationLoad" class="portal-load-message" aria-live="polite"></p><div class="academic-actions"><button class="portal-btn" id="registerCourses" disabled><i class="bi bi-check2-circle" aria-hidden="true"></i> Register selected courses</button></div>@endif
        </form>
    @endif
@endsection
@section('scripts')
    <script src="{{ asset('js/student-portal/course-registration.js') }}"></script>
@endsection
