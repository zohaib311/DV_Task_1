@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">

                    <h4 class="mb-0">Teachers List</h4>

                    <div class="add__user__btn">
                        <a href="{{ route('addTeacherForm') }}" class="nav-link">
                            <span>Add Teacher</span>
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
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($teachers as $teacher)
                                    <tr>

                                        <th>
                                            {{ $teacher->id }}
                                        </th>

                                        <td>
                                            <img src="{{ asset('storage/images/' . $teacher->image) }}"
                                                alt="{{ $teacher->name }}" class="user-image">
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $teacher->name }}
                                        </td>

                                        <td>
                                            {{ $teacher->email }}
                                        </td>

                                        <td>
                                            {{ $teacher->phone }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $teacher->course }}
                                            </span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            No teachers found.
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
