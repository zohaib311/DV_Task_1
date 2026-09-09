@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">

                    <h4 class="mb-0">Courses List</h4>

                    <div class="add__user__btn">
                        <a href="{{ route('addCourseForm') }}" class="nav-link">
                            <span>Add Course</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($courses as $course)
                                    <tr>

                                        <th>
                                            {{ $course->id }}
                                        </th>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $course->code }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $course->name }}
                                        </td>

                                        <td>
                                            {{ $course->description }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            No courses found.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
