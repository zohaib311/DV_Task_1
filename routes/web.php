<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('allStudents');
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


Route::prefix('student')->controller(StudentController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->name('addStudentForm');

    Route::post('/add', 'addStudent')->name('addStudent');

    Route::get('/show', 'allStudents')->name('allStudents');
});

Route::prefix('teacher')->controller(TeacherController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->name('addTeacherForm');

    Route::post('/add', 'addTeacher')->name('addTeacher');

    Route::get('/show', 'allTeachers')->name('allTeachers');
});

Route::prefix('course')->controller(CourseController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->name('addCourseForm');

    Route::post('/add', 'addCourse')->name('addCourse');

    Route::get('/show', 'allCourses')->name('allCourses');
});

Route::prefix('event')->controller(EventController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'eventForm')->name('addEventForm');

    Route::post('/add', 'addEvent')->name('addEvent');

    Route::get('/show', 'allEvents')->name('allEvents');
});

Route::prefix('users')->controller(UserController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'addUserForm')->name('addUserForm');

    Route::post('/add', 'addUser')->name('addUser');

    Route::get('/show', 'allUsers')->name('allUsers');
});

Route::prefix('user/profile')->controller(UserController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->name('profile.settings');

    Route::post('/add', 'addCourse')->name('addCourse');

    Route::get('/show', 'allCourses')->name('allCourses');
});
