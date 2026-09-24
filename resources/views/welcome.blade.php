<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Form Task')</title>

    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @yield('styles')
</head>

<body class="@auth has-sidebar @else is-guest @endauth">

    @auth
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand bg-white shadow-sm fixed-top">
            <div class="container-fluid px-3 px-md-4">

                <div class="d-flex align-items-center gap-2">
                    <!-- Mobile Hamburger Button for Sidebar -->
                    <button id="sidebarToggleBtn" class="sidebar-toggle-btn d-lg-none" type="button" aria-label="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>

                    <a class="navbar-brand fw-bold text-primary mb-0 d-flex align-items-center gap-2" href="{{ url('/') }}">
                        <i class="bi bi-mortarboard-fill d-none d-sm-inline"></i>
                        <span>My Form Task</span>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="dropdown">
                        <button class="user-nav-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('storage/images/' . (auth()->user()->image ?? 'default-user.png')) }}"
                                alt="{{ auth()->user()->name }}" class="user-nav-image">
                            <span class="d-none d-sm-inline user-nav-name">
                                {{ auth()->user()->name }}
                            </span>
                            <i class="bi bi-chevron-down user-nav-arrow"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end user-nav-dropdown">
                            <li class="px-3 py-2 border-bottom d-sm-none">
                                <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                                <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                            </li>

                            <li>
                                <a href="{{ route('profile.settings.form') }}" class="dropdown-item">
                                    <i class="bi bi-person-gear"></i>
                                    Profile Settings
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item logout-item">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </nav>

        <!-- Sidebar Component -->
        @include('sidebar')
    @endauth

    <!-- Main Content Area -->
    <main class="main-content @guest guest-layout @endguest">
        @guest
            @yield('content')
        @else
            <div class="content-container">
                @yield('content')
            </div>
        @endguest
    </main>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('appSidebar');
                const toggleBtn = document.getElementById('sidebarToggleBtn');
                const closeBtn = document.getElementById('sidebarCloseBtn');
                const backdrop = document.getElementById('sidebarBackdrop');

                function openSidebar() {
                    if (sidebar) sidebar.classList.add('show');
                    if (backdrop) backdrop.classList.add('show');
                    document.body.classList.add('sidebar-open');
                }

                function closeSidebar() {
                    if (sidebar) sidebar.classList.remove('show');
                    if (backdrop) backdrop.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }

                if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
                if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
                if (backdrop) backdrop.addEventListener('click', closeSidebar);

                // Close sidebar on ESC key
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
                        closeSidebar();
                    }
                });

                // Auto-close on resize to large screen
                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 992) {
                        closeSidebar();
                    }
                });
            });
        </script>
    @endauth

    @yield('scripts')
</body>

</html>
