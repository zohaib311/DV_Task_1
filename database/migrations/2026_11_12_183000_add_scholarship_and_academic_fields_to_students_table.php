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
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'cnic')) {
                $table->string('cnic', 30)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('students', 'inter_degree')) {
                $table->string('inter_degree', 100)->nullable()->after('admission_type');
            }
            if (! Schema::hasColumn('students', 'inter_board')) {
                $table->string('inter_board', 150)->nullable()->after('inter_degree');
            }
            if (! Schema::hasColumn('students', 'inter_roll_no')) {
                $table->string('inter_roll_no', 50)->nullable()->after('inter_board');
            }
            if (! Schema::hasColumn('students', 'inter_total_marks')) {
                $table->decimal('inter_total_marks', 7, 2)->nullable()->after('inter_roll_no');
            }
            if (! Schema::hasColumn('students', 'inter_obtained_marks')) {
                $table->decimal('inter_obtained_marks', 7, 2)->nullable()->after('inter_total_marks');
            }
            if (! Schema::hasColumn('students', 'inter_percentage')) {
                $table->decimal('inter_percentage', 5, 2)->nullable()->after('inter_obtained_marks');
            }
            if (! Schema::hasColumn('students', 'scholarship_name')) {
                $table->string('scholarship_name', 120)->nullable()->after('inter_percentage');
            }
            if (! Schema::hasColumn('students', 'scholarship_percentage')) {
                $table->decimal('scholarship_percentage', 5, 2)->default(0.00)->after('scholarship_name');
            }
            if (! Schema::hasColumn('students', 'scholarship_status')) {
                $table->string('scholarship_status', 30)->default('none')->after('scholarship_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'cnic',
                'inter_degree',
                'inter_board',
                'inter_roll_no',
                'inter_total_marks',
                'inter_obtained_marks',
                'inter_percentage',
                'scholarship_name',
                'scholarship_percentage',
                'scholarship_status',
            ]);
        });
    }
};
