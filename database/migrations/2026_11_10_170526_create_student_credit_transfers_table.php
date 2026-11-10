<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'admission_type')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('admission_type', 30)->default('regular')->after('section_id');
            });
        }

        if (! Schema::hasColumn('students', 'admitted_semester')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('admitted_semester', 30)->nullable()->after('admission_type');
            });
        }

        Schema::create('student_credit_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('transfer_type', 30)->default('adp_lateral'); // 'adp_lateral', 'migration'
            $table->string('previous_institution', 150);
            $table->string('previous_degree', 150);
            $table->string('previous_cgpa', 20)->nullable();
            $table->decimal('transferred_credits', 5, 1)->default(64.0);
            $table->unsignedTinyInteger('admitted_to_semester')->default(5);
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'transfer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_credit_transfers');

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'admitted_semester')) {
                $table->dropColumn('admitted_semester');
            }
            if (Schema::hasColumn('students', 'admission_type')) {
                $table->dropColumn('admission_type');
            }
        });
    }
};
