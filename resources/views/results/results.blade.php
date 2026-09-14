@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
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
                                    <th>Percentage</th>
                                    <th>GPA</th>
                                    <th>CGPA</th>
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
                                        <td>{{ $result->percentage }}%</td>
                                        <td>{{ $result->gpa }}</td>
                                        <td>{{ $result->cgpa }}</td>
                                        <td><span class="badge bg-secondary">{{ $result->grade }}</span></td>
                                        <td>
                                            <span
                                                class="badge {{ $result->status == 'Pass' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $result->status }}
                                            </span>
                                        </td>
                                        {{-- <td>
                                            <div class="action__buttons">
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
                                        </td> --}}
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

    {{-- @foreach ($results as $result)
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
    @endforeach --}}
@endsection
