<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferingController extends Controller
{
    public function index(Request $request, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'term_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['planned', 'active', 'marks_submitted', 'reviewed', 'completed'])],
        ]);
        $assigned = $workspace->offerings($teacher);

        return view('teaching.offerings.index', [
            'teacher' => $teacher,
            'terms' => AcademicTerm::with('academicYear')->whereIn('id', (clone $assigned)->select('academic_term_id'))->orderByDesc('starts_on')->get(),
            'offerings' => $assigned->with(['term.academicYear', 'section', 'department', 'program'])->withCount('enrollmentCourses')
                ->when($filters['term_id'] ?? null, fn ($query, $term) => $query->where('academic_term_id', $term))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('course_name', 'like', '%'.$search.'%')->orWhere('course_code', 'like', '%'.$search.'%')))
                ->orderByDesc('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request, string $offering, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offering = $workspace->offerings($teacher)->with(['term.academicYear', 'section', 'department', 'program', 'teachers'])->findOrFail($offering);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        return view('teaching.offerings.show', [
            'teacher' => $teacher,
            'offering' => $offering,
            'rosterCount' => $offering->enrollmentCourses()->count(),
            'roster' => $offering->enrollmentCourses()->with('enrollment.student')
                ->when($filters['q'] ?? null, fn ($query, $search) => $query->whereHas('enrollment.student', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('registration_no', 'like', '%'.$search.'%'))))
                ->orderBy('id')->paginate(25)->withQueryString(),
        ]);
    }
}
