# University Management System — Complete User and Operations Guide

**Project:** My Form Task / University Management System  
**Document type:** End-to-end user guide and operating procedure  
**Current implementation reviewed:** 08 October 2026  
**Application URL in local development:** `http://localhost:8001`

---

## Contents

1. Purpose and system overview
2. Academic concepts, roles, and permissions
3. Installation, services, and first administrator
4. Academic Admin setup
5. Student admission, enrollment, and course registration
6. Teacher attendance and assessment workflow
7. Student assessment submission workflow
8. Academic review and result publication
9. Promotion and failed-course handling
10. Student Portal, notifications, reports, and audits
11. Troubleshooting, safety rules, and acceptance testing

## 1. Purpose of this guide

This document explains how the current University Management System works from initial installation through academic setup, teaching, student learning, result publication, and promotion to the next semester.

It is intended for:

- Super Administrators
- Academic Administrators
- HODs / Program Coordinators
- Teachers
- Students
- Developers and testers maintaining the project

This guide describes the behavior that is currently implemented in the codebase. It also identifies important boundaries where the system intentionally restricts an action or where a feature is not yet available.

---

## 2. System overview

The system follows a semester-based academic model:

```text
Department
  └── Program (for example BSCS or BSIT)
      └── Semester Curriculum (Semester 1 to Semester 8)
          └── Curriculum Courses (required or elective)

Academic Year
  └── Teaching Term
      └── Course Offering / Semester Class
          ├── Section
          ├── Assigned Teacher(s)
          ├── Enrolled Students
          ├── Attendance Sessions
          ├── Assessments and Student Submissions
          └── Reviewed Course Marks

Student Semester Enrollment
  └── Published Semester Result
      ├── Course Grades
      ├── SGPA / CGPA
      └── Promotion and Backlog Courses
```

The normal academic lifecycle is:

```text
Academic setup
    → prepare semester classes
    → create/link students and teachers
    → enroll student
    → student registers eligible courses
    → teacher records attendance
    → teacher creates assessments
    → student submits assigned work
    → teacher marks and releases assessments
    → teacher submits course marks
    → academic reviewer approves course evidence
    → authorized user approves and publishes semester result
    → student views result
    → administrator promotes student
    → student selects the next semester and backlog courses
```

---

## 3. Important academic concepts

| Concept | Meaning |
| --- | --- |
| Course catalog item | A permanent course definition such as `CS101 — Introduction to Computing`. |
| Program | A degree program such as BSCS or BSIT. Each program has its own semester plan. |
| Semester curriculum | The approved list of required and elective courses for one program semester. |
| Academic year | A date range such as `2026-2027`. |
| Teaching term | The actual delivery period, such as Fall 2026. Its dates control attendance and assessment activity. |
| Course offering | A live class for one course, term, program, section, and teaching team. |
| Semester enrollment | The student's placement in a program semester and teaching term. |
| Enrollment course | The student's saved registration in one course offering. |
| Assessment scheme | The approved distribution of attendance, assignments, quizzes, midterm, final, practical, and project marks. |
| Course submission | The teacher's frozen course marks package sent for academic review. |
| Semester result | The reviewed and published academic record containing grades, SGPA, CGPA, and Pass/Fail. |
| Backlog repeat | A previously failed course registered again in a later term. |

### Snapshot rule

Curricula, offerings, enrollment courses, assessment evidence, and published results save historical snapshots. Later edits to a catalog course or policy do not rewrite completed student history.

---

## 4. Roles and default permissions

### Super Admin

The Super Admin has full access through the application's permission bypass. This role should be limited to trusted system owners.

Typical responsibilities:

- Manage roles and permissions
- Assign roles to users
- Perform all Academic Admin operations
- Recover access-control configuration

### Academic Admin

The default Academic Admin can manage:

