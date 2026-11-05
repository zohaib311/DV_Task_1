<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Models\Lms\StudentQuizAttempt;
use App\Services\Lms\LmsService;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseLmsController extends Controller
{
    public function __construct(
        protected LmsService $lmsService
    ) {}

    /**
     * Display course LMS workspace for the enrolled student.
     */
    public function show(StudentEnrollmentCourse $course, StudentWorkspace $workspace, Request $request)
    {
        $student = $workspace->student($request->user());
        abort_unless($course->enrollment && $course->enrollment->student_id === $student->id, 403, 'You are not enrolled in this course.');

        $offering = $course->offering;
        abort_unless($offering, 404, 'No active course offering linked to this registration.');

        $materials = $offering->materials()
            ->where('is_published', true)
            ->orderBy('week_number')
            ->latest()
            ->get()
            ->groupBy(fn ($item) => $item->week_number ? "Week {$item->week_number}" : 'General Resources');

        $announcements = $offering->announcements()->latest()->get();

        $quizzes = $offering->quizzes()
            ->where('is_published', true)
            ->with(['questions', 'attempts' => fn ($q) => $q->where('student_id', $student->id)])
            ->latest()
            ->get();

        return view('student-portal.lms.show', [
            'student' => $student,
            'course' => $course,
            'offering' => $offering,
            'materials' => $materials,
            'announcements' => $announcements,
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Securely download course material only if enrolled.
     */
    public function downloadMaterial(StudentEnrollmentCourse $course, CourseMaterial $material, StudentWorkspace $workspace, Request $request)
    {
        $student = $workspace->student($request->user());
        abort_unless($course->enrollment && $course->enrollment->student_id === $student->id, 403, 'Unauthorized access.');
        abort_unless($material->course_offering_id === $course->course_offering_id, 404);
        abort_unless($material->is_published, 404);
        abort_unless($material->hasFile(), 404, 'File not found on storage server.');

        return Storage::disk('local')->download($material->file_path, $material->file_name ?: 'material');
    }

    /**
     * Start a timed MCQ quiz.
     */
    public function startQuiz(StudentEnrollmentCourse $course, Quiz $quiz, StudentWorkspace $workspace, Request $request)
    {
        $student = $workspace->student($request->user());
        abort_unless($course->enrollment && $course->enrollment->student_id === $student->id, 403, 'Unauthorized.');
        abort_unless($quiz->course_offering_id === $course->course_offering_id, 404);

        $attempt = $this->lmsService->startQuizAttempt($quiz, $student);

        if ($attempt->isCompleted()) {
            return redirect()->route('student.courses.lms.quizzes.result', [$course, $quiz]);
        }

        $quiz->load('questions');

        return view('student-portal.lms.quiz-take', [
            'student' => $student,
            'course' => $course,
            'quiz' => $quiz,
            'attempt' => $attempt,
            'remainingSeconds' => $attempt->remainingSeconds(),
        ]);
    }

    /**
     * Submit quiz answers for auto-grading.
     */
    public function submitQuiz(Request $request, StudentEnrollmentCourse $course, Quiz $quiz, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        abort_unless($course->enrollment && $course->enrollment->student_id === $student->id, 403, 'Unauthorized.');
        abort_unless($quiz->course_offering_id === $course->course_offering_id, 404);

        $attempt = StudentQuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $answers = $request->input('answers', []);
        $this->lmsService->submitQuizAttempt($attempt, $answers);

        return redirect()->route('student.courses.lms.quizzes.result', [$course, $quiz])
            ->with('success', 'Quiz completed! Your score has been auto-graded.');
    }

    /**
     * View completed quiz result and score breakdown.
     */
    public function quizResult(StudentEnrollmentCourse $course, Quiz $quiz, StudentWorkspace $workspace, Request $request)
    {
        $student = $workspace->student($request->user());
        abort_unless($course->enrollment && $course->enrollment->student_id === $student->id, 403, 'Unauthorized.');
        abort_unless($quiz->course_offering_id === $course->course_offering_id, 404);

        $attempt = StudentQuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->with(['quiz.questions'])
            ->firstOrFail();

        return view('student-portal.lms.quiz-result', [
            'student' => $student,
            'course' => $course,
            'quiz' => $quiz,
            'attempt' => $attempt,
        ]);
    }
}

