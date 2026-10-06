<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\Academic\AcademicTermController;
use App\Http\Controllers\Academic\CurriculumController;
use App\Http\Controllers\Academic\CourseOfferingController;
use App\Http\Controllers\Academic\ProgramController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Dashboard\DashbaordController;
use App\Http\Controllers\Department\DepartmentController;
use App\Http\Controllers\Enrollment\StudentSemesterEnrollmentController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Result\ResultController;
use App\Http\Controllers\Section\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__.'/teaching.php';
require __DIR__.'/attendance.php';
require __DIR__.'/assessments.php';
require __DIR__.'/result-moderation.php';
require __DIR__.'/student-portal.php';
require __DIR__.'/notifications.php';
require __DIR__.'/reports.php';

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(app(\App\Services\StudentPortal\StudentWorkspace::class)->landing(Auth::user()) ?? 'dashboardView');
    }
    return redirect()->route('signup');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::controller(AuthController::class)->middleware('guest')->group(function () {

    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->name('login.submit');

    Route::get('/signup', 'signup')->name('signup');
    Route::post('/signup', 'signupSubmit')->name('signup.submit');
});


Route::prefix('dashboard')->controller(DashbaordController::class)->middleware(['auth', 'permission:dashboard.view'])->group(function () {

    // Route::get('/add', 'create')->name('addStudentForm');

    // Route::post('/add', 'addStudent')->name('addStudent');

    // Route::get('/edit/{id}', 'editStudentForm')->name('editStudentForm');

    // Route::put('/update/{id}', 'updateStudent')->name('updateStudent');

    // Route::delete('/delete/{id}', 'deleteStudent')->name('deleteStudent');

    Route::get('/dashboard', 'dashboardView')->name('dashboardView');
});

Route::prefix('student')->controller(StudentController::class)->middleware(['auth', 'permission:students.manage'])->group(function () {

    Route::get('/add', 'create')->name('addStudentForm');

    Route::post('/add', 'addStudent')->name('addStudent');

    Route::get('/edit/{id}', 'editStudentForm')->name('editStudentForm');

    Route::put('/update/{id}', 'updateStudent')->name('updateStudent');

    Route::delete('/delete/{id}', 'deleteStudent')->name('deleteStudent');

    Route::get('/show', 'allStudents')->name('allStudents');
});

Route::prefix('enrollment')->controller(StudentSemesterEnrollmentController::class)->middleware(['auth', 'permission:enrollments.view'])->group(function () {

    Route::get('/show', 'index')->name('allEnrollments');

    Route::get('/offerings', 'offerings')->middleware('permission:enrollments.create|enrollments.promote')->name('enrollment.offerings');

    Route::get('/add', 'create')->middleware('permission:enrollments.create')->name('addEnrollmentForm');

    Route::post('/add', 'store')->middleware('permission:enrollments.create')->name('addEnrollment');

    Route::get('/{enrollment}/promote', 'promote')->middleware('permission:enrollments.promote')->name('promoteEnrollmentForm');

    Route::post('/{enrollment}/promote', 'storePromotion')->middleware('permission:enrollments.promote')->name('promoteEnrollment');
});

Route::prefix('teacher')->controller(TeacherController::class)->middleware(['auth', 'permission:teachers.manage'])->group(function () {

    Route::get('/add', 'create')->name('addTeacherForm');

    Route::post('/add', 'addTeacher')->name('addTeacher');

    Route::get('/edit/{id}', 'editTeacherForm')->name('editTeacherForm');

    Route::put('/update/{id}', 'updateTeacher')->name('updateTeacher');

    Route::delete('/delete/{id}', 'deleteTeacher')->name('deleteTeacher');

    Route::get('/show', 'allTeachers')->name('allTeachers');
});

Route::prefix('course')->controller(CourseController::class)->middleware(['auth', 'permission:courses.manage'])->group(function () {

    Route::get('/add', 'create')->name('addCourseForm');

    Route::post('/add', 'addCourse')->name('addCourse');

    Route::get('/edit/{id}', 'editCourseForm')->name('editCourseForm');

    Route::put('/update/{id}', 'updateCourse')->name('updateCourse');

    Route::delete('/delete/{id}', 'deleteCourse')->name('deleteCourse');

    Route::get('/show', 'allCourses')->name('allCourses');
});

Route::prefix('event')->controller(EventController::class)->middleware(['auth', 'permission:events.manage'])->group(function () {

    Route::get('/add', 'eventForm')->name('addEventForm');

    Route::post('/add', 'addEvent')->name('addEvent');

    Route::get('/edit/{id}', 'editEventForm')->name('editEventForm');

    Route::put('/update/{id}', 'updateEvent')->name('updateEvent');

    Route::delete('/delete/{id}', 'deleteEvent')->name('deleteEvent');

    Route::get('/show', 'allEvents')->name('allEvents');
});


Route::prefix('section')->controller(SectionController::class)->middleware(['auth', 'permission:sections.manage'])->group(function () {

    Route::get('/add', 'create')->name('addSectionForm');

    Route::post('/add', 'addSection')->name('addSection');

    Route::get('/show', 'allSections')->name('allSections');

    Route::get('/edit/{id}', 'editSectionForm')->name('editSectionForm');

    Route::put('/update/{id}', 'updateSection')->name('updateSection');

    Route::delete('/delete/{id}', 'deleteSection')->name('deleteSection');
});

