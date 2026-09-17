<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Temporary Foreign Key Checks off karein taake purane data par error na aaye
        Schema::disableForeignKeyConstraints();

        Schema::table('sections', function (Blueprint $table) {
            // 1. Purani string field drop karein agar exist karti hai
            if (Schema::hasColumn('sections', 'department')) {
                $table->dropColumn('department');
            }

            // 2. Department ID Column Nullable add karein
            if (!Schema::hasColumn('sections', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('name');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('sections', function (Blueprint $table) {
            if (Schema::hasColumn('sections', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            $table->string('department')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }
};
