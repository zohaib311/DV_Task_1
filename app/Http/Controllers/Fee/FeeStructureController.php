<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\Program;
use App\Models\Fee\FeeStructure;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function index()
    {
        return view('fees.structures.index', [
            'structures' => FeeStructure::with(['program', 'term'])->latest()->get(),
            'programs' => Program::where('is_active', true)->orderBy('name')->get(),
            'terms' => AcademicTerm::with('academicYear')->orderByDesc('starts_on')->get(),
        ]);
    }

    public function store(Request $request)
    {
        FeeStructure::create($this->validated($request));
        return back()->with('success', 'Fee structure was created. It will apply to new or recalculated unpaid invoices.');
    }

    public function update(Request $request, FeeStructure $structure)
    {
        $structure->update($this->validated($request));
        return back()->with('success', 'Fee structure updated. Existing submitted and approved invoices remain unchanged.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'program_id' => ['required', 'exists:academic_programs,id'], 'academic_term_id' => ['nullable', 'exists:academic_terms,id'],
            'name' => ['required', 'string', 'max:120'], 'due_days' => ['required', 'integer', 'min:1', 'max:180'], 'is_active' => ['nullable', 'boolean'],
            'admission_fee' => ['nullable', 'numeric', 'min:0'], 'registration_fee' => ['nullable', 'numeric', 'min:0'], 'security_fee' => ['nullable', 'numeric', 'min:0'],
            'processing_fee' => ['nullable', 'numeric', 'min:0'], 'enrollment_fee' => ['nullable', 'numeric', 'min:0'], 'tuition_per_credit_hour' => ['required', 'numeric', 'min:0'],
            'exam_fee' => ['nullable', 'numeric', 'min:0'], 'convocation_fee' => ['nullable', 'numeric', 'min:0'], 'late_fee' => ['nullable', 'numeric', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
