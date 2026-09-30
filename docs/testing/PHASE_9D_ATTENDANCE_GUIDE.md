# Phase 9D attendance testing

## Access and prerequisites

- Apply pending migrations with `php artisan migrate` (never `migrate:fresh` on your working data).
- Existing Teacher role: `offerings.view-assigned` and `attendance.manage-assigned` are required, along with a linked teacher profile and explicit offering assignment.
- Earlier demo logins use the deliberately minimal **Demo Teacher (9C)** role. As Admin, enable **attendance.manage-assigned** for that role in Access Control. Do not rerun the main access-control seeder to reset permissions.
- A student needs a User account linked through the existing student form, and the Student role or `student.attendance.view-own`. The six seeded demo students do not have login accounts by default. Create a test login, assign the Student role, and link it to one demo student; do not link a teacher login to a student.
- Use an active term and active offering with unpublished active enrollments. For demo data use today's date, on/after the demo enrollment date. If you already published a student's result, that enrollment is intentionally read-only and is not added to new attendance rosters.

## Teacher checks

1. Log in as demo.teacher1@example.test (use the existing password). Open Teacher Panel → Attendance → Demo Programming. The Mathematics class assigned only to teacher 2 must not appear.
2. Create today's Lecture, slot 1. A draft session opens with eligible students and no automatically selected statuses. The draft must not affect any percentage.
3. Mark each student Present, Absent, Late, or Excused and optionally add a private note. Save complete attendance. A missing status must produce an English validation error; no partial save should occur.
4. Return to the class attendance page. Verify counts, percentages, and calculated marks. For a course with attendance allocation 10: one Present → 100% / 10 marks; one Late → 50% / 5 marks; one Absent → 0% / 0 marks; only Excused → N/A.
5. For the same student, create four valid sessions (different slots on today's date are fine for testing) with Present, Late, Absent, Excused. Expected denominator = 3, credits = 1.5, attendance = 50%, marks = 5/10.
6. Correct a completed session. A reason is required. Verify Audit history shows the actor, reason, previous status/note, and new status/note. Open the same session in two tabs, save one, then submit the other: the stale revision must be rejected. Reload before retrying.
7. Cancel a session with a reason. Its records remain visible to the assigned teacher, but the session is excluded from calculated totals. It cannot be edited or reactivated. Create another slot if a replacement is genuinely required.
8. Try a duplicate date/type/slot, a future date, a date outside the term, and a date before all students enrolled: each must fail with a validation message.
9. Test the shared Computing Lab: both assigned teachers can manage sessions. An unassigned teacher must receive 404 for a copied class/session URL. Removing attendance permission must produce 403.
10. After publishing a student's result or promoting an enrollment, sessions containing that enrollment become read-only. Published result marks/GPA must remain unchanged. Closed classes/terms remain readable but cannot accept new attendance.

## Student checks

1. Log in using the linked student account and open Student Portal → My Attendance.
2. Verify only that student's registered courses and attendance percentages/marks appear, including historical semesters when records exist.
3. Open course details. Only completed, non-cancelled sessions appear. Teacher notes, audits, draft entries, and other students' data must not be exposed.
4. Copy another student's attendance-course URL while testing as Admin or another student, then try it in this student's session: expect 404. Student accounts cannot post teacher attendance updates.
5. Courses without recorded attendance show N/A, not an invented zero. The page clearly distinguishes live attendance totals from published results.

## Scope and policy

Defaults are Present 1, Late 0.5, Absent 0, Excused excluded. Review `config/attendance.php` before recording real academic data. The policy is frozen on an offering when its first session is created; later config changes do not rewrite it. Credits and maxima are server-calculated; teachers cannot submit their own percentages or override calculated marks.

This phase does not replace the result drawer. Automatic aggregation into moderated results is Phase 9F, after assessments in 9E. Do not assume a changed live attendance total changes a published result.

Automated checks: `php artisan test --filter=AttendanceWorkflowTest`. Complete regression suite: `php artisan test`. Manually check responsive tables, validation visibility, and sidebar keyboard/mobile navigation in your browser.
