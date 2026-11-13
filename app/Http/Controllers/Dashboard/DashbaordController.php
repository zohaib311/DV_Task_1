<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\Program;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Event\Event;
use App\Models\Fee\StudentFeeInvoice;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class DashbaordController extends Controller
{
    function dashboardView()
    {
        if ($route = app(\App\Services\StudentPortal\StudentWorkspace::class)->landing(auth()->user())) {
            return redirect()->route($route);
        }
        if (auth()->user()->can('offerings.view-assigned') && ! auth()->user()->can('offerings.manage')) {
            return redirect()->route('teaching.dashboard');
        }

        // 1. Core Modules Statistics for quick actions & authorization
        $modules = [
            ['Students', 'students.manage', Student::class, 'allStudents', 'addStudentForm', 'students', 'bi-mortarboard-fill'],
            ['Teachers', 'teachers.manage', Teacher::class, 'allTeachers', 'addTeacherForm', 'teachers', 'bi-person-badge-fill'],
            ['Courses', 'courses.manage', Course::class, 'allCourses', 'addCourseForm', 'courses', 'bi-book-fill'],
            ['Events', 'events.manage', Event::class, 'allEvents', 'addEventForm', 'events', 'bi-calendar-event-fill'],
            ['Departments', 'departments.manage', Department::class, 'allDepartments', 'addDepartmentForm', 'departments', 'bi-building'],
            ['Sections', 'sections.manage', Section::class, 'allSections', 'addSectionForm', 'sections', 'bi-bar-chart-steps'],
            ['Published Results', 'results.view-all', SemesterResult::class, 'allResults', null, 'results', 'bi-journal-check'],
            ['System Users', 'users.manage', User::class, 'allUsers', null, 'users', 'bi-people-fill'],
        ];

        $statistics = collect($modules)->filter(fn ($module) => auth()->user()->can($module[1]))
            ->map(function ($module) {
                [$label, $permission, $model, $route, $createRoute, $style, $icon] = $module;
                $query = $model::query();
                if ($model === SemesterResult::class) {
                    $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail']);
                }
                $count = $query->count();

                return compact('label', 'route', 'createRoute', 'style', 'icon', 'count');
            })->values();

        // 2. High-Level Executive Counts
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalDepartments = Department::count();
        $totalPrograms = Program::count();
        $totalCourses = Course::count();
        $activeEnrollments = StudentSemesterEnrollment::where('status', 'active')->count();

        // Financial KPIs
        $totalInvoicedAmount = (float) StudentFeeInvoice::sum('total_amount');
        $totalScholarshipDiscount = (float) StudentFeeInvoice::sum('discount_amount');
        $totalPaidAmount = (float) StudentFeeInvoice::where('status', 'paid')->sum('total_amount');
        $totalPendingAmount = (float) StudentFeeInvoice::where('status', 'pending')->sum('total_amount');
        $invoicesCount = StudentFeeInvoice::count();
        $paidInvoicesCount = StudentFeeInvoice::where('status', 'paid')->count();
        $collectionRate = $invoicesCount > 0 ? round(($paidInvoicesCount / $invoicesCount) * 100, 1) : 0;

        // 3. Analytics Chart Data: Department-wise Students Distribution
        $departmentData = Department::has('students')
            ->withCount('students')
            ->orderByDesc('students_count')
            ->take(6)
            ->get();

        $chartDepartmentLabels = $departmentData->pluck('name')->toArray();
        $chartDepartmentCounts = $departmentData->pluck('students_count')->toArray();

        // 4. Analytics Chart Data: Admission Tracks & Scholarships
        $regularAdmissions = Student::where(function ($q) {
            $q->where('admission_type', 'regular')->orWhereNull('admission_type');
        })->count();
        $lateralAdmissions = Student::where('admission_type', 'lateral_adp')->count();
        $migrationAdmissions = Student::where('admission_type', 'migration')->count();
        $scholarshipStudents = Student::whereNotNull('scholarship_name')->where('scholarship_percentage', '>', 0)->count();

        // 5. Analytics Chart Data: Semester Progression Distribution (Semesters 1 - 8)
        $semesterLabels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'];
        $fullSemesterNames = [
            'Sem 1' => 'Semester 1',
            'Sem 2' => 'Semester 2',
            'Sem 3' => 'Semester 3',
            'Sem 4' => 'Semester 4',
            'Sem 5' => 'Semester 5',
            'Sem 6' => 'Semester 6',
            'Sem 7' => 'Semester 7',
            'Sem 8' => 'Semester 8',
        ];
        $semesterCountsMap = StudentSemesterEnrollment::selectRaw('semester, count(*) as count')
            ->groupBy('semester')
            ->pluck('count', 'semester')
            ->toArray();
        $chartSemesterCounts = array_map(fn($short) => $semesterCountsMap[$fullSemesterNames[$short]] ?? 0, $semesterLabels);

        // 6. Recent Student Admissions (Latest 5)
        $recentStudents = Student::with(['department', 'program'])
            ->latest()
            ->take(5)
            ->get();

        // 7. Recent Invoices (Latest 4)
        $recentInvoices = StudentFeeInvoice::with(['student'])
            ->latest()
            ->take(4)
            ->get();

        // 8. Upcoming Events (Next 3)
        $upcomingEvents = Event::latest()
            ->take(3)
            ->get();

        // 9. Active Academic Term
        $activeTerm = AcademicTerm::where('status', 'active')->first() ?? AcademicTerm::latest('id')->first();

        return view('dashboard.dashboard', compact(
            'statistics',
            'totalStudents',
            'totalTeachers',
            'totalDepartments',
            'totalPrograms',
            'totalCourses',
            'activeEnrollments',
            'totalInvoicedAmount',
            'totalScholarshipDiscount',
            'totalPaidAmount',
            'totalPendingAmount',
            'collectionRate',
            'chartDepartmentLabels',
            'chartDepartmentCounts',
            'regularAdmissions',
            'lateralAdmissions',
            'migrationAdmissions',
            'scholarshipStudents',
            'semesterLabels',
            'chartSemesterCounts',
            'recentStudents',
            'recentInvoices',
            'upcomingEvents',
            'activeTerm'
        ));
    }
}
