# Semester-Wise Enrollment & Result Management Plan

## Goal

University Management System mein student ko semester-wise enroll karwana hai, us semester ke courses assign karne hain, aur tamam courses ka result ek hi academic result sheet se create karna hai.

System ko historical data preserve karna hoga. Agar student Semester 2 mein chala jaye, tab bhi Semester 1 ki enrollment, courses, marks, grades, SGPA aur CGPA accessible rehne chahiye.

---

## Current Project Situation

Current project mein:

- `students` table mein current `semester` aur `course_ids` JSON column hai.
- `course_ids` student ke courses store karta hai, lekin semester history store nahi karta.
- `results` table ek student aur ek course ka result store karti hai.
- `results` mein percentage, GPA, CGPA, grade aur pass/fail fields hain.
- Result page par department aur section select karne ke baad students ki list AJAX se load hoti hai.

### Current limitation

Current setup se pata nahi chalega ke kaunsa course kis semester mein assigned tha. Student ka semester change hone ke baad purane courses aur purane semester ka accurate record maintain nahi hoga.

Isliye result system ko semester-based academic history ke around redesign karna hoga.

---

## Recommended Data Structure

Long-term clean approach mein result ko do levels par store karna chahiye:

1. **Semester enrollment / result sheet** — student ka ek semester ka overall academic record.
2. **Course result items** — us result sheet ke andar har course ke marks aur grade.

### Core tables

| Table | Purpose |
|---|---|
| `students` | Student ka base profile aur current academic placement |
| `courses` | Course code, name, description aur credit hours |
| `student_semester_enrollments` | Student ka semester-wise enrollment/history |
| `student_enrollment_courses` | Kisi enrollment mein assigned courses |
| `semester_results` | Semester ka overall outcome: SGPA, CGPA, percentage, status |
| `semester_result_items` | Har course ki marks/grade/grade-point detail |

---

# Phase 0 — Academic Rules Confirm Karna

Implementation se pehle university ki academic policy confirm karni hogi. Yeh rules hard-code nahi honge; pehle official rule decide hoga.

## Required decisions

- Passing marks: `50/100` ya koi aur threshold?
- Har course total marks `100` hain ya different ho sakte hain?
- Grade scale kya hai?
- Credit hours course-wise kitne hain? Usually 3 ya 4.
- CGPA mein failed attempt include hoga ya repeated course ki latest attempt replace hogi?
- Kya student ek semester mein ek se zyada section change kar sakta hai?
- Kya semester result tab save hoga jab tamam courses ke marks entered hon?
- Kya draft result allowed hoga, ya sirf final publish?

## Suggested 4.0 grade scale

| Percentage | Grade | Grade Point | Course Status |
|---:|---|---:|---|
| 85–100 | A | 4.00 | Pass |
| 80–84 | A- | 3.70 | Pass |
| 75–79 | B+ | 3.30 | Pass |
| 70–74 | B | 3.00 | Pass |
| 65–69 | B- | 2.70 | Pass |
| 61–64 | C+ | 2.30 | Pass |
| 58–60 | C | 2.00 | Pass |
| 55–57 | C- | 1.70 | Pass |
| 50–54 | D | 1.00 | Pass |
| Below 50 | F | 0.00 | Fail |

## Implemented system defaults

Phase 0 is implemented as a configurable policy baseline in `config/academic.php`. These values are used as the project defaults until an official university policy requires a change:

| Decision | Configured default |
|---|---|
| Passing marks | `50%` |
| Default total marks | `100` |
| Grade system | 4.0 scale shown above |
| Default credit hours | `3` |
| Repeat/improvement CGPA rule | Latest completed attempt replaces the earlier attempt |
| Section change within the same semester | Not allowed as an in-place history rewrite |
| Result drafts | Allowed |
| Publish rule | All enrolled course marks are required |
| Published-result editing | Disabled by default |

Environment overrides are documented in `.env.example`. Before result publishing starts, an admin can update the policy values without changing the future calculation code.

### Phase 0 completion checklist

- [x] Passing-mark system default configured
- [x] 4.0 grade-scale system default configured
- [x] Default credit-hour policy configured
- [x] Repeat/improvement CGPA policy configured
- [x] Draft/publish policy configured
- [x] Policy configuration tests added

