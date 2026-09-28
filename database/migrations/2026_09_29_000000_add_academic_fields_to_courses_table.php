<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Defaults intentionally match the Phase 0 academic policy baseline.
            $table->decimal('credit_hours', 4, 1)->default(3)->after('description');
            $table->unsignedInteger('total_marks')->default(100)->after('credit_hours');
            $table->boolean('is_active')->default(true)->after('total_marks');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['credit_hours', 'total_marks', 'is_active']);
        });
    }
};
