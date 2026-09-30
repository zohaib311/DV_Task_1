# Professional University Management Workflow

## Purpose

This document is the implementation blueprint for evolving the existing project into a professional, semester-based University Management System. It preserves completed Phases 0–8 and defines the next modules in a safe delivery order.

All application text, validation messages, notifications, and portal content must remain in professional English.

---

## 1. Existing foundation

The following parts already exist and must be preserved:

- Student profiles with department and section.
- Course definitions with credit hours and assessment schemes.
- Semester-wise enrollment and historical course snapshots.
- Semester result records, GPA, SGPA, CGPA, grading, and audit snapshots.
- Result entry/edit, academic history, result sheet, and promotion workflow.
- Promotion requiring a published passing result by default.

The legacy single-course results table is read-only history. New work must use semester enrollments, course offerings, assessments, and semester results.

---

## 2. Target academic structure

    University
      Department
        Section
          Academic Year / Term
            Semester Curriculum
              Course Offering
                Assigned Teacher(s)
                Enrolled Students
                Attendance Sessions
                Assessments
                Submitted / Published Results

| Record | Purpose |
|---|---|
| Course | Permanent catalog item, for example CS101 Introduction to Computing. |
| Semester curriculum | Defines catalog courses for a department/program and semester. |
| Course offering | Live delivery for an academic year, semester, section, and teacher. |
| Student semester enrollment | A student historical placement in a semester. |
| Student enrollment course | A snapshot of courses assigned to one student semester. |
| Assessment | Assignment, quiz, midterm, final, practical, or another assessed activity. |
| Attendance session | A dated lecture/lab session recorded by a teacher. |

Future curriculum, teacher, course, or marking-scheme changes must never alter completed student semesters.

---

## 3. Users, roles, and permissions

Every person who logs in uses a User account:

- Each teacher must be linked to one User account.
- Each student must be linked to one User account.
- Administrators manage roles and permissions from the Admin panel.

Use database-backed Laravel roles and permissions. Permissions are not hard-coded in Blade views.

### Default roles

| Role | Scope |
|---|---|
| Super Admin | Full configuration and role/permission management. |
| Academic Admin | Courses, curriculum, enrollments, promotions, result approval/publishing. |
| HOD / Program Coordinator | Department-scoped curriculum, offerings, teacher assignments, academic review. |
| Teacher | Only assigned course offerings, attendance, assessments, marks, and submissions. |
| Student | Only their profile, enrollment, attendance, assessments, and published results. |

### Permission groups

| Group | Examples |
|---|---|
| Access management | roles.view, roles.manage, permissions.assign, users.assign-role |
| Academic catalog | departments.manage, sections.manage, courses.manage, curriculum.manage, terms.manage |
| Enrollment | enrollments.view, enrollments.create, enrollments.promote, enrollments.manage-courses |
| Teaching | offerings.view-assigned, attendance.manage-assigned, assessments.manage-assigned, marks.manage-assigned |
| Results | results.view-all, results.submit, results.approve, results.publish, results.edit-published |
| Student portal | student.profile.view-own, student.attendance.view-own, student.result.view-own |
| Audit | audit.view, academic-history.view |

### Authorization rules

- Admin assigns roles to users and permissions to roles.
- A teacher accesses only a course offering explicitly assigned to them.
- A student accesses only records connected to their own student account.
- Published result edits require the results.edit-published permission.
- Controller policies enforce access; hiding a button is never enough.

---

## 4. End-to-end university workflow

### A. Academic setup by Admin / Academic Admin

1. Create Departments and Sections.
2. Create Academic Years and Terms/Semesters.
3. Maintain the Course Catalog.
4. Define a semester curriculum: department/program, semester, required/elective courses, credit hours, and approved assessment scheme.
5. Create course offerings for the active academic year, semester, and section.
6. Assign one or more teachers to each offering.

### B. Student admission and enrollment

1. Admin creates the student profile and student login.
2. Admin creates the first semester enrollment.
3. System loads approved semester curriculum.
4. Admin confirms required, elective, and repeat courses.
5. Enrollment stores snapshots of course, credit-hour, and assessment data.
6. Student portal displays the active semester and registered offerings.

### C. Teacher academic workflow