- Users, students, and teachers
- Departments, sections, courses, and events
- Academic years and teaching terms
- Programs and semester curricula
- Course offerings and teacher assignments
- Enrollments and promotions
- Assessment review
- Result approval and publication
- Academic reports and audit history
- Own notifications

The default role cannot edit an already published result unless `results.edit-published` is explicitly granted.

### HOD / Program Coordinator

The default HOD role can view/manage selected academic records, approve results, view history/audits, and manage courses according to its assigned permissions.

Important current boundary: HOD permissions are not automatically department-scoped. If an HOD receives a global permission, that permission applies globally. Configure this role carefully through Access Control.

### Teacher

The default Teacher can:

- View only assigned course offerings
- View the roster for assigned offerings
- Manage attendance for assigned offerings
- Create assessments for assigned offerings
- Review student uploads and assign marks
- Release assessment marks to students
- Submit completed course marks for academic review
- Receive related notifications

A Teacher profile must be linked to the Teacher's login account. The legacy `course` text field on a teacher profile does not assign a live class; Course Offerings are the source of teaching assignments.

### Student

The default Student can:

- View their own dashboard and academic profile
- View their own courses and teachers
- Register available semester, elective, and backlog courses
- View their own attendance
- Download assessment question files
- Submit or update assessment work before the deadline
- View teacher feedback and released assessment marks
- View only their own published results and history
- Receive and read their own notifications

A Student login must be linked to exactly one Student profile.

---

## 5. Local installation and first run

### Requirements

- PHP 8.2 or later
- Composer
- Node.js and npm
- SQLite by default, or a configured MySQL-compatible database
- Required PHP extensions for Laravel and the selected database

### Initial setup

From the project root:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
php artisan storage:link
npm run build
```

For an existing database, run only pending migrations:

```powershell
php artisan migrate
```

Never run `php artisan migrate:fresh` against working academic data. It deletes existing records.

### Start all local services

The recommended command is:

```powershell
composer run dev
```

It starts:

- Laravel application at `http://localhost:8001`
- Database queue listener
- Laravel Pail logs
- Vite development server
- Laravel Reverb WebSocket server

If services are started manually, keep all of the following processes running:

```powershell
php artisan serve --port=8001
php artisan queue:work --tries=3 --timeout=90
npm run dev
php artisan reverb:start
```

### Real-time notification environment

The local `.env` must contain matching Reverb and Vite values:

