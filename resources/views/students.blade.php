@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">
                    <h4 class="mb-0">Students List</h4>
                    <div class=" add__user__btn">
                        <a href="{{ route('addUsersForm') }}" class="   nav-link  ">

                            <span>Add Student</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Class</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($students as $student)
                                    <tr>
                                        <th scope="row">
                                            {{ $student->id }}
                                        </th>

                                        <td>
                                            <img src="{{ asset('storage/images/' . $student->image) }}"
                                                alt="{{ $student->name }}" class="user-image">
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $student->name }}
                                        </td>

                                        <td>
                                            {{ $student->email }}
                                        </td>

                                        <td>
                                            {{ $student->phone }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $student->class }}
                                            </span>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection
