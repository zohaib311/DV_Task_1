<?php

namespace App\Services;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Result\SemesterResultItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;

/**
 * The single server-side source of truth for academic result calculations.
 *
 * The browser may display a preview, but values returned by this service are
 * the only values that should be persisted for a semester result.
 */
class AcademicResultCalculator
{
    /** Call within the result transaction to serialize against scheme approval. */
    public function guardLegacyWrite(StudentSemesterEnrollment $enrollment): void
    {
        $offerings = \App\Models\Academic\CourseOffering::whereIn('id', $enrollment->courses()->select('course_offering_id'))->orderBy('id')->lockForUpdate()->get();
        if ($offerings->contains(fn ($offering) => $offering->assessment_scheme_approved_at !== null)) {
            throw ValidationException::withMessages(['assessment_workflow' => 'Teacher-managed assessment results must use Results > Moderation & Publication.']);
        }
    }

    /**
     * Calculate one course outcome from marks and the configured grade scale.
     *
     * @return array{obtained_marks: float|null, total_marks: float, percentage: float|null, grade: string|null, grade_point: float|null, status: string}
     */
    public function calculateCourse(mixed $obtainedMarks, mixed $totalMarks): array
    {
        $totalMarks = $this->validatedPositiveNumber($totalMarks, 'Total marks must be greater than zero.');

        if ($obtainedMarks === null || $obtainedMarks === '') {
            return [
                'obtained_marks' => null,
                'total_marks' => $totalMarks,
                'percentage' => null,
                'grade' => null,
                'grade_point' => null,
                'status' => 'Draft',
            ];
        }

        if (! is_numeric($obtainedMarks)) {
            throw new InvalidArgumentException('Obtained marks must be numeric.');
        }

        $obtainedMarks = (float) $obtainedMarks;

        if ($obtainedMarks < 0 || $obtainedMarks > $totalMarks) {
            throw new InvalidArgumentException("Obtained marks must be between 0 and {$totalMarks}.");
        }

        $percentage = round(($obtainedMarks / $totalMarks) * 100, 2);
        $grade = $this->gradeForPercentage($percentage);

        return [
            'obtained_marks' => $obtainedMarks,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'grade' => $grade['grade'],
            'grade_point' => (float) $grade['grade_point'],
            'status' => $grade['status'],
        ];
    }

    /**
     * Calculate a course from its separate Attendance, Midterm and Final
     * components. Combined obtained_marks is always derived here, never read
     * from a submitted request.
     *
     * @param  array<string, mixed>  $course
     * @return array<string, mixed>
     */
    public function calculateAssessmentCourse(array $course): array
    {
        $totalMarks = $this->validatedPositiveNumber($course['total_marks'] ?? null, 'Total marks must be greater than zero.');
        $components = [
            'attendance' => ['maximum' => 'attendance_marks', 'obtained' => 'attendance_obtained_marks', 'label' => 'Attendance'],
            'mid' => ['maximum' => 'mid_marks', 'obtained' => 'mid_obtained_marks', 'label' => 'Midterm'],
            'final' => ['maximum' => 'final_marks', 'obtained' => 'final_obtained_marks', 'label' => 'Final'],
        ];
        $componentValues = [];
        $componentMaximumTotal = 0.0;
        $isComplete = true;

        foreach ($components as $name => $definition) {
            $maximum = $this->validatedNonNegativeNumber(
                $course[$definition['maximum']] ?? null,
                "{$definition['label']} maximum marks must be zero or greater."
            );
            $submittedValue = $course[$definition['obtained']] ?? null;
            $componentMaximumTotal += $maximum;

            if ($maximum === 0.0) {
                if ($submittedValue !== null && $submittedValue !== '' && (! is_numeric($submittedValue) || (float) $submittedValue !== 0.0)) {
                    throw new InvalidArgumentException("{$definition['label']} marks must be 0 because this course has no {$definition['label']} component.");
                }
                $componentValues[$name] = 0.0;

                continue;
            }

            if ($submittedValue === null || $submittedValue === '') {
                $componentValues[$name] = null;
                $isComplete = false;

                continue;
            }

            if (! is_numeric($submittedValue) || (float) $submittedValue < 0 || (float) $submittedValue > $maximum) {
                throw new InvalidArgumentException("{$definition['label']} obtained marks must be between 0 and {$maximum}.");
            }

            $componentValues[$name] = (float) $submittedValue;
        }

        if (round($componentMaximumTotal, 2) !== round($totalMarks, 2)) {
            throw new InvalidArgumentException("Attendance, Midterm and Final maximum marks must equal the course total ({$totalMarks}).");
        }

        $obtainedMarks = $isComplete ? array_sum($componentValues) : null;
        $calculation = $this->calculateCourse($obtainedMarks, $totalMarks);

        if ($isComplete && config('academic.assessment.final_minimum.enabled')) {
            $finalMaximum = $this->validatedNonNegativeNumber($course['final_marks'] ?? null, 'Final maximum marks must be zero or greater.');
            $minimumPercentage = (float) config('academic.assessment.final_minimum.minimum_percentage');

            if ($finalMaximum > 0 && (($componentValues['final'] / $finalMaximum) * 100) < $minimumPercentage) {
                $calculation['grade'] = 'F';
                $calculation['grade_point'] = 0.0;
                $calculation['status'] = 'Fail';
            }
        }

        return array_merge($calculation, [
            'attendance_marks' => $this->normaliseIntegerOrFloat($course['attendance_marks']),
            'mid_marks' => $this->normaliseIntegerOrFloat($course['mid_marks']),
            'final_marks' => $this->normaliseIntegerOrFloat($course['final_marks']),
            'attendance_obtained_marks' => $componentValues['attendance'],
            'mid_obtained_marks' => $componentValues['mid'],
            'final_obtained_marks' => $componentValues['final'],
        ]);
    }

