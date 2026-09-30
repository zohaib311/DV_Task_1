<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    function create()
    {
        return view('teachers.add-teacher', ['users' => $this->availableUsers()]);
    }

    function allTeachers()
    {
        $teachers = Teacher::with('user')->get();

        return view('teachers.teachers', [
            'teachers' => $teachers
        ]);
    }

    function addTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string',
            'email'  => 'required|email|unique:teachers,email',
            'phone'  => 'required|digits:11',
            'course' => 'required|string|min:2|max:100',
            'user_id' => ['nullable', 'exists:users,id', 'unique:teachers,user_id'],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForTeacher($validated['user_id'] ?? null);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fileName = basename($path);
        } else {

            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        Teacher::create($validated);

        return redirect()
            ->route('allTeachers')
            ->with('success', 'Teacher added successfully!');
    }

    function editTeacherForm($id)
    {
        $teacher = Teacher::findOrFail($id);

        return view('teachers.edit-teacher', [
            'teacher' => $teacher,
            'users' => $this->availableUsers($teacher->user_id),
        ]);
    }

    function updateTeacher(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'required|digits:11',
            'course' => 'string|min:2|max:10',
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('teachers', 'user_id')->ignore($teacher->id)],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForTeacher($validated['user_id'] ?? null, $teacher->user_id);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $validated['image'] = basename($path);
        }

        $teacher->update($validated);

        return redirect()
            ->route('allTeachers')
            ->with('success', 'Teacher updated successfully!');
    }

    function deleteTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        if ($teacher->offerings()->exists()) {
            return redirect()->route('allTeachers')->with('error', 'This teacher is assigned to a course offering and cannot be deleted. Teaching history must be preserved.');
        }
        if ($teacher->image && $teacher->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $teacher->image);
        }

        $teacher->delete();

        return redirect()
            ->route('allTeachers')
            ->with('success', 'teacher deleted successfully!');
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

    private function ensureUserIsAvailableForTeacher(?int $userId, ?int $currentUserId = null): void
    {
        if (! $userId || $userId === $currentUserId) {
            return;
        }

        if (Student::query()->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'This user account is already linked to a student profile.',
            ]);
        }
    }
}
