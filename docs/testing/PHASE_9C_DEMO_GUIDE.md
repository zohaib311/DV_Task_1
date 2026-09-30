# Phase 9C local demo testing

## Setup

Run only on a local development database with the existing project migrations applied:

```powershell
php artisan db:seed --class="Database\Seeders\Demo\TeacherPanelDemoSeeder"
```

This opt-in seeder is not part of DatabaseSeeder. It refuses production environments, runs in a transaction, and does not reset existing accounts or roles. A repeated run skips the existing demo dataset, preserving passwords, permissions, results, and other edits. Do not use `migrate:fresh` to install this demo.

## Accounts

| Login email | Initial password | Assigned courses |
|---|---|---|
| demo.teacher1@example.test | UniDemo@2026! | Demo Programming, Demo Computing Lab |
| demo.teacher2@example.test | UniDemo@2026! | Demo Mathematics, Demo Computing Lab |

These are local-only test credentials. A dedicated `Demo Teacher (9C)` role grants only `dashboard.view` and `offerings.view-assigned`. Existing Teacher/Admin role customizations are untouched. Use your existing administrator account to inspect or manage the demo data. Six student profiles are created, but no student logins or results are generated; the Student Portal is a later phase.

## Expected fixture

- Department: DEMO - Computing (9C)
- Section: DEMO-A
- Academic year: 2026-2027
- Term: DEMO 9C Teaching Term (active)
- Approved Semester 1 curriculum: DEMO-9C
- Courses: DEMO-CS101, DEMO-CS102, DEMO-CS103
- Six enrolled students: Demo Student Ali, Sara, Ahmed, Hina, Bilal, Noor
- Three required courses per enrollment: 18 course registrations
- Each teacher: 2 assigned offerings, 2 active offerings, 6 distinct students
- Each roster: 6 students
- Shared lab: both teachers see the same roster
- Each course: 3 credits; attendance 10, midterm 30, final 60

## Test sequence

1. In your normal browser, log in with your existing Admin account. Open Academic Setup and verify the demo term, curriculum, and three offerings. Open Enrollments and check the six demo student enrollments. Do not change live records.
2. In a private/incognito window, open your application's `/login` page. Log in with teacher 1. You should land at `/teaching` and see the expected totals above.
3. Expand Teacher Panel, open My Courses & Rosters, and search `DEMO-CS101`. Check the term/status filters and Reset. Mathematics must not appear for teacher 1.
4. Open Programming's roster. Check the six students, registration numbers, assessment scheme, and active enrollment status. Search using a displayed registration number, then reset.
5. Copy the Programming roster URL. Log out of the private window and log in as teacher 2. Only Mathematics and Computing Lab should appear. Paste the copied Programming URL: it must return 404, not show student data.
6. Open Computing Lab as either teacher: both should see the same six students and both teacher names. There are no attendance/marks-entry buttons yet; those belong to Phases 9D/9E.
7. As Admin, open Access Control and remove `offerings.view-assigned` from the dedicated Demo Teacher (9C) role. Refresh the teacher page: it must return 403. Restore that permission after testing. This affects both demo teachers but does not change your real Teacher role.
8. Optional result check: as Admin, choose the demo department/section on Results, open a student's Add Result drawer, and enter attendance/mid/final marks within 10/30/60. Test draft/edit/publish using the existing result workflow. The teacher workspace stays read-only; it does not publish semester results.
9. Check mobile layout and keyboard operation of the sidebar dropdowns. Verify no admin student/enrollment/result management links appear for the demo teacher.

If login fails after you changed a password, use the changed password: rerunning the seeder deliberately does not reset it. If a demo identifier conflicts with an existing record, the seeder aborts without altering those records. Demo data is not automatically deleted; retain test history or remove it through a separately reviewed cleanup.
