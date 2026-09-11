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

                    <h2>Update Section</h2>

                </div>

            </div>

            @if (session('success'))
                <div class="alert alert-success" id="success-message">

                    <i class="bi bi-check-circle me-2"></i>

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

            <form action="{{ route('updateSection', $section->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label for="name" class="form-label">
                            Section Name
                        </label>

                        <input type="text" name="name" id="name" value="{{ old('name', $section->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter section name"
                            required>

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="col-md-6 mb-3">

                        <label for="department" class="form-label">
                            Department
                        </label>

                        <input type="text" name="department" id="department"
                            value="{{ old('department', $section->department) }}"
                            class="form-control @error('department') is-invalid @enderror" placeholder="Enter department"
                            required>

                        @error('department')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="col-12">

                        <div class="form__actions">

                            <a href="{{ route('allSections') }}" class="cancel__btn">

                                <i class="bi bi-arrow-left me-1"></i>

                                Back

                            </a>

                            <button type="submit" class="update__btn">

                                <i class="bi bi-check2-circle me-1"></i>

                                Update Section

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
