@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Department</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('addDepartment') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="name" class="form-label">Department Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Enter department name (e.g. Computer Science, Mathematics)" required>

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Department
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
