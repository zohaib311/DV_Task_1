<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_enrollment_course_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('leave_type', 30);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->unsignedInteger('attachment_size')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('teacher_remark')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['course_offering_id', 'status']);
            $table->index(['student_enrollment_course_id', 'status']);
            $table->index(['starts_on', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_leave_requests');
    }
};
