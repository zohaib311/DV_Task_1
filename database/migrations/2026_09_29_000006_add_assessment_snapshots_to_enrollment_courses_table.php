<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollment_courses', function (Blueprint $table) {
            $table->unsignedInteger('attendance_marks')->default(10)->after('total_marks');
            $table->unsignedInteger('mid_marks')->default(30)->after('attendance_marks');
            $table->unsignedInteger('final_marks')->default(60)->after('mid_marks');
        });

        DB::table('student_enrollment_courses')->orderBy('id')->get(['id', 'total_marks'])->each(function ($course) {
            $attendanceMarks = (int) round($course->total_marks * 0.10);
            $midMarks = (int) round($course->total_marks * 0.30);

            DB::table('student_enrollment_courses')->where('id', $course->id)->update([
                'attendance_marks' => $attendanceMarks,
                'mid_marks' => $midMarks,
                'final_marks' => (int) $course->total_marks - $attendanceMarks - $midMarks,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollment_courses', function (Blueprint $table) {
            $table->dropColumn(['attendance_marks', 'mid_marks', 'final_marks']);
        });
    }
};
