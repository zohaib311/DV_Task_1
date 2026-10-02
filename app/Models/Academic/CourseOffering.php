<?php

namespace App\Models\Academic;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Section\Section;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    protected $fillable = ['academic_term_id', 'curriculum_course_id', 'course_id', 'department_id', 'program_id', 'section_id', 'semester', 'course_code', 'course_name', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks', 'status'];

    protected $casts = ['credit_hours' => 'decimal:1', 'attendance_policy' => 'array', 'assessment_scheme_approved_at' => 'datetime'];

    public function assessmentComponents()
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentComponent::class);
    }

    public function assessmentSubmissions()
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentSubmission::class);
    }

    public function assessmentAudits()
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentAudit::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(\App\Models\Attendance\AttendanceSession::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function curriculumCourse()
    {
        return $this->belongsTo(CurriculumCourse::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class)->withTimestamps();
    }

    public function enrollmentCourses()
    {
        return $this->hasMany(StudentEnrollmentCourse::class);
    }
}
