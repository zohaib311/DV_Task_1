# Phase 9F — Result moderation and publication

## Before testing

- Apply outstanding migrations with `php artisan migrate`. Do not use `migrate:fresh` on your working database.
- Use the existing Phase 9C demo data or your own enrolled class. No new credentials, marks, or results are seeded by 9F.
- Complete [the Phase 9E checklist](PHASE_9E_ASSESSMENT_GUIDE.md) for **every course in the student's enrollment**. Each offering must have an approved flexible scheme, complete teacher marks/attendance, and an independently approved course submission.
- A course submission approval is not a semester publication. Check the offering shows `reviewed`.

## Roles

| Action | Permissions |
| --- | --- |
| Open result/moderation sheets | `results.view-all` |
| Approve semester sheet | `results.approve` plus view permission |
| Publish approved sheet | `results.publish` plus view permission |
| Correct published raw marks | `results.edit`, `results.edit-published`, `results.publish`, plus view permission |
| See before/after audit evidence | `audit.view` plus view permission |

Academic Admin has approval and publication. HOD / Program Coordinator has approval but no default publication. Grant permissions explicitly through Access Control if needed. Permissions are global; this release does not introduce department-scoped HOD accounts. Assigned teachers cannot approve/publish/correct their own classes even with extra permissions.

## 1. Review readiness

1. Open **Results → Moderation & Publication**.
2. Search by registration number/name and open the semester sheet.
3. Verify identity, semester, year, course list, credits, submission IDs, and assessment breakdown.
4. Expand assessment evidence: verify raw marks, maxima, contribution weights, and frozen attendance.
5. Try an enrollment with one unapproved/returned course: a visible warning must identify it and approval must be unavailable.

Example: attendance 10/10; assignment 75/100 weighted 10; quiz 75/100 weighted 10; midterm 75/100 weighted 30; final 75/100 weighted 40. Course total = 10 + 7.5 + 7.5 + 22.5 + 30 = **77.5/100**, B+, GP **3.30** under the default policy. If every course has these marks, semester percentage is 77.5 and SGPA is 3.30. Different credits weight SGPA, not the course percentage.

## 2. Approve, then publish

1. As an authorized reviewer, enter a review note and click **Approve semester sheet**.
2. Expect **Approved / awaiting publication**. This is stored as Draft but is already a frozen reviewed sheet; it is not a manual editable draft.
3. As a publisher, click **Publish approved result**.
4. Verify Pass/Fail, SGPA, CGPA, publication time, and increased revision. In the existing Student Results list, verify View and Academic History show the same summary and all flexible components.
5. Repeated publication or an old-tab request must fail visibly without duplicating results.
6. HOD without publish permission must not publish. An assigned teacher with extra administrative permissions must still be blocked.
7. No semester publication is possible by using the old manual drawer for these enrollments.

## 3. Complete the class and promote

1. Publish one student: offerings remain `reviewed` while classmates are unpublished.
2. Publish every student in the class: all fully published offerings become `completed` with audit records.
3. Existing academic-term closure becomes possible only after every offering is completed. It does not close automatically.
4. The passing student's enrollment remains active and eligible for the existing promotion workflow. Create/select the next active term and approved next-semester curriculum/offerings, then promote normally.
5. A failed result remains in history and blocks promotion under the default policy; no automatic promotion or course removal occurs.

## 4. Audited correction

1. Explicitly grant correction permissions to an independent authorized account. Academic Admin does **not** receive published-edit permission automatically.
2. Open a published moderation sheet and expand **Correct published assessment marks**.
3. Change an assessment's raw score within its saved maximum, enter a meaningful correction reason, and save.
4. Verify recalculated components, grade, SGPA, CGPA, and revision. Publication date/status is retained.
5. Inspect audit history: actor, reason, before/after marks, and GPA values must be preserved. The original course submission is unchanged.
6. If later semesters exist, verify their cumulative GPAs update without their course grades changing.
7. Try negative/over-maximum/blank/three-decimal marks, missing reason, stale revision, or no correction permission: saving must be rejected without partial writes.
8. Attendance is read-only evidence in this screen; it is not a manual override field. Historical attendance reopening/correction is outside this release.

## 5. Compatibility and visual acceptance

- Pure legacy enrollments retain their existing Add/Edit result workflow and result-sheet layout.
- Teacher-managed rows open the existing saved moderation sheet instead of creating duplicate results.
- Verify the Results sidebar dropdown, responsive tables, English validation alerts, and expandable evidence at desktop/mobile widths.
- Check Student Results' sheet/history modal alternately with a legacy and a flexible result; column headers must switch correctly.
- Full student portal/dashboard and release visibility are Phase 9G, not part of this acceptance.

## Automated checks

```sh
php artisan test
npm run build
php artisan view:cache
```

Targeted coverage: `php artisan test --filter="ResultModerationTest|FlexibleResultCalculatorTest"`.
Automated HTTP tests verify rendering and business logic; manual browser visual acceptance is still required.