    /**
     * Calculate a complete semester. A blank obtained_marks value is allowed
     * for a draft, but it never produces a final GPA or percentage.
     *
     * @param  iterable<array<string, mixed>>  $courses
     * @return array{items: array<int, array<string, mixed>>, total_obtained_marks: float|null, total_marks: float, semester_percentage: float|null, sgpa: float|null, status: string, is_complete: bool}
     */
    public function calculateSemester(iterable $courses, bool $requireAllMarks = false): array
    {
        $items = [];
        $totalMarks = 0.0;
        $totalObtainedMarks = 0.0;
        $totalCreditHours = 0.0;
        $qualityPoints = 0.0;
        $isComplete = true;
        $hasFailedCourse = false;

        foreach ($courses as $course) {
            $creditHours = $this->validatedPositiveNumber(
                $course['credit_hours'] ?? null,
                'Course credit hours must be greater than zero.'
            );
            $courseCalculation = array_key_exists('attendance_marks', $course)
                ? $this->calculateAssessmentCourse($course)
                : $this->calculateCourse(
                    $course['obtained_marks'] ?? null,
                    $course['total_marks'] ?? null
                );

            $items[] = array_merge($course, $courseCalculation, ['credit_hours' => $creditHours]);
            $totalMarks += $courseCalculation['total_marks'];

            if ($courseCalculation['obtained_marks'] === null) {
                $isComplete = false;

                continue;
            }

            $totalObtainedMarks += $courseCalculation['obtained_marks'];
            $totalCreditHours += $creditHours;
            $qualityPoints += $courseCalculation['grade_point'] * $creditHours;
            $hasFailedCourse = $hasFailedCourse || $courseCalculation['status'] === 'Fail';
        }

        if ($items === []) {
            throw new InvalidArgumentException('At least one enrolled course is required to calculate a result.');
        }

        if (! $isComplete) {
            if ($requireAllMarks) {
                throw ValidationException::withMessages([
                    'courses' => 'Obtained marks are required for every enrolled course before publishing.',
                ]);
            }

            return [
                'items' => $items,
                'total_obtained_marks' => null,
                'total_marks' => round($totalMarks, 2),
                'semester_percentage' => null,
                'sgpa' => null,
                'status' => 'Draft',
                'is_complete' => false,
            ];
        }

        return [
            'items' => $items,
            'total_obtained_marks' => round($totalObtainedMarks, 2),
            'total_marks' => round($totalMarks, 2),
            'semester_percentage' => round(($totalObtainedMarks / $totalMarks) * 100, 2),
            'sgpa' => round($qualityPoints / $totalCreditHours, 2),
            'status' => $hasFailedCourse ? 'Fail' : 'Pass',
            'is_complete' => true,
        ];
    }

