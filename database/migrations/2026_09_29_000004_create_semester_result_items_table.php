<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_result_id');
            $table->foreign('semester_result_id', 'semester_result_items_result_fk')
                ->references('id')
                ->on('semester_results')
                ->cascadeOnDelete();
            $table->foreignId('student_enrollment_course_id');
            $table->foreign('student_enrollment_course_id', 'semester_result_items_enroll_course_fk')
                ->references('id')
                ->on('student_enrollment_courses')
                ->cascadeOnDelete();
            $table->foreignId('course_id');
            $table->foreign('course_id', 'semester_result_items_course_fk')
                ->references('id')
                ->on('courses')
                ->restrictOnDelete();
            $table->string('course_code', 50);
            $table->string('course_name');
            $table->decimal('credit_hours', 4, 1);
            $table->unsignedInteger('total_marks');
            $table->decimal('obtained_marks', 6, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->decimal('grade_point', 3, 2)->nullable();
            $table->string('status', 20)->default('Draft');
            $table->timestamps();

            $table->unique(['semester_result_id', 'student_enrollment_course_id'], 'result_item_enrollment_course_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_result_items');
    }
};