---

# Phase 1 — Course Data Improve Karna

Har course ke liye credit hours zaroori hain, kyun ke accurate SGPA aur CGPA weighted formula se nikalte hain.

## Database changes

`courses` table mein fields add karni hain:

| Field | Type | Example | Reason |
|---|---|---|---|
| `credit_hours` | decimal/integer | `3` | Weighted GPA/CGPA calculation |
| `total_marks` | integer | `100` | Different assessment totals support karne ke liye |
| `is_active` | boolean | `true` | Inactive course ko future enrollment se hide karne ke liye |

## UI changes

- Add Course aur Edit Course forms mein Credit Hours aur Total Marks add honge.
- Course list par credit hours show honge.

### Phase 1 completion checklist

- [x] Course migration created
- [x] Existing courses ko Phase 0 defaults assigned
- [x] Add/Edit Course validation added
- [x] Course listing updated

---

# Phase 2 — Semester-Wise Student Enrollment

Yeh phase project ka foundation hai. Student ka current semester sirf profile field nahi rahega; har semester ki separate enrollment row create hogi.

## `student_semester_enrollments` table

Suggested fields:

| Field | Purpose |
|---|---|
| `id` | Enrollment ID |
| `student_id` | Related student |
| `department_id` | Enrollment ke waqt department |
| `section_id` | Enrollment ke waqt section |
| `semester` | Example: `Semester 1` |
| `academic_year` | Example: `2026-2027` |
| `status` | `active`, `completed`, `promoted`, `withdrawn` |
| `enrolled_at` | Enrollment date |
| `completed_at` | Semester completion date, if completed |
| timestamps | Audit/history |

## `student_enrollment_courses` table

Suggested fields:

| Field | Purpose |
|---|---|
| `id` | Row ID |
| `student_semester_enrollment_id` | Enrollment relation |
| `course_id` | Assigned course |
| `credit_hours` | Enrollment-time snapshot of credit hours |
| `total_marks` | Enrollment-time snapshot of total marks |
| timestamps | Audit/history |

### Why snapshots are important

Agar future mein course ke credit hours ya total marks change hon, purane semester ka result mutate nahi hona chahiye. Isliye enrollment course row mein values copy/store karna safer hai.

## Enrollment workflow

1. Admin student select karega.
2. Student ka registration number, name, current department aur section show honge.
3. Admin academic year aur semester choose karega.
4. Relevant courses select karega.
5. Enrollment save hoga.
6. Student ka current semester/profile value update hogi.
7. Previous enrollment records untouched rahenge.

## Important rules

- Same student ki same academic year aur semester ke liye duplicate active enrollment allow nahi hogi.
- Semester 2 enrollment create karne se pehle Semester 1 enrollment history remain karegi.
- Promotion se pehle last semester result status check ki ja sakti hai.
- Course list sirf selected enrollment ke records se aayegi; `students.course_ids` par future mein dependency remove hogi.

### Phase 2 completion checklist

- [x] Both enrollment migrations created
- [x] Eloquent models and relationships added
- [x] Create enrollment page created
- [x] Enrollment courses select UI created
- [x] Duplicate enrollment validation added
- [x] Current student semester sync policy implemented
- [x] Existing student `course_ids` compatibility strategy implemented
- [x] Student profile forms restricted to profile/placement data; enrollment owns semester and courses
- [x] Students list and detail view read current academic data from active enrollment

---

# Phase 3 — Result Database Redesign

Current `results` table single-course result ko represent karti hai. New flow mein one semester result sheet aur uske multiple course rows honge.

## `semester_results` table

Suggested fields:

| Field | Purpose |
|---|---|
| `id` | Result sheet ID |
| `student_semester_enrollment_id` | Semester enrollment relation |
| `student_id` | Quick lookup/reference |
| `semester_percentage` | Weighted/overall semester percentage |
| `sgpa` | Current semester GPA |
| `cgpa` | Cumulative GPA until this semester |
| `status` | `Pass`, `Fail`, `Draft`, `Published` policy ke according |
| `published_at` | Final result publish time |
| timestamps | Audit/history |

