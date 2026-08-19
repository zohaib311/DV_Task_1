<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Form Task')</title>


    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
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
            margin-left: 250px;
            margin-top: 64px;
            padding: 30px 20px;
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

            <a class="navbar-brand fw-bold text-primary" href="{{ route('allUsers') }}">
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

                <div class="d-flex gap-2">
                    <a href="{{ route('allUsers') }}" class="btn btn-outline-primary">
                        All Users
                    </a>

                    <a href="{{ route('addUsersForm') }}" class="btn btn-primary">
                        Add User
                    </a>
                </div>

            </div>
        </div>
    </nav>


    @include('sidebar')


    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

</body>

</html>
