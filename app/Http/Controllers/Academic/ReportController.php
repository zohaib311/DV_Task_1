<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Services\AcademicEnrollmentService;
use App\Services\Attendance\AttendanceCalculator;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, AcademicEnrollmentService $enrollmentService)
    {
        $data = $request->validate(['term' => ['nullable', 'integer', 'exists:academic_terms,id'], 'minimum_attendance' => ['nullable', 'numeric', 'min:0', 'max:100']]);
        $offerings = CourseOffering::with(['term', 'section', 'teachers'])->when($data['term'] ?? null, fn ($query, $term) => $query->where('academic_term_id', $term))->get();
        $threshold = (float) ($data['minimum_attendance'] ?? 75);
        $workload = $offerings->map(fn ($offering) => ['teacher_names' => $offering->teachers->pluck('name')->implode(', ') ?: 'Unassigned', 'course' => $offering->course_code.' · '.$offering->course_name, 'term' => $offering->term?->name, 'section' => $offering->section?->name, 'students' => $offering->enrollmentCourses()->count(), 'status' => $offering->status]);
        $attendance = app(AttendanceCalculator::class);
        $shortages = $offerings->flatMap(function ($offering) use ($threshold, $attendance) {
            return $offering->enrollmentCourses()->with('enrollment.student')->get()->map(function ($course) use ($offering, $attendance) {
                $summary = $attendance->summary($course);

                return ['course' => $course, 'offering' => $offering, 'valid' => $summary['valid_sessions'], 'percentage' => $summary['percentage'], 'student' => $course->enrollment->student];
            })->filter(fn ($row) => $row['percentage'] !== null && $row['percentage'] < $threshold);
        })->values();
        $results = SemesterResult::with('enrollment')->whereNotNull('published_at')->when($data['term'] ?? null, fn ($q, $term) => $q->whereHas('enrollment', fn ($q) => $q->where('academic_term_id', $term)))->get();
        $resultSummary = ['published' => $results->count(), 'pass' => $results->where('status', 'Pass')->count(), 'fail' => $results->where('status', 'Fail')->count(), 'average_sgpa' => $results->avg('sgpa') ? round($results->avg('sgpa'), 2) : null, 'average_percentage' => $results->avg('semester_percentage') ? round($results->avg('semester_percentage'), 2) : null];
        $promotion = StudentSemesterEnrollment::with(['student', 'semesterResult'])->where('status', 'active')->get()->map(fn ($row) => ['enrollment' => $row, 'eligibility' => $enrollmentService->promotionEligibility($row)])->filter(fn ($row) => $row['eligibility']['allowed'])->values();
        $terms = AcademicTerm::orderByDesc('starts_on')->get();

        return view('academic.reports.index', compact('terms', 'workload', 'shortages', 'threshold', 'resultSummary', 'promotion'));
    }
}
