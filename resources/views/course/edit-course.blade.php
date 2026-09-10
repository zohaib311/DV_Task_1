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
                    <h2>Update Course</h2>
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


            <form action="{{ route('updateCourse', $course->id) }}" method="POST" enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="row g-4">


                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">
                            Course Code
                        </label>

                        <input type="text" name="code" id="code" value="{{ old('code', $course->code) }}"
                            class="form-control @error('code') is-invalid @enderror" placeholder="e.g. CS101">

                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            Course Name
                        </label>

                        <input type="text" name="name" id="name" value="{{ old('name', $course->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter course name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">
                            Course Description
                        </label>

                        <textarea name="description" id="description" rows="5"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Enter course description (maximum 100 words)">{{ old('description', $course->description) }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>



                    <div class="col-12">

                        <div class="form__actions">

                            <a href="{{ route('allStudents') }}" class="cancel__btn">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back
                            </a>

                            <button type="submit" class="update__btn">
                                <i class="bi bi-check2-circle me-1"></i>
                                Update Course
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
