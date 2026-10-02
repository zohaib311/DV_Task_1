<?php

namespace App\Http\Controllers;

use App\Models\Department\Department;
use App\Models\Academic\Program;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    function create()
    {
        $departments = Department::all();
        $programs = Program::with('department')->where('is_active', true)->orderBy('name')->get();
        $sections    = Section::with('department')->get();

        $users = $this->availableUsers();

        return view('students.add-student', compact('departments', 'programs', 'sections', 'users'));
    }

    function allstudents(Request $request)
    {
        $students    = Student::with(['department', 'program', 'section', 'user', 'activeSemesterEnrollment.courses.course'])->get();
        $departments = Department::all();
        $programs    = Program::with('department')->where('is_active', true)->orderBy('name')->get();
        $sections    = Section::with('department')->get();
        $users       = $this->availableUsers();

        return view('students.students', compact('students', 'departments', 'programs', 'sections', 'users'));
    }

    function addStudent(Request $request)
    {
        $this->mergeLinkedIdentity($request);
        $createsPortalAccount = $request->boolean('create_portal_account') && ! $request->filled('user_id');
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email',
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'program_id'    => 'nullable|exists:academic_programs,id',
            'section_id'    => 'required|exists:sections,id',
            'user_id'       => ['nullable', 'exists:users,id', 'unique:students,user_id'],
            'create_portal_account' => ['nullable', 'boolean'],
            'account_password' => [$createsPortalAccount ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForStudent($validated['user_id'] ?? null);
        $this->validateAcademicPlacement($validated);

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

        $student = DB::transaction(function () use ($validated, $fileName, $createsPortalAccount) {
            if ($createsPortalAccount) {
                $account = User::create([
                    'name' => $validated['name'], 'email' => $validated['email'], 'phone' => $validated['phone'],
                    'password' => Hash::make($validated['account_password']), 'image' => $fileName,
                ]);
                $account->assignRole('Student');
                $validated['user_id'] = $account->id;
            }

            return Student::create(collect($validated)->only(['name', 'email', 'phone', 'department_id', 'program_id', 'section_id', 'user_id'])->all() + ['image' => $fileName]);
        });

        return redirect()
            ->route('addEnrollmentForm', ['student_id' => $student->id])
            ->with('success', 'Student profile created. Create the first semester enrollment next.');
    }

    function editStudentForm($id)
    {
        $student     = Student::findOrFail($id);
        $departments = Department::all();
        $programs    = Program::with('department')->where(function ($query) use ($student) {
            $query->where('is_active', true);
            if ($student->program_id) {
                $query->orWhere('id', $student->program_id);
            }
        })->orderBy('name')->get();
        $sections    = Section::with('department')->get();
        $activeEnrollment = $student->semesterEnrollments()
            ->with('courses.course')
            ->where('status', 'active')
            ->latest('enrolled_at')
            ->first();

        $users = $this->availableUsers($student->user_id);

        return view('students.edit-student', compact('student', 'departments', 'programs', 'sections', 'activeEnrollment', 'users'));
    }

    function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $this->mergeLinkedIdentity($request);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'program_id'    => 'nullable|exists:academic_programs,id',
            'section_id'    => 'required|exists:sections,id',
            'user_id'       => ['nullable', 'exists:users,id', Rule::unique('students', 'user_id')->ignore($student->id)],
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $this->ensureUserIsAvailableForStudent($validated['user_id'] ?? null, $student->user_id);
        $this->validateAcademicPlacement($validated);

        if ($request->hasFile('image')) {
            if ($student->image && $student->image !== 'default-user.png') {
                Storage::disk('public')->delete('images/' . $student->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = basename($path);
        }

        $student->update(collect($validated)->only(['name', 'email', 'phone', 'department_id', 'program_id', 'section_id', 'user_id', 'image'])->all());

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student updated successfully!');
    }

    function deleteStudent($id)
    {
        $student = Student::findOrFail($id);

        if ($student->semesterEnrollments()->exists()) {
            return redirect()->route('allStudents')->with('error', 'This student has academic enrollment history and cannot be deleted. Enrollment and attendance records must be preserved.');
        }

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
                    ->whereDoesntHave('teacherProfile')
                    ->whereHas('roles', fn ($role) => $role->where('name', 'Student'));

                if ($selectedUserId) {
                    $query->orWhere('id', $selectedUserId);
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

        if (! User::findOrFail($userId)->hasRole('Student')) {
            throw ValidationException::withMessages(['user_id' => 'Assign the Student role to this account before linking it to a student profile.']);
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

    private function validateAcademicPlacement(array $data): void
    {
        $programId = $data['program_id'] ?? null;
        $matches = Section::whereKey($data['section_id'])->where('department_id', $data['department_id'])->exists()
            && (! $programId || Program::whereKey($programId)->where('department_id', $data['department_id'])->where('is_active', true)->exists());
        if (! $matches) {
            throw ValidationException::withMessages(['program_id' => 'Select an active program and section from the chosen department.']);
        }
    }
}
