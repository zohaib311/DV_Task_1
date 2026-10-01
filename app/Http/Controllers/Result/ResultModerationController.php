<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Services\Result\ResultModerationService;
use Illuminate\Http\Request;

class ResultModerationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', 'in:pending,approved,published']]);
        $enrollments = StudentSemesterEnrollment::with(['student', 'term', 'section', 'semesterResult'])
            ->whereHas('courses.offering', fn ($query) => $query->whereNotNull('assessment_scheme_approved_at'))
            ->when($data['search'] ?? null, fn ($query, $search) => $query->whereHas('student', fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('registration_no', 'like', '%'.$search.'%')))
            ->when(($data['state'] ?? null) === 'pending', fn ($query) => $query->whereDoesntHave('semesterResult'))
            ->when(($data['state'] ?? null) === 'approved', fn ($query) => $query->whereHas('semesterResult', fn ($query) => $query->whereNull('published_at')->whereNotNull('reviewed_at')))
            ->when(($data['state'] ?? null) === 'published', fn ($query) => $query->whereHas('semesterResult', fn ($query) => $query->whereNotNull('published_at')))
            ->latest()->paginate(20)->withQueryString();

        return view('results.moderation.index', compact('enrollments'));
    }

    public function show(StudentSemesterEnrollment $enrollment, ResultModerationService $service)
    {
        $enrollment->load(['student', 'term', 'section', 'semesterResult.items', 'semesterResult.audits.updatedBy']);
        $result = $enrollment->semesterResult;
        abort_if($result && $result->source !== 'teacher', 422, 'This record uses the existing manual result workflow.');
        $preview = $result ? null : $service->preview($enrollment);

        return view('results.moderation.show', compact('enrollment', 'result', 'preview'));
    }

    public function approve(Request $request, StudentSemesterEnrollment $enrollment, ResultModerationService $service)
    {
        $data = $request->validate(['token' => ['required', 'string', 'size:64'], 'reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $service->approve($request->user(), $enrollment, $data['token'], $data['reason']);

        return back()->with('success', 'Semester sheet approved and frozen. It is awaiting publication.');
    }

    public function publish(Request $request, StudentSemesterEnrollment $enrollment, ResultModerationService $service)
    {
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        $service->publish($request->user(), $enrollment, $data['revision']);

        return back()->with('success', 'Semester result published. SGPA and cumulative GPA have been calculated.');
    }

    public function correct(Request $request, StudentSemesterEnrollment $enrollment, ResultModerationService $service)
    {
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1'], 'reason' => ['required', 'string', 'min:5', 'max:1000'], 'marks' => ['required', 'array'], 'marks.*' => ['required', 'numeric', 'min:0', 'max:10000', 'decimal:0,2']]);
        $service->correct($request->user(), $enrollment, $data['revision'], $data['marks'], $data['reason']);

        return back()->with('success', 'Published correction saved and audited. All affected cumulative GPAs were recalculated.');
    }
}
