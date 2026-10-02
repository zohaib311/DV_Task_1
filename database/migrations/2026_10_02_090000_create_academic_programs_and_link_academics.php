<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_programs')) {
            Schema::create('academic_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained()->restrictOnDelete();
                $table->string('name');
                $table->string('code', 30)->unique();
                $table->unsignedTinyInteger('duration_years')->default(4);
                $table->unsignedTinyInteger('total_semesters')->default(8);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('semester_curricula', 'program_id')) {
            Schema::table('semester_curricula', function (Blueprint $table) {
                $table->foreignId('program_id')->nullable()->after('department_id')->constrained('academic_programs')->restrictOnDelete();
            });
        }
        if (! Schema::hasIndex('semester_curricula', 'semester_curricula_department_id_index')) {
            Schema::table('semester_curricula', fn (Blueprint $table) => $table->index('department_id', 'semester_curricula_department_id_index'));
        }
        if (Schema::hasIndex('semester_curricula', 'curriculum_version_unique')) {
            Schema::table('semester_curricula', fn (Blueprint $table) => $table->dropUnique('curriculum_version_unique'));
        }
        if (! Schema::hasIndex('semester_curricula', 'program_curriculum_version_unique')) {
            Schema::table('semester_curricula', fn (Blueprint $table) => $table->unique(['program_id', 'semester', 'version'], 'program_curriculum_version_unique'));
        }

        if (! Schema::hasColumn('students', 'program_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('program_id')->nullable()->after('department_id')->constrained('academic_programs')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('student_semester_enrollments', 'program_id')) {
            Schema::table('student_semester_enrollments', function (Blueprint $table) {
                $table->foreignId('program_id')->nullable()->after('department_id')->constrained('academic_programs')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('course_offerings', 'program_id')) {
            Schema::table('course_offerings', function (Blueprint $table) {
                $table->foreignId('program_id')->nullable()->after('department_id')->constrained('academic_programs')->restrictOnDelete();
            });
        }
        if (! Schema::hasIndex('course_offerings', 'course_offerings_academic_term_id_index')) {
            Schema::table('course_offerings', fn (Blueprint $table) => $table->index('academic_term_id', 'course_offerings_academic_term_id_index'));
        }
        if (Schema::hasIndex('course_offerings', 'term_section_course_unique')) {
            Schema::table('course_offerings', fn (Blueprint $table) => $table->dropUnique('term_section_course_unique'));
        }
        if (! Schema::hasIndex('course_offerings', 'term_program_section_course_unique')) {
            Schema::table('course_offerings', fn (Blueprint $table) => $table->unique(['academic_term_id', 'program_id', 'section_id', 'course_id'], 'term_program_section_course_unique'));
        }
    }

    public function down(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropUnique('term_program_section_course_unique');
            $table->unique(['academic_term_id', 'section_id', 'course_id'], 'term_section_course_unique');
            $table->dropIndex('course_offerings_academic_term_id_index');
            $table->dropConstrainedForeignId('program_id');
        });
        Schema::table('student_semester_enrollments', fn (Blueprint $table) => $table->dropConstrainedForeignId('program_id'));
        Schema::table('students', fn (Blueprint $table) => $table->dropConstrainedForeignId('program_id'));
        Schema::table('semester_curricula', function (Blueprint $table) {
            $table->dropUnique('program_curriculum_version_unique');
            $table->unique(['department_id', 'semester', 'version'], 'curriculum_version_unique');
            $table->dropIndex('semester_curricula_department_id_index');
            $table->dropConstrainedForeignId('program_id');
        });
        Schema::dropIfExists('academic_programs');
    }
};
