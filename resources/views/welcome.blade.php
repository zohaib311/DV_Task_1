<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Form Task')</title>


    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    @yield('styles')

    <style>
        body {
            /* height: 100%; */
            /* overflow: hidden; */
        }

        .sidebar {
            width: 250px;
            height: calc(100vh - 64px);
            position: fixed;
            top: 64px;
            left: 0;
            z-index: 1040;
            overflow-y: auto;
        }

        .main-content {
            margin-left: @auth 250px
            @else
                0
            @endauth
            ;
            margin-top: 14px;
            padding: 20px 5px;
            min-height: calc(100vh - 64px);
            }

            .navbar {
                height: 64px;
                z-index: 1060;
            }
        </style>

    </head>

    <body>

        <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
            <div class="container-fluid px-4">

                <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">
                    My Form Task
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Home</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#">About</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        @auth
                            <span class="text-dark fw-semibold d-none d-sm-inline">
                                Welcome, {{ auth()->user()->name }}
                            </span>

                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf

                                <button type="submit" class="btn__logout">
                                    Logout
                                </button>
                            </form>
                        @else
                            <div class="login__btns">
                                <a href="{{ route('login') }}" class="btn__login">
                                    Login
                                </a>

                                <a href="{{ route('signup') }}" class="btn__signup">
                                    SignUp
                                </a>
                            </div>
                        @endauth
                    </div>

                </div>
            </div>
        </nav>


        @auth
            @include('sidebar')
        @endauth


        <main class="main-content">
            <div class="container">
                @yield('content')
            </div>
        </main>

    </body>

    </html>
