@extends('welcome')


@section('content')
    <div class="table__content">

        <div>
            <form action="search" method="get">
                <label for="search" class="form-label ">Search by Name</label>
                <input type="text" id="search" class="form-control" value="{{ @$search }}"
                    placeholder="Enter Name To Find" name="search">
            </form>

        </div>

        <div>
            @if (session('message'))
                <span class="green">{{ session('message') }}</span>
            @endif
            {{ session()->keep(['message']) }}
        </div>

        <div>

            <table class="table table-striped table-hover  table-bordered">

                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">class</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <th scope="row">{{ $user->id }}</th>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->class }}</td>



                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection


<style>
    .table__content {
        margin-top: 20px;
        max-width: 600px;
        margin-left: 240px
    }
</style>
