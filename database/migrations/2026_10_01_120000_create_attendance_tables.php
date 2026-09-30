<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->json('attendance_policy')->nullable();
        });
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->date('held_on');
            $table->string('type', 20);
            $table->unsignedSmallInteger('slot')->default(1);
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->unique(['course_offering_id', 'held_on', 'type', 'slot'], 'attendance_session_slot_unique');
        });
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_enrollment_course_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->unique(['attendance_session_id', 'student_enrollment_course_id'], 'attendance_student_session_unique');
        });
        Schema::create('attendance_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);
            $table->string('reason', 500)->nullable();
            $table->json('before')->nullable();
            $table->json('after');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_audits');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
        Schema::table('course_offerings', fn (Blueprint $table) => $table->dropColumn('attendance_policy'));
    }
};
