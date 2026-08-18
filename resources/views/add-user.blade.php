@extends('welcome')

@section('content')
    <div class="container py-5">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New User</h2>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('addUser') }}" method="POST">
                @csrf

                {{-- Name --}}
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


                {{-- Email --}}
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


                {{-- Phone --}}
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>

                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX">

                    @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Class --}}
                <div class="mb-3">
                    <label for="class" class="form-label">Class</label>

                    <input type="text" name="class" id="class" value="{{ old('class') }}"
                        class="form-control @error('class') is-invalid @enderror" placeholder="Enter your class">

                    @error('class')
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


<style>
    .add__form {
        max-width: 500px;
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .add__form h2 {
        font-weight: 600;
    }
</style>
