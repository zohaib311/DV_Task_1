<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashbaordController extends Controller
{
    //
    function dashboardView()
    {
        if ($route = app(\App\Services\StudentPortal\StudentWorkspace::class)->landing(auth()->user())) {
            return redirect()->route($route);
        }
        if (auth()->user()->can('offerings.view-assigned') && ! auth()->user()->can('offerings.manage')) {
            return redirect()->route('teaching.dashboard');
        }

        $modules = [
            ['Students', 'students.manage', \App\Models\Student::class, 'allStudents', 'addStudentForm', 'students', 'bi-mortarboard-fill'],
            ['Teachers', 'teachers.manage', \App\Models\Teacher::class, 'allTeachers', 'addTeacherForm', 'teachers', 'bi-person-badge-fill'],
            ['Courses', 'courses.manage', \App\Models\Course\Course::class, 'allCourses', 'addCourseForm', 'courses', 'bi-book-fill'],
            ['Events', 'events.manage', \App\Models\Event\Event::class, 'allEvents', 'addEventForm', 'events', 'bi-calendar-event-fill'],
            ['Departments', 'departments.manage', \App\Models\Department\Department::class, 'allDepartments', 'addDepartmentForm', 'departments', 'bi-building'],
            ['Sections', 'sections.manage', \App\Models\Section\Section::class, 'allSections', 'addSectionForm', 'sections', 'bi-bar-chart-steps'],
            ['Published Results', 'results.view-all', \App\Models\Result\SemesterResult::class, 'allResults', null, 'results', 'bi-journal-check'],
            ['System Users', 'users.manage', \App\Models\User::class, 'allUsers', null, 'users', 'bi-people-fill'],
        ];
        $statistics = collect($modules)->filter(fn ($module) => auth()->user()->can($module[1]))
            ->map(function ($module) {
                [$label, $permission, $model, $route, $createRoute, $style, $icon] = $module;
                $query = $model::query();
                if ($model === \App\Models\Result\SemesterResult::class) {
                    $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail']);
                }
                $count = $query->count();

                return compact('label', 'route', 'createRoute', 'style', 'icon', 'count');
            })->values();

        return view('dashboard.dashboard', compact('statistics'));
    }
}
