<?php

namespace Tests\Unit;

use App\Services\Result\FlexibleResultCalculator;
use Tests\TestCase;

class FlexibleResultCalculatorTest extends TestCase
{
    public function test_fractional_allocations_raw_weights_and_credit_weighted_sgpa(): void
    {
        $calculator = app(FlexibleResultCalculator::class);
        $snapshot = ['grading_policy' => $calculator->policy(), 'scheme' => [['code' => 'assignment', 'allocation' => 12.5], ['code' => 'final', 'allocation' => 87.5]],
            'student' => ['assessments' => [
                ['assessment_id' => 1, 'component' => 'assignment', 'maximum' => 20, 'weight' => 5, 'obtained' => 10],
                ['assessment_id' => 2, 'component' => 'assignment', 'maximum' => 10, 'weight' => 7.5, 'obtained' => 10],
                ['assessment_id' => 3, 'component' => 'final', 'maximum' => 100, 'weight' => 87.5, 'obtained' => 80],
            ]]];
        $course = $calculator->course($snapshot, 100);
        $this->assertEquals(80, $course['obtained_marks']);
        $this->assertEquals(10, $course['assessment_snapshot']['student']['components']['assignment']);
        $this->assertEquals(3.7, $course['grade_point']);
        $summary = $calculator->semester([array_merge($course, ['credit_hours' => 3]), ['credit_hours' => 1, 'grade_point' => 1, 'obtained_marks' => 50, 'total_marks' => 100, 'status' => 'Pass']]);
        $this->assertEquals(3.03, $summary['sgpa']);
        $this->assertEquals(65, $summary['semester_percentage']);
    }
}
