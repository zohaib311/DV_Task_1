@extends('welcome')

@section('title', 'Login - My Form Task')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

    <div class="login__page">

        <div class="login__container">

            <div class="login__image">

                <div class="login__image__overlay">

                    <div class="login__image__content">
                        <div class="login__image__badge">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Secure Dashboard Portal</span>
                        </div>

                        <h1>Welcome Back!</h1>

                        <p>
                            Log in to your account and continue managing your students, teachers, courses and events.
                        </p>
                    </div>

                </div>

            </div>


            <div class="login__form__section">

                <div class="login__form">

                    <div class="login__heading">
                        <h2>User Login</h2>
                        <p>Please enter your credentials to access your account.</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center gap-2" id="success-message">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" id="success-message">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <script>
                        setTimeout(function() {
                            const message = document.getElementById('success-message');

                            if (message) {
                                message.style.transition = 'opacity 0.5s ease';
                                message.style.opacity = '0';

                                setTimeout(() => message.remove(), 500);
                            }
                        }, 2000);
                    </script>

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com"
                                required autofocus>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password" required>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="login__button">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Login
                        </button>

                        <div class="login__signup">
                            <span>Don't have an account?</span>
                            <a href="{{ route('signup') }}">Create an account</a>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
