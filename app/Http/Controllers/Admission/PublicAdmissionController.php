<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Admission\AdmissionApplication;
use App\Models\Department\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicAdmissionController extends Controller
{
    /**
     * Smart landing router for "Apply Online" CTA.
     */
    public function portalLanding(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('admissions.login');
        }

        $user = Auth::user();

        // 1. If enrolled student, send to Student Portal
        if ($user->studentProfile && $user->hasRole('Student')) {
            return redirect()->route('student.dashboard')->with('info', 'You are already an enrolled university student.');
        }

        // 2. If already submitted an application, send to Applicant Dashboard
        if ($user->admissionApplications()->exists()) {
            return redirect()->route('admissions.applicant.dashboard');
        }

        // 3. Otherwise, go to Apply Form
        return redirect()->route('admissions.apply');
    }

    /**
     * Show the applicant login and registration portal.
     */
    public function showApplicantLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->portalLanding($request);
        }

        return view('website.admissions.login');
    }

    /**
     * Handle applicant sign-in.
     */
    public function applicantLoginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->studentProfile && $user->hasRole('Student')) {
                return redirect()->intended(route('student.dashboard'))->with('success', 'Logged in to Student Portal!');
            }

            if ($user->admissionApplications()->exists()) {
                return redirect()->intended(route('admissions.applicant.dashboard'))->with('success', 'Welcome back to your Admission Portal!');
            }

            return redirect()->intended(route('admissions.apply'))->with('success', 'Logged in! Please complete your admission application.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our applicant records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle applicant self-registration.
     */
    public function applicantRegisterSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|string|max:30|unique:users,phone',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'image' => 'default-user.png',
        ]);

        // Assign baseline Applicant role
        $user->assignRole('Applicant');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admissions.apply')
            ->with('success', 'Account created successfully! Please fill out your admission application details below.');
    }

    /**
     * Applicant Personal Dashboard / Admission Workspace.
     */
    public function applicantDashboard(Request $request)
    {
        $user = Auth::user();
        $application = $user->admissionApplications()
            ->with(['department', 'program', 'enrolledStudent.section'])
            ->latest()
            ->first();

        return view('website.admissions.applicant-dashboard', compact('user', 'application'));
    }

    /**
     * Show the online admission application form.
     */
    public function showApplyForm(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('admissions.login')->with('info', 'Please sign in or create an applicant account to start your admission application.');
        }

        $user = Auth::user();

        // If enrolled student, redirect to Student Portal
        if ($user->studentProfile && $user->hasRole('Student')) {
            return redirect()->route('student.dashboard')->with('info', 'You are already an enrolled university student.');
        }

        // If user already submitted an application and is not requesting an edit, show dashboard
        if ($user->admissionApplications()->exists() && ! $request->has('edit')) {
            return redirect()->route('admissions.applicant.dashboard');
        }

        $departments = Department::with(['programs' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->orderBy('name')->get();

        $allPrograms = Program::with('department')->where('is_active', true)->orderBy('name')->get();
        $selectedProgramId = $request->query('program');
        $defaultLateralCredits = config('academic.lateral_entry.default_transferred_credits', 64.0);
        $scholarships = config('fees.scholarships', []);

        return view('website.admissions.apply', compact(
            'departments',
            'allPrograms',
            'selectedProgramId',
            'defaultLateralCredits',
            'scholarships',
            'user'
        ));
    }

    /**
     * Handle submission of the online admission application.
     */
    public function submitApplication(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('admissions.login')->with('error', 'Please sign in or register before submitting your application.');
        }

        $isLateral = in_array($request->input('admission_type'), ['lateral_adp', 'migration'], true);

        $validated = $request->validate([
            // Personal & Contact
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'cnic' => 'nullable|string|max:30',
            'father_name' => 'nullable|string|max:150',
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',

            // Program Choice
            'department_id' => 'required|exists:departments,id',
            'program_id' => 'required|exists:academic_programs,id',
            'admission_type' => ['required', 'string', Rule::in(['regular', 'lateral_adp', 'migration'])],

            // Previous Qualification
            'inter_degree' => 'nullable|string|max:100',
            'inter_board' => 'nullable|string|max:150',
            'inter_roll_no' => 'nullable|string|max:50',
            'inter_total_marks' => 'nullable|numeric|min:0|max:5000',
            'inter_obtained_marks' => 'nullable|numeric|min:0|max:5000',

            // Lateral details
            'previous_institution' => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_degree' => [$isLateral ? 'required' : 'nullable', 'string', 'max:150'],
            'previous_cgpa' => ['nullable', 'string', 'max:20'],
            'transferred_credits' => ['nullable', 'numeric', 'min:1', 'max:120'],

            // Scholarship & Remarks
            'scholarship_name' => 'nullable|string|max:120',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'applicant_remarks' => 'nullable|string|max:1000',
        ]);

        // Compute percentage if total & obtained marks are present
        $interPercentage = null;
        if (! empty($validated['inter_total_marks']) && ! empty($validated['inter_obtained_marks']) && $validated['inter_total_marks'] > 0) {
            $interPercentage = round(($validated['inter_obtained_marks'] / $validated['inter_total_marks']) * 100, 2);
        }

        // Handle document upload if provided
        $docPath = null;
        $docName = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('admission_documents', 'public');
            $docName = $request->file('document')->getClientOriginalName();
        }

        $userId = Auth::id();

        $application = DB::transaction(function () use ($validated, $interPercentage, $docPath, $docName, $userId) {
            return AdmissionApplication::create([
                'user_id' => $userId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'cnic' => $validated['cnic'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'department_id' => $validated['department_id'],
                'program_id' => $validated['program_id'],
                'admission_type' => $validated['admission_type'],
                'inter_degree' => $validated['inter_degree'] ?? null,
                'inter_board' => $validated['inter_board'] ?? null,
                'inter_roll_no' => $validated['inter_roll_no'] ?? null,
                'inter_total_marks' => $validated['inter_total_marks'] ?? null,
                'inter_obtained_marks' => $validated['inter_obtained_marks'] ?? null,
                'inter_percentage' => $interPercentage,
                'previous_institution' => $validated['previous_institution'] ?? null,
                'previous_degree' => $validated['previous_degree'] ?? null,
                'previous_cgpa' => $validated['previous_cgpa'] ?? null,
                'transferred_credits' => $validated['transferred_credits'] ?? ($validated['admission_type'] === 'lateral_adp' ? 64.0 : null),
                'scholarship_name' => $validated['scholarship_name'] ?? null,
                'document_path' => $docPath,
                'document_name' => $docName,
                'applicant_remarks' => $validated['applicant_remarks'] ?? null,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('admissions.applicant.dashboard')
            ->with('application_submitted', true)
            ->with('success', "Application #{$application->application_no} submitted successfully! Your application is now queued for verification.");
    }

    /**
     * Show application receipt and tracking confirmation.
     */
    public function showConfirmation($applicationNo)
    {
        $application = AdmissionApplication::with(['department', 'program', 'enrolledStudent'])
            ->where('application_no', $applicationNo)
            ->firstOrFail();

        return view('website.admissions.confirmation', compact('application'));
    }

    /**
     * Public application status tracking lookup.
     */
    public function showStatusLookup(Request $request)
    {
        $application = null;
        $query = $request->query('query') ?? $request->query('search') ?? $request->query('q');

        if ($query) {
            $queryClean = trim($query);
            $application = AdmissionApplication::with(['department', 'program', 'enrolledStudent'])
                ->where(function ($q) use ($queryClean) {
                    $q->where('application_no', $queryClean)
                        ->orWhere('email', $queryClean)
                        ->orWhere('cnic', $queryClean);
                })
                ->latest()
                ->first();
        }

        return view('website.admissions.status', compact('application', 'query'));
    }
}
