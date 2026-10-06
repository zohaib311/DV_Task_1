<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\CourseOffering;
use App\Models\Assessment\AssessmentComponent;
use App\Services\Assessment\AssessmentWorkflow;
use Illuminate\Http\Request;
use App\Services\Notifications\AcademicNotificationService;

class AssessmentSchemeController extends Controller
{
    public function edit(CourseOffering $offering)
    {
        $offering->load(['assessmentComponents', 'term', 'section']);
        $codes = AssessmentComponent::CODES;

        return view('academic.assessments.scheme', compact('offering', 'codes'));
    }

    public function store(Request $request, CourseOffering $offering, AssessmentWorkflow $workflow)
    {
        $rules = ['allocations' => ['required', 'array:'.implode(',', AssessmentComponent::CODES)]];
        foreach (AssessmentComponent::CODES as $code) {
            $rules['allocations.'.$code] = ['required', 'numeric', 'min:0', 'max:1000', 'decimal:0,2'];
        }
        $workflow->approveScheme($request->user(), $offering->id, $request->validate($rules)['allocations']);
        $offering->refresh();
        $notifications = app(AcademicNotificationService::class);
        $notifications->users($notifications->assignedTeachers($offering), 'Assessment scheme approved',
            "The assessment scheme for {$offering->course_code} is approved. You can create assessments now.",
            route('teaching.assessments.show', $offering), 'assessment', ['offering_id' => $offering->id]);

        return back()->with('success', 'Assessment scheme approved and locked. Assigned teachers can now create assessments.');
    }
}
