<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL may leave the empty table behind when a later FK statement
        // fails because DDL is not transactional. This migration has never
        // been recorded in that case, so rebuilding the partial table is safe.
        Schema::dropIfExists('student_assessment_submissions');

        if (! Schema::hasColumn('assessments', 'instructions')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->text('instructions')->nullable()->after('title');
                $table->boolean('submission_required')->default(false)->after('weight');
                $table->dateTime('submissions_due_at')->nullable()->after('submission_required');
                $table->timestamp('marks_released_at')->nullable()->after('submissions_due_at');
            });
        }

        Schema::create('student_assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id');
            $table->foreign('assessment_id', 'student_submission_assessment_fk')->references('id')->on('assessments')->restrictOnDelete();
            $table->foreignId('student_enrollment_course_id');
            $table->foreign('student_enrollment_course_id', 'student_submission_course_fk')->references('id')->on('student_enrollment_courses')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->timestamp('submitted_at');
            $table->text('teacher_feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'student_enrollment_course_id'], 'student_assessment_submission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessment_submissions');
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['instructions', 'submission_required', 'submissions_due_at', 'marks_released_at']);
        });
    }
};
