# University Management System (UMS) — Future Roadmap & Enhancement Plan

## Purpose

This document serves as the official blueprint and implementation roadmap for the next developmental phases of the University Management System (UMS). It builds upon the established foundation of **Phases 0–8** (Semester Enrollments, Flexible Assessment Breakdown, and Academic Results Engine) and **Phases 9A–9H** (RBAC, Course Offerings, Teacher Panel, Attendance, Assessments, Moderation, Student Portal, Operational Hardening, Fee Management, and Student Services).

All code, database migrations, authorization policies, validation messages, and user-facing views must adhere to professional English, strict auditability, and academic data integrity.

---

## 1. Existing System Architecture (Baseline Summary)

The following core modules are implemented and operational:

- **Academic Hierarchy:** Academic Years, Academic Terms, Departments, Programs (e.g. BSCS, BBA), Sections, and 8-Semester Curricula.
- **Course Catalog & Offerings:** Catalog items, section-specific course offerings, teacher assignments, and teaching setup wizard.
- **Access Control (RBAC):** Database-backed roles (`Super Admin`, `Academic Admin`, `HOD`, `Teacher`, `Student`) and granular permissions.
- **Enrollment & Promotion:** Semester enrollments with historical snapshots, credit-hour caps (max 21), backlog/repeat carryover, and fee clearance checks prior to semester promotion.
- **Teacher Panel:** Assigned offerings, student rosters, session-based attendance (Present, Absent, Late, Excused), flexible assessment configurations (Quiz, Assignment, Midterm, Final, Practical, Project), teacher marking, and submission to review.
- **Student Portal:** Academic profile, self-service course registration, attendance monitoring, question file downloads, assignment submission uploads, published results, official transcript view, fee invoice review, fee payment proof submission, and leave applications.
- **Fee Management:** Configurable fee structures per program/semester, automated semester invoices, payment receipt upload, admin verification/approval, and integration with semester promotion eligibility.
- **Audit & Moderation:** Independent result review and publication stages, audited post-publication corrections, consolidated audit viewer, and analytical academic reports.

---

## 2. Immediate Housekeeping: Test Suite Alignment

### Objective

Restore 100% green status across the automated regression test suite following the integration of the fee clearance requirement during promotion (`commit 008062a`).

### Action Items

- Update test cases in `tests/Feature/Result/ResultModerationTest.php` and `tests/Feature/AcademicSetupTest.php`.
- Ensure test fixtures that test promotion eligibility either mock an approved/paid `StudentFeeInvoice` or explicitly configure the `academic.promotion.require_approved_fee` environment setting.
- Validate that all 138 feature and unit tests pass without regressions.

---

## 3. Future Roadmap Phases

```
┌────────────────────────────────────────────────────────────────────────┐
│                        UPCOMING UMS PHASES                             │
├────────────────────────────┬───────────────────────────────────────────┤
│ Phase 10: PDF & Documents  │ Transcripts, 3-Copy Challans, Admit Cards │
│ Phase 11: Timetable Engine │ Rooms, Time Slots, Clash Prevention       │
│ Phase 12: LMS Lite         │ Course Resources, Announcements, MCQs     │
│ Phase 13: Bulk Data Ops    │ Student CSV Import, Bulk Marks Upload     │
│ Phase 14: Payment Gateway  │ Online Payments, Installments, Late Fines │
│ Phase 15: QEC Evaluation   │ Anonymous Teacher Surveys & Analytics     │
│ Phase 16: Degree Clearance │ "No Dues" Clearance & Degree Audit        │
│ Phase 17: External Alerts  │ Email & SMS Notification Channels         │
└────────────────────────────┴───────────────────────────────────────────┘
```

---

### Phase 10 — Examination & Document Export Engine (PDF & Challans)

#### 10.1 Official Semester Transcript / DMC (Detailed Marks Certificate)

- Generate high-resolution, print-ready PDF transcripts using `barryvdh/laravel-dompdf` or Chromium-based PDF rendering.
- Include official university branding: crest/logo, institutional watermark, student registration number, department, program, semester-by-semester course outcomes, SGPA, CGPA, and grading key.
- Embed a dynamic verification QR code containing a secure URL with an encrypted hash to allow instant third-party authenticity verification.
- Include designated signature and stamp placeholders for the Controller of Examinations and Registrar.

#### 10.2 Three-Copy Bank Fee Challan Form (PDF)

- Standardize printable fee challans divided into 3 distinct perforated slips:
    1. **Student Copy**
    2. **Bank Copy**
    3. **University Accounts Copy**
- Display invoice number, barcode/reference ID, student details, breakdown of charges (Tuition, Examination, Admission, Library, Lab Charges), due date, and bank branch deposit instructions.

#### 10.3 Examination Admit Card / Roll Number Slip

