<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->timestamp('assessment_scheme_approved_at')->nullable();
            $table->foreignId('assessment_scheme_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->string('code', 30);
            $table->decimal('allocation', 7, 2);
            $table->timestamps();
            $table->unique(['course_offering_id', 'code']);
        });
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_component_id')->constrained()->restrictOnDelete();
            $table->string('title', 120);
            $table->date('held_on');
            $table->decimal('maximum', 7, 2);
            $table->decimal('weight', 7, 2);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->unique(['assessment_component_id', 'title']);
        });
        Schema::create('assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_enrollment_course_id')->constrained()->restrictOnDelete();
            $table->decimal('obtained', 7, 2)->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'student_enrollment_course_id'], 'assessment_student_unique');
        });
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('submitted');
            $table->json('snapshot');
            $table->string('review_note', 1000)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('assessment_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('reason', 1000)->nullable();
            $table->json('before')->nullable();
            $table->json('after');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_audits');
        Schema::dropIfExists('assessment_submissions');
        Schema::dropIfExists('assessment_marks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_components');
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assessment_scheme_approved_by');
            $table->dropColumn('assessment_scheme_approved_at');
        });
    }
};