## `semester_result_items` table

Suggested fields:

| Field | Purpose |
|---|---|
| `id` | Item ID |
| `semester_result_id` | Parent semester result |
| `student_enrollment_course_id` | Exact enrolled course reference |
| `course_id` | Quick course reference |
| `course_code` | Historical snapshot |
| `course_name` | Historical snapshot |
| `credit_hours` | Historical snapshot |
| `total_marks` | Historical snapshot |
| `obtained_marks` | Admin entered marks |
| `percentage` | Calculated course percentage |
| `grade` | Calculated grade |
| `grade_point` | Calculated 4.0 point |
| `status` | Individual course `Pass` / `Fail` |
| timestamps | Audit/history |

## Migration strategy from current `results` table

Before removing or changing old data:

1. Take database backup.
2. Keep existing `results` table temporarily as legacy data.
3. Create new tables first.
4. Decide whether existing results will be migrated manually, automatically, or left as historical legacy records.
5. Only remove old structure after migration verification.

### Implemented legacy-data decision

The current `results` table remains untouched as a **read-only legacy record** during Phases 3 and 4. It cannot be safely auto-migrated because its records do not identify a semester enrollment or an academic year. New semester-wise results will use `semester_results` and `semester_result_items` from Phase 5 onward. A future admin-led legacy migration may only run after each old record is mapped to a verified enrollment.

### Phase 3 completion checklist

- [x] New result migrations created
- [x] Models and relationships added
- [x] Legacy results retained as read-only records; no unsafe automatic migration
- [x] Foreign keys and unique constraints added
- [x] No legacy data migration attempted; current database records preserved

---

# Phase 4 — Calculation Rules and Backend Service

Calculation logic controller mein scattered nahi hogi. Iske liye dedicated service create hogi, for example:

```text
app/Services/AcademicResultCalculator.php
```

## Per-course calculation

```text
percentage = (obtained_marks / total_marks) × 100
```

Grade aur grade point approved grade scale se derive honge.

Example:

```text
marks: 82 out of 100
percentage: 82%
grade: A-
grade point: 3.70
status: Pass
```

## Semester GPA (SGPA)

```text
SGPA = Σ(grade_point × credit_hours) / Σ(credit_hours)
```

Example:

| Course | Credit Hours | Grade Point | Quality Points |
|---|---:|---:|---:|
| Programming | 3 | 4.00 | 12.00 |
| Database | 3 | 3.70 | 11.10 |
| Mathematics | 4 | 3.30 | 13.20 |
| Total | 10 | — | 36.30 |

```text
SGPA = 36.30 / 10 = 3.63
```

## CGPA

CGPA current aur previous completed semesters ke all course quality points se calculate hoga:

```text
CGPA = Σ(all semesters quality points) / Σ(all semesters credit hours)
```

CGPA client/browser par trust nahi kiya jayega. Server har save/update par dobara calculate karega.

## Overall percentage

```text
Semester Percentage = Σ(obtained marks) / Σ(total marks) × 100
```

## Overall Pass/Fail

Suggested rule:

- Har course pass ho → semester result `Pass`
- Ek ya zyada course fail ho → semester result `Fail`
- Incomplete marks → `Draft` / validation error, policy ke according

## Validation rules

- Obtained marks numeric hon aur `0` se `total_marks` ke beech hon.
- Course enrolled course list ka hissa hona chahiye.
- One final result per enrollment; duplicate final result block hoga.
- Student, enrollment aur course relationship validate hoga.
- Tampering se bachne ke liye percentage, grade, GP, SGPA aur CGPA backend par recalculate honge.

### Phase 4 completion checklist

- [x] Grade scale configuration created
- [x] Result calculator service created
- [x] Course, SGPA and CGPA unit tests written
- [x] Pass/fail rule implemented
- [x] Server-side validation completed

---

# Phase 5 — Add Result Right Drawer UI

Current Add Result right drawer ko semester result entry drawer mein convert kiya jayega.

## Drawer layout

### Header

- `Add Semester Result`
- Current action: Draft / Publish
- Close button

### Student academic summary

Read-only clean summary:

| Field | Example |
|---|---|
| Registration No. | `REG-2026-0001` |
| Student Name | `Ali Raza` |
| Department | `Computer Science` |
| Section | `A` |
| Semester | `Semester 2` |
| Academic Year | `2026-2027` |

### Semester selector

- Default selected: active/current enrollment semester.
- Historical semesters are visible but not editable after publish unless an authorized edit flow is used.
- Select semester change hone par us enrollment ke courses load honge.

### Courses marks table

| # | Course | Code | Cr. Hrs | Total | Obtained Marks | % | Grade | GP | Status |
|---:|---|---|---:|---:|---:|---:|---|---:|---|

User only `Obtained Marks` enter karega. Baqi columns live update honge.

### Live summary footer/body

| Metric | Meaning |
|---|---|
| Total Marks | Earned / possible marks |
| Percentage | Semester overall percentage |
| SGPA | Current semester GPA |
| CGPA | All completed semesters cumulative GPA |
| Status | Pass / Fail / Draft |

### Buttons

- Cancel
- Save Draft (agar policy permit kare)
- Save & Publish

## Front-end behaviour

1. Add button click par student ID aur current enrollment fetch hoga.
2. Drawer student summary aur selected semester show karega.
3. Enrollment courses API se table mein load honge.
4. Marks field type karte hi JavaScript row calculation update karega.
5. Summary real time update hogi.
6. Submit par data backend ko send hoga.
7. Backend values recalculate karke transaction mein save karega.
8. Success par drawer close aur current student list refresh hogi.

### Phase 5 completion checklist

- [ ] Student/enrollment result API created
- [ ] Right drawer UI built
- [ ] Dynamic course rows rendered
- [ ] Live marks calculation implemented
- [ ] Summary metrics implemented
- [ ] Submit errors drawer ke andar shown
- [ ] Success ke baad list refresh

---

# Phase 6 — Edit Semester Result Drawer

Edit Result drawer Add Result drawer jaisa hoga, lekin existing stored data prefilled hoga.

## Edit rules

- Published result edit karne ki permission role/policy ke through control hogi.
- Edit par all course marks loaded honge.
- Marks change hote hi grade, course status, SGPA, CGPA aur overall status live update honge.
- Save par backend purana result overwrite/update karega aur CGPA recalculation karega.
- Agar previous semester ka result update ho, to uske baad ke semesters ke CGPA bhi recalculate honge.

## Important historical case

Semester 1 ka result change hone par Semester 2, 3 aur baad ke CGPA depend karte hain. Isliye update operation ke baad selected student ke all later published semester results ka CGPA server-side recalculate karna hoga.

### Phase 6 completion checklist

- [ ] Edit drawer prefill implemented
- [ ] Permission policy added
- [ ] Result update transaction added
- [ ] Later semester CGPA recalculation added
- [ ] Update audit/history policy decided

---

# Phase 7 — Results Listing, History and Detail View

Current list department aur section ke basis par students show karti hai. Isko semester-aware banana hoga.

## Filters

- Department
- Section
- Academic year
- Semester
- Result status: Draft / Published / Pass / Fail

## Student result list

Suggested columns:

| Reg No. | Student | Semester | Courses | Percentage | SGPA | CGPA | Status | Actions |
|---|---|---|---:|---:|---:|---:|---|---|

## Actions

- View semester result sheet
- Add result, only if selected enrollment has no result
- Edit result, only if result exists and user is authorized
- Delete button current UI se remove rahega; historical academic records direct delete nahi honge

## Detail view

View modal/page mein:

- Student summary
- Semester and academic year
- All course marks table
- Percentage, SGPA, CGPA and final status
- Print / export option in future

## History view

Student profile par `Academic History` tab add ho sakta hai:

| Semester | Academic Year | SGPA | CGPA | Status | View |
|---|---|---:|---:|---|---|

Yahan se user purane semester ka result kabhi bhi dekh sakega.

### Phase 7 completion checklist

- [ ] Semester-aware filters added
- [ ] Result list API updated
- [ ] View result details updated
- [ ] Student academic history view added
- [ ] Direct delete disabled/removed

---

# Phase 8 — Student Promotion to Next Semester

Promotion ko manually controlled academic action rakhna chahiye.