- Printable student examination pass displaying enrolled exam courses, dates, timings, examination hall, and student photograph.
- **Eligibility Gates:** Download is programmatically blocked unless:
    1. Current semester fee invoice is marked `paid`.
    2. Overall attendance meets the minimum academic threshold (e.g., ≥ 75%).

---

### Phase 11 — Timetable & Academic Scheduling System

#### 11.1 Infrastructure & Resource Management

- **Rooms & Labs Management:** Room code/name, building, room type (Lecture Hall, Computer Lab, Science Lab, Auditorium), and seated capacity.
- **Time Slot Definitions:** Configurable class periods (e.g., 08:30–10:00, 10:00–11:30, 11:30–01:00) with prayer/lunch break windows.

#### 11.2 Timetable Generation & Allocation

- Map `CourseOffering` to:
    - Day of week (`Monday` to `Saturday`).
    - Time slot.
    - Room/Lab.
    - Teacher.
- Support single lecture slots and contiguous double lab sessions.

#### 11.3 Real-Time Clash Prevention Engine

- Server-side database validation and UI collision prevention:
    - **Teacher Conflict:** A teacher cannot be scheduled in two different rooms at the same time slot.
    - **Room Conflict:** A room cannot host two sections or courses simultaneously.
    - **Section/Student Conflict:** A section cohort cannot have concurrent course lectures.

#### 11.4 User Interfaces

- **Admin / Timetable Coordinator:** Drag-and-drop or interactive grid interface for scheduling by Department, Program, and Section.
- **Teacher Panel:** Personal weekly schedule matrix with 1-click attendance entry links for the active lecture.
- **Student Portal:** "My Weekly Timetable" view showing courses, assigned instructors, and room numbers.

---

### Phase 12 — Learning Management System (LMS Lite) & Class Materials

#### 12.1 Course Study Material & Digital Repository

- Teachers can organize and upload course materials by weekly modules or topic headings (e.g., Week 1: Lecture Slides, Week 2: Reference Code, Week 3: Reading List).
- Support secure storage for PDF documents, slide presentations, assignment guidelines, and external video/reference URLs.
- Student Portal interface with clean categorization, download tracking, and preview options.

#### 12.2 Course Announcements & Class Noticeboard

- Course-specific communication channel where assigned teachers broadcast urgent updates (e.g., room change, quiz postponement, office hour reschedule).
- Targetable to specific course offerings with automatic push to in-app student notification feeds.

#### 12.3 Online Timed Quizzes (MCQs & Short Questions)

- Teacher quiz builder: question bank, multiple-choice options, randomized question ordering, and allotted time limit.
- Student quiz interface with live countdown timer and anti-cheat blur/tab-switch detection.
- Automated grading that directly feeds into the approved `quiz` assessment component budget.

---

### Phase 13 — Bulk Operations & Data Management (Excel / CSV)

#### 13.1 Bulk Student Admissions & Batch Enrolment

- Downloadable Excel/CSV template with standardized columns (Name, Email, CNIC, Phone, Program, Department, Section).
- Validation engine providing row-by-row error reporting (duplicate email detection, invalid section mapping) with transactional rollbacks.
- Automatic generation of linked `User` accounts with secure initial passwords and the `Student` role.

#### 13.2 Bulk Assessment Marks Upload

- Pre-formatted CSV/Excel template exported from the teacher’s active roster containing Student ID, Registration Number, and Name.
- Bulk marks validation ensuring entered scores are numeric, non-negative, and do not exceed the assessment maximum.
- Preview confirmation screen highlighting newly updated scores before final commit.

#### 13.3 Academic Gazette & Operational Exports

- Export semester result gazettes, comprehensive attendance percentage sheets, and department-wise fee recovery reports in XLSX and CSV formats.

---

### Phase 14 — Financial Automation & Online Payment Gateways

#### 14.1 Payment Gateway Integration

- Integrate with online payment providers (e.g., 1Link / Kuickpay, JazzCash, EasyPaisa, Stripe, or direct bank webhooks).
- Automatic invoice reconciliation: instantaneous update to `paid` upon receipt of successful payment IPN/Webhook.
- Elimination of manual slip verification for digital payments while preserving manual upload for physical bank receipts.

#### 14.2 Installment & Fee Concession Plans

- Capability for Accounts Admin to break a semester invoice into 2 or 3 installment vouchers with independent due dates.
- Record scholarships, merit discounts, and financial aid adjustments directly against the student's fee invoice ledger.

#### 14.3 Automated Late Fee Fine Policy

- Configurable grace periods and daily or flat late fee surcharges applied automatically to overdue invoices.

---

### Phase 15 — Quality Enhancement Cell (QEC) & Teacher Evaluation

#### 15.1 Student Evaluation Surveys

