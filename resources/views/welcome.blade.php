<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Form Task')</title>


    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>

    <nav class="navbar container navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center " id="navbarSupportedContent">
                <ul class="navbar-nav me-auto  mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active btn btn-outline-success" aria-current="page"
                            href="{{ Route('allUsers') }}">All Users</a>
                    </li>
                    <li class="mx-3 nav-item">
                        <a class="nav-link active btn btn-info" href="{{ Route('addUsersForm') }}">Add User</a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>


    <!-- Main Content Area -->
    <main class="container">
        @yield('content')
    </main>

</body>

</html>
