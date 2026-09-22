<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('students', function (Blueprint $table) {
            // 1. Purani Class column drop karein
            if (Schema::hasColumn('students', 'class')) {
                $table->dropColumn('class');
            }

            // 2. Department Foreign Key add karein
            if (!Schema::hasColumn('students', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            }

            // 3. Section Foreign Key add karein
            if (!Schema::hasColumn('students', 'section_id')) {
                $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            }

            // 4. Multiple Course IDs (JSON) Column add karein
            if (!Schema::hasColumn('students', 'course_ids')) {
                $table->json('course_ids')->nullable();
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('students', function (Blueprint $table) {
            $table->string('class')->nullable();

            if (Schema::hasColumn('students', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            if (Schema::hasColumn('students', 'section_id')) {
                $table->dropForeign(['section_id']);
                $table->dropColumn('section_id');
            }

            if (Schema::hasColumn('students', 'course_ids')) {
                $table->dropColumn('course_ids');
            }
        });

        Schema::enableForeignKeyConstraints();
    }
};
