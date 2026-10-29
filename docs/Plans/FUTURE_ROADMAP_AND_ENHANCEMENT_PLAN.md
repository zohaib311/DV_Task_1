# University Management System (UMS) — Future Roadmap & Technical Blueprint

## Purpose

This document is the authoritative technical blueprint and implementation roadmap for the next developmental phases of the University Management System (UMS). It directly continues from:
- **Phases 0–8:** Semester Enrollments, Flexible Assessment Breakdown, and Academic Results Engine.
- **Phases 9A–9H:** Access Control (RBAC), Course Offerings, Teacher Panel, Session Attendance, Multi-Component Assessments, Independent Result Moderation, Student Portal, Operational Hardening, and Auditing.
- **Phases 9I–9M:** Student Submissions, Course Self-Registration, Student Leaves, Fee Structure & Invoices, Fee-Gated Promotions, and Official Student Transcripts.

Every phase defined herein specifies:
1. **Scope & High-Level Checklists (`- [ ]`)**
2. **Database Schema & Migrations (Tables, Columns, Indices, Foreign Keys)**
3. **Roles & Granular Permissions**
4. **Service-Layer Business Rules & Invariants**
5. **Controllers, Routes & Middleware Specifications**
6. **User Interface / Blade Specifications**
7. **Phase-Specific Manual Acceptance Checklist**
8. **Automated Test Coverage Requirements**

---

## 1. System Invariants & Non-Negotiable Rules

Any future phase implemented under this roadmap **must preserve** the following non-negotiable principles:

1. **Snapshot Immutability:** Once a semester enrollment, assessment submission, published result, or fee invoice is completed or published, its frozen snapshot must never change due to future edits in the course catalog, fee structures, or curricula.
2. **Strict Server-Side Authorization:** Hiding buttons or navigation links in Blade views is insufficient. Every route and mutation endpoint must enforce permissions and model ownership at the controller and policy level.
3. **Comprehensive Audit Trails:** Any administrative override, result correction, or financial recalculation must record the acting user ID, precise timestamp, justification reason, and before/after values.
4. **Atomic Transactions:** Any multi-table update (such as batch enrollment, timetable block allocation, or payment reconciliation) must execute inside database transactions (`DB::transaction`).

---

## 2. Immediate Housekeeping: P0 — Test Suite Alignment

### Objective
Restore 100% green status across the automated regression test suite (138 tests) following the introduction of the fee clearance requirement during student promotion (`commit 008062a`).

### Root Cause
Recent updates in `AcademicEnrollmentService::promotionEligibility()` block promotion if `config('academic.promotion.require_approved_fee')` is active and the enrollment lacks an approved/paid invoice. Pre-existing test cases in `tests/Feature/Result/ResultModerationTest.php` and `tests/Feature/AcademicSetupTest.php` do not create fee invoices during test fixture setup, causing 4 promotion eligibility assertions to fail.

### Implementation Checklist
- [x] In `tests/Feature/Result/ResultModerationTest.php`, ensure test enrollments undergoing promotion testing either generate a paid invoice or mock fee clearance.
- [x] In `tests/Feature/AcademicSetupTest.php`, attach a paid `StudentFeeInvoice` to the promoted test student.
- [x] Run `php artisan test` and confirm all 138 tests pass with zero failures.

---

## 3. Detailed Future Phases Specification

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

### Phase 10 — Examination & Financial Document PDF Engine *(Completed ✅)*

#### Scope & Deliverables
- [x] Install and configure PDF rendering service (`barryvdh/laravel-dompdf` v3.1.2) and QR Code generator (`simplesoftwareio/simple-qrcode` v4.2.0).
- [x] Official Semester Transcript / DMC (Detailed Marks Certificate) with university header, background watermark, Controller signatures, and dynamic inline Base64 SVG QR verification.
- [x] Three-copy Bank Fee Challan PDF in A4 landscape format (Bank Copy, Accounts Copy, Student Copy) with bank account particulars.
- [x] Examination Admit Card / Roll Number Slip PDF with dual clearance gates: 100% fee invoice approval and minimum attendance threshold (≥ 75%).
- [x] Public tamper-proof document verification endpoint: `/verify/document/{type}/{hash}` supporting transcripts, fee challans, and admit cards.