```dotenv
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
QUEUE_AFTER_COMMIT=true

REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_ALLOWED_ORIGINS=localhost,127.0.0.1

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

After changing `.env` or frontend assets, run:

```powershell
php artisan optimize:clear
npm run build
```

Restart the queue and Reverb processes, then perform one browser hard refresh. Normal notifications after that do not require page refresh.

### Academic policy configuration

Review these values before using the application with official academic records:

```dotenv
ACADEMIC_DEFAULT_TOTAL_MARKS=100
ACADEMIC_PASSING_PERCENTAGE=50
ACADEMIC_DEFAULT_CREDIT_HOURS=3
ACADEMIC_DEFAULT_ATTENDANCE_MARKS=10
ACADEMIC_DEFAULT_MID_MARKS=30
ACADEMIC_DEFAULT_FINAL_MARKS=60
ACADEMIC_MAXIMUM_CREDIT_HOURS=21
ACADEMIC_PROMOTION_REQUIRE_PUBLISHED_RESULT=true
ACADEMIC_FINAL_MINIMUM_ENABLED=false
ACADEMIC_FINAL_MINIMUM_PERCENTAGE=50
ACADEMIC_CGPA_ATTEMPT_POLICY=latest_attempt_replaces_previous
```

The complete result/grade policy is defined in `config/academic.php`; attendance credits are defined in `config/attendance.php`. Policy changes affect new/live calculations as described by those files and must not be used to rewrite already published snapshots.

---

## 6. First administrator and access control

1. Open `/signup` on a fresh installation.
2. Create the first user account.
3. When no Super Admin exists, the first account is assigned the Super Admin role.
4. Sign in and open **Access Control**.
5. Create or review the remaining staff accounts.
6. Assign the correct role to each account.

Later public signups do not receive academic access automatically. They remain pending until an authorized administrator assigns a role.

### Account/profile linking rules

- A Teacher login requires the Teacher role before it can be linked to a Teacher profile.
- A Student login requires the Student role before it can be linked to a Student profile.
- One User cannot be linked to both a Student and Teacher profile.
- One academic profile cannot be linked to multiple Users.
- Creating a Teacher or Student can optionally create and link the portal account in the same form.

---

## 7. Complete Academic Admin setup

The following order should be used for a new program.

### Step 1 — Create departments

Open **Departments** and create the academic department, for example `Computing`.

### Step 2 — Create sections

Open **Sections** and create sections inside the correct department, for example `BSCS-A`.

A section must belong to the same department as the student's program and future course offerings.

### Step 3 — Create catalog courses

Open **Courses** and create every required catalog course.

Each course requires:

- Unique course code
- Course name
- Positive credit hours
- Total marks
- A valid base Attendance + Midterm + Final distribution equal to the total marks
- Active status for use in new curricula and offerings

Used courses cannot be deleted because academic history must remain intact.

### Step 4 — Create the program

Open **Academic Setup → Programs** and create a program, for example:

- Code: `BSCS`
- Name: `BS Computer Science`
- Department: Computing
- Duration: 4 years
- Total semesters: 8
- Active: Yes

Each program has an independent curriculum. A catalog course selected in BSCS remains available for BSIT unless BSIT itself has already assigned it to one of its semesters.

### Step 5 — Create the eight-semester program plan

Open **Academic Setup → 8-Semester Program Plan**.

For each semester:

1. Select the program.
2. Select Semester 1, Semester 2, and so on.
3. Enter a clear curriculum version, for example `2026 Intake`.
4. Select the courses.
5. Mark optional courses as electives; unmarked selected courses are required.
6. Save the draft.
7. Review it carefully.
8. Approve and lock the curriculum.

Important rules:

- Within one program, a course can belong to only one semester plan.
- The same course can be used independently by a different program.
- Courses already used in another semester of the selected program are hidden/blocked.
- An approved curriculum is read-only. Create a new version for future changes.
- Do not place a course in a later semester simply to repeat it for one failed student. Backlog registration handles failed attempts separately.

### Step 6 — Create academic year and term

Open **Academic Setup → Academic Terms**.

Example:

- Academic year: `2026-2027`
- Academic year dates: `2026-08-01` to `2027-07-31`
- Teaching term: `Fall-2026`
- Term dates within the selected academic year
- Status: Active

Term behavior:

- `planned`: prepared but not accepting academic activity
- `active`: accepts offerings, enrollment, attendance, and assessment work
- `closed`: read-only

Once a term contains offerings, its identity and dates cannot be changed. It can be closed only after every offering is completed.

Create a later term for the next semester. Promotion requires the new term's start date to be later than the previous enrollment term's start date.

### Step 7 — Create and link teachers

Open **Teachers → Add Teacher**.

Either:

- Select an existing User that already has the Teacher role, or
- Select **Create Teacher Portal Account**, enter a password, and allow the system to create/link the account.

Only login-linked teachers can be assigned to offerings.

### Step 8 — Prepare semester classes

Open **Academic Setup → Prepare Semester Classes** and select:

1. Active teaching term
2. Program
3. Matching department/section
4. Approved semester plan
5. One teacher for every new course

Click **Prepare semester classes**.

This creates active Course Offerings. Reopening the same setup displays existing teacher assignments and preserves the existing classes. Missing classes can be created without duplicating existing ones.

Duplicate/conflict rules:

- The same catalog course cannot be created twice for the same term, program, and section.
- If a different semester plan tries to reuse that course in the same term/program/section, the system reports a conflict.
- Resolve the curriculum or select the correct later term; do not delete academic rows directly from the database.

### Step 9 — Approve each offering's assessment scheme

Open **Academic Setup → Course Offerings → Assessment scheme**.

Define the approved component allocation. Example:

| Component | Allocation |
| --- | ---: |
| Attendance | 10 |
| Assignment | 10 |
| Quiz | 5 |
| Midterm | 25 |
| Final | 50 |
| Total | 100 |

The allocations must equal the course total. Approval locks the scheme and notifies the assigned teacher that assessments can be created.

---

## 8. Student admission and first-semester enrollment

### Create the student

Open **Students → Add Student** and enter:

- Full name
- Unique email
- 11-digit phone number
- Department
- Program
- Section
- Optional profile image

For portal access, either link an existing Student-role user or select **Create Student Portal Account** and provide the password.

The program and section must belong to the selected department.

### Create the first enrollment

After saving the student, the system redirects to enrollment.

Select:

- Student
- Active teaching term
- Approved Semester 1 curriculum

For initial admission, all prepared required courses are registered automatically. Electives remain optional. The enrollment cannot be created if any required curriculum course does not have an active, teacher-assigned offering for the exact term/program/section.

The system saves the course code, name, credits, assessment values, offering, teachers, and registration type as enrollment history.

### Why “Semester teaching setup is incomplete” appears

The selected curriculum contains a required course for which no matching active offering exists.

Check all of the following:

- Same teaching term
- Same program
- Same section and department
- Same approved curriculum
- Course is active
- Offering is active
- At least one linked teacher is assigned

Return to **Prepare Semester Classes**, complete the missing offerings, and then retry enrollment.

---

## 9. Student course registration

Open **Student Portal → Course Registration**.

The page shows courses currently available for the student's active term, program, section, and semester curriculum.

### First enrollment

Required courses are normally already assigned by the administrator. The student can add eligible electives if the total load remains within the configured limit.

### Enrollment created through promotion

Promotion creates the next semester placement but intentionally leaves course selection to the student. The student can select:

- Prepared required courses from the new semester plan
- Prepared electives
- Eligible failed-course repeats

The default maximum load is **21 credit hours**. The server rechecks eligibility and the credit limit even if the browser form is changed manually.

Current boundary: registration is additive. The Student Portal can add eligible courses, but it does not provide a self-service drop/unregister action after registration.

When courses are registered:

- The student receives confirmation.
- Assigned teachers receive roster-update notifications.
- Academic staff receive a course-registration notification.

---

## 10. Teacher Panel workflow

### Dashboard and assigned classes

After login, a Teacher opens:

- **Teacher Panel → Overview**
- **Teacher Panel → My Courses & Rosters**

Only offerings explicitly assigned to the linked Teacher profile appear. A teacher cannot access another teacher's offering by changing the URL.

The roster is read-only and shows students who registered that exact offering. If a student is enrolled in the semester but has not registered the course, they will not appear in that course roster.

### Attendance

Open **Teacher Panel → Attendance** and select an assigned class.

1. Create a class session using a date inside the active term.
2. Select the session type and slot.
3. Mark each student as Present, Absent, Late, or Excused.
4. Save the complete session.

Default attendance calculation:

- Present = 1 credit
- Late = 0.5 credit
- Absent = 0 credit
- Excused = excluded from the denominator

Rules:

- Draft sessions do not affect attendance totals.
- Duplicate date/type/slot combinations are rejected.
- Future dates, out-of-term dates, and invalid roster entries are rejected.
- Corrections require a reason and are audited.
- Cancelled sessions remain in history but do not count.
- Submitted/reviewed/finalized academic records become read-only.

### Create an assessment

Open **Teacher Panel → Assessments & Marks** and select the class.

The Academic Admin must approve the offering's assessment scheme first.

For each assessment, select/enter:

- Component: Assignment, Quiz, Midterm, Final, Practical, or Project
- Title and instructions
- Assessment date inside the teaching term
- Raw maximum marks
- Weight contributed to the course total
- Whether student submission is required
- Submission deadline when submission is required
- Optional question file

Question-file types: `pdf`, `doc`, `docx`, `ppt`, `pptx`, `zip`, `txt`, `jpg`, `jpeg`, or `png`, up to 10 MB.

The combined assessment weights inside a component cannot exceed that component's approved allocation.

### Review student work and enter marks

Open the assessment's marks page.

- Use **Open submission** to read the student's written answer.
- Download the student's attachment when provided.
- Enter raw marks between zero and the assessment maximum.
- Add feedback for the student when appropriate.
- Save marks.

Blank means incomplete. `0` is a valid assessed score. Changing previously saved marks requires a correction reason.

Marks remain disabled when:

- The assessment date is in the future
- The term is not active
- The offering is not active
- An affected enrollment/result is finalized
- Course marks have already been submitted or reviewed
- The logged-in user lacks `marks.manage-assigned`

### Release assessment marks

After every enrolled student has a mark for the assessment, click **Release marks to students**.

Students can then see the released score immediately in **Assessments & Marks**. Releasing an individual assessment is separate from publishing the final semester result.

### Submit course marks for review

Before submission:

- All required assessment component weights must be complete.
- Every enrolled student must have complete assessment marks.
- Required attendance evidence must be complete.
- The preview must have no readiness errors.

Click **Submit course marks for review**. The offering changes from `active` to `marks_submitted`, and marks/attendance become read-only until an authorized reviewer returns or approves the submission.

---

## 11. Student assessment workflow

Open **Student Portal → Assessments & Marks**, then select a registered course.

The student can:

- Read assessment instructions
- See the assessment date and submission deadline
- Download the teacher's question file
- Enter a written response up to 10,000 characters
- Upload an answer file
- Update/resubmit while the deadline is still open
- Download their own submitted attachment
- View teacher feedback
- View marks after the teacher releases them

Student answer-file types: `pdf`, `doc`, `docx`, `zip`, `jpg`, `jpeg`, or `png`, up to 10 MB.

At least a written answer or attachment is required.

Submission closes automatically when:

- The configured deadline has passed
- No deadline was configured
- The teaching term is not active
- The enrollment is no longer active
- The assessment does not require a student submission

The deadline is enforced on the server; changing browser HTML or time controls cannot bypass it.

---

## 12. Academic assessment review

Open **Academic Setup → Assessment Reviews** with `assessments.review`.

The reviewer should not be an assigned teacher for the same offering.

1. Open the pending course submission.
2. Review assessment definitions, raw scores, weights, attendance evidence, and calculated totals.
3. Approve the submission or return it with a meaningful reason.

If returned:

- The offering returns to `active`.
- The teacher receives a real-time notification.
- The teacher corrects marks with reasons and resubmits.
- Earlier submission snapshots remain preserved.

If approved:

- The offering becomes `reviewed`.
- Course evidence remains locked.
- The course becomes eligible for semester-result moderation.

Approving course evidence does not publish the student's semester result.

---

## 13. Semester result moderation and publication

Open **Results → Moderation & Publication**.

### Readiness

A semester sheet is ready only when every registered course has an approved teacher course submission. The moderation screen shows student identity, courses, credits, component evidence, calculated totals, grades, and GPA data.

### Approve semester sheet

An authorized user with `results.approve` reviews the complete sheet and approves it. Approval freezes the reviewed semester calculation while it awaits publication.

### Publish semester result

An authorized user with `results.publish` publishes the approved sheet.

Publication calculates and saves:

- Course percentage
- Letter grade
- Grade point
- Course Pass/Fail
- Semester percentage
- SGPA
- CGPA
- Semester Pass/Fail
- Publication time and revision

Default pass threshold: 50%.

Default grade scale:

| Percentage | Grade | Grade point |
| ---: | --- | ---: |
| 85–100 | A | 4.00 |
| 80–84.99 | A- | 3.70 |
| 75–79.99 | B+ | 3.30 |
| 70–74.99 | B | 3.00 |
| 65–69.99 | B- | 2.70 |
| 61–64.99 | C+ | 2.30 |
| 58–60.99 | C | 2.00 |
| 55–57.99 | C- | 1.70 |
| 50–54.99 | D | 1.00 |
| Below 50 | F | 0.00 |

SGPA is credit-hour weighted. Under the current repeat policy, the latest completed course attempt replaces the earlier attempt for CGPA calculation.

The student receives a real-time notification and can view the published record in **Student Portal → Results & History**.

### Published correction

Published-result correction requires all of these permissions:

- `results.view-all`
- `results.edit`
- `results.edit-published`
- `results.publish`

The correction requires a reason, creates before/after audit evidence, recalculates affected SGPA/CGPA values, and notifies the student. Normal users cannot silently rewrite published history.

---

## 14. Promotion and failed-course handling

### Promotion prerequisites

The current enrollment must:

- Be the student's active enrollment
- Have a published Pass or Fail semester result
- Not already be Semester 8

The administrator must also prepare:

- A later active teaching term
- The approved next-semester curriculum
- Active, teacher-assigned offerings for every required next-semester course

### Promotion process

1. Open **Enrollments**.
2. Select the student's active enrollment.
3. Open **Promote Student**.
4. Select a teaching term whose start date is later than the previous enrollment term.
5. Confirm the approved next-semester curriculum.
6. Promote the student.

The previous enrollment becomes `promoted` and remains immutable history. The new enrollment becomes `active`.

Promotion itself does not force-register the new semester courses. The student receives a notification and selects available courses through **Course Registration**.

### Failed courses and backlogs

A failed course does not block promotion under the current policy.

When the next term is selected, the system attempts to carry the failed course forward automatically using the previous offering and teacher assignment. Automatic carry-forward requires:

- A published failed course result
- A genuinely later term
- Same student program and section placement
- Active catalog course
- Previous offering with at least one login-linked teacher
- No existing matching offering in the new term

The carried-forward offering is auditable, and its teacher receives a notification. The student may then select it as **Backlog repeat** in Course Registration, subject to the 21-credit-hour limit.

If the backlog does not appear, verify all of the above conditions and confirm the failed course's latest published attempt is still Fail.

---

## 15. Student Portal reference

| Page | Purpose |
| --- | --- |
| My Dashboard & Profile | Registration number, placement, active semester, course count, and latest published result. |
| My Courses | Current and historical registered-course snapshots and assigned teachers. |
| Course Registration | Add eligible current-semester, elective, and backlog offerings. |
| My Attendance | Live completed-session attendance totals and session history. |
| Assessments & Marks | Questions, deadlines, submissions, feedback, released marks, and published assessment evidence. |
| Results & History | Published result sheets, grades, SGPA, CGPA, and academic history. |
| Notifications | Student-owned enrollment, assessment, marks, and result updates. |

The portal is ownership-scoped. Changing a URL to another student's record returns not found rather than exposing the record.

---

## 16. Notifications and real-time updates

Notifications are stored in the database and broadcast through Laravel Reverb over a private per-user channel.

Current notification examples include:

| Event | Recipient |
| --- | --- |
| New course assigned | Assigned teacher |
| Assessment scheme approved | Assigned teacher |
| Student joined/registered in an offering | Assigned teacher and academic staff |
| Semester enrollment or promotion | Student and academic staff |
| Backlog offering carried forward | Previous/assigned teacher |
| Assessment created or updated | Enrolled students |
| Student submitted assessment work | Assigned teacher(s) |
| Assessment marks released | Enrolled students |
| Course marks submitted for review | Academic staff |
| Course review approved/returned | Assigned teacher(s) |
| Semester result published or corrected | Student |

The top notification panel:

- Updates without page reload
- Shows unread count and toast messages
- Is height-limited and scrollable
- Allows marking all notifications as read
- Uses private channel authorization
- Falls back to a safe 60-second refresh only when WebSocket connectivity is unavailable

The fallback avoids the previous three-second polling load. Application write endpoints still retain security rate limits.

---

## 17. Reports and audits

### Academic Reports

Open **Academic Setup → Academic Reports** with `reports.view`.

Available read-only reports:

- Teacher workload
- Attendance shortage by threshold
- Published-result summary
- Promotion-eligible students

The browser **Print report** layout is the supported export. Spreadsheet/PDF file generation is not currently included.

### Audit Viewer

Open **Academic Setup → Audit Viewer** with `audit.view`.

It consolidates:

- Attendance corrections and cancellations
- Assessment definition/mark changes
- Submission and review events
- Result publication and correction evidence

Audit records are read-only and include actor, time, action, reason, and available before/after evidence.

---

## 18. Important statuses

| Record | Typical lifecycle |
| --- | --- |
| Curriculum | `draft → approved` |
| Teaching term | `planned → active → closed` |
| Course offering | `planned → active → marks_submitted → reviewed → completed` |
| Student submission | `submitted → resubmitted → reviewed` |
| Semester enrollment | `active → promoted/completed/withdrawn` |
| Semester result | reviewed draft → published `Pass` or `Fail` → audited correction if authorized |

Status changes protect historical records. A locked record should not be unlocked by direct database editing.

---

## 19. Common problems and solutions

### Approved semester plan cannot be selected

- Select the program first.
- Select a section in the same department.
- Confirm the curriculum belongs to that program.
- Confirm the curriculum is approved.

### Student roster shows zero students

- Confirm the student has an active enrollment.
- Confirm the student registered the exact course offering.
- Confirm term, program, section, and offering all match.
- After promotion, remember that the student must select courses from Course Registration.

### Marks input is disabled

- Assessment date may still be in the future.
- Teaching term may not be active.
- Offering may be submitted, reviewed, or completed.
- Student enrollment/result may already be finalized.
- Teacher may not have the required permission or assignment.

### Student cannot submit an assessment

- The deadline has passed or is missing.
- Submission is not required for that assessment.
- The term/enrollment is no longer active.
- The student is not registered in that exact offering.
- The answer is empty and no file was uploaded.

### Course marks are not ready for submission

- Assessment weights do not fill each approved component.
- One or more marks are blank.
- Attendance is incomplete.
- Required assessments have not been created.
- A course enrollment is in an invalid/finalized state.

### Promotion says the term must start later

The new term's `starts_on` date must be later than the previous enrollment term's `starts_on` date. A different term name alone is not enough.

### Duplicate course/conflict while preparing classes

The same course already exists for that term, program, and section, possibly from another semester plan. Use the correct later term or correct the curriculum assignment.

### Student has no portal login

Edit the student and link an unused User account with the Student role, or create a new student portal account through the Student form.

### Real-time notifications are not arriving

1. Confirm `php artisan reverb:start` is running.
2. Confirm the queue worker/listener is running.
3. Confirm Vite/build contains the current frontend assets.
4. Confirm Reverb environment values match.
5. Run `php artisan optimize:clear`.
6. Restart services and hard-refresh the browser once.

### HTTP 429 Too Many Requests

The user exceeded a protected action limit, often by repeated clicking or repeated requests within one minute.

Current examples:

- Student assessment submission: 10/minute
- Student course registration: 10/minute
- Teacher course-mark submission: 10/minute
- Academic assessment review: 10/minute
- Result moderation writes: 20/minute
- Attendance session update: 30/minute
- Notification fallback feed: 60/minute

Wait briefly and retry once. Do not disable authorization, validation, or transactional protection to remove a 429 response.

---

## 20. Data integrity and safety rules

- Never use `migrate:fresh` on working academic data.
- Back up the database and uploaded files before deployment or structural changes.
- Do not resolve curriculum/offering conflicts through direct database deletion.
- Do not delete students, teachers, courses, or offerings that have academic history.
- A teacher must never manage an unassigned offering.
- A student must never access another student's data.
- Marks must remain inside the approved raw maximum.
- Component weights must match the approved allocation.
- Grades, SGPA, CGPA, and Pass/Fail are server-calculated.
- Published records are corrected only through the audited correction workflow.
- Use separate accounts for teaching, review, and publication when testing separation of duties.

---

## 21. Recommended complete acceptance test

Use separate browser profiles for Admin, Teacher, and Student.

1. Create Department `Computing`.
2. Create Program `BSCS`, 4 years, 8 semesters.
3. Create Section `BSCS-A`.
4. Create active catalog courses with valid credit/assessment totals.
5. Create and approve Semester 1 and Semester 2 curricula.
6. Create Academic Year `2026-2027`.
7. Create two active terms with the second term starting later.
8. Create a linked Teacher account.
9. Prepare Semester 1 classes and assign the teacher.
10. Approve every offering assessment scheme.
11. Create a linked Student portal account.
12. Create the student's Semester 1 enrollment.
13. Confirm the student appears in the correct Teacher rosters.
14. Record completed attendance sessions.
15. Create an assessment with a question file and future deadline.
16. Confirm the student receives the notification and downloads the question.
17. Submit a written answer/file as the student.
18. Confirm the teacher receives the notification and opens the submission.
19. Assign marks and feedback, then release the assessment marks.
20. Confirm the student sees the released marks without waiting for semester publication.
21. Complete all component assessments and attendance.
22. Submit each course for academic review.
23. Return one submission, correct it, resubmit, and approve it.
24. Approve and publish the complete semester result.
25. Confirm the student sees the published result, SGPA, CGPA, and history.
26. Prepare Semester 2 classes in the later term.
27. Promote the student.
28. Log in as the student and select the Semester 2 courses.
29. For a failed-course test, publish one failed course and verify the backlog repeat appears in a later term with the eligible carried-forward teacher assignment.
30. Verify notifications, reports, and audits for the actions above.

---

## 22. Automated verification

Run the complete backend test suite:

```powershell
php artisan test
```

Build production frontend assets:

```powershell
npm run build
```

Additional useful checks:

```powershell
php artisan route:list
php artisan view:cache
composer validate --no-check-publish
composer audit
npm audit --omit=dev
```

At the time this guide was created, the project passed **131 automated tests with 1044 assertions**, and the production frontend build completed successfully.

---

## 23. Current implementation boundaries

The guide deliberately does not claim the following as implemented:

- Department-scoped HOD authorization
- Email or SMS notifications
- Student self-service course dropping after registration
- Automatic timetable/room scheduling
- Fee management or payments
- Question banks, plagiarism checking, or rubric-based grading
- Generated Excel/PDF report files beyond browser printing
- Production process supervision, TLS termination, and multi-server Reverb deployment

These can be added later without bypassing the existing permission, snapshot, audit, and publication rules.

---

## 24. Related technical acceptance guides

- `docs/testing/PHASE_9D_ATTENDANCE_GUIDE.md`
- `docs/testing/PHASE_9E_ASSESSMENT_GUIDE.md`
- `docs/testing/PHASE_9F_RESULT_MODERATION_GUIDE.md`
- `docs/testing/PHASE_9G_STUDENT_PORTAL_GUIDE.md`
- `docs/testing/PHASE_9H_OPERATIONAL_HARDENING_GUIDE.md`

When an older phase guide conflicts with this document, verify the current code and automated tests. This guide reflects the consolidated current behavior, including student course registration, backlog carry-forward, assessment files/submissions, released marks, and Reverb-based real-time notifications.
