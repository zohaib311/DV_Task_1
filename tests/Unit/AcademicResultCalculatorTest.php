<?php

namespace Tests\Unit;

use App\Services\AcademicResultCalculator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicResultCalculatorTest extends TestCase
{
    private AcademicResultCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = app(AcademicResultCalculator::class);
    }

    public function test_it_calculates_course_percentage_grade_point_and_status_from_the_configured_scale(): void
    {
        $aMinus = $this->calculator->calculateCourse(82, 100);
        $passBoundary = $this->calculator->calculateCourse(50, 100);
        $fail = $this->calculator->calculateCourse(49, 100);

        $this->assertSame(82.0, $aMinus['percentage']);
        $this->assertSame('A-', $aMinus['grade']);
        $this->assertSame(3.7, $aMinus['grade_point']);
        $this->assertSame('Pass', $aMinus['status']);
        $this->assertSame('D', $passBoundary['grade']);
        $this->assertSame('Pass', $passBoundary['status']);
        $this->assertSame('F', $fail['grade']);
        $this->assertSame(0.0, $fail['grade_point']);
        $this->assertSame('Fail', $fail['status']);
    }

    public function test_it_calculates_weighted_semester_gpa_percentage_and_fail_status(): void
    {
        $semester = $this->calculator->calculateSemester([
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 85],
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 80],
            ['credit_hours' => 4, 'total_marks' => 100, 'obtained_marks' => 75],
        ]);

        $this->assertSame(80.0, $semester['semester_percentage']);
        $this->assertSame(3.63, $semester['sgpa']);
        $this->assertSame('Pass', $semester['status']);

        $failedSemester = $this->calculator->calculateSemester([
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 75],
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 49],
        ]);

        $this->assertSame(1.65, $failedSemester['sgpa']);
        $this->assertSame('Fail', $failedSemester['status']);
    }

    public function test_it_returns_a_draft_for_incomplete_marks_and_blocks_incomplete_publish(): void
    {
        $draft = $this->calculator->calculateSemester([
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 80],
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => null],
        ]);

        $this->assertFalse($draft['is_complete']);
        $this->assertSame('Draft', $draft['status']);
        $this->assertNull($draft['sgpa']);

        $this->expectException(ValidationException::class);
        $this->calculator->calculateSemester([
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => 80],
            ['credit_hours' => 3, 'total_marks' => 100, 'obtained_marks' => null],
        ], true);
    }

    public function test_it_calculates_cgpa_and_replaces_a_previous_attempt_with_the_latest_one(): void
    {
        $cgpa = $this->calculator->calculateCgpa([
            ['course_id' => 1, 'credit_hours' => 3, 'grade_point' => 1.0, 'attempted_at' => '2026-01-01'],
            ['course_id' => 2, 'credit_hours' => 3, 'grade_point' => 3.7, 'attempted_at' => '2026-01-01'],
            ['course_id' => 1, 'credit_hours' => 3, 'grade_point' => 4.0, 'attempted_at' => '2026-06-01'],
        ]);

        $this->assertSame(3.85, $cgpa);
    }

    public function test_it_derives_course_outcomes_from_attendance_midterm_and_final_marks(): void
    {
        $outcome = $this->calculator->calculateAssessmentCourse([
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'attendance_obtained_marks' => 8,
            'mid_obtained_marks' => 24,
            'final_obtained_marks' => 50,
        ]);

        $this->assertSame(82.0, $outcome['obtained_marks']);
        $this->assertSame(82.0, $outcome['percentage']);
        $this->assertSame('A-', $outcome['grade']);
        $this->assertSame(3.7, $outcome['grade_point']);
    }

    public function test_it_rejects_component_marks_above_their_own_maximum(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculateAssessmentCourse([
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'attendance_obtained_marks' => 11,
            'mid_obtained_marks' => 20,
            'final_obtained_marks' => 50,
        ]);
    }

    public function test_optional_final_minimum_rule_can_fail_an_otherwise_passing_course(): void
    {
        config()->set('academic.assessment.final_minimum.enabled', true);
        config()->set('academic.assessment.final_minimum.minimum_percentage', 50);

        $outcome = $this->calculator->calculateAssessmentCourse([
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'attendance_obtained_marks' => 10,
            'mid_obtained_marks' => 30,
            'final_obtained_marks' => 20,
        ]);

        $this->assertSame(60.0, $outcome['percentage']);
        $this->assertSame('F', $outcome['grade']);
        $this->assertSame(0.0, $outcome['grade_point']);
        $this->assertSame('Fail', $outcome['status']);
    }
}
