@extends('website.layout')

@section('title', 'My Form Task University - Premier Higher Education & Research Institution')

@section('content')

    <!-- ========================================================================
         Hero Section
         ======================================================================== -->
    <section id="home" class="uni-hero">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="uni-hero-badge">
                        <i class="bi bi-award-fill"></i>
                        <span>Chartered by Government · HEC "W" Category Ranked</span>
                    </div>

                    <h1>
                        Empowering Next-Gen Leaders Through <span class="text-highlight">World-Class Education</span> &amp; Innovation
                    </h1>

                    <p class="lead">
                        Welcome to My Form Task University (MFTU). Fostering technological innovation, academic brilliance, and ethical leadership across 13 specialized departments and nationally accredited undergraduate and graduate programs.
                    </p>

                    <div class="uni-hero-actions">
                        <a href="#programs" class="btn btn-primary px-4 py-3 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                            <i class="bi bi-mortarboard-fill"></i> Explore Academic Programs
                        </a>
                        <a href="{{ route('admissions.portal') }}" class="btn btn-warning px-4 py-3 fw-bold rounded-3 shadow-sm text-dark d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #ffffff !important;">
                            <i class="bi bi-send-fill"></i> Apply for Fall 2026
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light px-4 py-3 fw-bold rounded-3 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-in-right"></i> Portal Login
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                    <!-- Admission Callout Card -->
                    <div class="card border-0 rounded-4 p-4 shadow-lg" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.2) !important;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 rounded-circle bg-warning text-dark fs-3">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div>
                                <span class="badge bg-warning text-dark mb-1">ADMISSIONS OPEN</span>
                                <h5 class="text-white fw-bold mb-0">Fall 2026-2027</h5>
                            </div>
                        </div>
                        <ul class="list-unstyled text-light small mb-4" style="line-height: 1.8;">
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> BS Computer Science &amp; IT</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> BS Software Engineering</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> ADP in Computer Science (2 Years)</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>ADP Lateral Entry:</strong> 5th Sem Direct</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Up to 100% Merit Scholarships</li>
                        </ul>
                        <a href="#admissions" class="btn btn-light w-100 fw-bold py-2 text-primary">
                            View Admission Criteria <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Floating Stats Counter Strip -->
            <div class="hero-stats-strip">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">{{ $stats['departments_count'] }}<span class="stat-plus">+</span></span>
                            <span class="stat-label">Departments</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">{{ $stats['programs_count'] }}<span class="stat-plus">+</span></span>
                            <span class="stat-label">Degree Programs</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">{{ $stats['teachers_count'] }}<span class="stat-plus">+</span></span>
                            <span class="stat-label">Faculty Scholars</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">{{ number_format($stats['alumni_count']) }}<span class="stat-plus">+</span></span>
                            <span class="stat-label">Alumni Worldwide</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">98<span class="stat-plus">%</span></span>
                            <span class="stat-label">Graduate Hired</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-item">
                            <span class="stat-number">100<span class="stat-plus">%</span></span>
                            <span class="stat-label">Merit Waivers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Accreditation & Quality Ranking Ticker
         ======================================================================== -->
    <div class="accreditation-bar">
        <div class="container">
            <div class="row g-3 align-items-center justify-content-center">
                <div class="col-6 col-md-3">
                    <div class="accred-logo-pill">
                        <i class="bi bi-bank2"></i>
                        <div>
                            <strong>HEC Recognized</strong>
                            <small>Highest "W" Category</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="accred-logo-pill">
                        <i class="bi bi-cpu"></i>
                        <div>
                            <strong>NCEAC Accredited</strong>
                            <small>National Computing Council</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="accred-logo-pill">
                        <i class="bi bi-gear-wide-connected"></i>
                        <div>
                            <strong>PEC Approved</strong>
                            <small>Engineering Council</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="accred-logo-pill">
                        <i class="bi bi-briefcase"></i>
                        <div>
                            <strong>NBEAC Certified</strong>
                            <small>Business Education Council</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================
         About University & Vice Chancellor's Message
         ======================================================================== -->
    <section id="about" class="uni-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-buildings-fill"></i> Welcome to MFTU</span>
                <h2 class="section-heading">A Legacy of Academic Prestige &amp; Modern Innovation</h2>
                <p class="section-subheading">Dedicated to cultivating intellectual curiosity, rigorous technical competence, and groundbreaking research that impacts Pakistan and the world.</p>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="pe-lg-3">
                        <h3 class="fw-bold mb-3 text-dark">Transforming Ambition into Impactful Careers</h3>
                        <p class="text-secondary mb-3">
                            Established with the vision of bridging theoretical academia and high-impact industrial technologies, My Form Task University has grown into one of the country's most respected educational centers.
                        </p>
                        <p class="text-secondary mb-4">
                            Our state-of-the-art campus features 13 specialized departments, dedicated research centers, ultra-fast gigabit networking, modern auditoriums, and smart seminar rooms. With an ecosystem that nurtures startups through our Innovation Center, students graduate as problem solvers, technology architects, and industry pioneers.
                        </p>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-light-subtle">
                                    <i class="bi bi-mortarboard fs-2 text-primary"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Outcome-Based (OBE)</h6>
                                        <small class="text-muted">Global Washington Accord Standard</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-light-subtle">
                                    <i class="bi bi-globe-americas fs-2 text-success"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">International Linkages</h6>
                                        <small class="text-muted">Exchange &amp; Research MoUs</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="#admissions" class="btn btn-outline-primary fw-bold px-4 py-2">
                            Learn More About Campus Life <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Vice Chancellor's Message Card -->
                    <div class="vc-card">
                        <div class="vc-quote-box">
                            <i class="bi bi-quote vc-quote-icon"></i>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-3 px-3 py-1 fw-bold">
                                VICE CHANCELLOR'S MESSAGE
                            </span>
                            <p class="vc-quote-text">
                                "At MFT University, education is not an accumulation of facts, but the ignition of creative and analytical power. We challenge our students to question, innovate, and develop ethical solutions that serve humanity. Whether in advanced computing, artificial intelligence, biotechnology, or business leadership, our university prepares you to lead with confidence in an increasingly complex world."
                            </p>
                            <div class="vc-signature-block">
                                <img src="{{ asset('storage/images/default-user.png') }}" alt="Vice Chancellor" class="vc-avatar">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Prof. Dr. A. R. Rahman</h5>
                                    <small class="text-primary fw-semibold d-block">Ph.D. Computer Science (Stanford) · Post-Doc (MIT)</small>
                                    <small class="text-muted">Vice Chancellor &amp; President, MFTU</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Academic Faculties & Programs Showcase
         ======================================================================== -->
    <section id="programs" class="uni-section bg-subtle">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-book-half"></i> Academic Excellence</span>
                <h2 class="section-heading">Featured Degree Programs &amp; Faculties</h2>
                <p class="section-subheading">Choose from our market-aligned, HEC-accredited degree programs crafted with top corporate leaders and research scholars.</p>
            </div>

            <div class="row g-4">
                <!-- Program 1: BSCS -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="program-badge badge-computing"><i class="bi bi-code-slash"></i> Computing</span>
                                <span class="badge bg-light text-secondary border font-monospace">4 Years · 8 Sem</span>
                            </div>
                            <h4>BS Computer Science</h4>
                            <p class="program-dept"><i class="bi bi-building me-1"></i> Dept. of Computer Science</p>
                            <p class="small text-secondary mb-3">
                                Rigorous foundation in software engineering, algorithms, artificial intelligence, data structures, and distributed cloud computing.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-clock-history"></i> 133 Cr. Hrs.</div>
                                <div><i class="bi bi-briefcase"></i> 99% Placement</div>
                                <div><i class="bi bi-award"></i> NCEAC 'W'</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold small">Fall 2026 Admissions Open</span>
                                <a href="#inquiry" class="btn btn-sm btn-outline-primary fw-bold">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program 2: BSIT -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="program-badge badge-computing"><i class="bi bi-hdd-network"></i> IT &amp; Cloud</span>
                                <span class="badge bg-light text-secondary border font-monospace">4 Years · 8 Sem</span>
                            </div>
                            <h4>BS Information Technology</h4>
                            <p class="program-dept"><i class="bi bi-building me-1"></i> Dept. of Information Technology</p>
                            <p class="small text-secondary mb-3">
                                Specialization in enterprise cloud architecture, cyber security, DevOps, modern database management, and mobile application engineering.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-clock-history"></i> 132 Cr. Hrs.</div>
                                <div><i class="bi bi-briefcase"></i> 97% Placement</div>
                                <div><i class="bi bi-award"></i> HEC Approved</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold small">Fall 2026 Admissions Open</span>
                                <a href="#inquiry" class="btn btn-sm btn-outline-primary fw-bold">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program 3: BS Software Engineering -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="program-badge badge-computing"><i class="bi bi-braces-asterisk"></i> Software Eng.</span>
                                <span class="badge bg-light text-secondary border font-monospace">4 Years · 8 Sem</span>
                            </div>
                            <h4>BS Software Engineering</h4>
                            <p class="program-dept"><i class="bi bi-building me-1"></i> Dept. of Software Engineering</p>
                            <p class="small text-secondary mb-3">
                                Large-scale software systems, full-stack web and mobile development, agile practices, QA testing, and UI/UX engineering.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-clock-history"></i> 136 Cr. Hrs.</div>
                                <div><i class="bi bi-briefcase"></i> 98% Placement</div>
                                <div><i class="bi bi-award"></i> NCEAC Accredited</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold small">Fall 2026 Admissions Open</span>
                                <a href="#inquiry" class="btn btn-sm btn-outline-primary fw-bold">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program 4: ADP Lateral Entry Transfer -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card border-primary" style="background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success text-white"><i class="bi bi-arrow-right-circle-fill me-1"></i> LATERAL TRANSFER</span>
                                <span class="badge bg-light text-success border border-success-subtle font-monospace">2 Years · 5th Sem</span>
                            </div>
                            <h4 class="text-success">ADP Lateral Entry Program</h4>
                            <p class="program-dept"><i class="bi bi-award me-1"></i> Associate Degree Holders Pathway</p>
                            <p class="small text-secondary mb-3">
                                Completed a 2-Year Associate Degree (ADP)? Transfer directly into Semester 5 with approved course exemptions and earn a full 4-Year BS degree.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-award-fill text-warning"></i> Advanced Standing</div>
                                <div><i class="bi bi-clock-history"></i> 4 Semesters</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-success fw-bold small">Credit Exemption Active</span>
                                <a href="#admissions" class="btn btn-sm btn-success fw-bold">Transfer Guide</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program 5: Bachelor of Business Administration -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="program-badge badge-management"><i class="bi bi-graph-up-arrow"></i> Business</span>
                                <span class="badge bg-light text-secondary border font-monospace">4 Years · 8 Sem</span>
                            </div>
                            <h4>Bachelor of Business Admin (BBA)</h4>
                            <p class="program-dept"><i class="bi bi-building me-1"></i> Dept. of Business Administration</p>
                            <p class="small text-secondary mb-3">
                                Strategic management, marketing, financial analytics, human capital leadership, and venture capital entrepreneurship.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-clock-history"></i> 130 Cr. Hrs.</div>
                                <div><i class="bi bi-briefcase"></i> Corporate Ties</div>
                                <div><i class="bi bi-award"></i> NBEAC W-Cat</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold small">Fall 2026 Admissions Open</span>
                                <a href="#inquiry" class="btn btn-sm btn-outline-primary fw-bold">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program 6: BS Electrical Engineering & AI -->
                <div class="col-md-6 col-lg-4">
                    <div class="program-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="program-badge badge-engineering"><i class="bi bi-lightning-charge"></i> Engineering</span>
                                <span class="badge bg-light text-secondary border font-monospace">4 Years · 8 Sem</span>
                            </div>
                            <h4>BS Electrical Engineering &amp; AI</h4>
                            <p class="program-dept"><i class="bi bi-building me-1"></i> Dept. of Electrical Engineering</p>
                            <p class="small text-secondary mb-3">
                                Power electronics, embedded systems, robotics, IoT telemetry, and signal processing taught in cutting-edge simulation laboratories.
                            </p>
                        </div>
                        <div>
                            <div class="program-meta">
                                <div><i class="bi bi-clock-history"></i> 136 Cr. Hrs.</div>
                                <div><i class="bi bi-briefcase"></i> High Demand</div>
                                <div><i class="bi bi-award"></i> PEC Level-II</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold small">Fall 2026 Admissions Open</span>
                                <a href="#inquiry" class="btn btn-sm btn-outline-primary fw-bold">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All 13 University Departments Catalog Strip -->
            <div class="mt-5 p-4 rounded-4 bg-white border shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-collection-fill text-primary me-2"></i>All 13 Academic Departments</h5>
                    <span class="badge bg-primary rounded-pill px-3 py-2">{{ $departments->count() }} Authorized Departments</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($departments as $dept)
                        <span class="badge bg-light text-dark border px-3 py-2 d-inline-flex align-items-center" style="font-size: 0.83rem;">
                            <i class="bi bi-check2-circle text-primary me-2"></i> {{ $dept->name }}
                        </span>
                    @empty
                        <span class="text-muted">Academic departments are configured by the university administration.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Admissions Roadmap & Scholarships
         ======================================================================== -->
    <section id="admissions" class="uni-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-compass-fill"></i> Admission Guidelines</span>
                <h2 class="section-heading">Your Pathway to Joining MFT University</h2>
                <p class="section-subheading">A straightforward 4-step admission journey designed to evaluate your merit and get you enrolled seamlessly.</p>
            </div>

            <!-- 4-Step Admission Journey -->
            <div class="row g-4 mb-5">
                <div class="col-sm-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h5 class="fw-bold text-dark">Online Application</h5>
                        <p class="small text-muted mb-0">Fill out your profile, select your preferred degree program, and submit your academic intermediate / A-Levels credentials.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h5 class="fw-bold text-dark">Entry Test / Merit</h5>
                        <p class="small text-muted mb-0">Attend the university entry test or submit your NTS / SAT score. Merit lists are published officially on the university portal.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h5 class="fw-bold text-dark">Document Verification</h5>
                        <p class="small text-muted mb-0">Original CNIC / B-Form, educational certificates, and character certificate are reviewed by the admissions committee.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h5 class="fw-bold text-dark">Fee Challan &amp; Portal</h5>
                        <p class="small text-muted mb-0">Pay your semester invoice at any authorized bank branch, get your Student Portal login, and attend orientation week!</p>
                    </div>
                </div>
            </div>

            <!-- Scholarships and Lateral Entry Feature Boxes -->
            <div id="scholarships" class="row g-4">
                <div class="col-lg-6">
                    <div class="scholarship-box h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 rounded-circle bg-success text-white fs-3">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-success mb-1">Scholarships &amp; Tuition Fee Waivers</h4>
                                <small class="text-success-emphasis fw-semibold">No deserving student is left behind</small>
                            </div>
                        </div>
                        <p class="text-secondary mb-3">
                            MFT University commits millions annually to empower academic achievers and financially constrained students. Scholarships are directly applied as tuition waivers in your student fee challan:
                        </p>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border border-success-subtle">
                                <span class="fw-bold text-dark small"><i class="bi bi-star-fill text-warning me-1"></i> Chairman Merit Scholarship</span>
                                <span class="badge bg-success">100% Tuition Waiver</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border border-success-subtle">
                                <span class="fw-bold text-dark small"><i class="bi bi-award-fill text-primary me-1"></i> High Achievers Award (85%+ Marks)</span>
                                <span class="badge bg-success">50% Tuition Waiver</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border border-success-subtle">
                                <span class="fw-bold text-dark small"><i class="bi bi-heart-fill text-danger me-1"></i> Need-Based &amp; Kinship Aid</span>
                                <span class="badge bg-info text-dark">25% - 50% Waiver</span>
                            </div>
                        </div>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Maintained with a minimum 3.00 semester SGPA.</small>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="lateral-entry-box h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 rounded-circle bg-primary text-white fs-3">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-primary mb-1">ADP Lateral Entry Transfer</h4>
                                <small class="text-primary-emphasis fw-semibold">Direct Admission to 5th Semester</small>
                            </div>
                        </div>
                        <p class="text-secondary mb-3">
                            Have you completed your 2-Year Associate Degree (ADP / BA / BSc)? Upgrade to a 4-Year Bachelor of Science (BS) degree in just 4 additional semesters without losing academic credits:
                        </p>
                        <ul class="list-unstyled text-secondary small mb-4" style="line-height: 1.9;">
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Full credit transfer for up to 60+ credit hours from recognized universities.</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Official Transcript displays Advanced Standing &amp; Exemption boxes.</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Complete same rigorous curriculum alongside regular 5th semester batches.</li>
                            <li><i class="bi bi-check-circle-fill text-primary me-2"></i> Eligible for all university placement drives and campus interviews.</li>
                        </ul>
                        <a href="#inquiry" class="btn btn-primary fw-bold px-4 py-2">
                            Inquire for Lateral Entry <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Campus Life & World-Class Facilities
         ======================================================================== -->
    <section id="facilities" class="uni-section bg-subtle">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-stars"></i> Student Life &amp; Amenities</span>
                <h2 class="section-heading">World-Class Infrastructure Built for Success</h2>
                <p class="section-subheading">Experience a dynamic, inspiring university environment equipped with premier academic and recreational facilities.</p>
            </div>

            <div class="row g-4">
                <!-- Facility 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-blue">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">High-Tech Computing Labs</h4>
                        <p class="text-secondary small mb-0">
                            Equipped with Intel Core i9 / Apple Silicon workstations, dedicated NVIDIA GPU AI clusters, gigabit fiber internet, and Cisco networking switches for hands-on software development.
                        </p>
                    </div>
                </div>

                <!-- Facility 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-purple">
                            <i class="bi bi-book"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Central Digital Library</h4>
                        <p class="text-secondary small mb-0">
                            Access over 100,000+ physical volumes and instant digital access to IEEE Xplore, ACM Digital Library, SpringerLink, and ScienceDirect journals with dedicated quiet research cubicles.
                        </p>
                    </div>
                </div>

                <!-- Facility 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-green">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Sports &amp; Athletics Arena</h4>
                        <p class="text-secondary small mb-0">
                            Olympic-size heated swimming pool, floodlit cricket &amp; football grounds, international standard basketball courts, indoor badminton arena, and a modern fitness gymnasium.
                        </p>
                    </div>
                </div>

                <!-- Facility 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-amber">
                            <i class="bi bi-rocket-takeoff"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Startup &amp; Incubation Hub</h4>
                        <p class="text-secondary small mb-0">
                            ORIC-supported tech incubator providing student entrepreneurs with seed funding, patent mentoring, workspace, and direct pitch sessions to venture capitalists and angel investors.
                        </p>
                    </div>
                </div>

                <!-- Facility 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-rose">
                            <i class="bi bi-bus-front"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Hostels &amp; Transport Fleet</h4>
                        <p class="text-secondary small mb-0">
                            Separate air-conditioned boys and girls residencies with 24/7 security, high-speed Wi-Fi, and mess facilities. Extensive fleet of 30+ university buses covering all major city routes.
                        </p>
                    </div>
                </div>

                <!-- Facility 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="facility-card">
                        <div class="facility-icon icon-indigo">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Active Student Societies</h4>
                        <p class="text-secondary small mb-0">
                            Over 25+ student-run clubs including ACM Student Chapter, Robotics Club, Debating Society, Dramatic Club, Media &amp; Photography Cell, and Annual National Hackathon Teams.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Research & Innovation
         ======================================================================== -->
    <section id="research" class="uni-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="section-eyebrow"><i class="bi bi-lightbulb-fill"></i> Research &amp; Discovery</span>
                    <h2 class="section-heading">Pioneering Solutions for Global Challenges</h2>
                    <p class="text-secondary mb-4">
                        Research is the beating heart of MFT University. Our interdisciplinary research centers collaborate with leading international universities, HEC research grants, and industry giants to address pressing technological, environmental, and business challenges.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="p-2 rounded bg-primary-subtle text-primary fs-5 mt-1">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">500+ Scopus &amp; W-Category Publications</h6>
                                <p class="small text-muted mb-0">Faculty and graduate students publish regularly in IEEE, ACM, Elsevier, and Nature Partner journals.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="p-2 rounded bg-success-subtle text-success fs-5 mt-1">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">PKR 150M+ in Competitive Grants</h6>
                                <p class="small text-muted mb-0">Funded by the Higher Education Commission (HEC), Ignite National Technology Fund, and international consortiums.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="p-2 rounded bg-warning-subtle text-warning fs-5 mt-1">
                                <i class="bi bi-puzzle"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Patents &amp; Technology Commercialization</h6>
                                <p class="small text-muted mb-0">Direct intellectual property patenting and technology transfer to Pakistani and overseas industrial partners.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 p-md-5 rounded-4 bg-dark text-white shadow-lg" style="background: linear-gradient(135deg, #0b1329 0%, #1e293b 100%); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-1">ORIC IMPACT 2026</span>
                        <h3 class="text-white fw-bold mb-3">Key Research Laboratories</h3>
                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="p-3 rounded-3 bg-white bg-opacity-10">
                                <strong class="d-block text-white mb-1"><i class="bi bi-cpu-fill text-warning me-2"></i>Artificial Intelligence &amp; Computer Vision Lab</strong>
                                <small class="text-light text-opacity-75">Medical imaging, neural networks, speech recognition, and generative AI systems.</small>
                            </div>
                            <div class="p-3 rounded-3 bg-white bg-opacity-10">
                                <strong class="d-block text-white mb-1"><i class="bi bi-shield-lock-fill text-info me-2"></i>Cyber Security &amp; Blockchain Center</strong>
                                <small class="text-light text-opacity-75">Cryptography, threat intelligence, smart contract auditing, and digital forensics.</small>
                            </div>
                            <div class="p-3 rounded-3 bg-white bg-opacity-10">
                                <strong class="d-block text-white mb-1"><i class="bi bi-activity text-success me-2"></i>Computational Biology &amp; Genetics Lab</strong>
                                <small class="text-light text-opacity-75">Bioinformatics modeling, DNA sequencing, drug design, and genomic algorithms.</small>
                            </div>
                        </div>
                        <a href="#inquiry" class="btn btn-warning w-100 fw-bold py-2 text-dark">
                            Collaborate with Our Research Faculty <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Latest Events & Campus Notices
         ======================================================================== -->
    <section id="events" class="uni-section bg-subtle">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-5">
                <div>
                    <span class="section-eyebrow"><i class="bi bi-calendar-event-fill"></i> What's Happening</span>
                    <h2 class="section-heading mb-0">Upcoming Campus Events &amp; Notices</h2>
                </div>
                <a href="#inquiry" class="btn btn-outline-primary fw-bold">
                    View Academic Calendar <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                @forelse($events as $event)
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <div class="d-flex">
                                <div class="event-date-badge">
                                    <span class="event-day">{{ \Carbon\Carbon::parse($event->date)->format('d') }}</span>
                                    <span class="event-month">{{ \Carbon\Carbon::parse($event->date)->format('M Y') }}</span>
                                </div>
                                <div class="event-content">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">
                                        {{ $event->day }}
                                    </span>
                                    <h5 class="fw-bold text-dark mb-2">{{ $event->event_name }}</h5>
                                    <p class="small text-muted mb-2"><i class="bi bi-clock me-1"></i> {{ $event->start_time }} - {{ $event->end_time }}</p>
                                    <p class="small text-secondary mb-0" style="line-height: 1.5;">{{ Str::limit($event->description, 95) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Fallback Sample Real Events -->
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <div class="d-flex">
                                <div class="event-date-badge">
                                    <span class="event-day">28</span>
                                    <span class="event-month">Nov 2026</span>
                                </div>
                                <div class="event-content">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">Annual TechFest</span>
                                    <h5 class="fw-bold text-dark mb-2">National Hackathon &amp; Coding Summit</h5>
                                    <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i> Central Auditorium · 09:00 AM</p>
                                    <p class="small text-secondary mb-0">A 24-hour national competitive programming challenge with industry judges and cash prizes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <div class="d-flex">
                                <div class="event-date-badge" style="background: linear-gradient(135deg, #059669, #10b981);">
                                    <span class="event-day">05</span>
                                    <span class="event-month">Dec 2026</span>
                                </div>
                                <div class="event-content">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-2">Research Conference</span>
                                    <h5 class="fw-bold text-dark mb-2">International Symposium on Generative AI</h5>
                                    <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i> Video Con Hall · 10:30 AM</p>
                                    <p class="small text-secondary mb-0">Keynotes from Stanford, Oxford, and top tech executives on deep neural architectures.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <div class="d-flex">
                                <div class="event-date-badge" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                                    <span class="event-day">15</span>
                                    <span class="event-month">Dec 2026</span>
                                </div>
                                <div class="event-content">
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle mb-2">Campus Life</span>
                                    <h5 class="fw-bold text-dark mb-2">Annual Sports Gala &amp; Alumni Dinner</h5>
                                    <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i> Sports Arena · 04:00 PM</p>
                                    <p class="small text-secondary mb-0">Cricket finals, badminton championships, live musical performances, and networking.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Alumni Testimonials
         ======================================================================== -->
    <section class="uni-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-chat-quote-fill"></i> Voices of Success</span>
                <h2 class="section-heading">What Our Graduates Say</h2>
                <p class="section-subheading">Our alumni lead global technology companies, top financial institutions, and research labs around the world.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="testimonial-text">
                            "MFT University gave me more than just theoretical CS degrees; the practical lab projects, open-source workshops, and hackathons prepared me to crack Silicon Valley technical interviews on my first try."
                        </p>
                        <div class="testimonial-user">
                            <img src="{{ asset('storage/images/default-user.png') }}" alt="Alum" class="testimonial-avatar">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Hamza Tariq</h6>
                                <small class="text-primary fw-semibold d-block">Senior Software Engineer @ Google</small>
                                <small class="text-muted">BSCS Batch 2022</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="testimonial-text">
                            "The ADP Lateral Entry program was a game changer for me. I transferred my credits smoothly into Semester 5, received a 50% merit scholarship, and graduated with honors into a tier-1 software house."
                        </p>
                        <div class="testimonial-user">
                            <img src="{{ asset('storage/images/default-user.png') }}" alt="Alum" class="testimonial-avatar">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Ayesha Siddiqua</h6>
                                <small class="text-primary fw-semibold d-block">DevOps Engineer @ Systems Ltd</small>
                                <small class="text-muted">BSIT (Lateral Entry) Batch 2023</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="testimonial-text">
                            "The modern student portal, automated result tracking, and interactive faculty LMS made semester management so smooth. MFTU truly operates at international academic standards."
                        </p>
                        <div class="testimonial-user">
                            <img src="{{ asset('storage/images/default-user.png') }}" alt="Alum" class="testimonial-avatar">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Bilal Ahmed</h6>
                                <small class="text-primary fw-semibold d-block">Full Stack Architect @ DevVerx</small>
                                <small class="text-muted">BSSE Batch 2024</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Interactive FAQ Section
         ======================================================================== -->
    <section class="uni-section bg-subtle">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-eyebrow"><i class="bi bi-question-circle-fill"></i> Clarifications</span>
                <h2 class="section-heading">Frequently Asked Questions</h2>
                <p class="section-subheading">Everything you need to know about admissions, portals, degree transfers, and scholarships.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion shadow-sm rounded-4 overflow-hidden" id="uniFaqAccordion">
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I login to the Student, Teacher, or Administrative Portal?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#uniFaqAccordion">
                                <div class="accordion-body text-secondary small py-3" style="line-height: 1.8;">
                                    Click on the <strong>Portal Login</strong> button located in the top navigation bar of this website, or navigate directly to <code>/login</code>. Use your registered university email and password. Students, faculty members, and administrators are automatically routed to their personalized workspaces.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What are the eligibility requirements for ADP Lateral Entry transfer?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#uniFaqAccordion">
                                <div class="accordion-body text-secondary small py-3" style="line-height: 1.8;">
                                    Candidates with an HEC-recognized 2-Year Associate Degree (ADP / BA / BSc) with a minimum 2.00 CGPA or 50% marks are eligible for lateral entry admission into Semester 5. Course exemptions are granted based on credit hour mapping, and your final degree is awarded as a full 4-Year BS degree.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How are merit scholarship fee discounts calculated?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#uniFaqAccordion">
                                <div class="accordion-body text-secondary small py-3" style="line-height: 1.8;">
                                    Merit scholarships are strictly applied as discounts directly onto your registered semester tuition fee. When your semester fee challan is generated in the portal, your scholarship waiver is automatically deducted, allowing you to download and pay the discounted challan at the bank.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Can I verify my official transcripts and fee challans online?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#uniFaqAccordion">
                                <div class="accordion-body text-secondary small py-3" style="line-height: 1.8;">
                                    Yes! All official transcripts, examination admit cards, and bank challans printed through the university system include a cryptographic SHA-256 QR verification hash. Scanning or opening the link verifies the document live from the institutional records.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         Admissions Inquiry Form & Campus Location
         ======================================================================== -->
    <section id="contact" class="uni-section">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <!-- Contact Info Card -->
                <div class="col-lg-5">
                    <div class="contact-info-card d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-1">GET IN TOUCH</span>
                            <h3 class="text-white fw-bold mb-3">Visit Our Campus or Reach Out Online</h3>
                            <p class="text-light text-opacity-75 mb-4" style="line-height: 1.7;">
                                Our admissions officers, academic counselors, and department heads are available to guide you on programs, fee structures, and transfer criteria.
                            </p>

                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-white">Main Campus Address</strong>
                                    <span class="small text-light text-opacity-75">Knowledge City Campus, Sector H-12 / Canal Road, Lahore, Pakistan</span>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-white">Admissions Helpline</strong>
                                    <span class="small text-light text-opacity-75">+92 (42) 3588-2900 / +92 311 7524530</span>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-white">Official Email</strong>
                                    <span class="small text-light text-opacity-75">admissions@mftu.edu.pk · info@mftu.edu.pk</span>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-white">Visiting Hours</strong>
                                    <span class="small text-light text-opacity-75">Monday to Friday: 8:30 AM to 5:00 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-top border-white border-opacity-10">
                            <span class="small text-warning fw-bold d-block mb-1">Direct Portal Access:</span>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-light fw-bold text-primary">
                                <i class="bi bi-lock-fill me-1"></i> Open Portal Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Admissions Inquiry Form -->
                <div class="col-lg-7" id="inquiry">
                    <div class="inquiry-card h-100">
                        <!-- Direct Online Application Prompt Banner -->
                        <div class="p-3 mb-4 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 rounded-circle bg-primary text-white fs-5">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div>
                                    <strong class="text-primary d-block">Ready to Apply for Fall 2026?</strong>
                                    <small class="text-secondary">Submit your formal admission application in our online portal.</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admissions.portal') }}" class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-3 shadow-sm">
                                    <i class="bi bi-pencil-fill me-1"></i> Apply Online Now
                                </a>
                                <a href="{{ route('admissions.status') }}" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2 rounded-3">
                                    <i class="bi bi-search me-1"></i> Track Status
                                </a>
                            </div>
                        </div>

                        <span class="section-eyebrow"><i class="bi bi-send-fill"></i> Admissions 2026-2027</span>
                        <h3 class="fw-bold text-dark mb-2">Request Admission Info &amp; Prospectus</h3>
                        <p class="text-muted small mb-4">Leave your details and our academic counseling team will reach out with complete curriculum and scholarship guides.</p>

                        @if(session('inquiry_success'))
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <div>{{ session('inquiry_success') }}</div>
                            </div>
                        @endif

                        <form action="{{ route('website.inquiry') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Full Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                        <input type="text" name="name" class="form-control" placeholder="Muhammad Ali" required value="{{ old('name') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" placeholder="ali@example.com" required value="{{ old('email') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Phone Number / WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                                        <input type="text" name="phone" class="form-control" placeholder="0300 1234567" value="{{ old('phone') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Program of Interest</label>
                                    <select name="program_interest" class="form-select">
                                        <option value="">Select a Program</option>
                                        <option value="BS Computer Science">BS Computer Science (BSCS)</option>
                                        <option value="BS Information Technology">BS Information Technology (BSIT)</option>
                                        <option value="BS Software Engineering">BS Software Engineering (BSSE)</option>
                                        <option value="ADP Lateral Entry">ADP Lateral Entry (5th Semester)</option>
                                        <option value="BBA">Bachelor of Business Admin (BBA)</option>
                                        <option value="BS Electrical Engineering">BS Electrical Engineering</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-secondary">Your Message / Inquiry *</label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="Ask about eligibility, fee structures, scholarship criteria, or campus tour..." required>{{ old('message') }}</textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-5 py-3 fw-bold w-100 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); border: none;">
                                        <i class="bi bi-send-check-fill"></i> Submit Admission Inquiry
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
