<?php

namespace Tests\Feature;

use Tests\TestCase;

class AcademicPolicyConfigurationTest extends TestCase
{
    public function test_academic_policy_has_the_expected_default_rules(): void
    {
        $this->assertSame(100, config('academic.marks.default_total'));
        $this->assertSame(50.0, config('academic.marks.passing_percentage'));
        $this->assertSame(3.0, config('academic.courses.default_credit_hours'));
        $this->assertTrue(config('academic.results.allow_drafts'));
        $this->assertTrue(config('academic.results.require_all_course_marks_to_publish'));
        $this->assertFalse(config('academic.results.allow_published_result_edits'));
    }

    public function test_grade_scale_is_descending_and_uses_the_four_point_system(): void
    {
        $gradeScale = config('academic.grade_scale');

        $this->assertCount(10, $gradeScale);
        $this->assertSame('A', $gradeScale[0]['grade']);
        $this->assertSame(4.00, $gradeScale[0]['grade_point']);
        $this->assertSame('F', $gradeScale[array_key_last($gradeScale)]['grade']);
        $this->assertSame(0.00, $gradeScale[array_key_last($gradeScale)]['grade_point']);

        $minimumPercentages = array_column($gradeScale, 'minimum_percentage');
        $sortedPercentages = $minimumPercentages;
        rsort($sortedPercentages);

        $this->assertSame($sortedPercentages, $minimumPercentages);
    }
}