## Promotion workflow

1. Admin student ki current active enrollment dekhega.
2. Previous semester result/status check hoga.
3. Admin next semester choose karega.
4. New semester enrollment aur new course assignments create honge.
5. Current student profile ka semester, department aur section update ho sakta hai.
6. Previous enrollment and result immutable history ke taur par preserve honge.

## Promotion safety rules

- Same next-semester enrollment duplicate nahi honi chahiye.
- Failed student ke liye promotion policy follow hogi.
- Repeated/improvement courses explicitly choose kiye jayenge.
- Promotion history audit log mein store ho sakti hai.

### Phase 8 completion checklist

- [ ] Promote student action created
- [ ] Next semester enrollment creation implemented
- [ ] Course assignment flow connected
- [ ] Failure/repeat policy implemented
- [ ] Academic history verified

---

# Phase 9 — Security, Permissions and Data Integrity

## Recommended permissions

| Action | Suggested access |
|---|---|
| Enroll student | Admin / Academic Officer |
| Assign courses | Admin / Academic Officer |
| Enter draft marks | Teacher / Academic Officer |
| Publish result | Admin / Controller of Examinations |
| Edit published result | Authorized Admin only |
| View result | Relevant staff / student policy ke according |

## Data integrity rules

- Use database transactions for full semester result save.
- Invalid partial records save nahi honge.
- Client calculations ko final truth na samjha jaye.
- Student only apne assigned enrollment courses ka result receive kare.
- Course credit hours and total marks snapshots preserve hon.
- Published results ke direct delete ki bajaye correction/audit policy use ho.

### Phase 9 completion checklist

- [ ] Authorization middleware/policies added
- [ ] Transactions added
- [ ] Integrity validations tested
- [ ] Audit policy approved

---

# Phase 10 — Testing Checklist

## Enrollment tests

- [ ] Semester 1 enrollment create hota hai
- [ ] Duplicate Semester 1 enrollment reject hota hai
- [ ] Semester 2 enrollment previous history ko overwrite nahi karta
- [ ] Courses correct enrollment ke against save hote hain

## Result calculation tests

- [ ] 85 marks correctly A / 4.00 calculate karta hai
- [ ] 50 marks correctly D / 1.00 calculate karta hai
- [ ] 49 marks correctly F / 0.00 calculate karta hai
- [ ] Weighted SGPA correct calculate hota hai
- [ ] CGPA previous semesters ko include karta hai
- [ ] One failed course overall result ko Fail banata hai

## UI tests

- [ ] Add drawer correct student detail show karta hai
- [ ] Selected semester ke courses load hote hain
- [ ] Marks change par row and overall totals live update hote hain
- [ ] Invalid marks par readable validation error show hota hai
- [ ] Save ke baad student list refresh hoti hai
- [ ] Edit drawer existing values prefill karta hai
- [ ] Mobile par course table horizontally usable hai

## Regression tests

- [ ] Existing Students module unaffected
- [ ] Existing Courses module unaffected
- [ ] Existing dashboard routes work karte hain
- [ ] Legacy result data decision verified

---

# Recommended Implementation Order

Implementation ko isi order mein karna chahiye:

1. **Phase 0:** Academic rules final.
2. **Phase 1:** Course credit hours and total marks.
3. **Phase 2:** Semester enrollment and enrollment courses.
4. **Phase 3:** New result header/item data structure.
5. **Phase 4:** Backend calculation service and tests.
6. **Phase 5:** Add Semester Result right drawer.
7. **Phase 6:** Edit Semester Result right drawer.
8. **Phase 7:** Filters, listing, view and academic history.
9. **Phase 8:** Student promotion to next semester.
10. **Phase 9 and 10:** Permissions, data safety and full testing.

---

# First Work Item When Implementation Starts

Start with **Phase 0 and Phase 1** only:

1. Confirm grade scale, passing marks, total marks, and credit-hour policy.
2. Add `credit_hours`, `total_marks`, and `is_active` to courses.
3. Update Course add/edit/list UI.

Uske baad semester enrollment ka foundation build kiya jayega. Result drawer tab implement hoga jab enrollment history aur course assignments semester-wise available honge.
