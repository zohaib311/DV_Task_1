# Phase 9E assessment and marks testing

## Important boundary

Use test offerings/enrollments without any existing semester result. Approving a flexible assessment scheme permanently switches that offering to teacher-managed assessment entry. Do not approve a scheme for a live class that still needs the legacy semester-result drawer: publication of the new workflow is Phase 9F, not part of 9E. Existing unswitched offerings and published results are unchanged.

## Accounts and setup

1. Use your existing Academic Admin login. The additive migration registers `assessments.review` and grants it to Academic Admin. It does not reset your other role settings.
2. Standard Teacher already has `assessments.manage-assigned`, `marks.manage-assigned`, and `results.submit`. For **Demo Teacher (9C)**, explicitly enable these three permissions in Access Control, and `attendance.manage-assigned` to record attendance. Keep `offerings.view-assigned` enabled.
3. A reviewer must not be an assigned teacher for the class under review. Do not grant teachers the global review permission as a substitute for this separation.
4. Choose an active offering, active term, assigned teacher, and active unpublished enrolled students. Demo Programming can be used if you have not already saved a semester result for one of its enrollments. Otherwise create a separate test offering/enrollment.

## Admin: approve a component scheme

1. Academic Setup → Course Offerings → **Assessment scheme**.
2. For a 100-mark course with 10 saved attendance marks, enter Attendance 10, Assignment 10, Quiz 10, Midterm 30, Final 40, Practical 0, Project 0.
3. Try totals of 99/101, negative values, and changing the saved attendance allocation: all must fail. Restore valid values and select **Approve & lock scheme**.
4. The approved scheme becomes read-only and keeps its actor/time. It cannot rewrite a class with existing semester results. A planned offering may still be activated, but its approved course/term/section cannot be swapped.

## Teacher: assessments and raw marks

1. Teacher Panel → **Assessments & Marks** → open the assigned class.
2. Create Assignment 1: raw maximum 20, weight 5; Assignment 2: raw maximum 20, weight 5. Both belong to Assignment. A third assignment exceeding the 10-mark component budget must fail.
3. Create Quiz 1 (maximum 20, weight 10), Midterm (maximum 100, weight 30), and Final (maximum 100, weight 40). Dates must fit the term; use today's date to test mark entry. Future-date assessments cannot be marked yet.
4. Open each assessment and enter every student's raw score. Blank scores save as incomplete; zero is valid. Negative, over-maximum, and more-than-two-decimal scores must fail. The teacher cannot submit custom percentages or totals.
5. For a sample student, use Assignment 1 = 10/20, Assignment 2 = 20/20, Quiz = 20/20, Midterm = 80/100, Final = 90/100. With full attendance, the preview should be **10 + 7.5 + 10 + 24 + 36 = 87.5 / 100**.
6. Record completed attendance through Phase 9D. Attendance is calculated, not manually entered in this module. Missing attendance (N/A), unmarked students, insufficient assessment weights, or draft attendance sessions must block submission.
7. Correct a saved raw score: provide a reason. Check Change history for before/after data and actor. Open two tabs, save one, then submit the older revision: it must fail until reloaded. Unmarked definitions can be edited; definitions with nonblank marks are locked.

## Submit, return, and approve

1. Select **Submit course marks for review** after the preview has no issues. The offering becomes `marks_submitted`; teacher marks/definitions and attendance are read-only, and new course enrollment is blocked.
2. As Admin, open **Academic Setup → Assessment Reviews**, open the pending submission, inspect component/raw-mark evidence, and return it with a meaningful reason.
3. Teacher sees the return note, corrects marks with a reason, and resubmits. The first submission's snapshot must remain unchanged; the new attempt gets a new submission ID.
4. Reviewer approves the new submission with a note. Status becomes `approved`, offering becomes `reviewed`, and writes stay locked. Repeating the same review or approving your own assigned class must fail.
5. Verify no semester result was created by this approval. Final semester publication and SGPA/CGPA aggregation come in Phase 9F. Trying the legacy result drawer on this switched enrollment must show a clear validation message, not silently publish inconsistent old-style marks.

## Authorization and preservation

- A teacher cannot access an unassigned class or mix an assessment ID from another offering (404).
- Remove `marks.manage-assigned`: the teacher may retain authorized read access but cannot save marks (403). Definition and submission permissions are enforced separately.
- Student and guest accounts cannot open teacher mark pages or reviewer screens.
- Closed terms, non-active offerings, and finalized enrollments cannot be marked.
- Existing unswitched result workflows still work, and published result snapshots remain unchanged.
- Browser checks: sidebar dropdowns, responsive tables, keyboard navigation, visible error summaries, and saved input restoration. Manual visual acceptance is still required.

Automated checks: `php artisan test --filter=AssessmentWorkflowTest` and `php artisan test` for the complete regression suite. Do not use `migrate:fresh` or rerun seeders to reset your working data.
