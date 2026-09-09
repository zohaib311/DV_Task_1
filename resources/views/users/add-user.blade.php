@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Course</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('addCourse') }}" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">
                            Course Code
                        </label>

                        <input type="text" name="code" id="code" value="{{ old('code') }}"
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

                        <input type="text" name="name" id="name" value="{{ old('name') }}"
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
                            placeholder="Enter course description (maximum 100 words)">{{ old('description') }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Course
                        </button>
                    </div>

                </div>
            </form>

        </div>

    </div>
@endsection
