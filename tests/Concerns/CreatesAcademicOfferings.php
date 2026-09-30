<?php

namespace Tests\Concerns;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

trait CreatesAcademicOfferings
{
    private function offeringPlan(Student $student, array $courses, string $semester = 'Semester 2'): array
    {
        $year = AcademicYear::firstOrCreate(['name' => '2026-2027'], ['starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::firstOrCreate(['academic_year_id' => $year->id, 'name' => 'Spring 2027'], ['starts_on' => '2027-01-15', 'ends_on' => '2027-06-15', 'status' => 'active']);
        $curriculum = SemesterCurriculum::create(['department_id' => $student->department_id, 'semester' => $semester, 'version' => 'Version '.(SemesterCurriculum::count() + 1), 'status' => 'approved', 'approved_at' => now()]);
        $number = User::count() + 1;
        $login = User::create(['name' => 'Course Teacher', 'email' => "teacher{$number}@example.com", 'phone' => '0311'.str_pad((string) $number, 7, '0', STR_PAD_LEFT), 'password' => bcrypt('password')]);
        $teacher = Teacher::create(['user_id' => $login->id, 'name' => 'Course Teacher', 'email' => $login->email, 'phone' => $login->phone, 'course' => 'Teaching']);
        $ids = [];
        foreach ($courses as $course) {
            $entry = $curriculum->courses()->create([
                'course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
                'credit_hours' => $course->credit_hours, 'total_marks' => $course->total_marks,
                'attendance_marks' => $course->attendance_marks, 'mid_marks' => $course->mid_marks, 'final_marks' => $course->final_marks, 'type' => 'required',
            ]);
            $offering = CourseOffering::create($entry->only(CurriculumCourse::SNAPSHOT_FIELDS) + [
                'academic_term_id' => $term->id, 'curriculum_course_id' => $entry->id,
                'department_id' => $student->department_id, 'section_id' => $student->section_id, 'semester' => $semester, 'status' => 'active',
            ]);
            $offering->teachers()->attach($teacher);
            $ids[] = $offering->id;
        }

        return ['academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id, 'offering_ids' => $ids];
    }
}
