<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('allUsers');
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


Route::controller(StudentController::class)->middleware('auth')->group(function () {

    Route::get('/add', 'create')->name('addUsersForm');

    Route::post('/add', 'addUser')->name('addUser');

    Route::get('/show', 'allUsers')->name('allUsers');

    // Route::get('/add/teacher', 'allUsers')->name('allUsers');
});
