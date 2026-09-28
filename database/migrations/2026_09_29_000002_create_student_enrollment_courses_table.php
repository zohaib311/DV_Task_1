<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL created the table before an earlier failed foreign-key attempt.
        // The pending migration has never recorded data, so rebuild that partial table safely.
        Schema::dropIfExists('student_enrollment_courses');

        Schema::create('student_enrollment_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_semester_enrollment_id');
            $table->foreign('student_semester_enrollment_id', 'enrollment_courses_enrollment_fk')
                ->references('id')
                ->on('student_semester_enrollments')
                ->cascadeOnDelete();
            $table->foreignId('course_id');
            $table->foreign('course_id', 'enrollment_courses_course_fk')
                ->references('id')
                ->on('courses')
                ->restrictOnDelete();
            $table->decimal('credit_hours', 4, 1);
            $table->unsignedInteger('total_marks');
            $table->timestamps();

            $table->unique(['student_semester_enrollment_id', 'course_id'], 'enrollment_course_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollment_courses');
    }
};