#### Database Schema & Model Updates
- Added `verification_hash` (`string(64)`, nullable, unique, indexed) to `semester_results` and `student_fee_invoices` via migration `2026_11_01_000000_add_verification_hash_to_results_and_invoices.php`.
- Added permission `student.admit-card.view-own` via migration `2026_11_01_000001_add_admit_card_permission.php`.
- Added auto-hash generation on model creation in `SemesterResult` and `StudentFeeInvoice`.
- Added configuration in `config/fees.php` for university meta, banking accounts, and admit card thresholds.

#### Permissions & Routes
- `student.result.view-own` -> `GET /student-portal/transcript/pdf`
- `student.fees.view-own` -> `GET /student-portal/fees/{invoice}/challan-pdf`
- `student.admit-card.view-own` -> `GET /student-portal/admit-card`, `GET /student-portal/admit-card/pdf`
- `results.view-all` -> `GET /results/{result}/transcript-pdf`
- Public / No Auth -> `GET /verify/document/{type}/{hash}`

#### Manual Acceptance Checklist
1. Sign in as a Student with a published result. Navigate to **Student Portal → My Transcript** and click **Download Official PDF**.
2. Verify PDF contains university logo, watermark, course breakdowns, SGPA/CGPA, and a visible QR code.
3. Scan the QR code; verify it opens `/verify/document/transcript/{hash}` displaying authenticated student and result details.
4. Navigate to **Fees & Payments**, select an invoice, and click **Download Bank Challan**. Verify a 3-part perforated sheet renders with all bank deposit lines.
5. If student attendance is < 75% or fee invoice is unpaid, verify **Download Admit Card** is blocked with an actionable alert.

#### Automated Test Coverage
- `tests/Feature/Documents/DocumentPdfGenerationTest.php` (8 tests, 33 assertions — **100% PASS**):
  - `test_student_can_download_official_transcript_pdf()`
  - `test_student_can_download_fee_challan_pdf()`
  - `test_student_cannot_download_another_students_challan()`
  - `test_admin_can_download_semester_result_transcript_pdf()`
  - `test_public_verification_endpoint_resolves_valid_transcript()`
  - `test_public_verification_endpoint_resolves_valid_challan()`
  - `test_public_verification_endpoint_rejects_tampered_hash()`
  - `test_admit_card_view_and_download_flow_with_clearance_gates()`

---

### Phase 11 — Timetable & Academic Scheduling System

#### Scope & Deliverables
- [ ] Physical infrastructure management: Rooms, Computer Labs, Science Labs, Auditoriums with capacities.
- [ ] Time slot matrix per academic term (class periods and prayer/lunch break windows).
- [ ] Course offering timetable allocations (mapping offering, room, slot, day, and teacher).
- [ ] Real-time server-side clash prevention engine (Room, Teacher, and Student Section conflicts).
- [ ] Interactive weekly scheduling grid for Admin, personal schedule for Teachers, and "My Timetable" for Students.

#### Database Schema

##### Table: `rooms`
- `id` (bigIncrements)
- `name` (string, e.g. "Lab 3")
- `code` (string, unique, e.g. "CS-LAB-03")
- `building` (string, nullable)
- `type` (enum: `lecture_hall`, `computer_lab`, `science_lab`, `auditorium`)
- `capacity` (unsigned integer)
- `is_active` (boolean, default true)