1. Teacher sees only assigned course offerings.
2. Teacher creates attendance sessions for each class date.
3. Teacher marks each student Present, Absent, Late, or Excused.
4. Teacher creates assignments, quizzes, midterms, finals, and optional practical/project assessments.
5. Teacher enters marks only within approved maximum values.
6. Teacher submits course marks for review.
7. Teacher cannot publish a final semester result unless explicitly permitted.

### D. Result approval and publication

1. System aggregates approved teacher assessment marks per student/course.
2. Attendance marks are calculated from attendance policy or authorized manual override.
3. Course percentage, grade, grade point, and pass/fail are server-calculated.
4. Teacher submits course results.
5. Academic Admin/HOD reviews incomplete marks and failures.
6. Authorized user publishes the semester result.
7. System calculates SGPA and CGPA and preserves history.
8. Student portal exposes only published results.

### E. Promotion

1. Admin selects active enrollment.
2. System confirms published result under promotion policy.
3. Admin selects next-semester approved courses.
4. Repeat/improvement courses are selected explicitly.
5. New enrollment becomes Active; old enrollment becomes Promoted.
6. Previous enrollment, attendance, assessments, and results remain immutable history.

---

## 5. Required academic states

### Enrollment

    active → completed / promoted
    active → withdrawn

- Only one active enrollment per student.
- A promoted enrollment cannot be directly edited or deleted.
- Completed/published records are historical snapshots.

### Course offering

    planned → active → marks_submitted → reviewed → completed

### Semester result

    draft → submitted → approved → published
    published → corrected (audited only, with permission)

Draft, Pass, and Fail remain calculated academic outcomes. Submitted and approved are workflow statuses stored separately.

---

## 6. Attendance workflow

### Required data

- Attendance sessions: course offering, date, session type, teacher.
- Attendance records: attendance session, student enrollment, Present/Absent/Late/Excused status, optional note.

### Rules

- Teachers manage attendance only for assigned offerings.
- One attendance record per student per session.
- Attendance percentage is calculated from valid sessions.
- Course policy converts attendance percentage into marks.
- Manual overrides are audited and restricted.
- Published result attendance snapshots never change after later attendance edits.

---

## 7. Assessment and marks workflow

The current Attendance + Midterm + Final model remains a safe bridge. The professional model must allow configurable components:

| Component | Example |
|---|---:|
| Attendance | 10 |
| Assignment | 10 |
| Quiz | 10 |
| Midterm | 30 |
| Final | 40 |
| Practical / Project | Optional |

The total equals the course total, normally 100. A curriculum/offering stores the approved scheme snapshot. Multiple assignments or quizzes are summed within their approved component allocation.

Required records:

- Assessment component configuration
- Individual assessment definition
- Student assessment mark
- Moderation/submission status
- Historical marks snapshot in the published result item

---

## 8. Panels

### Admin Panel

- Manage users, roles, permissions, teachers, students, departments, and sections.
- Create academic years, curriculum, and course offerings.
- Assign teachers.
- Manage enrollments and promotions.
- Review, publish, and correct results.
- View attendance, academic history, teacher workload, audits, and reports.

### Teacher Panel

- Assigned course offerings.
- Student roster.
- Attendance sessions and entry.
- Assessment creation and mark entry.
- Course-result preview and submission.
- Read-only published history for assigned offerings.

### Student Portal

- Profile and current enrollment.
- Registered courses and teachers.
- Attendance percentage/details.
- Released assessment marks.
- Published semester result sheet, SGPA, CGPA, and history.
- Enrollment and result notifications.

---

## 9. Delivery roadmap

### Phase 9A — Access control foundation (Completed)

- Database-backed roles and permissions, default-role seeding, and Super Admin gate bypass are in place.
- Student and teacher profiles can be linked to one User account; existing profiles are matched by email during seeding where safe.
- The Admin Role & Permission screen supports role creation, permission assignment, and user-role assignment.
- Existing administrative routes, result policies, and sidebar navigation enforce permissions server-side.

### Phase 9B — Academic term, curriculum, and offering (Completed)

- [x] Academic Year/Term models, migrations, administration screens, date validation, and term lifecycle guards.
- [x] Department-semester curriculum versions with required/elective courses, saved credit/assessment snapshots, and approval locking.
- [x] Section-specific course offerings linked to a term, department, curriculum course, and one or more login-linked teachers.
- [x] Existing enrollment and promotion screens connected to active offerings, with server-side placement checks and student course snapshots.

Implementation notes:

- The sidebar now includes **Academic Terms**, **Semester Curriculum**, and **Course Offerings**. Management routes require `terms.manage`, `curriculum.manage`, or `offerings.manage`; Academic Admin receives these permissions, and Super Admin retains its existing bypass. These are global administration permissions, not department-scoped HOD access.
- New enrollments require an active term, an approved curriculum in the student's department, and active teacher-assigned offerings in the student's section. Every required course must be available and selected. Electives are optional; out-of-curriculum repeat/improvement courses require a previous published attempt.
- Enrollment stores the offering link plus course name/code, credit hours, assessment maxima, and registration type. Result entry and calculations use these saved values. Catalog edits do not rewrite the new enrollment snapshots.
- Promotions keep the existing published-pass policy, use the next semester curriculum, and require a later teaching term when the previous enrollment has a term. Transactions prevent two concurrent active enrollments for the same student.
- Existing enrollments/results are preserved without guessed term or teacher assignments. Older enrollments remain marked **Historical enrollment** until explicitly migrated in a future, separately reviewed backfill.
- Approved curricula are read-only; create a new version for future changes. Planned offerings can be revised, but activation locks their scheme and teaching assignments. Used courses and assigned teachers cannot be deleted through their existing screens.
- A term's identity/dates cannot change after offerings exist. Closed terms reject changes and new enrollment. Terms with unfinished offerings cannot close; offering completion/publication belongs to the later result lifecycle work.
- The current Attendance + Midterm + Final scheme remains unchanged. Flexible assignments/quizzes belong to 9E; Teacher Panel to 9C; attendance to 9D; moderation to 9F; Student Portal to 9G.
- Re-running the access-control seeder preserves existing role permission customizations instead of restoring every default permission.

#### Phase 9B manual acceptance checklist

Use an Academic Admin or Super Admin login. Start with test records; do not delete real academic history.

1. **Prerequisites:** Create a department, a section in it, and active catalog courses with valid credit hours and assessment totals. Link each teacher profile to a User account in the existing teacher form. The old teacher `course` text field is not a teaching assignment; offerings are the assignment source.
2. **Academic Terms:** Add year `2026-2027`, dates `2026-08-01` to `2027-07-31`. Add active term `Fall 2026`, dates `2026-09-01` to `2026-12-31`. Dates outside the year and duplicate term names in that year must show validation errors.
3. **Semester Curriculum:** Create a Semester 1 version for the department, select courses, optionally mark electives, and save the draft. Review before approving; approval uses the saved draft and locks it. A subsequent catalog edit must not change the approved snapshot.
4. **Course Offerings:** Choose the active term, matching section, approved curriculum course, and linked teacher(s). Create an active offering for every required course. Duplicate term/section/course combinations and department mismatches must fail. A planned offering can be edited before activation.
5. **Enrollment:** In the existing New Enrollment form, select a student in that department/section, the active term, and approved curriculum. Check that the correct courses, teacher names, credit hours, and assessment maxima load. Save and verify the term and course/teacher details on the enrollment list.
6. **Validation:** Missing required offerings/courses, wrong-section selections, an inactive term/offering, or a second active enrollment must be rejected. Electives may be omitted. Repeat/improvement courses require a prior published attempt and an eligible offering in the selected term/section.
7. **Results:** Use the existing result drawer on the new enrollment. Save/publish a passing result. Verify saved course names, codes, and assessment maxima remain unchanged even after editing the catalog.
8. **Promotion:** Create a later active term, approved Semester 2 curriculum, and its active offerings. Promote the passing enrollment through its existing action. Verify the previous record is preserved and only the new enrollment is active. Failed/unpublished results, the wrong next semester, and an earlier/equal term must be blocked.
9. **Permissions/history:** Teacher and Student default roles must not open these academic-admin routes. Existing historical results must remain accessible through their existing authorized screens. Removing a role permission and re-running the seeder must not silently restore it.

Automated coverage: `tests/Feature/AcademicSetupTest.php` and the updated `StudentSemesterEnrollmentTest.php` cover academic setup, enrollment, promotion, snapshot stability, deletion protection, and access restrictions. Run `php artisan test` for the complete regression suite.

### Phase 9C — Teacher Panel (Completed)

- [x] Teacher dashboard with assigned/active offering totals, distinct student count across assigned terms, and an active-first class overview.
- [x] Searchable, paginated assigned offering list with scoped term/status filters and read-only student rosters.
- [x] Server-side permission, linked-profile, and offering-ownership checks with teacher-specific authorization tests.

