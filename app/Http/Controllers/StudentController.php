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
        $defaultLateralCredits = config('academic.lateral_entry.default_transferred_credits', 64.0);
        $defaultAdmittedSemester = config('academic.lateral_entry.default_admitted_semester', 5);

        return view('students.add-student', compact('departments', 'programs', 'sections', 'users', 'defaultLateralCredits', 'defaultAdmittedSemester'));
    }

    function allstudents(Request $request)
    {
        $students    = Student::with(['department', 'program', 'section', 'user', 'creditTransfer', 'activeSemesterEnrollment.courses.course'])->get();
        $departments = Department::all();
        $programs    = Program::with('department')->where('is_active', true)->orderBy('name')->get();
        $sections    = Section::with('department')->get();
        $users       = $this->availableUsers();
        $defaultLateralCredits = config('academic.lateral_entry.default_transferred_credits', 64.0);
        $defaultAdmittedSemester = config('academic.lateral_entry.default_admitted_semester', 5);

        return view('students.students', compact('students', 'departments', 'programs', 'sections', 'users', 'defaultLateralCredits', 'defaultAdmittedSemester'));
    }

    function addStudent(Request $request)
    {
        $this->mergeLinkedIdentity($request);
        $createsPortalAccount = $request->boolean('create_portal_account') && ! $request->filled('user_id');
        $isLateral = in_array($request->input('admission_type'), ['lateral_adp', 'migration'], true);

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
            'admission_type'=> ['nullable', 'string', Rule::in(['regular', 'lateral_adp', 'migration'])],
            'previous_institution' => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_degree'      => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_cgpa'        => ['nullable', 'string', 'max:20'],
            'transferred_credits'  => [$isLateral ? 'required' : 'nullable', 'numeric', 'min:1', 'max:120'],
            'admitted_to_semester' => ['nullable', 'integer', 'min:2', 'max:8'],
            'transcript_document'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'remarks'              => ['nullable', 'string', 'max:500'],
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

        $student = DB::transaction(function () use ($validated, $fileName, $createsPortalAccount, $request, $isLateral) {
            if ($createsPortalAccount) {
                $account = User::create([
                    'name' => $validated['name'], 'email' => $validated['email'], 'phone' => $validated['phone'],
                    'password' => Hash::make($validated['account_password']), 'image' => $fileName,
                ]);
                $account->assignRole('Student');
                $validated['user_id'] = $account->id;
            }

            $admissionType = $validated['admission_type'] ?? 'regular';
            $admittedSemester = $isLateral
                ? 'Semester ' . ($validated['admitted_to_semester'] ?? config('academic.lateral_entry.default_admitted_semester', 5))
                : null;

            $studentData = collect($validated)->only(['name', 'email', 'phone', 'department_id', 'program_id', 'section_id', 'user_id'])->all() + [
                'image' => $fileName,
                'admission_type' => $admissionType,
                'admitted_semester' => $admittedSemester,
                'semester' => $admittedSemester,
            ];

            $createdStudent = Student::create($studentData);

            if ($isLateral) {
                $docPath = null;
                $docName = null;
                if ($request->hasFile('transcript_document')) {
                    $docPath = $request->file('transcript_document')->store('credit_transfers', 'public');
                    $docName = $request->file('transcript_document')->getClientOriginalName();
                }

                \App\Models\Academic\StudentCreditTransfer::create([
                    'student_id' => $createdStudent->id,
                    'transfer_type' => $admissionType,
                    'previous_institution' => $validated['previous_institution'],
                    'previous_degree' => $validated['previous_degree'],
                    'previous_cgpa' => $validated['previous_cgpa'] ?? null,
                    'transferred_credits' => $validated['transferred_credits'] ?? config('academic.lateral_entry.default_transferred_credits', 64.0),
                    'admitted_to_semester' => $validated['admitted_to_semester'] ?? config('academic.lateral_entry.default_admitted_semester', 5),
                    'document_path' => $docPath,
                    'document_name' => $docName,
                    'remarks' => $validated['remarks'] ?? null,
                    'verified_by' => auth()->id(),
                ]);
            }

            return $createdStudent;
        });

        return redirect()
            ->route('addEnrollmentForm', ['student_id' => $student->id])
            ->with('success', 'Student profile created. Create the first semester enrollment next.');
    }

    function editStudentForm($id)
    {
        $student     = Student::with('creditTransfer')->findOrFail($id);
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
        $defaultLateralCredits = config('academic.lateral_entry.default_transferred_credits', 64.0);
        $defaultAdmittedSemester = config('academic.lateral_entry.default_admitted_semester', 5);

        return view('students.edit-student', compact('student', 'departments', 'programs', 'sections', 'activeEnrollment', 'users', 'defaultLateralCredits', 'defaultAdmittedSemester'));
    }

    function updateStudent(Request $request, $id)
    {
        $student = Student::with('creditTransfer')->findOrFail($id);
        $this->mergeLinkedIdentity($request);

        $isLateral = in_array($request->input('admission_type'), ['lateral_adp', 'migration'], true);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'program_id'    => 'nullable|exists:academic_programs,id',
            'section_id'    => 'required|exists:sections,id',
            'user_id'       => ['nullable', 'exists:users,id', Rule::unique('students', 'user_id')->ignore($student->id)],
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'admission_type'=> ['nullable', 'string', Rule::in(['regular', 'lateral_adp', 'migration'])],
            'previous_institution' => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_degree'      => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_cgpa'        => ['nullable', 'string', 'max:20'],
            'transferred_credits'  => [$isLateral ? 'required' : 'nullable', 'numeric', 'min:1', 'max:120'],
            'admitted_to_semester' => ['nullable', 'integer', 'min:2', 'max:8'],
            'transcript_document'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'remarks'              => ['nullable', 'string', 'max:500'],
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

        $studentUpdateData = collect($validated)->only(['name', 'email', 'phone', 'department_id', 'program_id', 'section_id', 'user_id', 'image'])->all();
        if (isset($validated['admission_type'])) {
            $studentUpdateData['admission_type'] = $validated['admission_type'];
            if ($isLateral && ! $student->admitted_semester) {
                $studentUpdateData['admitted_semester'] = 'Semester ' . ($validated['admitted_to_semester'] ?? 5);
            }
        }

        DB::transaction(function () use ($student, $studentUpdateData, $isLateral, $validated, $request) {
            $student->update($studentUpdateData);

            if ($isLateral) {
                $creditTransfer = $student->creditTransfer;
                $docPath = $creditTransfer?->document_path;
                $docName = $creditTransfer?->document_name;

                if ($request->hasFile('transcript_document')) {
                    if ($docPath) {
                        Storage::disk('public')->delete($docPath);
                    }
                    $docPath = $request->file('transcript_document')->store('credit_transfers', 'public');
                    $docName = $request->file('transcript_document')->getClientOriginalName();
                }

                \App\Models\Academic\StudentCreditTransfer::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'transfer_type' => $validated['admission_type'] ?? 'adp_lateral',
                        'previous_institution' => $validated['previous_institution'],
                        'previous_degree' => $validated['previous_degree'],
                        'previous_cgpa' => $validated['previous_cgpa'] ?? null,
                        'transferred_credits' => $validated['transferred_credits'] ?? config('academic.lateral_entry.default_transferred_credits', 64.0),
                        'admitted_to_semester' => $validated['admitted_to_semester'] ?? config('academic.lateral_entry.default_admitted_semester', 5),
                        'document_path' => $docPath,
                        'document_name' => $docName,
                        'remarks' => $validated['remarks'] ?? null,
                        'verified_by' => auth()->id(),
                    ]
                );
            }
        });

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student updated successfully!');
    }

    public function downloadTransferDocument(Student $student)
    {
        $creditTransfer = $student->creditTransfer;
        abort_unless($creditTransfer && $creditTransfer->document_path, 404, 'No transfer document on file.');
        abort_unless(Storage::disk('public')->exists($creditTransfer->document_path), 404, 'File not found in storage.');

        return Storage::disk('public')->download($creditTransfer->document_path, $creditTransfer->document_name ?? 'transfer_document.pdf');
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