    /**
     * Validate a result request against the server's enrollment records and
     * return safe, calculated result/item data ready for a transaction.
     *
     * @param  array<int, array<string, mixed>>  $submittedCourses
     * @return array{result: array<string, mixed>, items: array<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function prepareEnrollmentResult(
        StudentSemesterEnrollment $enrollment,
        array $submittedCourses,
        bool $publish,
        ?SemesterResult $resultBeingUpdated = null,
        ?int $submittedStudentId = null,
    ): array {
        $errors = [];

        if ($submittedStudentId !== null && $submittedStudentId !== (int) $enrollment->student_id) {
            $errors['student_id'] = 'The selected student does not own this semester enrollment.';
        }

        if ($resultBeingUpdated !== null
            && ((int) $resultBeingUpdated->student_semester_enrollment_id !== (int) $enrollment->id
                || (int) $resultBeingUpdated->student_id !== (int) $enrollment->student_id)) {
            $errors['result'] = 'The result record does not belong to this student enrollment.';
        }

        $existingResult = $enrollment->semesterResult()->first();
        if ($existingResult !== null && ($resultBeingUpdated === null || ! $existingResult->is($resultBeingUpdated))) {
            $errors['result'] = 'A semester result already exists for this enrollment.';
        }

        if (! $publish && ! config('academic.results.allow_drafts')) {
            $errors['result'] = 'Saving result drafts is disabled by the current academic policy.';
        }

        $enrollment->loadMissing('courses.course');
        if ($enrollment->courses()->whereHas('offering', fn ($query) => $query->whereNotNull('assessment_scheme_approved_at'))->exists()) {
            $errors['assessment_workflow'] = 'This enrollment uses teacher-managed assessments. Open Results > Moderation & Publication to review and publish its semester sheet.';
        }
        $enrolledCourses = $enrollment->courses->keyBy('id');
        $submittedByEnrollmentCourse = [];

        foreach ($submittedCourses as $index => $submittedCourse) {
            $enrollmentCourseId = $submittedCourse['student_enrollment_course_id'] ?? null;

            if (! is_numeric($enrollmentCourseId) || ! $enrolledCourses->has((int) $enrollmentCourseId)) {
                $errors["courses.{$index}.student_enrollment_course_id"] = 'This course is not assigned to the selected semester enrollment.';

                continue;
            }

            if (array_key_exists((int) $enrollmentCourseId, $submittedByEnrollmentCourse)) {
                $errors["courses.{$index}.student_enrollment_course_id"] = 'Each enrolled course may be submitted only once.';

                continue;
            }

            $submittedByEnrollmentCourse[(int) $enrollmentCourseId] = $submittedCourse;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $coursesForCalculation = $enrollment->courses->map(function ($enrollmentCourse) use ($submittedByEnrollmentCourse) {
            $submittedCourse = $submittedByEnrollmentCourse[$enrollmentCourse->id] ?? [];

            return [
                'student_enrollment_course_id' => $enrollmentCourse->id,
                'course_id' => $enrollmentCourse->course_id,
                'course_code' => $enrollmentCourse->course_code ?? $enrollmentCourse->course->code,
                'course_name' => $enrollmentCourse->course_name ?? $enrollmentCourse->course->name,
                'credit_hours' => $enrollmentCourse->credit_hours,
                'total_marks' => $enrollmentCourse->total_marks,
                'attendance_marks' => $enrollmentCourse->attendance_marks,
                'mid_marks' => $enrollmentCourse->mid_marks,
                'final_marks' => $enrollmentCourse->final_marks,
                'attendance_obtained_marks' => $submittedCourse['attendance_obtained_marks'] ?? null,
                'mid_obtained_marks' => $submittedCourse['mid_obtained_marks'] ?? null,
                'final_obtained_marks' => $submittedCourse['final_obtained_marks'] ?? null,
            ];
        })->all();

        try {
            $summary = $this->calculateSemester(
                $coursesForCalculation,
                $publish
                    && config('academic.results.require_all_course_marks_to_publish')
                    && config('academic.assessment.require_all_components_to_publish')
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['courses' => $exception->getMessage()]);
        }

        return [
            'result' => [
                'student_semester_enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'semester_percentage' => $summary['semester_percentage'],
                'sgpa' => $summary['sgpa'],
                // Phase 5/6 supplies the server-calculated cumulative value before persistence.
                'cgpa' => null,
                'status' => $publish ? $summary['status'] : 'Draft',
                'published_at' => $publish ? now() : null,
            ],
            'items' => $summary['items'],
            'summary' => $summary,
        ];
    }

    /**
     * Calculate a student's CGPA from course result items. Items should be
     * passed oldest-to-newest; attempted_at/published_at makes ordering
     * explicit when the data comes from different semester result sheets.
     *
     * @param  iterable<array<string, mixed>|SemesterResultItem>  $items
     */
    public function calculateCgpa(iterable $items): ?float
    {
        $attempts = collect($items)
            ->map(fn ($item) => $this->normaliseCgpaItem($item))
            ->sortBy([
                ['attempted_at', 'asc'],
                ['result_id', 'asc'],
                ['item_id', 'asc'],
            ])
            ->values();

        if ($attempts->isEmpty()) {
            return null;
        }

        $policy = config('academic.repeat_course.cgpa_attempt_policy');
        if ($policy !== 'latest_attempt_replaces_previous') {
            throw new LogicException("Unsupported CGPA attempt policy [{$policy}].");
        }

        /** @var Collection<string, array<string, mixed>> $attemptsByCourse */
        $attemptsByCourse = $attempts->keyBy('course_key');
        $totalCreditHours = (float) $attemptsByCourse->sum('credit_hours');

        if ($totalCreditHours <= 0) {
            return null;
        }

        $qualityPoints = $attemptsByCourse->sum(
            fn (array $attempt) => $attempt['grade_point'] * $attempt['credit_hours']
        );

        return round($qualityPoints / $totalCreditHours, 2);
    }

