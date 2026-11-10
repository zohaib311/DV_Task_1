<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\Program;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Academic\StudentCreditTransfer;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Result\SemesterResultItem;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class Semester5DemoSeeder extends Seeder
{
    /**
     * Seeds Semester 5 Curriculum, Course Offerings, and an ADP Lateral Entry Demo Student.
     * Fully idempotent — safe to re-run without duplicating or changing existing data.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Department, Program, Section, and Term
            $department = Department::find(5) ?? Department::firstOrCreate(['name' => 'Computer Section (IT)']);
            $program = Program::find(1) ?? Program::firstOrCreate(
                ['code' => 'BSCS'],
                ['name' => 'BS COMPUTER SCIENCE', 'department_id' => $department->id, 'is_active' => true]
            );
            $section = Section::find(1) ?? Section::firstOrCreate(
                ['name' => 'Computer Section', 'department_id' => $department->id]
            );
            $term = AcademicTerm::where('status', 'active')->latest('id')->first();
            if (! $term) {
                $year = AcademicYear::firstOrCreate(['name' => '2026-2027'], ['starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
                $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall-2026', 'starts_on' => '2026-08-15', 'ends_on' => '2026-12-31', 'status' => 'active']);
            }

            // 2. Teachers
            $teacher = Teacher::first();
            if (! $teacher) {
                $teacherUser = User::create([
                    'name' => 'Dr. Asad CS',
                    'email' => 'asad.cs@example.test',
                    'phone' => '03001239988',
                    'password' => Hash::make('UniDemo@2026!'),
                ]);
                $teacher = Teacher::create([
                    'name' => 'Dr. Asad CS',
                    'email' => $teacherUser->email,
                    'phone' => $teacherUser->phone,
                    'user_id' => $teacherUser->id,
                    'course' => 'CS',
                    'image' => 'default-user.png',
                ]);
            }

            // 3. Semester 5 Curriculum
            $curriculum = SemesterCurriculum::firstOrCreate(
                [
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'semester' => 'Semester 5',
                ],
                [
                    'version' => '2026-v1',
                    'status' => 'approved',
                    'approved_at' => now(),
                ]
            );

            // 4. Semester 5 Courses
            $sem5CoursesData = [
                ['code' => 'CS301', 'name' => 'Operating Systems', 'credits' => 3, 'att' => 10, 'mid' => 30, 'fin' => 60],
                ['code' => 'CS302', 'name' => 'Software Engineering', 'credits' => 3, 'att' => 10, 'mid' => 30, 'fin' => 60],
                ['code' => 'CS303', 'name' => 'Computer Networks', 'credits' => 3, 'att' => 10, 'mid' => 30, 'fin' => 60],
            ];

            $offeringIds = [];
            foreach ($sem5CoursesData as $data) {
                $course = Course::firstOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'description' => $data['name'] . ' core course for Semester 5',
                        'credit_hours' => $data['credits'],
                        'total_marks' => 100,
                        'attendance_marks' => $data['att'],
                        'mid_marks' => $data['mid'],
                        'final_marks' => $data['fin'],
                        'is_active' => true,
                    ]
                );

                $curriculumCourse = CurriculumCourse::firstOrCreate(
                    [
                        'semester_curriculum_id' => $curriculum->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'course_code' => $course->code,
                        'course_name' => $course->name,
                        'credit_hours' => $course->credit_hours,
                        'total_marks' => 100,
                        'attendance_marks' => $data['att'],
                        'mid_marks' => $data['mid'],
                        'final_marks' => $data['fin'],
                        'type' => 'required',
                    ]
                );

                $offering = CourseOffering::firstOrCreate(
                    [
                        'academic_term_id' => $term->id,
                        'curriculum_course_id' => $curriculumCourse->id,
                        'section_id' => $section->id,
                    ],
                    [
                        'department_id' => $department->id,
                        'program_id' => $program->id,
                        'course_id' => $course->id,
                        'course_code' => $course->code,
                        'course_name' => $course->name,
                        'credit_hours' => $course->credit_hours,
                        'total_marks' => 100,
                        'attendance_marks' => $data['att'],
                        'mid_marks' => $data['mid'],
                        'final_marks' => $data['fin'],
                        'semester' => 'Semester 5',
                        'status' => 'active',
                    ]
                );

                if (! $offering->teachers()->where('teachers.id', $teacher->id)->exists()) {
                    $offering->teachers()->attach($teacher->id);
                }

                $offeringIds[] = $offering->id;
            }

            // 5. Create Demo ADP Student with Transferred Credits & Published Semester 5 Result
            $studentEmail = 'adp.student@gmail.com';
            $studentUser = User::where('email', $studentEmail)->first();
            if (! $studentUser) {
                $studentUser = User::create([
                    'name' => 'Zubair Ahmed (ADP Graduate)',
                    'email' => $studentEmail,
                    'phone' => '03009876543',
                    'password' => Hash::make('1234'),
                    'image' => 'default-user.png',
                ]);
                $studentRole = Role::findOrCreate('Student', 'web');
                $studentUser->assignRole($studentRole);
            }

            $student = Student::where('email', $studentEmail)->first();
            if (! $student) {
                $student = Student::create([
                    'user_id' => $studentUser->id,
                    'name' => $studentUser->name,
                    'email' => $studentEmail,
                    'phone' => $studentUser->phone,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'section_id' => $section->id,
                    'admission_type' => 'lateral_adp',
                    'admitted_semester' => 'Semester 5',
                    'semester' => 'Semester 5',
                    'image' => 'default-user.png',
                ]);
            } else {
                $student->update([
                    'admission_type' => 'lateral_adp',
                    'admitted_semester' => 'Semester 5',
                    'semester' => 'Semester 5',
                ]);
            }

            // 6. Credit Transfer Record (64 Credits)
            StudentCreditTransfer::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'transfer_type' => 'lateral_adp',
                    'previous_institution' => 'Govt Post Graduate College Lahore',
                    'previous_degree' => 'Associate Degree in Computer Science (ADCS)',
                    'previous_cgpa' => '3.45',
                    'transferred_credits' => 64.0,
                    'admitted_to_semester' => 5,
                    'remarks' => 'Equivalence Committee Approval Notification # EC-2026/08',
                ]
            );

            // 7. Enroll Demo Student in Semester 5
            $enrollment = StudentSemesterEnrollment::where('student_id', $student->id)
                ->where('semester', 'Semester 5')
                ->first();

            if (! $enrollment) {
                $enrollment = StudentSemesterEnrollment::create([
                    'student_id' => $student->id,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'section_id' => $section->id,
                    'academic_term_id' => $term->id,
                    'semester_curriculum_id' => $curriculum->id,
                    'academic_year' => $term->academicYear?->name ?? '2026-2027',
                    'semester' => 'Semester 5',
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);

                foreach ($offeringIds as $offId) {
                    $off = CourseOffering::find($offId);
                    StudentEnrollmentCourse::create([
                        'student_semester_enrollment_id' => $enrollment->id,
                        'course_id' => $off->course_id,
                        'course_offering_id' => $off->id,
                        'course_code' => $off->course_code,
                        'course_name' => $off->course_name,
                        'credit_hours' => $off->credit_hours,
                        'total_marks' => 100,
                        'attendance_marks' => $off->attendance_marks,
                        'mid_marks' => $off->mid_marks,
                        'final_marks' => $off->final_marks,
                        'registration_type' => 'required',
                    ]);
                }
            }

            // 8. Publish Semester 5 Result for Demo Student
            if (! SemesterResult::where('student_id', $student->id)->where('student_semester_enrollment_id', $enrollment->id)->exists()) {
                $result = SemesterResult::create([
                    'student_id' => $student->id,
                    'student_semester_enrollment_id' => $enrollment->id,
                    'status' => 'Pass',
                    'semester_percentage' => 84.50,
                    'sgpa' => 3.67,
                    'cgpa' => 3.67,
                    'published_at' => now(),
                    'verification_hash' => hash('sha256', 'res_' . $student->id . '_sem5'),
                ]);

                $grades = [
                    ['code' => 'CS301', 'name' => 'Operating Systems', 'marks' => 85, 'pct' => 85.0, 'g' => 'A', 'gp' => 4.00],
                    ['code' => 'CS302', 'name' => 'Software Engineering', 'marks' => 82, 'pct' => 82.0, 'g' => 'A-', 'gp' => 3.70],
                    ['code' => 'CS303', 'name' => 'Computer Networks', 'marks' => 86.5, 'pct' => 86.5, 'g' => 'A', 'gp' => 4.00],
                ];

                foreach ($grades as $g) {
                    $course = Course::where('code', $g['code'])->first();
                    $sec = StudentEnrollmentCourse::where('student_semester_enrollment_id', $enrollment->id)
                        ->where('course_id', $course->id)
                        ->first();

                    SemesterResultItem::create([
                        'semester_result_id' => $result->id,
                        'student_enrollment_course_id' => $sec?->id,
                        'course_id' => $course->id,
                        'course_code' => $g['code'],
                        'course_name' => $g['name'],
                        'credit_hours' => 3.0,
                        'total_marks' => 100,
                        'obtained_marks' => $g['marks'],
                        'percentage' => $g['pct'],
                        'grade' => $g['g'],
                        'grade_point' => $g['gp'],
                        'status' => 'Pass',
                    ]);
                }
            }
        });
    }
}
