@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">

            <div class="edit__header mb-4">
                <div>
                    <h2>Profile Settings</h2>
                    <p class="text-muted small mb-0">Update your account credentials and personal information</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success" id="success-message">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Please fix the following errors:
                    </div>

                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email">

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Password <small class="text-muted">(Leave blank to keep current)</small>
                        </label>

                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password (optional)">

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX">

                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="image__section">

                            <div class="current__image__box">
                                <div class="image__section__title">
                                    <div class="image__section__icon">
                                        <i class="bi bi-image"></i>
                                    </div>

                                    <div>
                                        <h6>Current Avatar</h6>
                                        <small>Your current profile picture</small>
                                    </div>
                                </div>

                                <div class="current__image__preview">
                                    <img src="{{ asset('storage/images/' . $user->image) }}" alt="{{ $user->name }}"
                                        class="user-image">
                                </div>
                            </div>

                            <div class="change__image__box">
                                <div class="image__section__title">
                                    <div class="image__section__icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>

                                    <div>
                                        <h6>Change Avatar</h6>
                                        <small>Upload a new profile picture</small>
                                    </div>
                                </div>

                                <label for="image" class="upload__box">
                                    <div class="upload__icon">
                                        <i class="bi bi-upload"></i>
                                    </div>

                                    <div class="upload__text">
                                        <span>Choose a new image</span>
                                        <small>JPG, JPEG or PNG • Max 2MB</small>
                                    </div>
                                </label>

                                <input type="file" name="image" id="image" class="d-none">

                                @error('image')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                        <div class="col-12 mt-4">
                            <div class="form__actions">
                                <a href="{{ url('/') }}" class="cancel__btn">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back
                                </a>

                                <button type="submit" class="update__btn">
                                    <i class="bi bi-check2-circle me-1"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>

    </div>

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
@endsection
