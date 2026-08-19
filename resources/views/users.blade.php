@extends('welcome')

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Users List</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Class</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <th scope="row">
                                            {{ $user->id }}
                                        </th>
                                        <td class="fw-semibold">
                                            {{ $user->name }}
                                        </td>
                                        <td>
                                            {{ $user->email }}
                                        </td>
                                        <td>
                                            {{ $user->phone }}
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $user->class }}
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
    }

    .table th,
    .table td {
        padding: 12px;
    }
</style>
