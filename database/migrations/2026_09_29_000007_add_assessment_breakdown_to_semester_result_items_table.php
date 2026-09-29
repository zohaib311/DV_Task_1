<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_result_items', function (Blueprint $table) {
            // Nullable keeps Phase-5 single-total records intact until a verified manual breakdown is available.
            $table->unsignedInteger('attendance_marks')->nullable()->after('total_marks');
            $table->unsignedInteger('mid_marks')->nullable()->after('attendance_marks');
            $table->unsignedInteger('final_marks')->nullable()->after('mid_marks');
            $table->decimal('attendance_obtained_marks', 6, 2)->nullable()->after('obtained_marks');
            $table->decimal('mid_obtained_marks', 6, 2)->nullable()->after('attendance_obtained_marks');
            $table->decimal('final_obtained_marks', 6, 2)->nullable()->after('mid_obtained_marks');
        });
    }

    public function down(): void
    {
        Schema::table('semester_result_items', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_marks',
                'mid_marks',
                'final_marks',
                'attendance_obtained_marks',
                'mid_obtained_marks',
                'final_obtained_marks',
            ]);
        });
    }
};
