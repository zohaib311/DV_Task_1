@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enrollment/enrollment.css') }}">
    <link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">
@endsection

@section('content')
    @php($isPromotion = isset($promotionEnrollment))
    <div class="container add__form_cont py-5">
        <div class="add__form enrollment-form mx-auto">
            <div class="enrollment-form-header">
                <div class="enrollment-form-icon"><i class="bi {{ $isPromotion ? 'bi-arrow-up-right-circle' : 'bi-journal-plus' }}"></i></div>
                <div>
                    <h2>{{ $isPromotion ? 'Promote Student to Next Semester' : 'New Semester Enrollment' }}</h2>
                    <p>Select a teaching term and approved curriculum to load the assigned courses.</p>
                </div>
            </div>
            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div class="alert alert-danger enrollment-errors" role="alert"><strong>Please correct the following:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if($isPromotion)
                <div class="promotion-policy-note {{ $promotionEligibility['allowed'] ? 'is-allowed' : 'is-blocked' }}" role="status"><i class="bi bi-shield-check"></i><div><strong>{{ $promotionEnrollment->semester }} → {{ $nextSemester ?? 'Final semester' }}</strong><span>{{ $promotionEligibility['message'] }}</span></div></div>
            @endif
            @if($terms->isEmpty() || $curricula->isEmpty())
                <p class="academic-note">Enrollment requires an active academic term, an approved curriculum, and active course offerings with assigned teachers.</p>
            @endif

            <form action="{{ $isPromotion ? route('promoteEnrollment', $promotionEnrollment) : route('addEnrollment') }}" method="POST" id="academicEnrollmentForm"
                data-offerings-url="{{ route('enrollment.offerings') }}" data-next-semester="{{ $isPromotion ? ($nextSemester ?? 'None') : '' }}"
                data-self-registration="{{ $isPromotion ? 'true' : 'false' }}"
                data-blocked="{{ $isPromotion && !$promotionEligibility['allowed'] ? 'true' : 'false' }}">
                @csrf
                <section class="enrollment-form-section">
                    <div class="enrollment-section-title"><i class="bi bi-person-vcard"></i><div><h5>Student placement</h5><p>The department and section determine the available course offerings.</p></div></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="student_id" class="form-label">Student</label>
                            @if($isPromotion)<input type="hidden" name="student_id" value="{{ $promotionEnrollment->student_id }}">@endif
                            <select name="student_id" id="student_id" class="form-select" required @disabled($isPromotion)>
                                <option value="">Select student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" data-department-id="{{ $student->department_id }}" data-program-id="{{ $student->program_id }}" data-placement="{{ $student->registration_no }} · {{ $student->program?->code ?? 'Legacy program' }} · {{ $student->department?->name }} / {{ $student->section?->name }}"
                                        @selected(old('student_id', $isPromotion ? $promotionEnrollment->student_id : request('student_id')) == $student->id)>{{ $student->name }} ({{ $student->registration_no }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12"><div class="student-placement-summary" id="studentPlacementSummary" aria-live="polite">Select a student to see their current placement.</div></div>
                        <div class="col-md-5">
                            <label for="academic_term_id" class="form-label">Academic term</label>
                            <select name="academic_term_id" id="academic_term_id" class="form-select" required>
                                <option value="">Select active term</option>
                                @foreach($terms as $term)<option value="{{ $term->id }}" @selected(old('academic_term_id') == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="semester_curriculum_id" class="form-label">{{ $isPromotion ? 'Next semester curriculum' : 'Semester curriculum' }}</label>
                            <select name="semester_curriculum_id" id="semester_curriculum_id" class="form-select" required>
                                <option value="">Select an approved curriculum</option>
                                @foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" data-department-id="{{ $curriculum->department_id }}" data-program-id="{{ $curriculum->program_id }}" data-semester="{{ $curriculum->semester }}" @selected(old('semester_curriculum_id') == $curriculum->id)>{{ $curriculum->semester }} · {{ $curriculum->version }} · {{ $curriculum->program?->code ?? 'Legacy program' }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </section>
                <section class="enrollment-form-section">
                    <div class="enrollment-section-title"><i class="bi bi-journal-richtext"></i><div><h5>Semester course registration</h5><p>{{ $isPromotion ? 'These courses will be available for the student to select in the Student Portal after promotion.' : 'Required courses are automatic. Available electives and failed-course repeats are optional and subject to the credit-hour limit.' }}</p></div></div>
                    <p id="offeringStatus" class="text-muted small" aria-live="polite">Select a student, term, and curriculum to load the automatic course plan.</p>
                    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Include</th><th>Course / Teacher</th><th>Type</th><th>Credits</th><th>Assessment</th></tr></thead><tbody id="enrollmentOfferingRows"></tbody></table></div>
                    <p class="text-muted small mb-0">Assessment: Attendance / Midterm / Final. Approved credit hours and marking schemes are saved with this enrollment.</p>
                </section>
                <div class="form__actions mt-4"><a href="{{ route('allEnrollments') }}" class="cancel__btn">Back</a><button type="submit" class="update__btn" id="saveEnrollment" disabled>{{ $isPromotion ? 'Promote Student' : 'Create Enrollment' }}</button></div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/enrollment/academic-enrollment.js') }}"></script>
@endsection