##### Table: `time_slots`
- `id` (bigIncrements)
- `academic_term_id` (foreignId constrained)
- `day_of_week` (enum: `Monday`, `Tuesday`, `Wednesday`, `Thursday`, `Friday`, `Saturday`)
- `start_time` (time)
- `end_time` (time)
- `slot_type` (enum: `regular`, `lab_double`, `break`)

##### Table: `timetable_allocations`
- `id` (bigIncrements)
- `course_offering_id` (foreignId constrained)
- `room_id` (foreignId constrained)
- `time_slot_id` (foreignId constrained)
- `teacher_id` (foreignId constrained)
- `created_by` (foreignId constrained to `users`)
- *Unique Constraints:*
  - `[room_id, time_slot_id]` -> Prevents room double-booking.
  - `[teacher_id, time_slot_id]` -> Prevents teacher schedule conflict.
  - `[course_offering_id, time_slot_id]` -> Prevents section cohort clash.

#### Clash Prevention Business Rules
`TimetableService::allocate()` evaluates:
1. Is the room already occupied at this time slot? -> Reject with conflict message.
2. Is the teacher assigned to another class at this time slot? -> Reject.
3. Does this section already have another lecture scheduled at this time slot? -> Reject.

#### Permissions & Routes
- `timetable.manage` -> `GET /academic/timetable`, `POST /academic/timetable/allocations`, `DELETE /academic/timetable/allocations/{id}`
- `offerings.view-assigned` -> `GET /teaching/timetable`
- `student.courses.view-own` -> `GET /student-portal/timetable`

#### Manual Acceptance Checklist
1. As Academic Admin, create Room "CS-101" (capacity 50) and Time Slot "Monday 08:30-10:00".
2. Allocate Course Offering "CS101 - Section A" with Teacher 1 in CS-101.
3. Try allocating another class to CS-101 at the same slot; verify system rejects with "Room is already booked".
4. Try assigning Teacher 1 to another room at the same slot; verify system rejects with "Teacher has a schedule conflict".
5. Sign in as Teacher 1; verify "My Weekly Schedule" displays the lecture with a 1-click link to mark attendance.
6. Sign in as an enrolled Student; verify "My Timetable" displays the class, room, and timings.

#### Automated Test Coverage
- `tests/Feature/Academic/TimetableSchedulingTest.php`:
  - `test_admin_can_allocate_valid_class_slot()`
  - `test_room_clash_is_rejected()`
  - `test_teacher_clash_is_rejected()`
  - `test_cohort_section_clash_is_rejected()`
  - `test_student_and_teacher_see_their_own_schedule()`

---

### Phase 12 — Learning Management System (LMS Lite) & Class Materials

#### Scope & Deliverables
- [ ] Weekly course materials repository (Lecture slides, reading PDFs, assignments, external links).
- [ ] Course-specific announcements and noticeboard with student notifications.
- [ ] Online timed objective quizzes (MCQs) with auto-grading directly feeding assessment component marks.

#### Database Schema
- `course_materials`: `[id, course_offering_id, title, week_number, description, file_path, file_type, is_published]`
- `course_announcements`: `[id, course_offering_id, title, content, priority (normal/urgent), created_by]`
- `quizzes`: `[id, course_offering_id, assessment_id, title, duration_minutes, starts_at, ends_at, is_published]`
- `quiz_questions`: `[id, quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, points]`
- `student_quiz_attempts`: `[id, quiz_id, student_id, started_at, completed_at, score_obtained, status]`

#### Integration Business Logic
- When a student finishes an MCQ quiz attempt or timer reaches 00:00, score is calculated automatically.
- Score is synced to `assessment_marks` for that student and course assessment component.

#### Manual Acceptance Checklist
1. Teacher uploads "Week 1 - Introduction Slides (PDF)" to an assigned offering.
2. Enrolled student opens course portal and downloads the file; un-enrolled student is denied access.
3. Teacher publishes an Urgent Announcement; student receives an in-app notification.
4. Teacher creates a 10-minute 5-question MCQ quiz.
5. Student starts the quiz; timer counts down; answers are submitted; score auto-grades and populates the assessment gradebook.

