# Phase 9G — Student Portal acceptance

## Set up student access

1. Run outstanding migrations with `php artisan migrate` (never `migrate:fresh` on your working data).
2. Create a separate user account through the existing account workflow. As administrator, assign the **Student** role in Access Control.
3. Open the corresponding academic student's Edit screen and select that account in the existing linked-user field. Role assignment alone does not identify the academic student. Do not link a teacher account to a student.
4. Use two different student logins for isolation testing. No new passwords or accounts are automatically created by this phase.
5. Sign in as the student: landing should be **My Dashboard & Profile**. The profile must match the linked student, not the login's display name or a student ID from the URL.

## Permissions

| Screen | Permission |
| --- | --- |
| Dashboard / academic profile | `student.profile.view-own` |
| Enrolled courses / teachers | `student.courses.view-own` |
| Live attendance / sessions | `student.attendance.view-own` |
| Released raw assessment marks | `student.assessments.view-own` |
| Published result sheets / history | `student.result.view-own` |

The migration adds the two new permissions to the standard Student role only. Existing role customizations are retained. Custom student roles can be configured through Access Control. Academic identity is read-only in the portal; general account settings do not replace the university-maintained academic profile.

## Functional checks

1. **Dashboard:** verify registration, department, current section/enrollment, year/term, course count, and latest published result. A profile without an active enrollment should show clear empty states.
2. **Courses:** verify courses/credits against semester enrollment snapshots and teacher assignments. Filter both current and previous enrollments. Repeats remain separate registration records.
3. **Attendance:** open the existing attendance screens. Confirm percentages/session status and that private teacher notes, draft sessions, and cancelled sessions are absent. These are live attendance totals, not the immutable attendance marks on a published result.
4. **Before publication:** create teacher marks, submit/approve course submissions, and approve the semester sheet using Phase 9F. Student result history must still exclude that unpublished sheet. Course assessment detail must say that marks have not been released.
5. **After publication:** publish through Phase 9F. Student history should include the result, full course breakdown, grades, SGPA/CGPA, percentage, and Pass/Fail. Released Marks should show the student's individual assessment raw scores/maxima and weighted contributions, plus frozen attendance when present.
6. **Corrections:** perform an authorized Phase 9F correction. Reopen the student's published sheet and assessment page: revised saved marks should appear. Correction reasons, reviewer identities, class submission data, and before/after audits must not appear.
7. **Legacy records:** open a published legacy three-component result and a historical total-only result. They must render without pretending individual assignment/quiz records exist.
8. **Promotion:** promote an eligible student using the existing enrollment workflow. Dashboard should show the new active enrollment; previous courses/results remain accessible in history.

## Security checks

- In Student A's session, request Student B's result URL, assessment course URL, attendance course URL, and enrollment filter ID. Each must return 404, not the foreign record.
- Request a draft result directly by ID: 404. Course approval alone must not reveal scores.
- Remove individual student permissions using an administrator account; corresponding navigation and endpoint access should disappear/be denied.
- A Student role without a linked academic profile must receive a clear link-required 403, not another student's data.
- Confirm student accounts cannot open administrative result moderation, student management, or teacher marks-entry routes.
- Test mobile navigation, wide result tables, empty states, and all English interface messages manually.

## Verification commands

```sh
php artisan test --filter=StudentPortalTest
php artisan test
npm run build
php artisan view:cache
```

Release policy: semester publication releases assessment marks; early independent assessment release is not provided. Notification delivery, reports, and operational hardening remain Phase 9H.
