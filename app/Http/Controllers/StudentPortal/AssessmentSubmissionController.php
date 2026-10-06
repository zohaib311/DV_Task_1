<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAssessmentSubmission;
use App\Services\Assessment\AssessmentWorkflow;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssessmentSubmissionController extends Controller
{
    public function store(Request $request, int $course, int $assessment, StudentWorkspace $workspace)
    {
        $data = $request->validate([
            'answer_text' => ['nullable', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png'],
        ]);
        if (! trim($data['answer_text'] ?? '') && ! $request->hasFile('attachment')) {
            throw ValidationException::withMessages(['submission' => 'Write an answer or attach a file before submitting.']);
        }
        $course = $workspace->courses($request->user())->with(['enrollment.term', 'offering'])->findOrFail($course);
        $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $course->course_offering_id))->findOrFail($assessment);
        if (! $assessment->submission_required || $course->enrollment->status !== 'active' || $course->enrollment->term?->status !== 'active') {
            throw ValidationException::withMessages(['submission' => 'This assessment is not open for student submissions.']);
        }
        if (! $assessment->submissions_due_at || now()->isAfter($assessment->submissions_due_at)) {
            throw ValidationException::withMessages(['submission' => 'The submission deadline has passed.']);
        }

        $newPath = $request->file('attachment')?->store('student-assessments');
        $oldPath = null;
        DB::transaction(function () use ($request, $data, $course, $assessment, $newPath, &$oldPath) {
            $submission = StudentAssessmentSubmission::where('assessment_id', $assessment->id)
                ->where('student_enrollment_course_id', $course->id)->lockForUpdate()->first();
            $oldPath = $newPath ? $submission?->attachment_path : null;
            StudentAssessmentSubmission::updateOrCreate(
                ['assessment_id' => $assessment->id, 'student_enrollment_course_id' => $course->id],
                ['submitted_by' => $request->user()->id, 'answer_text' => trim($data['answer_text'] ?? ''),
                    'attachment_path' => $newPath ?: $submission?->attachment_path,
                    'original_filename' => $newPath ? $request->file('attachment')->getClientOriginalName() : $submission?->original_filename,
                    'status' => $submission ? 'resubmitted' : 'submitted', 'submitted_at' => now(),
                    'teacher_feedback' => null, 'reviewed_at' => null]
            );
        });
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return back()->with('success', 'Your assessment submission has been saved.');
    }

    public function studentDownload(Request $request, int $submission, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $submission = StudentAssessmentSubmission::whereHas('enrollmentCourse.enrollment', fn ($query) => $query->where('student_id', $student->id))->findOrFail($submission);

        return $this->download($submission);
    }

    public function teacherShow(Request $request, int $offering, int $assessment, int $submission, AssessmentWorkflow $workflow)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $offering->id))->findOrFail($assessment);
        $submission = StudentAssessmentSubmission::with('enrollmentCourse.enrollment.student')
            ->where('assessment_id', $assessment->id)
            ->whereHas('enrollmentCourse', fn ($query) => $query->where('course_offering_id', $offering->id))
            ->findOrFail($submission);

        return view('teaching.assessments.student-submission', compact('offering', 'assessment', 'submission'));
    }

    public function teacherDownload(Request $request, int $offering, int $assessment, int $submission, AssessmentWorkflow $workflow)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $offering->id))->findOrFail($assessment);
        $submission = StudentAssessmentSubmission::where('assessment_id', $assessment->id)
            ->whereHas('enrollmentCourse', fn ($query) => $query->where('course_offering_id', $offering->id))->findOrFail($submission);

        return $this->download($submission);
    }

    private function download(StudentAssessmentSubmission $submission)
    {
        abort_unless($submission->attachment_path && Storage::disk('local')->exists($submission->attachment_path), 404);

        return Storage::disk('local')->download($submission->attachment_path, $submission->original_filename ?: 'assessment-submission');
    }
}
