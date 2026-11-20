<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('cnic', 30)->nullable();
            $table->string('father_name')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();

            // Academic Program Choice
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->string('admission_type', 30)->default('regular'); // regular, lateral_adp, migration

            // Previous Qualification (Intermediate / HSSC)
            $table->string('inter_degree', 100)->nullable();
            $table->string('inter_board', 150)->nullable();
            $table->string('inter_roll_no', 50)->nullable();
            $table->decimal('inter_total_marks', 7, 2)->nullable();
            $table->decimal('inter_obtained_marks', 7, 2)->nullable();
            $table->decimal('inter_percentage', 5, 2)->nullable();

            // ADP Lateral Entry / Transfer Specifics
            $table->string('previous_institution', 150)->nullable();
            $table->string('previous_degree', 150)->nullable();
            $table->string('previous_cgpa', 20)->nullable();
            $table->decimal('transferred_credits', 5, 1)->nullable()->default(64.0);

            // Scholarship & Financial Aid
            $table->string('scholarship_name', 120)->nullable();

            // Supporting Documents
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->text('applicant_remarks')->nullable();

            // Workflow status: pending, under_review, approved, enrolled, rejected
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();

            // Conversion to Student
            $table->foreignId('enrolled_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('admission_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('program_interest', 100)->nullable();
            $table->text('message');
            $table->string('status', 30)->default('new'); // new, contacted, closed
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
        Schema::dropIfExists('admission_inquiries');
    }
};
