@extends('welcome')

@section('content')
    <div class="add__form">
        <h1>User Form</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ Route('addUser') }}" method="post">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" class="form-control" id="name">
            </div>
            <div class="mb-3">
                <label for="exampleInputEmail" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="exampleInputEmail">
            </div>
            <div class="mb-3">
                <label for="exampleInputPhone" class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" id="exampleInputphone">
            </div>

            <div class="mb-3">
                <label for="exampleInputclass" class="form-label">Class</label>
                <input type="text" name="class" class="form-control" id="exampleInputclass">
            </div>


            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
    </div>
@endsection


<style>
    .add__form {
        margin-left: 350px;
        width: 400px;
    }
</style>
