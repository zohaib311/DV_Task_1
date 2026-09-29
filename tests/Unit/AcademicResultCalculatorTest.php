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
}
