<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// // Route::view('/show', 'allUsers')->name('allUsers');

// Route::view('/add', 'add-user')->name('addUsersForm');
// Route::post('/add', [StudentController::class, 'addUser'])->name('addUser');
// Route::get('/login', [AuthController::class, 'login'])->name('login');
// Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
// Route::get('/show', [StudentController::class, 'allUsers'])->name('allUsers');


Route::controller(AuthController::class)
    ->middleware('guest')
    ->group(function () {

        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'loginSubmit')->name('login.submit');

        Route::get('/signup', 'signup')->name('signup');
        Route::post('/signup', 'signupSubmit')->name('signup.submit');
    });


Route::controller(StudentController::class)
    ->middleware('auth')
    ->group(function () {

        Route::get('/add', 'create')->name('addUsersForm');

        Route::post('/add', 'addUser')->name('addUser');

        Route::get('/show', 'allUsers')->name('allUsers');
    });
