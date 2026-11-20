<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Form Task University - Premier Higher Education & Research Institution')</title>
    <meta name="description" content="Official website of My Form Task University (MFTU). Chartered higher education institution offering accredited degree programs in Computing, Engineering, Business, and Sciences.">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- University Website Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/university-website.css') }}?v={{ file_exists(public_path('css/university-website.css')) ? filemtime(public_path('css/university-website.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ file_exists(public_path('css/admissions.css')) ? filemtime(public_path('css/admissions.css')) : time() }}">

    @yield('styles')
</head>

<body class="university-public-site">

    <!-- Top Announcement & Quick Contact Bar -->
    <div class="uni-topbar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="topbar-badge"><i class="bi bi-mortarboard-fill me-1"></i> Admissions 2026-27</span>
                <span>Fall Intake Open for BS, ADP &amp; Graduate Programs</span>
                <span class="text-secondary">|</span>
                <a href="#scholarships" class="text-warning"><i class="bi bi-award-fill me-1"></i> Merit Scholarships up to 100%</a>
            </div>
            <div class="d-flex align-items-center gap-4">
                <span><i class="bi bi-telephone-fill me-1 text-primary"></i> +92 (42) 3588-2900</span>
                <span><i class="bi bi-envelope-fill me-1 text-primary"></i> info@mftu.edu.pk</span>
                <div class="topbar-social">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg uni-navbar sticky-top">
        <div class="container">
            <!-- Brand / Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.jfif') }}" alt="MFT University Logo" class="uni-brand-logo" onerror="this.onerror=null;this.src='{{ asset('storage/images/default-user.png') }}';">
                <div class="uni-brand-text">
                    <h4>My Form Task University</h4>
                    <span>Higher Education &amp; Research</span>
                </div>
            </a>

            <!-- Mobile Navbar Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#uniNavContent" aria-controls="uniNavContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>

            <!-- Navbar Links & Portal Login CTA -->
            <div class="collapse navbar-collapse" id="uniNavContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link active" href="{{ url('/') }}#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#programs">Academics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#admissions">Admissions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#facilities">Campus Life</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#research">Research</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link uni-nav-link" href="#contact">Contact</a>
                    </li>
                </ul>

                <!-- Portal Login Action Button -->
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <a href="{{ route('admissions.portal') }}" class="btn apply-nav-btn d-none d-xl-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-fill me-1"></i> Apply Online
                    </a>

                    @guest
                        <!-- Portal Login button for guests -->
                        <a href="{{ route('login') }}" class="btn portal-login-nav-btn">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Portal Login</span>
                        </a>
                    @else
                        <!-- Authenticated User Quick Portal Menu -->
                        <div class="dropdown">
                            <button class="btn portal-login-nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ Str::limit(auth()->user()->name, 14) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 230px;">
                                <li class="px-3 py-2 border-bottom bg-light rounded-top">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.68rem;">Signed In As</small>
                                    <strong class="text-dark d-block text-truncate">{{ auth()->user()->name }}</strong>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                                        {{ auth()->user()->roles->pluck('name')->first() ?? 'Authorized User' }}
                                    </span>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ url('/dashboard') }}">
                                        <i class="bi bi-speedometer2 text-primary"></i>
                                        <span>Main Dashboard</span>
                                    </a>
                                </li>
                                @if(auth()->user()->studentProfile)
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('student.dashboard') }}">
                                            <i class="bi bi-mortarboard-fill text-success"></i>
                                            <span>Student Portal</span>
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->teacherProfile)
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('teaching.dashboard') }}">
                                            <i class="bi bi-person-video3 text-info"></i>
                                            <span>Teacher Workspace</span>
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger d-flex align-items-center gap-2">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <span>Sign Out</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Official University Footer -->
    <footer class="uni-footer">
        <div class="container">
            <div class="row g-4">
                <!-- Col 1: University Identity -->
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('images/logo.jfif') }}" alt="MFT University Logo" class="uni-brand-logo bg-white p-1" onerror="this.onerror=null;this.src='{{ asset('storage/images/default-user.png') }}';">
                        <div>
                            <h5 class="text-white mb-0">My Form Task University</h5>
                            <small class="text-warning fw-semibold">Chartered by Government · HEC Recognized</small>
                        </div>
                    </div>
                    <p class="small text-secondary mb-4" style="line-height: 1.7;">
                        A center of world-class academic distinction, innovation, and ethical leadership. Committed to transforming student potential into global impact across multidisciplinary domains.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border"><i class="bi bi-award-fill text-warning me-1"></i> HEC "W" Ranked</span>
                        <span class="badge bg-light text-dark border"><i class="bi bi-patch-check-fill text-primary me-1"></i> NCEAC Accredited</span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-6 col-lg-2">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#about"><i class="bi bi-chevron-right small"></i> About MFTU</a></li>
                        <li><a href="#programs"><i class="bi bi-chevron-right small"></i> Academic Programs</a></li>
                        <li><a href="#admissions"><i class="bi bi-chevron-right small"></i> Admissions 2026</a></li>
                        <li><a href="#scholarships"><i class="bi bi-chevron-right small"></i> Scholarships &amp; Aid</a></li>
                        <li><a href="#facilities"><i class="bi bi-chevron-right small"></i> Campus Facilities</a></li>
                        <li><a href="#events"><i class="bi bi-chevron-right small"></i> News &amp; Events</a></li>
                    </ul>
                </div>

                <!-- Col 3: Academic Faculties -->
                <div class="col-6 col-lg-3">
                    <h5>Academic Faculties</h5>
                    <ul>
                        <li><a href="#programs"><i class="bi bi-chevron-right small"></i> Computing &amp; IT</a></li>
                        <li><a href="#programs"><i class="bi bi-chevron-right small"></i> Management Sciences</a></li>
                        <li><a href="#programs"><i class="bi bi-chevron-right small"></i> Engineering &amp; Applied Tech</a></li>
                        <li><a href="#programs"><i class="bi bi-chevron-right small"></i> Sciences &amp; Humanities</a></li>
                        <li><a href="#admissions"><i class="bi bi-chevron-right small"></i> ADP Lateral Entry Program</a></li>
                    </ul>
                </div>

                <!-- Col 4: Portals & Contact -->
                <div class="col-lg-3">
                    <h5>Portals &amp; Verification</h5>
                    <ul class="mb-4">
                        <li><a href="{{ route('login') }}"><i class="bi bi-lock-fill text-warning"></i> Student / Faculty Portal Login</a></li>
                        <li><a href="{{ route('signup') }}"><i class="bi bi-person-plus-fill text-info"></i> Account Registration</a></li>
                        <li><a href="{{ url('/dashboard') }}"><i class="bi bi-speedometer2 text-primary"></i> Administrative Dashboard</a></li>
                    </ul>
                    <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <small class="d-block text-white fw-bold mb-1"><i class="bi bi-headset text-warning me-1"></i> Admissions Helpline</small>
                        <span class="d-block text-light fs-6 fw-bold">+92 (42) 3588-2900</span>
                        <small class="text-secondary">Mon - Fri: 8:30 AM - 5:00 PM</small>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Strip -->
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <p class="mb-0">&copy; {{ date('Y') }} My Form Task University. All rights reserved.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-secondary text-decoration-none">Privacy Policy</a>
                    <span>•</span>
                    <a href="#" class="text-secondary text-decoration-none">Terms of Admission</a>
                    <span>•</span>
                    <a href="#" class="text-secondary text-decoration-none">HEC Compliance</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top" title="Back to top" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Smooth Scrolling & Navbar Effects -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navbar = document.querySelector('.uni-navbar');
            const backToTop = document.getElementById('backToTopBtn');

            window.addEventListener('scroll', function () {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                if (window.scrollY > 300) {
                    backToTop.classList.add('active');
                } else {
                    backToTop.classList.remove('active');
                }
            });

            if (backToTop) {
                backToTop.addEventListener('click', function () {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
