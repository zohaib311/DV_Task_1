@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">

                    <h4 class="mb-0">Users List</h4>

                    @if (session('success'))
                        <div class="alert alert-success" id="success-message">
                            {{ session('success') }}
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

                    <div class="add__user__btn">
                        <a href="{{ route('addUserForm') }}" class="nav-link">
                            <span>Add User</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>phone</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($users as $user)
                                    <tr>

                                        <th>
                                            {{ $user->id }}
                                        </th>

                                        <td>
                                            <img src="{{ asset('storage/images/' . $user->image) }}"
                                                alt="{{ $user->name }}" class="user-image">
                                        </td>

                                        <td class="">
                                            {{ $user->name }}
                                        </td>

                                        <td>
                                            <span class="">
                                                {{ $user->email }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="">
                                                {{ $user->phone }}
                                            </span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
