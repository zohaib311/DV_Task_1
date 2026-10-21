@extends('student-portal.layout')

@section('heading', 'My academic transcript')
@section('description', 'Your official published academic record in one consolidated transcript.')

@section('header-actions')
    @if($print)
        <a class="btn academic-header-btn" href="{{ route('student.transcript') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Transcript</a>
        <button class="btn academic-header-btn" type="button" onclick="window.print()"><i class="bi bi-printer" aria-hidden="true"></i> Print / Save PDF</button>
    @else
        <a class="btn academic-header-btn" href="{{ route('student.results.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Results</a>
        <a class="btn academic-header-btn" href="{{ route('student.transcript', ['print' => 1]) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Print / Save PDF</a>
    @endif
@endsection

@section('student-content')
    <div class="academic-note portal-note-with-icon transcript-note no-print">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>This is a consolidated record of published semesters. Failed attempts are excluded; a later cleared repeat is marked in the remarks column.</span>
    </div>

    <article class="official-transcript {{ $transcript['shown_courses'] > 18 ? 'is-dense' : 'is-standard' }}">
        <header class="official-transcript-header">
            <div class="official-transcript-brand">
                <span class="official-transcript-mark"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                <div>
                    <strong>My Form Task</strong>
                    <span>Academic Management System</span>
                </div>
            </div>
            <div class="official-transcript-title">
                <span>Official academic record</span>
                <h2>Transcript of Record</h2>
                <p>{{ $student->program?->name ?? 'Academic Program' }}</p>
            </div>
            <div class="official-transcript-reference">
                <span>Transcript no.</span>
                <strong>TR-{{ $student->registration_no }}</strong>
                <small>Issued {{ $transcript['issued_at']->format('d M Y') }}</small>
            </div>
        </header>

        <section class="official-transcript-profile" aria-label="Student academic profile">
            <div><span>Student name</span><strong>{{ $student->name }}</strong></div>
            <div><span>Registration no.</span><strong>{{ $student->registration_no }}</strong></div>
            <div><span>Program</span><strong>{{ $student->program?->code ?: ($student->program?->name ?? 'Not recorded') }}</strong></div>
            <div><span>Department / section</span><strong>{{ $student->department?->name ?? 'Not recorded' }}@if($student->section?->name) · {{ $student->section->name }}@endif</strong></div>
        </section>

        <section class="official-transcript-records">
            <div class="official-transcript-section-title">
                <div>
                    <span>Academic performance</span>
                    <h3>Published course record</h3>
                </div>
                <small>Only cleared course outcomes appear on this transcript.</small>
            </div>

            @if($transcript['semesters']->isNotEmpty())
                <div class="table-responsive official-transcript-table-wrap">
                    <table class="table official-transcript-table align-middle">
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th>Code</th>
                                <th>Course title</th>
                                <th>Cr. hrs.</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>G.P.</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transcript['semesters'] as $semester)
                                @forelse($semester['courses'] as $course)
                                    <tr>
                                        @if($loop->first)
                                            <td class="official-semester-cell" rowspan="{{ $semester['courses']->count() }}">
                                                <strong>{{ $semester['semester'] }}</strong>
                                                <span>{{ $semester['academic_year'] }}</span>
                                                <small>SGPA {{ $semester['sgpa'] === null ? '—' : number_format((float) $semester['sgpa'], 2) }}</small>
                                                <small>CGPA {{ $semester['cgpa'] === null ? '—' : number_format((float) $semester['cgpa'], 2) }}</small>
                                            </td>
                                        @endif
                                        <td class="official-course-code">{{ $course['code'] }}</td>
                                        <td class="official-course-title">{{ $course['name'] }}</td>
                                        <td>{{ number_format($course['credits'], 1) }}</td>
                                        <td>{{ number_format($course['percentage'], 2) }}</td>
                                        <td><strong>{{ $course['grade'] }}</strong></td>
                                        <td>{{ number_format($course['grade_point'], 2) }}</td>
                                        <td>
                                            @if($course['repeated'])
                                                <span class="official-repeat"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Repeat cleared</span>
                                            @else
                                                <span class="official-cleared"><i class="bi bi-check2" aria-hidden="true"></i> Cleared</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="official-empty-semester-row">
                                        <td class="official-semester-cell">
                                            <strong>{{ $semester['semester'] }}</strong>
                                            <span>{{ $semester['academic_year'] }}</span>
                                            <small>SGPA {{ $semester['sgpa'] === null ? '—' : number_format((float) $semester['sgpa'], 2) }}</small>
                                            <small>CGPA {{ $semester['cgpa'] === null ? '—' : number_format((float) $semester['cgpa'], 2) }}</small>
                                        </td>
                                        <td colspan="7">No cleared course outcome for this published semester.</td>
                                    </tr>
                                @endforelse
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="portal-empty-state official-transcript-empty"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><strong>Your transcript is not available yet.</strong><span>It will be generated after a semester result is officially published.</span></div>
            @endif
        </section>

        <footer class="official-transcript-footer">
            <div class="official-transcript-totals">
                <div><span>Published semesters</span><strong>{{ $transcript['published_semesters'] }}</strong></div>
                <div><span>Passed course attempts</span><strong>{{ $transcript['shown_courses'] }}</strong></div>
                <div><span>Credits earned</span><strong>{{ number_format((float) $transcript['earned_credits'], 1) }}</strong></div>
                <div><span>Cumulative GPA</span><strong>{{ $transcript['cgpa'] === null ? '—' : number_format((float) $transcript['cgpa'], 2) }}</strong></div>
            </div>
            <div class="official-transcript-certification">
                <span><i class="bi bi-patch-check" aria-hidden="true"></i> System-generated student copy</span>
                <strong>Controller of Examinations</strong>
            </div>
        </footer>
    </article>

    @if($print)
        <p class="transcript-print-hint no-print"><i class="bi bi-printer" aria-hidden="true"></i> In the browser print dialog, choose “Save to PDF” to download this transcript.</p>
    @endif
@endsection

@if($print)
    @section('scripts')
        <script>window.addEventListener('load', () => window.print());</script>
    @endsection
@endif
