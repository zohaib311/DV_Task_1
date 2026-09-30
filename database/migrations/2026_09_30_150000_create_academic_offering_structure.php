<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 9)->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
        });
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status', 20)->default('planned');
            $table->timestamps();
            $table->unique(['academic_year_id', 'name']);
        });
        Schema::create('semester_curricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('semester', 50);
            $table->string('version', 60);
            $table->string('status', 20)->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['department_id', 'semester', 'version'], 'curriculum_version_unique');
        });
        Schema::create('curriculum_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_curriculum_id')->constrained('semester_curricula')->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('type', 20)->default('required');
            $table->string('course_code');
            $table->string('course_name');
            $table->decimal('credit_hours', 4, 1);
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('attendance_marks');
            $table->unsignedInteger('mid_marks');
            $table->unsignedInteger('final_marks');
            $table->timestamps();
            $table->unique(['semester_curriculum_id', 'course_id']);
        });
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained()->restrictOnDelete();
            $table->foreignId('curriculum_course_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->string('semester', 50);
            $table->string('course_code');
            $table->string('course_name');
            $table->decimal('credit_hours', 4, 1);
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('attendance_marks');
            $table->unsignedInteger('mid_marks');
            $table->unsignedInteger('final_marks');
            $table->string('status', 20)->default('planned');
            $table->timestamps();
            $table->unique(['academic_term_id', 'section_id', 'course_id'], 'term_section_course_unique');
        });
        Schema::create('course_offering_teacher', function (Blueprint $table) {
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->primary(['course_offering_id', 'teacher_id']);
        });
        Schema::table('student_semester_enrollments', function (Blueprint $table) {
            // Null means a historical enrollment recorded before academic terms existed.
            $table->foreignId('academic_term_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('semester_curriculum_id')->nullable()->constrained('semester_curricula')->restrictOnDelete();
        });
        Schema::table('student_enrollment_courses', function (Blueprint $table) {
            $table->foreignId('course_offering_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('course_code')->nullable();
            $table->string('course_name')->nullable();
            $table->string('registration_type', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollment_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_offering_id');
            $table->dropColumn(['course_code', 'course_name', 'registration_type']);
        });
        Schema::table('student_semester_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_term_id');
            $table->dropConstrainedForeignId('semester_curriculum_id');
        });
        Schema::dropIfExists('course_offering_teacher');
        Schema::dropIfExists('course_offerings');
        Schema::dropIfExists('curriculum_courses');
        Schema::dropIfExists('semester_curricula');
        Schema::dropIfExists('academic_terms');
        Schema::dropIfExists('academic_years');
    }
};
