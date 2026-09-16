@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/result/result-model.css') }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="table__content">
            <div class="card shadow-sm border-0">
                <div class="card-header flex-auto bg-primary text-white">
                    <h4 class="mb-0">Students Results</h4>
                    @if (session('success'))
                        <div class="alert alert-success mt-2 mb-0" id="success-message">
                            {{ session('success') }}
                        </div>
                    @endif

                    <script>
                        setTimeout(function() {
                            const message = document.getElementById('success-message');

                            if (message) {
                                message.style.transition = 'opacity 0.5s ease';
                                message.style.opacity = '0';

                                setTimeout(() => message.remove(), 500);
                            }
                        }, 2000);
                    </script>

                    <div class="add__user__btn">
                        <a href="{{ route('addResultForm') }}" class="nav-link">
                            <span>Add Result</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Section / Dept</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($results as $result)
                                    <tr>
                                        <th>{{ $result->id }}</th>
                                        <td class="fw-semibold">{{ $result->student->name ?? 'N/A' }}</td>
                                        <td>{{ $result->course->name ?? 'N/A' }}</td>
                                        <td>{{ $result->section->name ?? '' }} ({{ $result->section->department ?? '' }})
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $result->grade }}</span></td>
                                        <td>
                                            <span
                                                class="badge {{ $result->status == 'Pass' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $result->status }}
                                            </span>
                                        </td>
                                        <td>

                                            <div class="action__buttons">

                                                <button type="button" class="action__btn action__info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#infoResultModal{{ $result->id }}">
                                                    <i class="bi bi-info-circle "></i>
                                                </button>

                                                <a href="{{ route('editResultForm', $result->id) }}"
                                                    class="action__btn action__edit" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteResultModal{{ $result->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">No results found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($results as $result)
        <div class="modal fade" id="deleteResultModal{{ $result->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content delete__modal">
                    <div class="modal-body text-center">
                        <div class="delete__modal__icon"><i class="bi bi-trash3"></i></div>
                        <h4>Delete Result?</h4>
                        <p>Are you sure you want to delete this result for
                            <strong>{{ $result->student->name ?? 'Student' }}</strong>?
                        </p>
                        <div class="delete__modal__actions">
                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('deleteResult', $result->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete__confirm"><i class="bi bi-trash3 me-1"></i>
                                    Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($results as $result)
        <div class="modal fade result-detail-modal" id="infoResultModal{{ $result->id }}" tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    {{-- Modal Header --}}
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-person-fill fs-5"></i>
                            <h5 class="modal-title mb-0">Student Result Summary</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body">

                        {{-- 1. Student Profile Header (Top Section) --}}
                        <div class="student-profile-card">
                            <img src="{{ asset('storage/images/' . ($result->student->image ?? 'default-user.png')) }}"
                                alt="{{ $result->student->name ?? 'Student' }}" class="student-modal-avatar">

                            <div class="student-info-meta">
                                <h4>{{ $result->student->name ?? 'N/A' }}</h4>
                                <p><i class="bi bi-envelope me-1"></i> {{ $result->student->email ?? 'N/A' }}</p>

                                <div class="student-meta-pills">
                                    <span class="student-meta-pill">
                                        <i class="bi bi-telephone me-1"></i> {{ $result->student->phone ?? 'N/A' }}
                                    </span>
                                    @if (!empty($result->student->class))
                                        <span class="student-meta-pill">
                                            <i class="bi bi-building me-1"></i> Class: {{ $result->student->class }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 2. Result Key Metrics Grid --}}
                        <div class="result-metrics-grid">
                            <div class="metric-card percentage-card">
                                <label>Percentage</label>
                                <div class="metric-value">{{ $result->percentage }}%</div>
                            </div>

                            <div class="metric-card gpa-card">
                                <label>GPA</label>
                                <div class="metric-value">{{ $result->gpa }}</div>
                            </div>

                            <div class="metric-card cgpa-card">
                                <label>CGPA</label>
                                <div class="metric-value">{{ $result->cgpa }}</div>
                            </div>

                            <div class="metric-card grade-card">
                                <label>Grade</label>
                                <div class="metric-value">{{ $result->grade }}</div>
                            </div>
                        </div>

                        {{-- 3. Detailed Course & Academic Record --}}
                        <div class="result-details-box">
                            <table class="table result-details-table align-middle">
                                <tbody>
                                    <tr>
                                        <th>Result Record ID</th>
                                        <td class="fw-semibold">#{{ $result->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Course Title</th>
                                        <td>
                                            <span class="fw-semibold">{{ $result->course->name ?? 'N/A' }}</span>
                                            @if (!empty($result->course->code))
                                                <small class="text-muted">({{ $result->course->code }})</small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Section / Department</th>
                                        <td>
                                            {{ $result->section->name ?? 'N/A' }}
                                            @if ($result->section && !empty($result->section->department))
                                                <span class="text-muted">({{ $result->section->department }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Academic Status</th>
                                        <td>
                                            <span
                                                class="status-badge {{ strtolower($result->status) == 'pass' ? 'pass' : 'fail' }}">
                                                <i
                                                    class="bi {{ strtolower($result->status) == 'pass' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                                {{ $result->status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer bg-white border-0 justify-content-center py-3">
                        <button type="button" class="btn btn-secondary px-4 rounded-3 fw-semibold"
                            data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endsection
