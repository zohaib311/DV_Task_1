<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Department\Department; // 1. Department Import
use App\Models\Section\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    function create()
    {
        // 2. Fetch all departments for Add Form dropdown
        $departments = Department::all();
        return view('section.add-section', compact('departments'));
    }

    function addSection(Request $request)
    {
        // 3. Validate department_id
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Section::create($validated);

        return redirect()
            ->route('allSections')
            ->with('success', 'Section added successfully!');
    }

    function allSections()
    {
        // 4. Eager load department relation
        $sections = Section::with('department')->get();

        return view('section.sections', compact('sections'));
    }

    function editSectionForm($id)
    {
        $section = Section::findOrFail($id);
        $departments = Department::all();

        return view('section.edit-section', compact('section', 'departments'));
    }

    function updateSection(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $section->update($validated);

        return redirect()
            ->route('allSections')
            ->with('success', 'Section updated successfully!');
    }

    function deleteSection($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return redirect()
            ->route('allSections')
            ->with('success', 'Section deleted successfully!');
    }
}
