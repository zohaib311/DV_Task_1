<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Services\Assessment\AssessmentCalculator;
use App\Services\Assessment\AssessmentWorkflow;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AssessmentController extends Controller
{
    public function index(Request $request, TeacherWorkspace $workspace)
    {
        $offerings = $workspace->offerings($workspace->profile($request->user()))->with(['term', 'section'])->latest()->paginate(15);

        return view('teaching.assessments.index', compact('offerings'));
    }

    public function show(Request $request, int $offering, AssessmentWorkflow $workflow, AssessmentCalculator $calculator)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $offering->load('assessmentComponents.assessments');
        $preview = $calculator->preview($offering);
        $submissions = $offering->assessmentSubmissions()->with(['submitter', 'reviewer'])->latest()->get();
        $audits = $offering->assessmentAudits()->with('user')->latest('id')->paginate(10);

        return view('teaching.assessments.offering', compact('offering', 'preview', 'submissions', 'audits'));
    }

    public function store(Request $request, int $offering, AssessmentWorkflow $workflow)
    {
        $data = $request->validate($this->definitionRules());
        $newPath = $this->storeQuestionFile($request, $data);
        try {
            $assessment = $workflow->saveDefinition($request->user(), $offering, $data);
        } catch (Throwable $exception) {
            if ($newPath) Storage::disk('local')->delete($newPath);
            throw $exception;
        }

        return redirect()->route('teaching.assessments.edit', [$offering, $assessment])->with('success', 'Assessment created. Enter raw marks for the enrolled students.');
    }

    public function edit(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $assessment = Assessment::with(['component', 'marks', 'studentSubmissions'])->whereHas('component', fn ($q) => $q->where('course_offering_id', $offering->id))->findOrFail($assessment);
        $courses = $offering->enrollmentCourses()->with('enrollment.student')->orderBy('id')->get();
        $locked = $offering->status !== 'active' || $offering->term->status !== 'active'
            || $offering->enrollmentCourses()->whereHas('enrollment', fn ($query) => $query->where('status', '!=', 'active')->orWhereHas('semesterResult', fn ($query) => $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])))->exists();

        return view('teaching.assessments.marks', compact('offering', 'assessment', 'courses', 'locked'));
    }

    public function update(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $assigned = $workflow->assigned($request->user(), $offering);
        $existing = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $assigned->id))->findOrFail($assessment);
        $data = $request->validate($this->definitionRules() + ['revision' => ['required', 'integer', 'min:1']]);
        $removeQuestionFile = (bool) ($data['remove_question_file'] ?? false);
        $newPath = $this->storeQuestionFile($request, $data);
        $oldPath = ($newPath || $removeQuestionFile) ? $existing->question_file_path : null;
        if (! $newPath && $removeQuestionFile) {
            $data = array_merge($data, ['question_file_path' => null, 'question_original_filename' => null,
                'question_mime_type' => null, 'question_file_size' => null]);
        }
        try {
            $workflow->saveDefinition($request->user(), $offering, $data, $assessment);
        } catch (Throwable $exception) {
            if ($newPath) Storage::disk('local')->delete($newPath);
            throw $exception;
        }
        if ($oldPath && $oldPath !== $newPath) Storage::disk('local')->delete($oldPath);

        return back()->with('success', 'Unmarked assessment definition updated.');
    }

    public function questionFile(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $offering->id))->findOrFail($assessment);

        return $this->downloadQuestionFile($assessment);
    }

    public function marks(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1'], 'reason' => ['nullable', 'string', 'max:1000'],
            'marks' => ['required', 'array', 'min:1'], 'marks.*.course_id' => ['required', 'integer', 'distinct'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0', 'max:10000', 'decimal:0,2'],
            'marks.*.feedback' => ['nullable', 'string', 'max:5000']]);
        $workflow->saveMarks($request->user(), $offering, $assessment, $data);

        return back()->with('success', 'Marks saved. Blank entries remain incomplete; zero is a recorded score.');
    }

    public function release(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $workflow->releaseMarks($request->user(), $offering, $assessment);

        return back()->with('success', 'Assessment marks are now visible to enrolled students.');
    }

    public function submit(Request $request, int $offering, AssessmentWorkflow $workflow)
    {
        $workflow->submit($request->user(), $offering);

        return back()->with('success', 'Course marks submitted for review. Marks and attendance are now locked.');
    }

    private function definitionRules(): array
    {
        return ['component_id' => ['required', 'integer'], 'title' => ['required', 'string', 'max:120'], 'instructions' => ['nullable', 'string', 'max:5000'], 'held_on' => ['required', 'date_format:Y-m-d'],
            'maximum' => ['required', 'numeric', 'min:0.01', 'max:10000', 'decimal:0,2'], 'weight' => ['required', 'numeric', 'min:0.01', 'max:1000', 'decimal:0,2'],
            'submission_required' => ['nullable', 'boolean'], 'submissions_due_at' => ['nullable', 'date'],
            'question_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,zip,txt,jpg,jpeg,png'],
            'remove_question_file' => ['nullable', 'boolean']];
    }

    private function storeQuestionFile(Request $request, array &$data): ?string
    {
        $file = $request->file('question_file');
        unset($data['question_file'], $data['remove_question_file']);
        if (! $file) return null;
        $path = $file->store('assessment-questions');
        $data = array_merge($data, ['question_file_path' => $path, 'question_original_filename' => $file->getClientOriginalName(),
            'question_mime_type' => $file->getMimeType(), 'question_file_size' => $file->getSize()]);

        return $path;
    }

    private function downloadQuestionFile(Assessment $assessment)
    {
        abort_unless($assessment->question_file_path && Storage::disk('local')->exists($assessment->question_file_path), 404);

        return Storage::disk('local')->download($assessment->question_file_path, $assessment->question_original_filename ?: 'assessment-question');
    }
}
