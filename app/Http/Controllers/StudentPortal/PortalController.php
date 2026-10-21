<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    public function dashboard(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user())->load(['department', 'section', 'activeSemesterEnrollment.term', 'activeSemesterEnrollment.section']);
        $enrollment = $student->activeSemesterEnrollment;
        $courseCount = $enrollment?->courses()->count() ?? 0;
        $latestResult = $request->user()->can('student.result.view-own') ? $workspace->results($request->user())->with('enrollment')->get()
            ->sortByDesc(fn ($result) => $result->enrollment->academic_year.'-'.str_pad(preg_replace('/\D/', '', $result->enrollment->semester), 2, '0', STR_PAD_LEFT))->first() : null;

        return view('student-portal.dashboard', compact('student', 'enrollment', 'courseCount', 'latestResult'));
    }

    public function courses(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $enrollments = $student->semesterEnrollments()->orderByDesc('id')->get();
        $data = $request->validate(['enrollment' => ['nullable', 'integer']]);
        if (isset($data['enrollment'])) {
            $student->semesterEnrollments()->findOrFail($data['enrollment']);
        }
        $courses = $workspace->courses($request->user())->with(['enrollment.term', 'offering.teachers'])
            ->when($data['enrollment'] ?? null, fn ($query, $id) => $query->where('student_semester_enrollment_id', $id))
            ->orderByDesc('student_semester_enrollment_id')->orderBy('course_code')->paginate(20)->withQueryString();

        return view('student-portal.courses', compact('enrollments', 'courses'));
    }

    public function results(Request $request, StudentWorkspace $workspace)
    {
        $history = $workspace->results($request->user())->with(['enrollment', 'items'])->get();
        $sortKey = fn ($result) => sprintf(
            '%s-%02d-%s-%010d',
            $result->enrollment->academic_year ?? '0000-0000',
            (int) preg_replace('/\D/', '', $result->enrollment->semester ?? ''),
            $result->published_at?->format('YmdHis') ?? '',
            $result->id,
        );
        $latestAttempts = collect();
        $history->sortBy($sortKey)->each(function ($result) use ($latestAttempts) {
            $result->items->each(function ($item) use ($latestAttempts, $result) {
                $key = $item->course_id ? 'id:'.$item->course_id : 'code:'.$item->course_code;
                $latestAttempts->put($key, ['item' => $item, 'result' => $result]);
            });
        });
        $latestResult = $history->sortBy($sortKey)->last();
        $backlogs = $latestAttempts->filter(fn ($attempt) => $attempt['item']->status === 'Fail');
        $degreeProgress = [
            'current_cgpa' => $latestResult?->cgpa,
            'published_semesters' => $history->count(),
            'cgpa_credits' => $latestAttempts->sum(fn ($attempt) => (float) $attempt['item']->credit_hours),
            'passed_credits' => $latestAttempts->reject(fn ($attempt) => $attempt['item']->status === 'Fail')->sum(fn ($attempt) => (float) $attempt['item']->credit_hours),
            'backlog_courses' => $backlogs->count(),
            'backlog_credits' => $backlogs->sum(fn ($attempt) => (float) $attempt['item']->credit_hours),
        ];
        $results = $workspace->results($request->user())->with(['enrollment', 'items'])->orderByDesc('id')->paginate(15);

        return view('student-portal.results.index', compact('results', 'degreeProgress'));
    }

    public function result(Request $request, int $result, StudentWorkspace $workspace)
    {
        $result = $workspace->results($request->user())->with(['enrollment', 'items'])->findOrFail($result);
        $student = $workspace->student($request->user());

        return view('student-portal.results.show', compact('result', 'student'));
    }

    /**
     * A student-owned, publication-only transcript. Failed attempts are kept in
     * the academic record for progression, but are deliberately excluded from
     * the transcript's course rows. A later passed attempt remains visible and
     * is labelled as a repeat when an earlier attempt exists.
     */
    public function transcript(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user())->load(['department', 'program', 'section']);
        $results = $workspace->results($request->user())->with(['enrollment', 'items'])->get()
            ->sortBy(fn ($result) => sprintf(
                '%s-%02d-%s-%010d',
                $result->enrollment->academic_year ?? '0000-0000',
                (int) preg_replace('/\D/', '', $result->enrollment->semester ?? ''),
                $result->published_at?->format('YmdHis') ?? '',
                $result->id,
            ))->values();

        $attemptCounts = collect();
        $latestPassedCourses = collect();
        $semesters = $results->map(function ($result) use ($attemptCounts, $latestPassedCourses) {
            $courses = $result->items->map(function ($item) use ($attemptCounts, $latestPassedCourses) {
                $key = $item->course_id ? 'id:'.$item->course_id : 'code:'.$item->course_code;
                $isRepeat = $attemptCounts->has($key);
                $attemptCounts->put($key, ((int) $attemptCounts->get($key, 0)) + 1);

                if ($item->status !== 'Pass') {
                    return null;
                }

                $latestPassedCourses->put($key, $item);

                return [
                    'code' => $item->course_code,
                    'name' => $item->course_name,
                    'credits' => (float) $item->credit_hours,
                    'percentage' => (float) $item->percentage,
                    'grade' => $item->grade,
                    'grade_point' => (float) $item->grade_point,
                    'repeated' => $isRepeat,
                ];
            })->filter()->values();

            return [
                'semester' => $result->enrollment->semester,
                'academic_year' => $result->enrollment->academic_year,
                'sgpa' => $result->sgpa,
                'cgpa' => $result->cgpa,
                'published_at' => $result->published_at,
                'courses' => $courses,
            ];
        });

        $latestResult = $results->last();
        $transcript = [
            'semesters' => $semesters,
            'published_semesters' => $results->count(),
            'shown_courses' => $semesters->sum(fn ($semester) => $semester['courses']->count()),
            'earned_credits' => $latestPassedCourses->sum(fn ($course) => (float) $course->credit_hours),
            'cgpa' => $latestResult?->cgpa,
            'issued_at' => now(),
        ];

        return view('student-portal.transcript', [
            'student' => $student,
            'transcript' => $transcript,
            'print' => $request->boolean('print'),
        ]);
    }

    public function assessments(Request $request, StudentWorkspace $workspace)
    {
        $courses = $workspace->courses($request->user())->with('enrollment')->orderByDesc('id')->paginate(20);

        return view('student-portal.assessments.index', compact('courses'));
    }

    public function assessment(Request $request, int $course, StudentWorkspace $workspace)
    {
        $course = $workspace->courses($request->user())->with(['enrollment.term', 'offering.assessmentComponents.assessments.marks', 'offering.assessmentComponents.assessments.studentSubmissions'])->findOrFail($course);
        $result = $workspace->results($request->user())->where('student_semester_enrollment_id', $course->student_semester_enrollment_id)->first();
        $item = $result?->items()->where('student_enrollment_course_id', $course->id)->first();

        $liveAssessments = $course->offering?->assessmentComponents->flatMap->assessments->sortBy('held_on') ?? collect();

        return view('student-portal.assessments.show', compact('course', 'item', 'liveAssessments'));
    }

    public function assessmentQuestion(Request $request, int $course, int $assessment, StudentWorkspace $workspace)
    {
        $course = $workspace->courses($request->user())->findOrFail($course);
        $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $course->course_offering_id))->findOrFail($assessment);
        abort_unless($assessment->question_file_path && Storage::disk('local')->exists($assessment->question_file_path), 404);

        return Storage::disk('local')->download($assessment->question_file_path, $assessment->question_original_filename ?: 'assessment-question');
    }
}