    /**
     * Fetch published history from the database and combine it with optional
     * in-memory pending items. This lets the save transaction calculate CGPA
     * without trusting a number sent from the browser.
     *
     * @param  iterable<array<string, mixed>>  $pendingItems
     */
    public function calculateStudentCgpa(
        int $studentId,
        iterable $pendingItems = [],
        ?int $excludeSemesterResultId = null,
    ): ?float {
        $items = SemesterResultItem::query()
            ->whereHas('semesterResult', function ($query) use ($studentId, $excludeSemesterResultId) {
                $query->where('student_id', $studentId)->whereNotNull('published_at');

                if ($excludeSemesterResultId !== null) {
                    $query->whereKeyNot($excludeSemesterResultId);
                }
            })
            ->with('semesterResult:id,published_at')
            ->get()
            ->map(function (SemesterResultItem $item) {
                return [
                    'course_id' => $item->course_id,
                    'course_code' => $item->course_code,
                    'credit_hours' => $item->credit_hours,
                    'grade_point' => $item->grade_point,
                    'attempted_at' => $item->semesterResult->published_at,
                    'result_id' => $item->semester_result_id,
                    'item_id' => $item->id,
                ];
            })
            ->all();

        return $this->calculateCgpa([...$items, ...collect($pendingItems)->all()]);
    }

