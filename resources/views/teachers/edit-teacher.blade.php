@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">


            <div class="edit__header">
                <div>
                    <h2>Update Student</h2>
                    <p>Edit student information and update the profile.</p>
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


            <form action="{{ route('updateTeacher', $teacher->id) }}" method="POST" enctype="multipart/form-data" data-teacher-form>

                @csrf
                @method('PUT')

                <div class="row g-4">

                    <div class="col-12">
                        <label for="user_id" class="form-label">Login Account <small class="text-muted">(Optional)</small></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">No portal account linked</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id', $teacher->user_id) == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">A linked account will receive Teacher Panel access in Phase 9C.</small>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">

                        <label for="name" class="form-label">
                            Name
                        </label>

                        <input type="text" name="name" id="name" value="{{ old('name', $teacher->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter teacher name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="col-md-6">

                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input type="email" name="email" id="email" value="{{ old('email', $teacher->email) }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter teacher email">

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-6">

                        <label for="class" class="form-label">
                            Academic specialization
                        </label>

                        <input type="text" name="course" id="course" value="{{ old('course', $teacher->course) }}"
                            class="form-control @error('course') is-invalid @enderror" placeholder="e.g. Computer Science">

                        @error('course')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="col-md-6">

                        <label for="phone" class="form-label">
                            Phone
                        </label>

                        <input type="text" name="phone" id="phone" value="{{ old('phone', $teacher->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX">

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
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
                                        <h6>Current Image</h6>
                                        <small>Your current profile image</small>
                                    </div>
                                </div>

                                <div class="current__image__preview">
                                    <img src="{{ asset('storage/images/' . $teacher->image) }}" alt="{{ $teacher->name }}"
                                        class="user-image">
                                </div>

                            </div>


                            <div class="change__image__box">

                                <div class="image__section__title">
                                    <div class="image__section__icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>

                                    <div>
                                        <h6>Change Image</h6>
                                        <small>Upload a new profile image</small>
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

                    </div>


                    <div class="col-12">

                        <div class="form__actions">

                            <a href="{{ route('allTeachers') }}" class="cancel__btn">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back
                            </a>

                            <button type="submit" class="update__btn">
                                <i class="bi bi-check2-circle me-1"></i>
                                Update Teacher
                            </button>

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

@section('scripts')
    <script src="{{ asset('js/student/student-account-flow.js') }}"></script>
@endsection