#### Automated Test Coverage
- `tests/Feature/Teaching/CourseLmsTest.php`
- `tests/Feature/StudentPortal/OnlineQuizTest.php`

---

### Phase 13 — Bulk Operations & Batch Data Management (Excel / CSV)

#### Scope & Deliverables
- [ ] Fast, transactional CSV/Excel bulk student admission with row-by-row error reporting.
- [ ] Auto-creation of linked User accounts, profile generation, and role assignment.
- [ ] Teacher bulk marks upload via standardized CSV template.
- [ ] Institutional Result Gazette and Term-wise Fee Collection export.

#### Technical Safeguards
- Chunked processing (100 rows per batch) wrapped in `DB::transaction`.
- Format validation with detailed downloadable failure error log.

#### Manual Acceptance Checklist
1. Admin downloads the Sample Student Import CSV template.
2. Populates 5 student rows (including 1 with an invalid email and 1 with a duplicate CNIC).
3. Uploads file: System shows a preview identifying the 2 invalid rows with clear errors while importing valid rows or rolling back gracefully.
4. Teacher downloads the class marks CSV, inputs scores, and re-uploads; preview table highlights changes before confirming.

#### Automated Test Coverage
- `tests/Feature/Academic/BulkStudentImportTest.php`
- `tests/Feature/Teaching/BulkMarksUploadTest.php`

---

### Phase 14 — Financial Automation & Online Payment Gateways

#### Scope & Deliverables
- [ ] Online payment gateway integration (Stripe / JazzCash / 1Link sandbox).
- [ ] Instant invoice reconciliation via secure webhook handlers (`payment_transactions`).
- [ ] Fee installment plans (splitting semester fee into 2 or 3 vouchers with independent due dates).
- [ ] Automated late fee fine engine executed via daily scheduled Artisan command.

#### Schema
- `fee_installments`: `[id, student_fee_invoice_id, installment_no, amount, due_date, status, paid_at]`
- `payment_transactions`: `[id, student_fee_invoice_id, gateway, transaction_ref, amount, payload, status]`

#### Manual Acceptance Checklist
1. Accounts Admin configures 2 installments for a student's semester invoice.
2. Student views two separate installment vouchers with independent due dates.
3. Student completes sandbox payment via payment gateway.
4. Webhook executes; installment status updates to `paid` immediately without manual admin approval.
5. Artisan command `fees:apply-late-fines` runs; invoices past due date + grace period receive configured late fee surcharge.

#### Automated Test Coverage
- `tests/Feature/Fee/PaymentGatewayWebhookTest.php`
- `tests/Feature/Fee/FeeInstallmentWorkflowTest.php`

---

### Phase 15 — Quality Enhancement Cell (QEC) & Teacher Evaluation

#### Scope & Deliverables
- [ ] 100% anonymous student evaluation surveys (5-point Likert scale) assessing course content, teacher punctuality, and grading fairness.
- [ ] Mandatory gate middleware: Students must complete evaluations for active courses before viewing published semester results.
- [ ] HOD and Dean Quality Analytics Dashboard with performance radar charts.

#### Schema & Privacy Mandate
- `qec_surveys`: `[id, academic_term_id, title, is_active, starts_at, ends_at]`
- `qec_questions`: `[id, qec_survey_id, question_text, category]`
- `qec_responses`: `[id, qec_survey_id, course_offering_id, question_id, rating_score, feedback_comment]` (*NO student_id is stored*).
- `qec_student_completions`: `[id, student_id, course_offering_id, completed_at]` (*Tracks completion status only*).

#### Manual Acceptance Checklist
1. Activate QEC evaluation survey for the term.
2. Student attempts to navigate to **Student Portal → Results & History**; middleware intercepts and redirects to course survey.
3. Student completes survey; answers are stored anonymously; completion is logged.
4. Student is redirected to results page with unmasked grades.
5. HOD signs in and views anonymous department-wide teacher scorecards.