- Configurable Likert-scale questionnaire (1 to 5 stars) assessing:
    - Course delivery and syllabus coverage.
    - Instructor punctuality, clarity, and fairness in grading.
    - Availability of lab resources and practical guidance.
- 100% anonymous submissions: ratings and written remarks cannot be traced back to the student's identity.

#### 15.2 Mandatory Completion Gate

- University policy enforcement: Students must complete the QEC evaluation for all enrolled courses before final examination results or grade sheets are unmasked in the portal.

#### 15.3 Analytics & HOD Reports

- Aggregated departmental scorecards, performance radar charts, and trend analysis for Deans and Program Coordinators.

---

### Phase 16 — Degree Clearance & Graduation Audit Workflow

#### 16.1 Automated Degree Audit Engine

- Evaluation of graduation criteria for final-semester students:
    - Total completed credit hours (e.g., 130+ for BS degrees).
    - Core vs. Elective requirements satisfaction.
    - Minimum cumulative GPA requirement (e.g., CGPA ≥ 2.00).
    - No lingering "Fail" or un-cleared backlog courses.

#### 16.2 "No Dues" Digital Clearance System

- Multi-department digital clearance workflow replacing manual paper signature sheets:
    1. **Library Department:** Book return and fine clearance.
    2. **Hostel Administration:** Room clearance and dues check.
    3. **Laboratories:** Equipment return check.
    4. **Sports Directorate:** Equipment return check.
    5. **Accounts Department:** Comprehensive financial ledger clearance.
    6. **HOD / Department Chair:** Final recommendation.
    7. **Examination Branch:** Final transcript and degree issuance clearance.

#### 16.3 Honors & Dean's Honor List

- Automated computation of semester-wise Dean's Honor Roll (e.g., SGPA ≥ 3.50 with full course load).
- Batch graduation rankings, Gold/Silver medalists, and summa/magna cum laude distinctions.

---

### Phase 17 — External Communication & Automated Alerts (Email / SMS)

#### 17.1 Asynchronous Email Notifications (Laravel Queues)

- Welcome emails containing newly created login credentials for admitted students and newly recruited faculty.
- Automated email dispatches upon:
    - Fee invoice generation and approaching due dates.
    - Submission of teacher marks and publishing of official semester results.
    - Leave request approval or rejection.

#### 17.2 Critical SMS / Push Alerts

- SMS gateway integration for high-priority operational dispatches:
    - Emergency university closures or weather alerts.
    - Critical attendance shortage warnings (< 75%) dispatched to students and registered guardians.
    - Final fee deadline alerts before late fine imposition.

---

## 4. Suggested Implementation Phasing Order

To ensure safe, agile, and modular delivery without breaking existing database constraints, implementation should proceed in the following sequence:

| Priority | Phase                    | Core Deliverable                             | Key Benefit                                                |
| :------: | :----------------------- | :------------------------------------------- | :--------------------------------------------------------- |
|  **P0**  | **Test Alignment**       | Fix 4 promotion fee tests                    | Restores 100% green regression test suite                  |
|  **P1**  | **Phase 10: PDF Engine** | Official Transcript & 3-Copy Challan PDFs    | Immediate utility for students, banks, and administration  |
|  **P2**  | **Phase 11: Timetable**  | Room allocation & clash-free scheduling      | Eliminates operational scheduling chaos across departments |
|  **P3**  | **Phase 12: LMS Lite**   | Study materials & course announcements       | Enriches student engagement and teacher course management  |
|  **P4**  | **Phase 13: Bulk Ops**   | CSV/Excel student admissions & bulk marks    | Drastically cuts administrative data-entry workload        |
|  **P5**  | **Phase 15: QEC**        | Anonymous teacher evaluation surveys         | Satisfies HEC and accreditation quality compliance         |
|  **P6**  | **Phase 14: Payments**   | Online payment gateway integration           | Accelerates fee collections and automated reconciliation   |
|  **P7**  | **Phase 16: Clearance**  | Multi-department "No Dues" digital clearance | Streamlines graduation and degree verification             |
|  **P8**  | **Phase 17: Alerts**     | Queued email and SMS notifications           | Professional real-time communication across all channels   |

---

## 5. Architectural Integrity Rules

As with all prior phases, future developments must uphold these fundamental system invariants:

1. **Snapshot Preservation:** Historical semesters, published results, course snapshots, and fee invoices must never mutate retroactively when catalog items, fee structures, or curricula are updated.
2. **Authorization Enforcement:** Access control must be validated at the route and controller policy layer—never purely via hidden UI elements.
3. **Audit Trail Mandate:** Any post-publication corrections, financial adjustments, or administrative overrides must record the actor, timestamp, justification, and before/after values.
4. **Clean Transaction Boundaries:** All multi-table insertions (such as batch admissions, timetable block allocations, and payment clearances) must execute inside database transactions.
