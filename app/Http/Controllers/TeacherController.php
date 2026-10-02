<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $this->mergeLinkedIdentity($request);
        $createsPortalAccount = $request->boolean('create_portal_account') && ! $request->filled('user_id');
        $validated = $request->validate([
            'name'   => 'required|string',
            'email'  => 'required|email|unique:teachers,email',
            'phone'  => 'required|digits:11',
            'course' => 'nullable|string|max:100',
            'user_id' => ['nullable', 'exists:users,id', 'unique:teachers,user_id'],
            'create_portal_account' => ['nullable', 'boolean'],
            'account_password' => [$createsPortalAccount ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForTeacher($validated['user_id'] ?? null);
        if ($createsPortalAccount) {
            $request->validate([
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'phone' => ['required', 'digits:11', Rule::unique('users', 'phone')],
            ]);
        }

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fileName = basename($path);
        } else {

            $fileName = 'default-user.png';
        }

        DB::transaction(function () use ($validated, $fileName, $createsPortalAccount) {
            if ($createsPortalAccount) {
                $account = User::create([
                    'name' => $validated['name'], 'email' => $validated['email'], 'phone' => $validated['phone'],
                    'password' => Hash::make($validated['account_password']), 'image' => $fileName,
                ]);
                $account->assignRole('Teacher');
                $validated['user_id'] = $account->id;
            }
            Teacher::create(collect($validated)->only(['name', 'email', 'phone', 'course', 'user_id'])->all() + ['image' => $fileName]);
        });

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
        $this->mergeLinkedIdentity($request);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'required|digits:11',
            'course' => 'nullable|string|max:100',
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('teachers', 'user_id')->ignore($teacher->id)],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForTeacher($validated['user_id'] ?? null, $teacher->user_id);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $validated['image'] = basename($path);
        }

        $teacher->update(collect($validated)->only(['name', 'email', 'phone', 'course', 'user_id', 'image'])->all());

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
                    ->whereDoesntHave('teacherProfile')
                    ->whereHas('roles', fn ($role) => $role->where('name', 'Teacher'));

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

        if (! User::findOrFail($userId)->hasRole('Teacher')) {
            throw ValidationException::withMessages(['user_id' => 'Assign the Teacher role to this account before linking it to a teacher profile.']);
        }
    }

    private function mergeLinkedIdentity(Request $request): void
    {
        if (! $request->filled('user_id') || ! ctype_digit((string) $request->input('user_id'))) {
            return;
        }
        if ($user = User::find($request->integer('user_id'))) {
            $request->merge(['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);
        }
    }
}