#### Automated Test Coverage
- `tests/Feature/Qec/TeacherEvaluationTest.php`

---

### Phase 16 — Degree Clearance & Graduation Audit Workflow

#### Scope & Deliverables
- [ ] Automated Degree Audit Calculation Service (Total 130+ credits, all required curriculum courses cleared, CGPA ≥ 2.0, no failed backlogs).
- [ ] Multi-department digital "No Dues" clearance workflow (`Library`, `Hostel`, `Accounts`, `Sports`, `HOD`, `Examination`).
- [ ] Automated Dean's Honor Roll and batch graduation rankings calculation.

#### Schema
- `graduation_clearances`: `[id, student_id, status, submitted_at, completed_at]`
- `department_clearance_signoffs`: `[id, graduation_clearance_id, department_name, status, remarks, signed_by, signed_at]`

#### Manual Acceptance Checklist
1. Final semester student requests Graduation Clearance.
2. Degree Audit Service validates that student meets degree criteria.
3. Respective department heads (Library, Accounts, Hostel, Sports, HOD) approve digital sign-offs.
4. Examination branch issues final degree clearance; status displays on student transcript.

#### Automated Test Coverage
- `tests/Feature/Graduation/DegreeAuditAndClearanceTest.php`

---

### Phase 17 — External Communication & Automated Alerts (Email / SMS)

#### Scope & Deliverables
- [ ] Background queued email dispatches via Laravel Queues (`php artisan queue:work`).
- [ ] Welcome email with credentials for newly enrolled students and hired faculty.
- [ ] Automated fee invoice generation emails with PDF challan attached.
- [ ] Published semester result alerts.
- [ ] High-priority SMS alerts for critical low attendance (< 75%) and impending fee deadlines.

#### Manual Acceptance Checklist
1. Create new student; verify `StudentAdmissionWelcomeMail` queues and delivers with temporary login credentials.
2. Publish semester result; verify student receives result summary email with link to portal.
3. Run attendance warning job; students with attendance below 75% receive warning SMS and email notifications.

#### Automated Test Coverage
- `tests/Feature/Notifications/ExternalCommunicationTest.php`

---

## 4. Master Phasing & Execution Order

| Phase | Priority | Primary Deliverable | Target Timeline |
|:---:|:---:|:---|:---:|
| **P0** | **Immediate** | Fix 4 promotion fee tests & verify 138/138 green tests | Day 1 (15m) |
| **Phase 10** | **P1** | Official Transcript PDF + QR, 3-Copy Fee Challan, Admit Card | Days 1–2 |
| **Phase 11** | **P2** | Rooms, Time slots, Clash prevention, Weekly grid views | Days 3–4 |
| **Phase 12** | **P3** | Weekly course resources, Class notices, Timed MCQ quizzes | Days 5–6 |
| **Phase 13** | **P4** | Excel/CSV student bulk import, Teacher marks upload template | Day 7 |
| **Phase 14** | **P5** | Online payment integration, Installment vouchers, Late fine | Days 8–9 |
| **Phase 15** | **P6** | Anonymous teacher surveys, Result-viewing gate, HOD radar charts | Day 10 |
| **Phase 16** | **P7** | Degree audit engine, Multi-department "No Dues" digital signoff | Days 11–12 |
| **Phase 17** | **P8** | Queued email notifications (PDF attachments) & SMS alerts | Day 13 |

---

## 5. Architectural Verification & Acceptance Criteria

Before any phase is marked as completed in this document:
1. All database migrations must include rollback (`down()`) support and foreign key constraints.
2. All feature routes must be protected by explicit database-backed permissions.
3. All feature and unit tests must execute and pass cleanly under `php artisan test`.
4. Browser visual acceptance must be tested across mobile and desktop viewport widths.
