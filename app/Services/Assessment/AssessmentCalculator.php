<?php

namespace App\Services\Assessment;

use App\Models\Academic\CourseOffering;
use App\Services\Attendance\AttendanceCalculator;

class AssessmentCalculator
{
    public function preview(CourseOffering $offering): array
    {
        $components = $offering->assessmentComponents()->with('assessments.marks')->get();
        $courses = $offering->enrollmentCourses()->with(['enrollment.student', 'offering'])->orderBy('id')->get();
        $issues = [];
        if (! $offering->assessment_scheme_approved_at) {
            $issues[] = 'An administrator must approve the assessment scheme first.';
        }
        if ($courses->isEmpty()) {
            $issues[] = 'This offering has no enrolled students.';
        }
        foreach ($components as $component) {
            if ($component->code !== 'attendance' && (int) round($component->assessments->sum('weight') * 100) !== (int) round($component->allocation * 100)) {
                $issues[] = ucfirst($component->code).' assessment weights must equal '.$component->allocation.'.';
            }
        }
        if ($offering->attendanceSessions()->where('status', 'draft')->exists()) {
            $issues[] = 'Complete or cancel all draft attendance sessions before submitting.';
        }
        $rows = [];
        foreach ($courses as $course) {
            $scores = [];
            $raw = [];
            $attendance = null;
            $complete = true;
            foreach ($components as $component) {
                if ($component->code === 'attendance') {
                    $attendance = app(AttendanceCalculator::class)->summary($course);
                    $score = $attendance['marks'];
                } else {
                    $score = 0;
                    foreach ($component->assessments as $assessment) {
                        $mark = $assessment->marks->firstWhere('student_enrollment_course_id', $course->id)?->obtained;
                        $raw[] = ['assessment_id' => $assessment->id, 'title' => $assessment->title, 'held_on' => $assessment->held_on->toDateString(), 'component' => $component->code, 'maximum' => $assessment->maximum, 'weight' => $assessment->weight, 'obtained' => $mark];
                        if ($mark === null) {
                            $score = null;
                        } elseif ($score !== null) {
                            $score += (float) $mark / (float) $assessment->maximum * (float) $assessment->weight;
                        }
                    }
                    if ($component->assessments->isEmpty()) {
                        $score = null;
                    }
                }
                $scores[$component->code] = $score === null ? null : round($score, 2);
                $complete = $complete && $score !== null;
            }
            $total = $complete ? round(array_sum($scores), 2) : null;
            if (! $complete) {
                $issues[] = $course->enrollment->student->registration_no.': marks or counted attendance are incomplete.';
            }
            $rows[] = [
                'student_enrollment_course_id' => $course->id, 'enrollment_id' => $course->student_semester_enrollment_id,
                'student_id' => $course->enrollment->student_id, 'student_name' => $course->enrollment->student->name,
                'registration_no' => $course->enrollment->student->registration_no, 'credit_hours' => $course->credit_hours,
                'components' => $scores, 'assessments' => $raw, 'attendance' => $attendance,
                'obtained' => $total, 'maximum' => $course->total_marks,
                'percentage' => $total === null ? null : round($total / $course->total_marks * 100, 2),
            ];
        }

        return ['offering_id' => $offering->id, 'course_code' => $offering->course_code, 'course_name' => $offering->course_name,
            'academic_term_id' => $offering->academic_term_id, 'term_name' => $offering->term->name, 'department_id' => $offering->department_id,
            'section_id' => $offering->section_id, 'semester' => $offering->semester, 'scheme_approved_at' => $offering->assessment_scheme_approved_at?->toIso8601String(),
            'scheme' => $components->map->only(['code', 'allocation'])->all(), 'students' => $rows,
            'issues' => array_values(array_unique($issues)), 'complete' => $issues === []];
    }
}
