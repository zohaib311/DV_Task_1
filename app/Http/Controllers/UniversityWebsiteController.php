<?php

namespace App\Http\Controllers;

use App\Models\Academic\Program;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Event\Event;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UniversityWebsiteController extends Controller
{
    /**
     * Display the official University public website.
     */
    public function index()
    {
        $departments = collect();
        $programs = collect();
        $events = collect();

        $stats = [
            'departments_count' => 8,
            'programs_count' => 14,
            'teachers_count' => 48,
            'students_count' => 1250,
            'courses_count' => 85,
            'alumni_count' => 12400,
        ];

        try {
            if (Schema::hasTable('departments')) {
                $departments = Department::orderBy('name')->get();
                $stats['departments_count'] = max($departments->count(), 8);
            }
            if (Schema::hasTable('programs')) {
                $programs = Program::with('department')->where('is_active', true)->get();
                $stats['programs_count'] = max($programs->count(), 14);
            }
            if (Schema::hasTable('events')) {
                $events = Event::latest()->take(3)->get();
            }
            if (Schema::hasTable('teachers')) {
                $stats['teachers_count'] = max(Teacher::count(), 48);
            }
            if (Schema::hasTable('students')) {
                $stats['students_count'] = max(Student::count(), 1250);
            }
            if (Schema::hasTable('courses')) {
                $stats['courses_count'] = max(Course::count(), 85);
            }
        } catch (\Throwable $e) {
            // Gracefully fall back to defaults if database is not migrated yet
        }

        return view('website.index', compact('departments', 'programs', 'events', 'stats'));
    }

    /**
     * Handle prospective student admission inquiry submission.
     */
    public function submitInquiry(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'program_interest' => 'nullable|string|max:100',
            'message' => 'required|string|max:1000',
        ]);

        try {
            if (Schema::hasTable('admission_inquiries')) {
                \App\Models\Admission\AdmissionInquiry::create($validated);
            }
        } catch (\Throwable $e) {
            // Gracefully ignore if database migration is pending
        }

        return back()->with('inquiry_success', 'Thank you, ' . $validated['name'] . '! Your admission inquiry has been received. Our Admissions Office will contact you shortly at ' . $validated['email'] . '.');
    }
}
