<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_semester_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->string('semester', 50);
            $table->string('academic_year', 9);
            $table->string('status', 20)->default('active');
            $table->date('enrolled_at');
            $table->date('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year', 'semester'], 'student_academic_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_semester_enrollments');
    }
};
