<?php

namespace Database\Seeders;

use App\Models\Academic\CourseOffering;
use App\Models\Lms\CourseAnnouncement;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LmsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds sample LMS materials, announcements, and an online MCQ quiz
     * for active course offerings without modifying or removing existing data.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Ensure permissions are cached and assigned
            $lmsManage = Permission::findOrCreate('lms.manage-assigned', 'web');
            $lmsView = Permission::findOrCreate('student.lms.view-own', 'web');

            $teacherRole = Role::where('name', 'Teacher')->where('guard_name', 'web')->first();
            if ($teacherRole && ! $teacherRole->hasPermissionTo($lmsManage)) {
                $teacherRole->givePermissionTo($lmsManage);
            }

            $demoTeacherRole = Role::where('name', 'Demo Teacher (9C)')->where('guard_name', 'web')->first();
            if ($demoTeacherRole && ! $demoTeacherRole->hasPermissionTo($lmsManage)) {
                $demoTeacherRole->givePermissionTo($lmsManage);
            }

            $studentRole = Role::where('name', 'Student')->where('guard_name', 'web')->first();
            if ($studentRole && ! $studentRole->hasPermissionTo($lmsView)) {
                $studentRole->givePermissionTo($lmsView);
            }

            // 2. Find active course offerings
            $offerings = CourseOffering::where('status', 'active')->get();
            if ($offerings->isEmpty()) {
                $offerings = CourseOffering::all();
            }

            if ($offerings->isEmpty()) {
                $this->command?->warn('No course offerings found to attach LMS demo data to.');
                return;
            }

            // Prepare a sample file in storage
            $sampleDir = 'course_materials';
            Storage::disk('public')->makeDirectory($sampleDir);
            $sampleFilePath = $sampleDir . '/lecture_01_sample_slides.txt';
            if (! Storage::disk('public')->exists($sampleFilePath)) {
                Storage::disk('public')->put($sampleFilePath, "Course Material Sample File\nTitle: Introduction & Fundamentals\nSemester Course Content Reference.");
            }

            $adminUser = User::where('email', 'admin@gmail.com')->first()
                ?? User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first()
                ?? User::first();

            $targetOfferingIds = collect([1, 2, 3, 11, 12, 13, 26, 27, 28])
                ->merge(CourseOffering::pluck('id'))
                ->unique();

            $targetOfferings = CourseOffering::whereIn('id', $targetOfferingIds)->get();

            foreach ($targetOfferings as $offering) {
                $teacherUser = $offering->teachers->first()?->user ?? $adminUser;

                // A. Materials
                if (! CourseMaterial::where('course_offering_id', $offering->id)->exists()) {
                    CourseMaterial::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'week_number' => 1,
                        'title' => 'Lecture 01 - Course Overview & Foundations',
                        'description' => 'Orientation lecture notes and basic introductory concepts.',
                        'file_path' => $sampleFilePath,
                        'file_name' => 'lecture_01_sample_slides.txt',
                        'file_size' => 1024,
                        'file_type' => 'document',
                        'is_published' => true,
                    ]);

                    CourseMaterial::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'week_number' => 2,
                        'title' => 'Lecture 02 - Core Principles & Methodology',
                        'description' => 'In-depth breakdown of standard algorithms and architectural patterns.',
                        'file_path' => $sampleFilePath,
                        'file_name' => 'lecture_02_principles.txt',
                        'file_size' => 2048,
                        'file_type' => 'document',
                        'is_published' => true,
                    ]);

                    CourseMaterial::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'week_number' => 3,
                        'title' => 'Official Reference Documentation',
                        'description' => 'Comprehensive external documentation and development guide.',
                        'external_url' => 'https://laravel.com/docs',
                        'file_type' => 'external_link',
                        'is_published' => true,
                    ]);
                }

                // B. Announcements
                if (! CourseAnnouncement::where('course_offering_id', $offering->id)->exists()) {
                    CourseAnnouncement::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'title' => 'Welcome to ' . ($offering->course_name ?? 'the course') . '!',
                        'content' => 'Welcome all students! Please download the Week 1 and Week 2 materials from the LMS portal and check the timetable for lecture schedules.',
                        'priority' => 'normal',
                    ]);

                    CourseAnnouncement::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'title' => '📢 Quiz 1 Announcement — Read Carefully',
                        'content' => 'Quiz 1 has been scheduled and published. It is a 10-minute online timed MCQ quiz. Once started, the timer cannot be paused. Make sure to complete before the deadline.',
                        'priority' => 'urgent',
                    ]);
                }

                // C. Online Timed Quiz
                if (! Quiz::where('course_offering_id', $offering->id)->exists()) {
                    $quiz = Quiz::create([
                        'course_offering_id' => $offering->id,
                        'created_by' => $teacherUser->id,
                        'title' => 'Quiz 1 — Fundamentals & Concepts',
                        'description' => '10-minute timed quiz covering introductory course concepts. 3 MCQs.',
                        'duration_minutes' => 10,
                        'starts_at' => now()->subDay(),
                        'ends_at' => now()->addDays(7),
                        'total_points' => 15,
                        'is_published' => true,
                    ]);

                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => 'Which data structure follows the First-In, First-Out (FIFO) principle?',
                        'option_a' => 'Stack',
                        'option_b' => 'Queue',
                        'option_c' => 'Tree',
                        'option_d' => 'Graph',
                        'correct_option' => 'b',
                        'points' => 5,
                    ]);

                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => 'What is the standard time complexity of accessing an array element by index?',
                        'option_a' => 'O(1)',
                        'option_b' => 'O(n)',
                        'option_c' => 'O(log n)',
                        'option_d' => 'O(n^2)',
                        'correct_option' => 'a',
                        'points' => 5,
                    ]);

                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => 'In database management, what does the "A" in ACID transaction properties stand for?',
                        'option_a' => 'Availability',
                        'option_b' => 'Accuracy',
                        'option_c' => 'Atomicity',
                        'option_d' => 'Authentication',
                        'correct_option' => 'c',
                        'points' => 5,
                    ]);
                }
            }
        });
    }
}
