<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Services\Assessment\AssessmentCalculator;
use App\Services\Assessment\AssessmentWorkflow;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;

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
        $assessment = $workflow->saveDefinition($request->user(), $offering, $request->validate($this->definitionRules()));

        return redirect()->route('teaching.assessments.edit', [$offering, $assessment])->with('success', 'Assessment created. Enter raw marks for the enrolled students.');
    }

    public function edit(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $offering = $workflow->assigned($request->user(), $offering);
        $assessment = Assessment::with(['component', 'marks'])->whereHas('component', fn ($q) => $q->where('course_offering_id', $offering->id))->findOrFail($assessment);
        $courses = $offering->enrollmentCourses()->with('enrollment.student')->orderBy('id')->get();
        $locked = $offering->status !== 'active' || $offering->term->status !== 'active'
            || $offering->enrollmentCourses()->whereHas('enrollment', fn ($query) => $query->where('status', '!=', 'active')->orWhereHas('semesterResult', fn ($query) => $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])))->exists();

        return view('teaching.assessments.marks', compact('offering', 'assessment', 'courses', 'locked'));
    }

    public function update(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $workflow->saveDefinition($request->user(), $offering, $request->validate($this->definitionRules() + ['revision' => ['required', 'integer', 'min:1']]), $assessment);

        return back()->with('success', 'Unmarked assessment definition updated.');
    }

    public function marks(Request $request, int $offering, int $assessment, AssessmentWorkflow $workflow)
    {
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1'], 'reason' => ['nullable', 'string', 'max:1000'],
            'marks' => ['required', 'array', 'min:1'], 'marks.*.course_id' => ['required', 'integer', 'distinct'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0', 'max:10000', 'decimal:0,2']]);
        $workflow->saveMarks($request->user(), $offering, $assessment, $data);

        return back()->with('success', 'Marks saved. Blank entries remain incomplete; zero is a recorded score.');
    }

    public function submit(Request $request, int $offering, AssessmentWorkflow $workflow)
    {
        $workflow->submit($request->user(), $offering);

        return back()->with('success', 'Course marks submitted for review. Marks and attendance are now locked.');
    }

    private function definitionRules(): array
    {
        return ['component_id' => ['required', 'integer'], 'title' => ['required', 'string', 'max:120'], 'held_on' => ['required', 'date_format:Y-m-d'],
            'maximum' => ['required', 'numeric', 'min:0.01', 'max:10000', 'decimal:0,2'], 'weight' => ['required', 'numeric', 'min:0.01', 'max:1000', 'decimal:0,2']];
    }
}
