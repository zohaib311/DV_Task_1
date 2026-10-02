@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Teacher</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('addTeacher') }}" method="POST" enctype="multipart/form-data" data-teacher-form>
                @csrf

                <div class="row">

                    <div class="col-12 mb-3">
                        <label for="user_id" class="form-label">Login Account <small class="text-muted">(Optional)</small></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Create profile without portal access</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name</label>

                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter teacher name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>

                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter teacher email">

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>

                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX">

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="course" class="form-label">Academic specialization <small class="text-muted">(Optional)</small></label>

                        <input type="text" name="course" id="course" value="{{ old('course') }}"
                            class="form-control @error('course') is-invalid @enderror" placeholder="e.g. Computer Science">

                        @error('course')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label">
                            Teacher Image
                        </label>

                        <input type="file" name="image" id="image"
                            class="form-control @error('image') is-invalid @enderror">

                        @error('image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Teacher
                        </button>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="create_portal_account" value="1" id="create_portal_account" @checked(old('create_portal_account', !old('user_id')))><label class="form-check-label fw-semibold" for="create_portal_account">Create Teacher Panel account</label></div>
                            <small class="text-muted d-block mt-1">Course teaching assignments are made later in Prepare Semester Classes.</small>
                            <div class="row g-3 mt-1" data-account-passwords><div class="col-md-6"><label class="form-label" for="account_password">Panel password</label><input class="form-control @error('account_password') is-invalid @enderror" type="password" name="account_password" id="account_password" autocomplete="new-password"></div><div class="col-md-6"><label class="form-label" for="account_password_confirmation">Confirm password</label><input class="form-control" type="password" name="account_password_confirmation" id="account_password_confirmation" autocomplete="new-password"></div></div>
                            @error('account_password')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                </div>
            </form>

        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/student/student-account-flow.js') }}"></script>
@endsection
