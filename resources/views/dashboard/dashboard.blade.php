@extends('welcome')

@section('title', 'Dashboard - My Form Task')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endsection

@section('content')
    <div class="container-fluid py-3">

        {{-- Welcome Hero Banner --}}
        <div class="dashboard-hero-banner">
            <h2>Welcome back, {{ auth()->user()->name ?? 'User' }}! 👋</h2>
            <p>Here is an overview of your academic management dashboard statistics and quick actions.</p>
        </div>

        {{-- Quick Actions Bar --}}
        <div class="quick-actions-card">
            <h5><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h5>
            <div class="action-buttons-grid">
                <a href="{{ route('addStudentForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-person-plus-fill"></i> Add Student
                </a>
                <a href="{{ route('addTeacherForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-person-badge-fill"></i> Add Teacher
                </a>
                <a href="{{ route('addCourseForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-journal-plus"></i> Add Course
                </a>
                <a href="{{ route('addEventForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-calendar-plus-fill"></i> Add Event
                </a>
                <a href="{{ route('addDepartmentForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-building-add"></i> Add Department
                </a>
                <a href="{{ route('addSectionForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-plus-square-fill"></i> Add Section
                </a>
                <a href="{{ route('addResultForm') }}" class="action-shortcut-btn">
                    <i class="bi bi-award-fill"></i> Add Result
                </a>
            </div>
        </div>

        {{-- Statistics Cards Grid --}}
        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-speedometer2 me-2"></i>Overview Statistics</h5>

        <div class="stats-grid">

            {{-- 1. Total Students --}}
            <a href="{{ route('allStudents') }}" class="stat-card students">
                <div class="stat-card-info">
                    <h6>Total Students</h6>
                    <h3>124</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </a>

            {{-- 2. Total Teachers --}}
            <a href="{{ route('allTeachers') }}" class="stat-card teachers">
                <div class="stat-card-info">
                    <h6>Total Teachers</h6>
                    <h3>28</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </a>

            {{-- 3. Total Courses --}}
            <a href="{{ route('allCourses') }}" class="stat-card courses">
                <div class="stat-card-info">
                    <h6>Total Courses</h6>
                    <h3>16</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-book-fill"></i>
                </div>
            </a>

            {{-- 4. Total Events --}}
            <a href="{{ route('allEvents') }}" class="stat-card events">
                <div class="stat-card-info">
                    <h6>Total Events</h6>
                    <h3>9</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-calendar-event-fill"></i>
                </div>
            </a>

            {{-- 5. Total Departments --}}
            <a href="{{ route('allDepartments') }}" class="stat-card departments">
                <div class="stat-card-info">
                    <h6>Departments</h6>
                    <h3>6</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-building"></i>
                </div>
            </a>

            {{-- 6. Total Sections --}}
            <a href="{{ route('allSections') }}" class="stat-card sections">
                <div class="stat-card-info">
                    <h6>Total Sections</h6>
                    <h3>14</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-bar-chart-steps"></i>
                </div>
            </a>

            {{-- 7. Total Results --}}
            <a href="{{ route('allResults') }}" class="stat-card results">
                <div class="stat-card-info">
                    <h6>Published Results</h6>
                    <h3>98</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-journal-check"></i>
                </div>
            </a>

            {{-- 8. Total Users --}}
            <a href="{{ route('allUsers') }}" class="stat-card users">
                <div class="stat-card-info">
                    <h6>System Users</h6>
                    <h3>12</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </a>

        </div>

    </div>
@endsection
