<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Assessment\AssessmentSubmission;
use App\Services\Assessment\AssessmentWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssessmentReviewController extends Controller
{
    public function index()
    {
        $submissions = AssessmentSubmission::with(['offering.term', 'offering.section', 'submitter'])->orderByRaw("CASE WHEN status = 'submitted' THEN 0 ELSE 1 END")->latest()->paginate(20);

        return view('academic.assessments.reviews', compact('submissions'));
    }

    public function show(AssessmentSubmission $submission)
    {
        $submission->load(['offering.term', 'offering.section', 'submitter', 'reviewer']);

        return view('academic.assessments.review', compact('submission'));
    }

    public function update(Request $request, AssessmentSubmission $submission, AssessmentWorkflow $workflow)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'returned'])], 'reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $workflow->review($request->user(), $submission->id, $data['decision'], $data['reason']);

        return back()->with('success', $data['decision'] === 'approved' ? 'Course submission approved. Semester publication remains a separate workflow.' : 'Submission returned to the teacher for correction.');
    }
}
