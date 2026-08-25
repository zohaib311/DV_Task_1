@extends('welcome')

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

<style>
    .table__content {
        max-width: 900px;
        margin: 0 auto;
    }

    .card {
        border-radius: 10px;
        overflow: hidden;
    }

    .card-header {
        padding: 15px 20px;
        display: flex;
        align-content: center;
        align-items: center;
        align-items: center;
        justify-content: space-between
    }

    .table th,
    .table td {
        padding: 12px;
    }

    .user-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #0d6efd;
    }

    .add__user__btn a {
        padding: 12px;
        border: 2px solid #f6ff00;
        border-radius: 30px;
        border-radius: 30px;
        background-color: #504af9;
        color: #0e0e0e padding: 12px 15px !important;
        display: flex !important;
        align-items: center;
        gap: 12px;
        transition: 0.3s;
        font-weight: 500;

    }

    .add__user__btn a:hover {
        padding: 12px;
        background-color: #0d06df;
    }
</style>
