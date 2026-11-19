@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}?v={{ filemtime(public_path('css/students.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-modal.css') }}?v={{ filemtime(public_path('css/teacher/teacher-modal.css')) }}">
@endsection

@section('content')
<div class="container py-4">

    <!-- Top Breadcrumb / Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('allTeachers') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 rounded-3">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Teachers List</span>
            </a>
            <span class="text-muted">/</span>
            <span class="fw-bold text-dark">{{ $teacher->name }}</span>
            <span class="badge bg-primary text-white ms-1">Teacher #{{ $teacher->id }}</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('editTeacherForm', $teacher->id) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 rounded-3 shadow-sm">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Profile</span>
            </a>
            <button type="button" class="btn btn-light border btn-sm d-inline-flex align-items-center gap-1 rounded-3" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>Print Profile</span>
            </button>
        </div>
    </div>

    <!-- Main Card Body -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header py-3 px-4 bg-primary text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-workspace fs-5"></i>
                <h5 class="mb-0 fw-bold">Faculty Member Profile &amp; Academic Record</h5>
            </div>
            <span class="badge bg-white text-primary fw-bold px-3 py-1.5 rounded-pill">
                {{ $teacher->course ?? 'Faculty Member' }}
            </span>
        </div>

        <div class="card-body p-4 bg-light">
            @include('teachers.partials.teacher-detail-content', ['teacher' => $teacher, 'isFullPage' => true])
        </div>
    </div>

</div>
@endsection
