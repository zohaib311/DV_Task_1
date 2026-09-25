@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/result/add-result.css') }}">
@endsection

@section('content')
    <div class="add-result-container">

        <div class="page-header-card">
            <div>
                <h3 class="page-header-title">Student Results</h3>
                <p class="page-header-sub">Select department and section to view students and record results.</p>
            </div>
            <a href="{{ route('allResults') }}" class="btn btn-outline-secondary rounded-pill px-3">
                View All Results <i class="bi bi-arrow-right mx-1"></i>
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="filter-card">
            <h5 class="filter-card-title">
                <i class="bi bi-funnel-fill"></i>Select Department & Section
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="department_id" class="form-label">1. Department</label>
                    <select id="department_id" class="form-select">
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="section_id" class="form-label">2. Section</label>
                    <select id="section_id" class="form-select" disabled>
                        <option value="">-- Select Department First --</option>
                    </select>
                </div>
            </div>


            <div class="students-card mt-3">
                <h5 class="students-card-title">
                    <i class="bi bi-people-fill"></i>Students List
                </h5>

                <div id="initial_state_msg" class="alert alert-light text-center border py-4 mb-0 rounded-3">
                    <i class="bi bi-arrow-up-circle fs-3 text-primary d-block mb-2"></i>
                    <span class="fw-semibold text-secondary">Select Department First, Then Section, To View Students.</span>
                </div>

                <div id="loading_spinner" class="spinner-container">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0 fw-medium">Loading Students...</p>
                </div>

                <div id="students_table_wrapper" style="display: none;">
                    <div class="students-table-responsive">
                        <table class="students-table table align-middle">
                            <thead>
                                <tr>
                                    <th width="60">#</th>
                                    <th width="140">Student ID</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th width="160" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="students_table_body">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="no_students_msg" class="alert alert-warning border-0 text-center py-4 mb-0 rounded-3"
                    style="display: none;">
                    <i class="bi bi-exclamation-triangle fs-4 me-2"></i> There are no Students in This Section.
                </div>
            </div>
        </div>

    </div>

    <x-result.add-result-modal :courses="$courses" />

@endsection

@section('scripts')
    <script src="{{ asset('js/result/add-result-filter.js') }}"></script>
@endsection