Route::prefix('result')->controller(ResultController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->middleware('permission:results.create')->name('addResultForm');

    Route::post('/add', 'addResult')->middleware('permission:results.create')->name('addResult');

    Route::get('/student/{student}/semester-enrollments', 'getStudentResultEnrollments')
        ->middleware('permission:results.create')
        ->name('result.student.enrollments');

    Route::get('/semester-enrollment/{enrollment}/data', 'getEnrollmentResultData')
        ->middleware('permission:results.create')
        ->name('result.enrollment.data');

    Route::get('/get-filter-options/{section}', 'getResultFilterOptions')
        ->middleware('permission:results.view-all')
        ->name('result.filter.options');

    Route::post('/semester-result', 'storeSemesterResult')->name('result.semester.store');

    Route::get('/semester-result/{semesterResult}/data', 'getSemesterResultData')
        ->name('result.semester.data');

    Route::get('/semester-result/{semesterResult}/sheet', 'getSemesterResultSheet')
        ->middleware('permission:results.view-all')
        ->name('result.semester.sheet');

    Route::get('/student/{student}/academic-history', 'getStudentAcademicHistory')
        ->middleware('permission:academic-history.view')
        ->name('result.student.history');

    Route::put('/semester-result/{semesterResult}', 'updateSemesterResult')
        ->name('result.semester.update');

    Route::get('/get-sections/{department_id}', 'getSectionsByDepartment')->middleware('permission:results.view-all')->name('getSectionsByDepartment');

    Route::get('/get-students/{section_id}', 'getStudentsBySection')->middleware('permission:results.view-all')->name('getStudentsBySection');

    Route::get('/show', 'allResults')->middleware('permission:results.view-all')->name('allResults');
});


Route::prefix('users')->controller(UserController::class)->middleware(['auth', 'permission:users.manage'])->group(function () {

    Route::get('/add', 'addUserForm')->name('addUserForm');

    Route::post('/add', 'addUser')->name('addUser');

    Route::get('/edit/{id}', 'editUserForm')->name('editUserForm');

    Route::put('/update/{id}', 'updateUser')->name('updateUser');

    Route::delete('/delete/{id}', 'deleteUser')->name('deleteUser');

    Route::get('/show', 'allUsers')->name('allUsers');
});

Route::prefix('department')->controller(DepartmentController::class)->middleware(['auth', 'permission:departments.manage'])->group(function () {

    Route::get('/add', 'create')->name('addDepartmentForm');

    Route::post('/add', 'addDepartment')->name('addDepartment');

    Route::get('/edit/{id}', 'editDepartmentForm')->name('editDepartmentForm');

    Route::put('/update/{id}', 'updateDepartment')->name('updateDepartment');

    Route::delete('/delete/{id}', 'deleteDepartment')->name('deleteDepartment');

    Route::get('/show', 'allDepartments')->name('allDepartments');
});

Route::prefix('user/profile')->controller(UserController::class)->middleware('auth')->group(function () {

    Route::get('/settings', 'userSettingForm')->name('profile.settings.form');

    Route::put('/update', 'updateProfile')->name('profile.settings.update');
});

Route::prefix('access-control')->controller(AccessControlController::class)->middleware(['auth', 'permission:roles.view'])->group(function () {
    Route::get('/', 'index')->name('access.index');
    Route::post('/roles', 'storeRole')->middleware('permission:roles.manage')->name('access.roles.store');
    Route::put('/roles/{role}', 'updateRole')->middleware('permission:permissions.assign')->name('access.roles.update');
    Route::post('/permissions', 'storePermission')->middleware('permission:permissions.assign')->name('access.permissions.store');
    Route::put('/users/{user}/roles', 'updateUserRoles')->middleware('permission:users.assign-role')->name('access.users.roles.update');
});

Route::prefix('academic')->name('academic.')->middleware('auth')->group(function () {
    Route::middleware('permission:curriculum.manage')->controller(ProgramController::class)->group(function () {
        Route::get('/programs', 'index')->name('programs.index');
        Route::post('/programs', 'store')->name('programs.store');
        Route::put('/programs/{program}', 'update')->name('programs.update');
    });
    Route::middleware('permission:terms.manage')->controller(AcademicTermController::class)->group(function () {
        Route::get('/terms', 'index')->name('terms.index');
        Route::post('/years', 'storeYear')->name('years.store');
        Route::post('/terms', 'store')->name('terms.store');
        Route::get('/terms/{term}/edit', 'edit')->name('terms.edit');
        Route::put('/terms/{term}', 'update')->name('terms.update');
    });
    Route::middleware('permission:curriculum.manage')->controller(CurriculumController::class)->group(function () {
        Route::get('/curricula', 'index')->name('curricula.index');
        Route::get('/curricula/create', 'create')->name('curricula.create');
        Route::post('/curricula', 'store')->name('curricula.store');
        Route::get('/curricula/{curriculum}/edit', 'edit')->name('curricula.edit');
        Route::put('/curricula/{curriculum}', 'update')->name('curricula.update');
        Route::post('/curricula/{curriculum}/approve', 'approve')->name('curricula.approve');
    });
    Route::middleware('permission:offerings.manage')->controller(CourseOfferingController::class)->group(function () {
        Route::get('/offerings', 'index')->name('offerings.index');
        Route::get('/offerings/create', 'create')->name('offerings.create');
        Route::post('/offerings', 'store')->name('offerings.store');
        Route::get('/offerings/{offering}/edit', 'edit')->name('offerings.edit');
        Route::put('/offerings/{offering}', 'update')->name('offerings.update');
    });
    Route::middleware('permission:offerings.manage')->controller(\App\Http\Controllers\Academic\SemesterTeachingSetupController::class)->group(function () {
        Route::get('/teaching-setup', 'create')->name('teaching-setup.create');
        Route::post('/teaching-setup', 'store')->name('teaching-setup.store');
    });
});
