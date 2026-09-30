<?php

namespace Database\Seeders\Demo;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AcademicEnrollmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TeacherPanelDemoSeeder extends Seeder
{
    public const DEPARTMENT = 'DEMO - Computing (9C)';

    public const PASSWORD = 'UniDemo@2026!';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo data is allowed only in local/testing environments.');
        }

        DB::transaction(function () {
            // The department is created in the same transaction as the full fixture.
            // A rerun must not reset passwords, assignments, marks, or permissions.
            if (Department::where('name', self::DEPARTMENT)->exists()) {
                $this->command?->warn('Demo dataset already exists. Nothing changed; passwords were not reset.');

                return;
            }

            if (User::whereIn('email', ['demo.teacher1@example.test', 'demo.teacher2@example.test'])->exists()
                || Teacher::whereIn('email', ['demo.teacher1@example.test', 'demo.teacher2@example.test'])->exists()
                || Course::whereIn('code', ['DEMO-CS101', 'DEMO-CS102', 'DEMO-CS103'])->exists()
                || Student::where('email', 'like', 'demo.student%@example.test')->exists()
                || Role::where('name', 'Demo Teacher (9C)')->exists()) {
                throw new RuntimeException('Demo identifiers already exist outside the demo dataset. No records were changed.');
            }

            $role = Role::create(['name' => 'Demo Teacher (9C)', 'guard_name' => 'web']);
            foreach (['dashboard.view', 'offerings.view-assigned'] as $permission) {
                $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
            }
            $department = Department::create(['name' => self::DEPARTMENT]);
            $section = Section::create(['name' => 'DEMO-A', 'department_id' => $department->id]);
            $year = AcademicYear::firstOrCreate(['name' => '2026-2027'], ['starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
            $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'DEMO 9C Teaching Term', 'starts_on' => $year->starts_on, 'ends_on' => $year->ends_on, 'status' => 'active']);
            $curriculum = SemesterCurriculum::create(['department_id' => $department->id, 'semester' => 'Semester 1', 'version' => 'DEMO-9C', 'status' => 'approved', 'approved_at' => now()]);

            $teachers = [];
            foreach ([1 => 'Demo Teacher Ayesha', 2 => 'Demo Teacher Hamza'] as $number => $name) {
                $user = User::create(['name' => $name, 'email' => "demo.teacher{$number}@example.test", 'phone' => '0399900000'.$number, 'password' => Hash::make(self::PASSWORD), 'image' => 'default-user.png']);
                $user->assignRole($role);
                $teachers[$number] = Teacher::create(['name' => $name, 'email' => $user->email, 'phone' => $user->phone, 'user_id' => $user->id, 'course' => 'DEMO', 'image' => 'default-user.png']);
            }

            $offeringIds = [];
            foreach ([1 => 'Demo Programming', 2 => 'Demo Mathematics', 3 => 'Demo Computing Lab'] as $number => $name) {
                $course = Course::create(['code' => 'DEMO-CS10'.$number, 'name' => $name, 'description' => 'Local Phase 9C testing course.', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
                $entry = $curriculum->courses()->create($course->only(['credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks']) + ['course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name, 'type' => 'required']);
                $offering = CourseOffering::create($entry->only(CurriculumCourse::SNAPSHOT_FIELDS) + ['academic_term_id' => $term->id, 'curriculum_course_id' => $entry->id, 'department_id' => $department->id, 'section_id' => $section->id, 'semester' => 'Semester 1', 'status' => 'active']);
                $offering->teachers()->attach($number === 3 ? [$teachers[1]->id, $teachers[2]->id] : [$teachers[$number]->id]);
                $offeringIds[] = $offering->id;
            }

            foreach (['Ali', 'Sara', 'Ahmed', 'Hina', 'Bilal', 'Noor'] as $index => $name) {
                $student = Student::create(['name' => 'Demo Student '.$name, 'email' => 'demo.student'.($index + 1).'@example.test', 'phone' => '0399910000'.($index + 1), 'department_id' => $department->id, 'section_id' => $section->id, 'image' => 'default-user.png']);
                app(AcademicEnrollmentService::class)->enroll(['student_id' => $student->id, 'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id, 'offering_ids' => $offeringIds]);
            }
            $this->command?->info('Created 2 teacher logins, 6 students, 3 offerings and 6 enrollments (18 course registrations).');
            $this->command?->info('Local demo logins: demo.teacher1@example.test / demo.teacher2@example.test');
            $this->command?->info('Initial password for both: '.self::PASSWORD);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