Implementation details:

- Teachers land at `/teaching` after sign-in. Existing generic dashboard links redirect teaching users there; administrators retain their existing dashboard.
- Access uses the existing `offerings.view-assigned` permission, a linked teacher/User profile, and the offering's teacher assignments. Custom roles work through permissions; a role name alone does not grant access.
- Every offering lookup is assignment-scoped, including searches and direct URLs. An unassigned/nonexistent offering returns 404. Even a Super Admin using this personal workspace must have a linked teacher profile and assignment; global administration stays under Academic Setup.
- An unlinked account sees a styled setup-required screen (403). Linked teachers without assignments see an empty-state guide. Revoking the permission or removing an assignment removes access.
- Rosters use the exact offering-to-enrollment-course link, retain promoted/completed enrollment history, and show name, registration number, semester, registration type, and enrollment status. Private contact details and semester-wide marks are not exposed.
- Multiple assigned teachers can view the same roster. Counts and term choices use only the current teacher's assigned offerings. Assessment maxima are displayed from the offering snapshot, not the editable catalog.
- Sidebar navigation groups **Teacher Panel → Overview / My Courses & Rosters** and **Academic Setup → Academic Terms / Semester Curriculum / Course Offerings**. Accessible native dropdowns open on the active section and respect permissions; no unfinished attendance/marks buttons are displayed.
- UI reuses the existing purple academic shell with one main panel, a compact summary strip, responsive tables, filters, pagination, and English messages.
- Code is grouped in `app/Http/Controllers/Teaching`, `app/Services/Teaching`, `routes/teaching.php`, `resources/views/teaching` (layouts, offerings, reusable partials), `resources/views/layouts/partials` (sidebar groups), `public/css/teaching`, and `tests/Feature/Teaching`.
- No new database tables or default permission changes are required. Existing Phase 9B records and administrator-customized roles remain unchanged. Attendance, assessments, and result submission are not part of this phase.

#### Phase 9C manual acceptance checklist

1. As Admin, link a teacher profile to the intended User account. Give that account the Teacher role (or a custom role with `offerings.view-assigned`).
2. In Academic Setup, assign that teacher to an offering and enroll students through the existing enrollment workflow. For a locked active offering, retain its existing teacher assignment; use a planned/new offering for a new setup.
3. Sign in as that teacher. Check the overview totals and Teacher Panel dropdown. Open My Courses & Rosters and filter by name/code, term, and status.
4. Open a class roster. Check its department/section, term, assigned teachers, saved assessment scheme, student names/registration numbers, and enrollment statuses. Search a registration number; clear the search. Lists paginate when there are enough rows.
5. Sign in as another teacher. The first teacher's unassigned offering must not appear; its direct `/teaching/offerings/{id}` URL must return 404. Co-assigned teachers should both see their shared class.
6. Check an account with no linked teacher profile (setup-required screen), a linked teacher without assignments (empty state), and a Student/default Academic Admin account (403 on teacher routes).
7. Remove `offerings.view-assigned` in Access Control and retry: access must be denied. The default Teacher role must remain unable to open admin enrollment/result-management pages or publish results.
8. Check the sidebar dropdown with keyboard and at mobile width. The current group stays open on navigation; Academic Setup children appear only for their respective permissions.

Automated coverage: `tests/Feature/Teaching/TeacherPanelTest.php`. Run `php artisan test` for the full regression suite. Browser visual acceptance remains a manual check.

### Phase 9D — Attendance (Completed)

- [x] Offering-based attendance sessions, per-enrollment-course records, and immutable audit entries.
- [x] Assigned teacher entry for Present, Absent, Late, and Excused, with optional private notes.
- [x] Student-owned attendance summary and session history, protected against other-student access.
- [x] Server-calculated percentage and attendance marks using each enrollment course's saved attendance maximum.

Implementation and policy:

- Teacher Panel now includes **Attendance**; each class roster also links to its attendance workspace. Student Portal includes **My Attendance** (attendance only; the full portal remains Phase 9G).
- Routes, controllers, services, models, views, and tests are grouped under attendance-specific folders/files. Existing purple academic layouts and sidebar dropdowns are reused.
- A session is identified by offering, class date, type (lecture/lab/tutorial), and slot (1–20). A second genuine lecture on the same day uses another slot; duplicate combinations are blocked. Dates must be within the term and not in the future.
- Creating a session snapshots its eligible roster: active, unpublished enrollments registered on/before that date. New students do not get silently added to an earlier session. Empty rosters are rejected.
- New sessions are drafts and start with all statuses unmarked. Every saved roster entry must have an explicit valid status before the session becomes completed. Missing, foreign, or duplicated submitted records are rejected transactionally.
- Only completed sessions count. Cancellation requires a reason, retains all records/audits, excludes the session from totals, and cannot be undone. Class dates/types/rosters cannot be rewritten after creation.
- Configurable defaults in `config/attendance.php`: Present = 1 credit, Late = 0.5, Absent = 0, Excused excluded. These are implementation defaults, not a claimed official university policy; confirm them before live academic use. The policy is snapshotted on each offering's first session and is not editable through teacher forms.
- Percentage = attendance credits / counted sessions × 100. Marks = credits / counted sessions × the enrollment course's saved attendance allocation, rounded to two decimals. No counted sessions (including all-excused sessions) displays N/A instead of zero.
- Teachers with `offerings.view-assigned` AND `attendance.manage-assigned`, a linked teacher profile, and an explicit offering assignment can manage attendance. Co-teachers share access. Corrections to completed sessions require a reason and preserve before/after records plus the acting user in the audit. Revision checks reject stale submissions.
- Non-active offerings/terms and sessions containing promoted/completed/withdrawn/published enrollments are read-only. Existing published semester result snapshots are never recalculated by attendance edits. Students with academic enrollment history cannot be deleted through the existing student screen.
- Student access requires `student.attendance.view-own` and a linked student profile. Only that student's completed attendance records are exposed; drafts, cancellations, teacher notes, audits, and other students' records are excluded.
- Attendance totals are currently **live calculated previews**, not an automatic replacement for the existing manual result drawer. Flexible assessment entry is 9E; aggregating teacher attendance/marks into moderated final results is 9F. No unrestricted manual override of the new calculated attendance totals is provided; corrections use the audited session workflow.
- Existing roles and passwords are not reset. The standard Teacher role already includes attendance permission. The earlier **Demo Teacher (9C)** role intentionally has fewer permissions: explicitly enable `attendance.manage-assigned` in Access Control to test attendance with those demo logins.

Manual acceptance checklist: [Phase 9D testing guide](docs/testing/PHASE_9D_ATTENDANCE_GUIDE.md). Automated tests: `tests/Feature/Attendance/AttendanceWorkflowTest.php`. Browser visual acceptance remains manual.

### Phase 9E — Assessments and teacher marks (Completed)

- [x] Admin-approved offering-level component allocations: attendance, assignment, quiz, midterm, final, practical, and project.
- [x] Individual assessment definitions, dates, raw maxima, and contribution weights within approved budgets.
- [x] Assigned-teacher mark entry, incomplete/zero distinction, range/roster validation, optimistic revision checks, and correction audits.
- [x] Course marks previews, immutable submission snapshots, reviewer return/approval, and editing locks.

Implementation details:

