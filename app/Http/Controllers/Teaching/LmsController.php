<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Academic\CourseOffering;
use App\Models\Lms\CourseAnnouncement;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Services\Lms\LmsService;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LmsController extends Controller
{
    public function __construct(
        protected LmsService $lmsService
    ) {}

    /**
     * Display course LMS management hub (Materials, Announcements, Quizzes).
     */
    public function index(Request $request, CourseOffering $offering, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->with(['term.academicYear', 'section', 'department'])->findOrFail($offering->id);

        $materials = $offering->materials()->orderBy('week_number')->latest()->get();
        $announcements = $offering->announcements()->latest()->get();
        $quizzes = $offering->quizzes()->withCount(['questions', 'attempts'])->latest()->get();

        // Get available quiz assessment components if teacher wants to link auto-grading
        $assessments = \App\Models\Assessment\Assessment::whereHas('component', fn ($q) => $q->where('course_offering_id', $offering->id))
            ->orderBy('title')
            ->get();

        return view('teaching.lms.index', [
            'teacher' => $teacher,
            'offering' => $offering,
            'materials' => $materials,
            'announcements' => $announcements,
            'quizzes' => $quizzes,
            'assessments' => $assessments,
        ]);
    }

    /**
     * Upload and publish course material.
     */
    public function storeMaterial(Request $request, CourseOffering $offering, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'week_number' => 'nullable|integer|min:1|max:30',
            'description' => 'nullable|string|max:1000',
            'file' => 'nullable|file|max:20480|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,zip,txt,jpg,jpeg,png',
            'external_url' => 'nullable|url|max:1000',
            'is_published' => 'nullable|boolean',
        ]);

        if (! $request->hasFile('file') && empty($validated['external_url'])) {
            return redirect()->back()->withErrors(['file' => 'Please either upload a document file or provide an external URL link.'])->withInput();
        }

        $this->lmsService->createMaterial($offering, $validated, $request->file('file'), $request->user());

        return redirect()->back()->with('success', "Course material '{$validated['title']}' has been added.");
    }

    /**
     * Download material as assigned teacher.
     */
    public function downloadMaterial(CourseOffering $offering, CourseMaterial $material, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        abort_unless($material->course_offering_id === $offering->id, 404);
        abort_unless($material->hasFile(), 404, 'Material file not found in storage.');

        return Storage::disk('local')->download($material->file_path, $material->file_name ?: 'material');
    }

    /**
     * Delete course material.
     */
    public function destroyMaterial(CourseOffering $offering, CourseMaterial $material, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        abort_unless($material->course_offering_id === $offering->id, 404);
        $this->lmsService->deleteMaterial($material);

        return redirect()->back()->with('success', 'Material deleted successfully.');
    }

    /**
     * Post a course announcement.
     */
    public function storeAnnouncement(Request $request, CourseOffering $offering, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string|max:5000',
            'priority' => 'required|in:normal,urgent',
        ]);

        $this->lmsService->createAnnouncement($offering, $validated, $request->user());

        return redirect()->back()->with('success', "Announcement '{$validated['title']}' posted and students notified.");
    }

    /**
     * Delete course announcement.
     */
    public function destroyAnnouncement(CourseOffering $offering, CourseAnnouncement $announcement, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        abort_unless($announcement->course_offering_id === $offering->id, 404);
        $this->lmsService->deleteAnnouncement($announcement);

        return redirect()->back()->with('success', 'Announcement removed.');
    }

    /**
     * Show quiz creation form with dynamic question builder.
     */
    public function createQuiz(CourseOffering $offering, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        $assessments = \App\Models\Assessment\Assessment::whereHas('component', fn ($q) => $q->where('course_offering_id', $offering->id))
            ->orderBy('title')
            ->get();

        return view('teaching.lms.quizzes.create', [
            'teacher' => $teacher,
            'offering' => $offering,
            'assessments' => $assessments,
        ]);
    }

    /**
     * Store new quiz with questions.
     */
    public function storeQuiz(Request $request, CourseOffering $offering, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:1|max:180',
            'assessment_id' => 'nullable|exists:assessments,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_published' => 'nullable|boolean',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string|max:500',
            'questions.*.option_b' => 'required|string|max:500',
            'questions.*.option_c' => 'required|string|max:500',
            'questions.*.option_d' => 'required|string|max:500',
            'questions.*.correct_option' => 'required|in:a,b,c,d',
            'questions.*.points' => 'required|numeric|min:0.5|max:100',
        ]);

        $quiz = $this->lmsService->createQuiz($offering, $validated, $validated['questions'], $request->user());

        return redirect()->route('teaching.offerings.lms.quizzes.show', [$offering, $quiz])
            ->with('success', "Quiz '{$quiz->title}' created with {$quiz->questions()->count()} questions.");
    }

    /**
     * View quiz details, questions, and student submission attempts.
     */
    public function showQuiz(CourseOffering $offering, Quiz $quiz, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        abort_unless($quiz->course_offering_id === $offering->id, 404);

        $quiz->load(['questions', 'assessment']);
        $attempts = $quiz->attempts()->with('student')->latest('completed_at')->get();

        return view('teaching.lms.quizzes.show', [
            'teacher' => $teacher,
            'offering' => $offering,
            'quiz' => $quiz,
            'attempts' => $attempts,
        ]);
    }

    /**
     * Delete quiz.
     */
    public function destroyQuiz(CourseOffering $offering, Quiz $quiz, TeacherWorkspace $workspace, Request $request)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->findOrFail($offering->id);

        abort_unless($quiz->course_offering_id === $offering->id, 404);
        $this->lmsService->deleteQuiz($quiz);

        return redirect()->route('teaching.offerings.lms.index', $offering)
            ->with('success', 'Quiz deleted successfully.');
    }
}

