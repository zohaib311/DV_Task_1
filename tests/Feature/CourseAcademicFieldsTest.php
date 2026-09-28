<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAcademicFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_persists_its_academic_fields(): void
    {
        $course = Course::create([
            'code' => 'CS999',
            'name' => 'Academic Test Course',
            'description' => 'Course used to verify academic course fields.',
            'credit_hours' => 4.0,
            'total_marks' => 150,
            'is_active' => false,
        ]);

        $course->refresh();

        $this->assertSame('4.0', $course->credit_hours);
        $this->assertSame(150, $course->total_marks);
        $this->assertFalse($course->is_active);
    }
}
