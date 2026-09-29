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

    public function getStudentsBySection($section_id)
    {
        $students = Student::with(['results.course', 'department', 'section'])
            ->with(['activeSemesterEnrollment.semesterResult'])
            ->where('section_id', $section_id)
            ->get();

        return response()->json($students);
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
                'course_name' => $course->course->name,
                'course_code' => $course->course->code,
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
                'course_name' => $course->course->name,
                'course_code' => $course->course->code,
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
        $validated = $request->validate($this->semesterResultRules());

        $enrollment = StudentSemesterEnrollment::query()
            ->whereKey($validated['enrollment_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();
        $publish = $validated['action'] === 'publish';

        $prepared = $calculator->prepareEnrollmentResult(
            $enrollment,
            $validated['courses'],
            $publish,
            submittedStudentId: (int) $validated['student_id'],
        );

        $semesterResult = DB::transaction(function () use ($calculator, $enrollment, $prepared, $publish) {
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
        $wasPublished = $semesterResult->published_at !== null;

        $prepared = $calculator->prepareEnrollmentResult(
            $enrollment,
            $validated['courses'],
            $publish,
            resultBeingUpdated: $semesterResult,
            submittedStudentId: (int) $validated['student_id'],
        );

        $semesterResult = DB::transaction(function () use ($calculator, $semesterResult, $enrollment, $prepared, $publish, $wasPublished, $request) {
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

    public function deleteResult($id)
    {
        $result = Result::findOrFail($id);
        $result->delete();

        return redirect()
            ->route('allResults')
            ->with('success', 'Result deleted successfully!');
    }
}