- **Academic Setup → Course Offerings → Assessment scheme** configures each offering's component allocation. Allocations must sum exactly to the saved course total; attendance retains the existing saved attendance maximum so Phase 9D/enrollment snapshots remain consistent. Zero disables a component. Approval is permanent for that offering, with actor/time recorded; it does not rewrite the catalog, curriculum, or old enrollment/result snapshots.
- Schemes can be approved for planned or active offerings, but not for offerings already used in an existing semester result (including a draft). Approved schemes also lock planned offering academic identity; activation and pre-activation teacher assignment remain available.
- **Teacher Panel → Assessments & Marks** lists only assigned offerings. Teachers create assessments under the approved non-attendance components. Definition changes are allowed only while all marks are blank, with version checks and audit history. No destructive assessment-delete endpoint is exposed.
- Each assessment has a raw maximum and a course-mark weight. Example: two assignments, each out of 20, can each carry 5 marks in a 10-mark assignment component. Scoring 10/20 and 20/20 produces 2.5 + 5 = 7.5/10. Assessment weights cannot exceed the component budget and must exactly cover it before submission.
- A blank mark is incomplete; numeric zero is a real score. Scores must be nonnegative, within the assessment maximum, and have at most two decimal places. Every current enrolled student must appear exactly once in a save payload. New enrollments produce unmarked rows until assessed; stale roster/version submissions are rejected.
- Assessment dates must be within the term. Future assessments can be planned but cannot receive marks. Changing an existing nonblank score requires a correction reason; actors and before/after data are audited.
- Attendance is calculated by the Phase 9D service, not entered manually as an assessment. Missing counted attendance, any draft attendance sessions, blank marks, missing component allocations, or an empty roster block submission. No automatic zero is invented for N/A attendance.
- Submission freezes the component scheme, raw scores/maxima/weights, attendance policy/counts/marks, enrollment identities, and course totals in a JSON snapshot. It changes the offering to `marks_submitted`, blocking teacher marks/definitions, attendance changes, and new enrollment through the normal workflow.
- **Academic Setup → Assessment Reviews** allows independent reviewers to return a submission with instructions (offering becomes active) or approve it (offering becomes reviewed/read-only). Assigned teachers cannot review their own class even if given the review permission. Returning never modifies the old snapshot; resubmission creates a new snapshot. Duplicate/stale reviews are rejected.
- New global permission `assessments.review` is granted additively to Academic Admin; Super Admin retains its existing bypass. HOD is not granted unscoped global review access automatically. Teacher definitions, marks, and submission separately require `assessments.manage-assigned`, `marks.manage-assigned`, and `results.submit`, in addition to assignment/linked-profile checks.
- Existing role customizations and demo passwords are preserved. For old **Demo Teacher (9C)** accounts, explicitly enable the three teacher permissions above in Access Control (plus attendance permission for recording sessions).
- **Compatibility boundary:** offerings without an approved flexible scheme keep the existing manual result workflow. Once a flexible scheme is approved, the legacy result drawer cannot save/publish a result for an enrollment containing that offering. This prevents an inconsistent 3-component result bypass. Approval of a course submission does not publish a semester result or recalculate SGPA/CGPA; the Phase 9F integration remains to be implemented.
- Models/services use `app/Models/Assessment` and `app/Services/Assessment`; teacher and academic controllers remain in their existing module folders. Routes are in `routes/assessments.php`; shared previews, teacher views, and admin views live in their respective assessment folders. Existing purple layouts/sidebar groups are reused.

Manual acceptance checklist: [Phase 9E testing guide](docs/testing/PHASE_9E_ASSESSMENT_GUIDE.md). Automated coverage: `tests/Feature/Assessment/AssessmentWorkflowTest.php` plus the existing regression suite. Browser visual acceptance remains manual.

### Phase 9F — Result moderation and publication

- Replace manual result-entry dependency with aggregated teacher marks.
- HOD/Admin review and publish flow.
- Preserve result snapshots and audit corrections.

### Phase 9G — Student Portal

- Student dashboard, attendance, courses, assessment marks, results, and history.
- Student-specific authorization tests.

### Phase 9H — Reporting and operational hardening

- Teacher workload, attendance shortage, result summary, and promotion reports.
- Notifications and audit viewer.
- Database constraints, rate limiting, export/print, and full regression tests.

---

## 10. Non-negotiable integrity rules

- A student never receives marks for a course they are not enrolled in.
- A teacher never manages an offering they are not assigned to.
- Every mark is between zero and its approved maximum.
- Assessment components equal the stored course total.
- Grade, SGPA, CGPA, pass/fail, and attendance marks are server-calculated.
- Published records are snapshots and never recalculate from later course changes.
- Academic history is not deleted; corrections are audited.
- Promotion, final publication, and published-result correction are transactional.

---

## 11. Decisions to confirm during implementation

1. Must failed students remain blocked from promotion, or may they promote with mandatory repeat courses?
2. Which roles may publish results: Academic Admin, HOD, Controller of Examinations, or a combination?
3. Should attendance be percentage-based, marks-based, or both?
4. Should students see assessment marks immediately or only after release?
5. Are electives and multiple teacher assignments required in the first release?

---

## Next implementation step

Phases 9A–9E are complete. Next is **Phase 9F — Result moderation and publication**:

1. Aggregate approved offering submissions into semester results without relying on manual component entry.
2. Implement authorized final review/publication, grade/SGPA/CGPA calculation, and offering completion.
3. Preserve flexible component/attendance snapshots in published result items and audit authorized corrections.

Sections 1–8 above describe the overall requirements, not additional independent implementation phases. Their work is delivered through roadmap phases 9A–9H. Result aggregation and moderation remain scheduled for 9F.
