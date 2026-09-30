<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicTermController extends Controller
{
    public function index()
    {
        return view('academic.terms', [
            'years' => AcademicYear::orderByDesc('starts_on')->get(),
            'terms' => AcademicTerm::with('academicYear')->withCount('offerings')->orderByDesc('starts_on')->paginate(20),
        ]);
    }

    public function storeYear(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^\d{4}-\d{4}$/', 'unique:academic_years,name'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after:starts_on'],
        ]);
        [$start, $end] = array_map('intval', explode('-', $data['name']));
        if ($end !== $start + 1 || (int) substr($data['starts_on'], 0, 4) !== $start || (int) substr($data['ends_on'], 0, 4) !== $end) {
            throw ValidationException::withMessages(['name' => 'Use consecutive years and dates within those years, for example 2026-2027.']);
        }
        AcademicYear::create($data);

        return back()->with('success', 'Academic year created. Add its teaching terms next.');
    }

    public function store(Request $request)
    {
        $this->save($request, new AcademicTerm);

        return redirect()->route('academic.terms.index')->with('success', 'Academic term created successfully.');
    }

    public function edit(AcademicTerm $term)
    {
        return view('academic.term-edit', ['term' => $term, 'years' => AcademicYear::orderByDesc('starts_on')->get()]);
    }

    public function update(Request $request, AcademicTerm $term)
    {
        $this->save($request, $term);

        return redirect()->route('academic.terms.index')->with('success', 'Academic term updated successfully.');
    }

    private function save(Request $request, AcademicTerm $term): void
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('academic_terms')->where('academic_year_id', $request->input('academic_year_id'))->ignore($term->id)],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after:starts_on'],
            'status' => ['required', Rule::in(['planned', 'active', 'closed'])],
        ]);
        DB::transaction(function () use ($term, $data) {
            $year = AcademicYear::findOrFail($data['academic_year_id']);
            if ($data['starts_on'] < $year->starts_on->toDateString() || $data['ends_on'] > $year->ends_on->toDateString()) {
                throw ValidationException::withMessages(['starts_on' => 'The term dates must fall within the selected academic year.']);
            }
            if ($term->exists) {
                $term = AcademicTerm::lockForUpdate()->findOrFail($term->id);
                if ($term->status === 'closed' || ($term->status === 'active' && $data['status'] === 'planned')) {
                    throw ValidationException::withMessages(['status' => 'A closed term is read-only; an active term cannot return to planned.']);
                }
                $term->fill($data);
                if ($term->offerings()->exists() && $term->isDirty(['academic_year_id', 'name', 'starts_on', 'ends_on'])) {
                    throw ValidationException::withMessages(['name' => 'A term with course offerings retains its year, name, and dates. Only its status can change.']);
                }
                if ($data['status'] === 'closed' && $term->offerings()->where('status', '!=', 'completed')->exists()) {
                    throw ValidationException::withMessages(['status' => 'All course offerings must be completed before closing this term.']);
                }
            } else {
                if ($data['status'] === 'closed') {
                    throw ValidationException::withMessages(['status' => 'Create a term as planned or active.']);
                }
                $term->fill($data);
            }
            $term->save();
        });
    }
}
