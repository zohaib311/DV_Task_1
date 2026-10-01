# Phase 9H — Reporting and operational hardening acceptance

## Before testing

1. Run `php artisan migrate`. This additively creates database notifications and grants `reports.view` to Academic Admin plus `notifications.view-own` to Student. Do not run `migrate:fresh` on working data.
2. Use separate Academic Admin, Teacher, and linked Student accounts. Confirm official grade, final-minimum, promotion, and attendance policies before real result publication.

## Reports and printing

1. Open **Academic Setup → Academic Reports** with `reports.view`.
2. Filter by term and verify teacher workload/course/section/roster against Course Offerings.
3. Set an attendance threshold such as 75%. Compare shortage rows with completed-session attendance details. Draft/cancelled sessions must not count.
4. Verify published result summary excludes drafts and pending review sheets.
5. Promotion report must only link to the existing promotion workflow; viewing it must not change an enrollment.
6. Use **Print report** and check browser print output. Browser printing is the supported export in this release.
7. A user without `reports.view` must receive 403. Grant roles only through Access Control.

## Audit viewer

1. Make a legitimate attendance, assessment, or published-result correction through its original workflow.
2. Open **Academic Setup → Audit Viewer** with `audit.view`.
3. Verify actor, time, action, reason, subject, and before/after evidence. Filter Result, Assessment, Attendance, and text terms.
4. Confirm it has no edit/delete controls and unavailable users receive 403.

## Notifications

1. Link a Student user to an academic student record using the existing student edit form.
2. Create a semester enrollment, publish a result, then make an authorized correction.
3. Check **Student Portal → Notifications** for each appropriate update.
4. Opening a notification should mark only the current student's notice read and open their owned record.
5. A foreign notification ID must return 404. Unlinked students must receive the link-required 403.
6. These are in-app database notices only; email/SMS, queues, and announcements are outside this release.

## Rate limits and safety

- More than 20 invalid result moderation requests within one minute should receive HTTP 429 without writes. Attendance saves allow 30/minute.
- Rate limits supplement, never replace, permissions, validation, transaction locks, revisions, snapshots, foreign keys, and unique constraints.
- Back up the database before live use and test restoring a backup in a safe environment. Use audited corrections instead of deleting published history.

## Verification

```sh
php artisan test --filter=OperationalHardeningTest
php artisan test
php artisan view:cache
npm run build
```

The frontend build can report upstream Bootstrap Sass deprecation warnings; a completed production build is still valid. Manually test mobile tables, empty states, navigation, printing, and all English messages.
