<?php

namespace App\Http\Controllers;

use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    function create()
    {
        $departments = Department::all();
        $sections    = Section::with('department')->get();

        $users = $this->availableUsers();

        return view('students.add-student', compact('departments', 'sections', 'users'));
    }

    function allstudents(Request $request)
    {
        $students    = Student::with(['department', 'section', 'user', 'activeSemesterEnrollment.courses.course'])->get();
        $departments = Department::all();
        $sections    = Section::with('department')->get();
        $users       = $this->availableUsers();

        return view('students.students', compact('students', 'departments', 'sections', 'users'));
    }

    function addStudent(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email',
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'user_id'       => ['nullable', 'exists:users,id', 'unique:students,user_id'],
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForStudent($validated['user_id'] ?? null);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $fileName = basename($path);
        } else {
            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        $student = Student::create($validated);

        return redirect()
            ->route('addEnrollmentForm', ['student_id' => $student->id])
            ->with('success', 'Student profile created. Create the first semester enrollment next.');
    }

    function editStudentForm($id)
    {
        $student     = Student::findOrFail($id);
        $departments = Department::all();
        $sections    = Section::with('department')->get();
        $activeEnrollment = $student->semesterEnrollments()
            ->with('courses.course')
            ->where('status', 'active')
            ->latest('enrolled_at')
            ->first();

        $users = $this->availableUsers($student->user_id);

        return view('students.edit-student', compact('student', 'departments', 'sections', 'activeEnrollment', 'users'));
    }

    function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'user_id'       => ['nullable', 'exists:users,id', Rule::unique('students', 'user_id')->ignore($student->id)],
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForStudent($validated['user_id'] ?? null, $student->user_id);

        if ($request->hasFile('image')) {
            if ($student->image && $student->image !== 'default-user.png') {
                Storage::disk('public')->delete('images/' . $student->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = basename($path);
        }

        $student->update($validated);

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student updated successfully!');
    }

    function deleteStudent($id)
    {
        $student = Student::findOrFail($id);

        if ($student->image && $student->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $student->image);
        }

        $student->delete();

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student deleted successfully!');
    }

    private function availableUsers(?int $selectedUserId = null)
    {
        return User::query()
            ->where(function ($query) use ($selectedUserId) {
                $query->whereDoesntHave('studentProfile')
                    ->whereDoesntHave('teacherProfile');

                if ($selectedUserId) {
                    $query->orWhereKey($selectedUserId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function ensureUserIsAvailableForStudent(?int $userId, ?int $currentUserId = null): void
    {
        if (! $userId || $userId === $currentUserId) {
            return;
        }

        if (Teacher::query()->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'This user account is already linked to a teacher profile.',
            ]);
        }
    }
}
