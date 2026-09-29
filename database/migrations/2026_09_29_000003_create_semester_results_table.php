<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_semester_enrollment_id');
            $table->foreign('student_semester_enrollment_id', 'semester_results_enrollment_fk')
                ->references('id')
                ->on('student_semester_enrollments')
                ->cascadeOnDelete();
            $table->foreignId('student_id');
            $table->foreign('student_id', 'semester_results_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();
            $table->decimal('semester_percentage', 5, 2)->nullable();
            $table->decimal('sgpa', 3, 2)->nullable();
            $table->decimal('cgpa', 3, 2)->nullable();
            // Draft is allowed by Phase 0. Published results use Pass or Fail and have a published_at value.
            $table->string('status', 20)->default('Draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique('student_semester_enrollment_id', 'semester_result_enrollment_unique');
            $table->index('student_id', 'semester_results_student_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_results');
    }
};
