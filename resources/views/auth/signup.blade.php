@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}">
@endsection


@section('content')
    <div class="container add__form_cont ">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">SignUp User</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="#" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>

                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" placeholder="Enter your name">

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>

                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email">

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>

                    <input type="text" name="password" id="password" value="{{ old('password') }}"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Password">

                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">ConfirmPassword</label>

                    <input type="password" name="password_confirmation" id="password_confirmation"
                        value="{{ old('password_confirmation') }}"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        placeholder="Enter your Password Again">

                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Add User
                </button>

            </form>
        </div>

    </div>
@endsection
