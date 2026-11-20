<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\StudentCreditTransfer;
use App\Models\Admission\AdmissionApplication;
use App\Models\Admission\AdmissionInquiry;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminAdmissionController extends Controller
{
    /**
     * Display all online admission applications in the Admin Dashboard.
     */
    public function index(Request $request)
    {
        $query = AdmissionApplication::with(['department', 'program', 'enrolledStudent.section'])
            ->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Department Filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Admission Type Filter
        if ($request->filled('admission_type')) {
            $query->where('admission_type', $request->admission_type);
        }

        // Text Search
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('application_no', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        // KPI Counts
        $kpi = [
            'total' => AdmissionApplication::count(),
            'pending' => AdmissionApplication::where('status', 'pending')->count(),
            'under_review' => AdmissionApplication::where('status', 'under_review')->count(),
            'approved' => AdmissionApplication::where('status', 'approved')->count(),
            'enrolled' => AdmissionApplication::where('status', 'enrolled')->count(),
            'rejected' => AdmissionApplication::where('status', 'rejected')->count(),
        ];

        $departments = Department::orderBy('name')->get();
        $sections = Section::with('department')->orderBy('name')->get();

        return view('admissions.admin.index', compact('applications', 'kpi', 'departments', 'sections'));
    }

    /**
     * Update the review status of an application.
     */
    public function updateStatus(Request $request, $id)
    {
        $application = AdmissionApplication::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'under_review', 'approved', 'rejected'])],
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($application->status === 'enrolled') {
            return back()->with('error', 'Enrolled applications cannot have their status reverted. Modify the student record directly if needed.');
        }

        $application->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $application->admin_notes,
        ]);

        return back()->with('success', "Application #{$application->application_no} status updated to " . $application->getStatusLabel() . '.');
    }

    /**
     * Officially enroll an approved applicant as a student into the university.
     */
    public function enrollStudent(Request $request, $id)
    {
        $application = AdmissionApplication::findOrFail($id);

        if ($application->isEnrolled()) {
            return back()->with('error', "This applicant is already enrolled as Student #{$application->enrolled_student_id} ({$application->enrolledStudent->registration_no}).");
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'create_portal_account' => 'nullable|boolean',
            'account_password' => 'nullable|string|min:8',
            'scholarship_percentage' => 'nullable|numeric|min:0|max:100',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $section = Section::findOrFail($validated['section_id']);
        $isLateral = in_array($application->admission_type, ['lateral_adp', 'migration'], true);
        $admittedSemester = $isLateral ? 'Semester 5' : 'Semester 1';
        $scholarshipPct = ! empty($validated['scholarship_percentage']) ? (float) $validated['scholarship_percentage'] : 0.0;
        $scholarshipStatus = $scholarshipPct > 0 ? 'active' : 'none';

        $student = DB::transaction(function () use ($application, $validated, $section, $isLateral, $admittedSemester, $scholarshipPct, $scholarshipStatus) {
            $userId = null;
            $createsAccount = ! empty($validated['create_portal_account']);

            if ($createsAccount) {
                $user = null;
                if ($application->user_id) {
                    $user = User::find($application->user_id);
                }
                if (! $user) {
                    $user = User::where('email', $application->email)->first();
                }

                if ($user) {
                    $userId = $user->id;
                    if (! $user->hasRole('Student')) {
                        $user->assignRole('Student');
                    }
                } else {
                    $password = ! empty($validated['account_password']) ? $validated['account_password'] : 'Password@123';
                    $user = User::create([
                        'name' => $application->name,
                        'email' => $application->email,
                        'phone' => $application->phone,
                        'password' => Hash::make($password),
                        'image' => 'default-user.png',
                    ]);
                    $user->assignRole('Student');
                    $userId = $user->id;
                }
            }

            // Create Official Student Record
            $student = Student::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'cnic' => $application->cnic,
                'department_id' => $application->department_id,
                'program_id' => $application->program_id,
                'section_id' => $section->id,
                'admission_type' => $application->admission_type,
                'semester' => $admittedSemester,
                'admitted_semester' => $admittedSemester,
                'inter_degree' => $application->inter_degree,
                'inter_board' => $application->inter_board,
                'inter_roll_no' => $application->inter_roll_no,
                'inter_total_marks' => $application->inter_total_marks,
                'inter_obtained_marks' => $application->inter_obtained_marks,
                'inter_percentage' => $application->inter_percentage,
                'scholarship_name' => $application->scholarship_name,
                'scholarship_percentage' => $scholarshipPct,
                'scholarship_status' => $scholarshipStatus,
                'image' => 'default-user.png',
                'user_id' => $userId,
            ]);

            // Handle Lateral Transfer record if applicable
            if ($isLateral) {
                StudentCreditTransfer::create([
                    'student_id' => $student->id,
                    'transfer_type' => $application->admission_type,
                    'previous_institution' => $application->previous_institution ?? 'Previous Institution',
                    'previous_degree' => $application->previous_degree ?? 'Associate Degree (ADP)',
                    'previous_cgpa' => $application->previous_cgpa,
                    'transferred_credits' => $application->transferred_credits ?? 64.0,
                    'admitted_to_semester' => 5,
                    'document_path' => $application->document_path,
                    'document_name' => $application->document_name,
                    'remarks' => "Enrolled via Online Admission Application #{$application->application_no}",
                    'verified_by' => auth()->id(),
                ]);
            }

            // Update Application record
            $application->update([
                'status' => 'enrolled',
                'enrolled_student_id' => $student->id,
                'enrolled_at' => now(),
                'enrolled_by' => auth()->id(),
                'admin_notes' => $validated['admin_notes'] ?? $application->admin_notes,
            ]);

            return $student;
        });

        return back()->with('success', "Applicant {$application->name} successfully enrolled as an official Student! Registration Number: {$student->registration_no}. Section {$section->name} assigned.");
    }

    /**
     * Display general inquiries received from website visitors.
     */
    public function inquiries(Request $request)
    {
        $inquiries = AdmissionInquiry::latest()->paginate(25);
        return view('admissions.admin.inquiries', compact('inquiries'));
    }

    /**
     * Update inquiry follow-up status.
     */
    public function updateInquiryStatus(Request $request, $id)
    {
        $inquiry = AdmissionInquiry::findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['new', 'contacted', 'closed'])],
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $inquiry->update($validated);

        return back()->with('success', "Inquiry from {$inquiry->name} marked as {$validated['status']}.");
    }
}
