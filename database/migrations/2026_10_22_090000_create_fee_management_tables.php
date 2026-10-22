<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->string('name');
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->decimal('security_fee', 12, 2)->default(0);
            $table->decimal('processing_fee', 12, 2)->default(0);
            $table->decimal('enrollment_fee', 12, 2)->default(0);
            $table->decimal('tuition_per_credit_hour', 12, 2)->default(0);
            $table->decimal('exam_fee', 12, 2)->default(0);
            $table->decimal('convocation_fee', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->unsignedSmallInteger('due_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['program_id', 'academic_term_id', 'is_active']);
        });

        Schema::create('student_fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_semester_enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('unpaid');
            $table->string('currency', 3)->default('PKR');
            $table->decimal('credit_hours', 6, 1)->default(0);
            $table->json('line_items');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('late_fee_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('proof_path');
            $table->string('status')->default('submitted');
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['student_fee_invoice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('student_fee_invoices');
        Schema::dropIfExists('fee_structures');
    }
};
