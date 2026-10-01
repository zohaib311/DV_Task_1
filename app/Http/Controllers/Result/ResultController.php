<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\Result;
use App\Models\Result\SemesterResult;
use App\Models\Result\SemesterResultItem;
use App\Models\Section\Section;
use App\Models\Student;
use App\Services\AcademicResultCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ResultController extends Controller
{
    public function allResults()
    {
        $departments = Department::all();
        $courses = Course::all();
        $results = Result::with(['student', 'course', 'section.department'])->get();

        return view('results.results', compact('departments', 'courses', 'results'));
    }

    public function create()
    {
        $departments = Department::all();
        $courses = Course::all();

        return view('results.add-result', compact('departments', 'courses'));
    }

    public function getSectionsByDepartment($department_id)
    {
        $sections = Section::where('department_id', $department_id)->get();

        return response()->json($sections);
    }

    /**
     * Semester-aware student/result rows for the Results Management listing.
     */
    public function getStudentsBySection(Request $request, $section_id)
    {
        $validated = $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:all,Draft,Published,Pass,Fail'],
        ]);

        $enrollments = StudentSemesterEnrollment::query()
            ->with(['student', 'department', 'section', 'courses.offering', 'semesterResult'])
            ->where('section_id', $section_id)
            ->when($validated['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($validated['academic_year'] ?? null, fn ($query, $year) => $query->where('academic_year', $year))
            ->when($validated['semester'] ?? null, fn ($query, $semester) => $query->where('semester', $semester))
            ->when(($validated['status'] ?? 'all') !== 'all', function ($query) use ($validated) {
                $status = $validated['status'];

                $query->whereHas('semesterResult', function ($resultQuery) use ($status) {
                    if ($status === 'Published') {
                        $resultQuery->whereNotNull('published_at');
                    } else {
                        $resultQuery->where('status', $status);
                    }
                });
            })
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->orderBy('id')
            ->get()
            ->sortBy(fn (StudentSemesterEnrollment $enrollment) => sprintf(
                '%s-%03d-%010d',
                $enrollment->academic_year,
                $this->semesterNumber($enrollment->semester),
                $enrollment->id,
            ))
            ->values();

        return response()->json($enrollments->map(fn (StudentSemesterEnrollment $enrollment) => [
            'id' => $enrollment->student->id,
            'registration_no' => $enrollment->student->registration_no,
            'name' => $enrollment->student->name,
            'email' => $enrollment->student->email,
            'listed_enrollment' => $this->listingEnrollment($enrollment),
        ])->values());
    }

    /**
     * Available academic year/semester combinations for a selected section.
     */
    public function getResultFilterOptions($section_id)
    {
        $enrollments = StudentSemesterEnrollment::query()
            ->where('section_id', $section_id)
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->get(['academic_year', 'semester']);

        return response()->json([
            'academic_years' => $enrollments->pluck('academic_year')->unique()->values(),
            'enrollments' => $enrollments
                ->unique(fn (StudentSemesterEnrollment $enrollment) => $enrollment->academic_year.'|'.$enrollment->semester)
                ->map(fn (StudentSemesterEnrollment $enrollment) => [
                    'academic_year' => $enrollment->academic_year,
                    'semester' => $enrollment->semester,
                ])->values(),
        ]);
    }

    /**
     * Full semester result sheet plus the student's academic history.
     */
    public function getSemesterResultSheet(SemesterResult $semesterResult)
    {
        $semesterResult->load([
            'student.department',
            'student.section',
            'enrollment.department',
            'enrollment.section',
            'items',
        ]);

        return response()->json([
            'student' => $this->resultStudent($semesterResult->student),
            'sheet' => $this->resultSheet($semesterResult),
            'history' => $this->studentAcademicHistory($semesterResult->student_id),
        ]);
    }

    /**
     * Academic history can be opened even when the selected enrollment has no result yet.
     */
    public function getStudentAcademicHistory(Student $student)
    {
        $student->load(['department', 'section']);

        return response()->json([
            'student' => $this->resultStudent($student),
            'history' => $this->studentAcademicHistory($student->id),
        ]);
    }

    /**
     * Semester choices for the Add Semester Result drawer.
     */
    public function getStudentResultEnrollments(Student $student)
    {
        $student->load([
            'semesterEnrollments' => fn ($query) => $query
                ->orderByDesc('academic_year')
                ->orderByDesc('enrolled_at')
                ->orderByDesc('id'),
            'semesterEnrollments.department',
            'semesterEnrollments.section',
            'semesterEnrollments.semesterResult',
        ]);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'registration_no' => $student->registration_no,
                'name' => $student->name,
            ],
            'enrollments' => $student->semesterEnrollments->map(fn (StudentSemesterEnrollment $enrollment) => [
                'id' => $enrollment->id,
                'semester' => $enrollment->semester,
                'academic_year' => $enrollment->academic_year,
                'enrollment_status' => $enrollment->status,
                'department_name' => $enrollment->department?->name,
                'section_name' => $enrollment->section?->name,
                'existing_result' => $enrollment->semesterResult ? [
                    'id' => $enrollment->semesterResult->id,
                    'status' => $enrollment->semesterResult->status,
                    'published_at' => $enrollment->semesterResult->published_at?->toIso8601String(),
                ] : null,
            ])->values(),
        ]);
    }

    /**
     * Read-only enrollment snapshot used when a semester is selected.
     */
    public function getEnrollmentResultData(StudentSemesterEnrollment $enrollment)
    {
        $enrollment->load([
            'student',
            'department',
            'section',
            'courses.course',
            'semesterResult',
        ]);

        $priorResultItems = SemesterResultItem::query()
            ->whereHas('semesterResult', fn ($query) => $query
                ->where('student_id', $enrollment->student_id)
                ->whereNotNull('published_at')
                ->where('student_semester_enrollment_id', '!=', $enrollment->id))
            ->with('semesterResult:id,published_at')
            ->get()
            ->map(fn (SemesterResultItem $item) => [
                'course_id' => $item->course_id,
                'course_code' => $item->course_code,
                'credit_hours' => (float) $item->credit_hours,
                'grade_point' => (float) $item->grade_point,
                'attempted_at' => $item->semesterResult->published_at?->toIso8601String(),
                'result_id' => $item->semester_result_id,
                'item_id' => $item->id,
            ])->values();

        return response()->json([
            'student' => [
                'id' => $enrollment->student->id,
                'registration_no' => $enrollment->student->registration_no,
                'name' => $enrollment->student->name,
            ],
            'enrollment' => [
                'id' => $enrollment->id,
                'semester' => $enrollment->semester,
                'academic_year' => $enrollment->academic_year,
                'department_name' => $enrollment->department?->name,
                'section_name' => $enrollment->section?->name,
                'existing_result' => $enrollment->semesterResult ? [
                    'id' => $enrollment->semesterResult->id,
                    'status' => $enrollment->semesterResult->status,
                    'published_at' => $enrollment->semesterResult->published_at?->toIso8601String(),
                ] : null,
            ],
            'courses' => $enrollment->courses->map(fn ($course) => [
                'student_enrollment_course_id' => $course->id,
                'course_id' => $course->course_id,
                'course_name' => $course->course_name ?? $course->course->name,
                'course_code' => $course->course_code ?? $course->course->code,
                'credit_hours' => (float) $course->credit_hours,
                'total_marks' => $course->total_marks,
                'attendance_marks' => $course->attendance_marks,
                'mid_marks' => $course->mid_marks,
                'final_marks' => $course->final_marks,
            ])->values(),
            'prior_result_items' => $priorResultItems,
        ]);
    }

    /**
     * Prefill data for the Phase 6 edit drawer.
     */
    public function getSemesterResultData(SemesterResult $semesterResult)
    {
        Gate::authorize('update', $semesterResult);
        abort_if($semesterResult->source === 'teacher', 422, 'Open Results > Moderation & Publication for this teacher-managed result.');

        $semesterResult->load([
            'enrollment.student',
            'enrollment.department',
            'enrollment.section',
            'enrollment.courses.course',
            'items',
        ]);
        $enrollment = $semesterResult->enrollment;

        return response()->json([
            'student' => [
                'id' => $enrollment->student->id,
                'registration_no' => $enrollment->student->registration_no,
                'name' => $enrollment->student->name,
            ],
            'enrollment' => [
                'id' => $enrollment->id,
                'semester' => $enrollment->semester,
                'academic_year' => $enrollment->academic_year,
                'department_name' => $enrollment->department?->name,
                'section_name' => $enrollment->section?->name,
            ],
            'courses' => $enrollment->courses->map(fn ($course) => [
                'student_enrollment_course_id' => $course->id,
                'course_id' => $course->course_id,
                'course_name' => $course->course_name ?? $course->course->name,
                'course_code' => $course->course_code ?? $course->course->code,
                'credit_hours' => (float) $course->credit_hours,
                'total_marks' => $course->total_marks,
                'attendance_marks' => $course->attendance_marks,
                'mid_marks' => $course->mid_marks,
                'final_marks' => $course->final_marks,
            ])->values(),
            'result' => [
                'id' => $semesterResult->id,
                'status' => $semesterResult->status,
                'semester_percentage' => $semesterResult->semester_percentage,
                'sgpa' => $semesterResult->sgpa,
                'cgpa' => $semesterResult->cgpa,
                'published_at' => $semesterResult->published_at?->toIso8601String(),
                'items' => $semesterResult->items->mapWithKeys(fn (SemesterResultItem $item) => [
                    $item->student_enrollment_course_id => [
                        'attendance_obtained_marks' => $item->attendance_obtained_marks,
                        'mid_obtained_marks' => $item->mid_obtained_marks,
                        'final_obtained_marks' => $item->final_obtained_marks,
                    ],
                ]),
            ],
            'prior_result_items' => $this->priorResultItems($enrollment),
        ]);
    }

    /**
     * Save a new semester result. All academic values are calculated again on
     * the server; submitted grade, GPA, CGPA and percentages are never used.
     */
    public function storeSemesterResult(Request $request, AcademicResultCalculator $calculator)
    {
        Gate::authorize('create', SemesterResult::class);
        $validated = $request->validate($this->semesterResultRules());

        $enrollment = StudentSemesterEnrollment::query()
            ->whereKey($validated['enrollment_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();
        $publish = $validated['action'] === 'publish';
        if ($publish) {
            Gate::authorize('publish', SemesterResult::class);
        }

        $prepared = $calculator->prepareEnrollmentResult(
            $enrollment,
            $validated['courses'],
            $publish,
            submittedStudentId: (int) $validated['student_id'],
        );

        $semesterResult = DB::transaction(function () use ($calculator, $enrollment, $prepared, $publish) {
            $calculator->guardLegacyWrite($enrollment);
            $resultData = $prepared['result'];
            $semesterResult = SemesterResult::create($resultData);
            $this->syncSemesterResultItems($semesterResult, $prepared['items']);

            if ($publish) {
                $calculator->recalculatePublishedCgpas($enrollment->student_id);
                $semesterResult->refresh();
            }

            return $semesterResult;
        });

        $message = $publish
            ? 'Semester result has been published successfully.'
            : 'Semester result draft has been saved successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'result' => [
                    'id' => $semesterResult->id,
                    'status' => $semesterResult->status,
                    'cgpa' => $semesterResult->cgpa,
                ],
            ], 201);
        }

        return redirect()->route('allResults')->with('success', $message);
    }

    /**
     * Update an existing draft or an authorized published semester result.
     */
    public function updateSemesterResult(Request $request, SemesterResult $semesterResult, AcademicResultCalculator $calculator)
    {
        Gate::authorize('update', $semesterResult);
        abort_if($semesterResult->source === 'teacher', 422, 'Teacher-managed results can only be changed through Moderation & Publication.');
        $validated = $request->validate($this->semesterResultRules());
        $enrollment = StudentSemesterEnrollment::query()
            ->whereKey($validated['enrollment_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();
        abort_unless(
            $enrollment->id === $semesterResult->student_semester_enrollment_id
                && $enrollment->student_id === $semesterResult->student_id,
            422,
            'The selected enrollment does not belong to this semester result.'
        );
        $publish = $validated['action'] === 'publish';
        if ($publish) {
            Gate::authorize('publish', SemesterResult::class);
        }
        $wasPublished = $semesterResult->published_at !== null;

        $prepared = $calculator->prepareEnrollmentResult(
            $enrollment,
            $validated['courses'],
            $publish,
            resultBeingUpdated: $semesterResult,
            submittedStudentId: (int) $validated['student_id'],
        );

        $semesterResult = DB::transaction(function () use ($calculator, $semesterResult, $enrollment, $prepared, $publish, $wasPublished, $request) {
            $calculator->guardLegacyWrite($enrollment);
            $semesterResult->load('items');
            $before = $this->resultAuditSnapshot($semesterResult);
            $resultData = $prepared['result'];
            $resultData['published_at'] = $publish
                ? ($semesterResult->published_at ?? now())
                : null;
            $semesterResult->update($resultData);
            $this->syncSemesterResultItems($semesterResult, $prepared['items']);

            if ($publish || $wasPublished) {
                $calculator->recalculatePublishedCgpas($enrollment->student_id);
                $semesterResult->refresh()->load('items');
            }

            $semesterResult->audits()->create([
                'updated_by' => $request->user()?->id,
                'action' => $publish ? 'saved_and_published' : 'saved_as_draft',
                'before' => $before,
                'after' => $this->resultAuditSnapshot($semesterResult),
            ]);

            return $semesterResult;
        });

        $message = $publish
            ? 'Semester result updated and published successfully.'
            : 'Semester result draft updated successfully.';

        return response()->json([
            'message' => $message,
            'result' => [
                'id' => $semesterResult->id,
                'status' => $semesterResult->status,
                'cgpa' => $semesterResult->cgpa,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function listingEnrollment(StudentSemesterEnrollment $enrollment): array
    {
        $result = $enrollment->semesterResult;

        return [
            'id' => $enrollment->id,
            'semester' => $enrollment->semester,
            'academic_year' => $enrollment->academic_year,
            'course_count' => $enrollment->courses->count(),
            'moderation_url' => $enrollment->courses->contains(fn ($course) => $course->offering?->assessment_scheme_approved_at !== null)
                ? route('results.moderation.show', $enrollment) : null,
            'semester_result' => $result ? [
                'id' => $result->id,
                'semester_percentage' => $result->semester_percentage,
                'sgpa' => $result->sgpa,
                'cgpa' => $result->cgpa,
                'status' => $result->status,
                'published_at' => $result->published_at?->toIso8601String(),
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function resultStudent(Student $student): array
    {
        return [
            'id' => $student->id,
            'registration_no' => $student->registration_no,
            'name' => $student->name,
            'email' => $student->email,
            'department_name' => $student->department?->name,
            'section_name' => $student->section?->name,
        ];
    }

    /** @return array<string, mixed> */
    private function resultSheet(SemesterResult $semesterResult): array
    {
        return [
            'id' => $semesterResult->id,
            'semester' => $semesterResult->enrollment->semester,
            'academic_year' => $semesterResult->enrollment->academic_year,
            'semester_percentage' => $semesterResult->semester_percentage,
            'sgpa' => $semesterResult->sgpa,
            'cgpa' => $semesterResult->cgpa,
            'status' => $semesterResult->status,
            'published_at' => $semesterResult->published_at?->toIso8601String(),
            'items' => $semesterResult->items->map(fn (SemesterResultItem $item) => [
                'assessment_snapshot' => $item->assessment_snapshot,
                'course_code' => $item->course_code,
                'course_name' => $item->course_name,
                'credit_hours' => $item->credit_hours,
                'attendance_marks' => $item->attendance_marks,
                'attendance_obtained_marks' => $item->attendance_obtained_marks,
                'mid_marks' => $item->mid_marks,
                'mid_obtained_marks' => $item->mid_obtained_marks,
                'final_marks' => $item->final_marks,
                'final_obtained_marks' => $item->final_obtained_marks,
                'obtained_marks' => $item->obtained_marks,
                'total_marks' => $item->total_marks,
                'percentage' => $item->percentage,
                'grade' => $item->grade,
                'grade_point' => $item->grade_point,
                'status' => $item->status,
            ])->values(),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function studentAcademicHistory(int $studentId)
    {
        return StudentSemesterEnrollment::query()
            ->where('student_id', $studentId)
            ->with(['semesterResult', 'courses'])
            ->get()
            ->sortBy(fn (StudentSemesterEnrollment $enrollment) => sprintf(
                '%s-%03d-%010d',
                $enrollment->academic_year,
                $this->semesterNumber($enrollment->semester),
                $enrollment->id,
            ))
            ->values()
            ->map(fn (StudentSemesterEnrollment $enrollment) => [
                'enrollment_id' => $enrollment->id,
                'semester' => $enrollment->semester,
                'academic_year' => $enrollment->academic_year,
                'course_count' => $enrollment->courses->count(),
                'result_id' => $enrollment->semesterResult?->id,
                'semester_percentage' => $enrollment->semesterResult?->semester_percentage,
                'sgpa' => $enrollment->semesterResult?->sgpa,
                'cgpa' => $enrollment->semesterResult?->cgpa,
                'status' => $enrollment->semesterResult?->status ?? 'Not entered',
                'published_at' => $enrollment->semesterResult?->published_at?->toIso8601String(),
            ])->values();
    }

    private function semesterNumber(?string $semester): int
    {
        preg_match('/\d+/', $semester ?? '', $matches);

        return (int) ($matches[0] ?? 0);
    }

    private function semesterResultRules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'enrollment_id' => ['required', 'integer', 'exists:student_semester_enrollments,id'],
            'action' => ['required', 'in:draft,publish'],
            'courses' => ['required', 'array'],
            'courses.*.student_enrollment_course_id' => ['required', 'integer'],
            'courses.*.attendance_obtained_marks' => ['nullable', 'numeric'],
            'courses.*.mid_obtained_marks' => ['nullable', 'numeric'],
            'courses.*.final_obtained_marks' => ['nullable', 'numeric'],
        ];
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncSemesterResultItems(SemesterResult $semesterResult, array $items): void
    {
        foreach ($items as $item) {
            $semesterResult->items()->updateOrCreate(
                ['student_enrollment_course_id' => $item['student_enrollment_course_id']],
                Arr::only($item, [
                    'course_id',
                    'course_code',
                    'course_name',
                    'credit_hours',
                    'total_marks',
                    'attendance_marks',
                    'mid_marks',
                    'final_marks',
                    'obtained_marks',
                    'attendance_obtained_marks',
                    'mid_obtained_marks',
                    'final_obtained_marks',
                    'percentage',
                    'grade',
                    'grade_point',
                    'status',
                ])
            );
        }
    }

    /** @return array<string, mixed> */
    private function resultAuditSnapshot(SemesterResult $semesterResult): array
    {
        return [
            'summary' => Arr::only($semesterResult->toArray(), [
                'semester_percentage',
                'sgpa',
                'cgpa',
                'status',
                'published_at',
            ]),
            'items' => $semesterResult->items->map(fn (SemesterResultItem $item) => Arr::only($item->toArray(), [
                'student_enrollment_course_id',
                'attendance_obtained_marks',
                'mid_obtained_marks',
                'final_obtained_marks',
                'obtained_marks',
                'percentage',
                'grade',
                'grade_point',
                'status',
            ]))->values()->all(),
        ];
    }

    private function priorResultItems(StudentSemesterEnrollment $enrollment)
    {
        return SemesterResultItem::query()
            ->whereHas('semesterResult', fn ($query) => $query
                ->where('student_id', $enrollment->student_id)
                ->whereNotNull('published_at')
                ->where('student_semester_enrollment_id', '!=', $enrollment->id))
            ->with('semesterResult:id,published_at')
            ->get()
            ->map(fn (SemesterResultItem $item) => [
                'course_id' => $item->course_id,
                'course_code' => $item->course_code,
                'credit_hours' => (float) $item->credit_hours,
                'grade_point' => (float) $item->grade_point,
                'attempted_at' => $item->semesterResult->published_at?->toIso8601String(),
                'result_id' => $item->semester_result_id,
                'item_id' => $item->id,
            ])->values();
    }

    public function addResult(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
            'percentage' => 'required|numeric|min:0|max:100',
            'gpa' => 'required|numeric|min:0|max:4.0',
            'cgpa' => 'required|numeric|min:0|max:4.0',
            'grade' => 'required|string|max:10',
            'status' => 'required|in:Pass,Fail',
        ]);

        Result::create($validated);

        return redirect()
            ->route('allResults')
            ->with('success', 'Result added successfully!');
    }

}
