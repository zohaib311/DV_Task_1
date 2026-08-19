<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Form Task')</title>


    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">

            <a class="navbar-brand fw-bold text-primary" href="{{ Route('allUsers') }}">
                My Form Task
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            About
                        </a>
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


    <main class="container">
        @yield('content')
    </main>

    <style>
        .navbar {
            padding: 12px 0;
        }

        .navbar-brand {
            font-size: 22px;
        }

        .nav-link {
            margin: 0 5px;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #0d6efd;
        }

        .btn {
            border-radius: 6px;
            padding: 7px 16px;
        }
    </style>

</body>

</html>