    /**
     * Rebuild every published result's cumulative GPA in academic order.
     * This must run after a historical result edit because later semesters
     * inherit all earlier quality points.
     */
    public function recalculatePublishedCgpas(int $studentId): void
    {
        $publishedResults = SemesterResult::query()
            ->where('student_id', $studentId)
            ->whereNotNull('published_at')
            ->with(['items', 'enrollment'])
            ->get()
            ->sortBy(fn (SemesterResult $result) => $this->semesterSortKey($result))
            ->values();
        $attempts = [];

        foreach ($publishedResults as $semesterResult) {
            foreach ($semesterResult->items as $item) {
                $attempts[] = [
                    'course_id' => $item->course_id,
                    'course_code' => $item->course_code,
                    'credit_hours' => $item->credit_hours,
                    'grade_point' => $item->grade_point,
                    'attempted_at' => $semesterResult->published_at,
                    'result_id' => $semesterResult->id,
                    'item_id' => $item->id,
                ];
            }

            $semesterResult->forceFill(['cgpa' => $this->calculateCgpa($attempts)])->saveQuietly();
        }
    }

    /** @return array{minimum_percentage: float, grade: string, grade_point: float, status: string} */
    private function gradeForPercentage(float $percentage): array
    {
        foreach (config('academic.grade_scale', []) as $grade) {
            if ($percentage >= (float) $grade['minimum_percentage']) {
                return [
                    'minimum_percentage' => (float) $grade['minimum_percentage'],
                    'grade' => $grade['grade'],
                    'grade_point' => (float) $grade['grade_point'],
                    'status' => $grade['status'],
                ];
            }
        }

        throw new LogicException('Academic grade scale does not cover the calculated percentage.');
    }

    private function validatedPositiveNumber(mixed $value, string $message): float
    {
        if (! is_numeric($value) || (float) $value <= 0) {
            throw new InvalidArgumentException($message);
        }

        return (float) $value;
    }

    private function validatedNonNegativeNumber(mixed $value, string $message): float
    {
        if (! is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException($message);
        }

        return (float) $value;
    }

    private function normaliseIntegerOrFloat(mixed $value): int|float
    {
        $number = (float) $value;

        return floor($number) === $number ? (int) $number : $number;
    }

    private function semesterSortKey(SemesterResult $result): string
    {
        $academicYear = $result->enrollment?->academic_year ?? '0000-0000';
        preg_match('/\d+/', $result->enrollment?->semester ?? '', $matches);
        $semesterNumber = (int) ($matches[0] ?? 0);

        return sprintf('%s-%02d-%s-%010d', $academicYear, $semesterNumber, $result->published_at?->format('YmdHis') ?? '', $result->id);
    }

    /** @param array<string, mixed>|SemesterResultItem $item @return array<string, mixed> */
    private function normaliseCgpaItem(array|SemesterResultItem $item): array
    {
        $value = $item instanceof SemesterResultItem
            ? [
                'course_id' => $item->course_id,
                'course_code' => $item->course_code,
                'credit_hours' => $item->credit_hours,
                'grade_point' => $item->grade_point,
                'attempted_at' => $item->semesterResult?->published_at,
                'result_id' => $item->semester_result_id,
                'item_id' => $item->id,
            ]
            : $item;

        $courseId = $value['course_id'] ?? null;
        $courseCode = $value['course_code'] ?? null;
        if ($courseId === null && blank($courseCode)) {
            throw new InvalidArgumentException('Each CGPA item must identify its course.');
        }

        if (! isset($value['grade_point']) || ! is_numeric($value['grade_point'])) {
            throw new InvalidArgumentException('Each CGPA item requires a numeric grade point.');
        }

        $attemptedAt = $value['attempted_at'] ?? $value['published_at'] ?? null;
        if ($attemptedAt instanceof CarbonInterface) {
            $attemptedAt = $attemptedAt->getTimestamp();
        } elseif ($attemptedAt !== null) {
            $attemptedAt = strtotime((string) $attemptedAt) ?: 0;
        }

        return [
            'course_key' => $courseId !== null ? "id:{$courseId}" : "code:{$courseCode}",
            'credit_hours' => $this->validatedPositiveNumber(
                $value['credit_hours'] ?? null,
                'Each CGPA item requires credit hours greater than zero.'
            ),
            'grade_point' => (float) $value['grade_point'],
            'attempted_at' => $attemptedAt ?? 0,
            'result_id' => (int) ($value['result_id'] ?? 0),
            'item_id' => (int) ($value['item_id'] ?? 0),
        ];
    }
}
