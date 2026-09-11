@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

    <div class="login__page">

        <div class="login__container">

            <div class="login__image">

                <div class="login__image__overlay">

                    <div class="login__image__content">
                        <h1>Welcome Back!</h1>

                        <p>
                            Login to your account and continue
                            managing your dashboard.
                        </p>
                    </div>

                </div>

            </div>


            <div class="login__form__section">

                <div class="login__form">

                    <div class="login__heading">
                        <h2>User Login</h2>

                        <p>
                            Welcome back! Please enter your details.
                        </p>
                    </div>


                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif


                    @if ($errors->any())
                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>
                    @endif


                    <form action="{{ route('login.submit') }}" method="POST">

                        @csrf


                        <div class="mb-3">

                            <label for="email" class="form-label">
                                Email
                            </label>

                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email"
                                required>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="mb-4">

                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password" required>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <button type="submit" class="btn btn-primary login__button">
                            Login
                        </button>


                        <div class="login__signup">

                            <span>
                                Don't have an account?
                            </span>

                            <a href="{{ route('signup') }}">
                                Signup here
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
