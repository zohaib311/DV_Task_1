<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Section\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    function create()
    {
        return view('section.add-section');
    }

    function addSection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
        ]);

        Section::create($validated);

        return redirect()
            ->route('allSections')
            ->with('success', 'Section added successfully!');
    }

    function allSections()
    {
        $sections = Section::all();

        return view('section.sections', [
            'sections' => $sections
        ]);
    }

    function editSectionForm($id)
    {
        $section = Section::findOrFail($id);

        return view('section.edit-section', [
            'section' => $section
        ]);
    }

    function updateSection(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
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
