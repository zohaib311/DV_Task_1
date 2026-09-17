<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    function create()
    {
        return view('department.add-department');
    }

    function addDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create($validated);

        return redirect()
            ->route('allDepartments')
            ->with('success', 'Department added successfully!');
    }

    function allDepartments()
    {
        $departments = Department::all();

        return view('department.departments', compact('departments'));
    }

    function editDepartmentForm($id)
    {
        $department = Department::findOrFail($id);

        return view('department.edit-department', compact('department'));
    }

    function updateDepartment(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($validated);

        return redirect()
            ->route('allDepartments')
            ->with('success', 'Department updated successfully!');
    }

    function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()
            ->route('allDepartments')
            ->with('success', 'Department deleted successfully!');
    }
}
