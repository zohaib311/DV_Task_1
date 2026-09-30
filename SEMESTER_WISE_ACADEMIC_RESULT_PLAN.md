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

- [x] Student/enrollment result API created
- [x] Right drawer UI built
- [x] Dynamic course rows rendered
- [x] Live marks calculation implemented
- [x] Summary metrics implemented
- [x] Submit errors drawer ke andar shown
- [x] Success ke baad list refresh

---

# Phase 5A — Course Assessment Breakdown: Attendance, Midterm and Final

Har course ka result sirf aik `obtained_marks` field se enter nahi hoga. University-style assessment structure mein marks ke components clear aur separately auditable hone chahiye:

| Component | Recommended default | Meaning |
|---|---:|---|
| Attendance | `10` | Attendance-based score |
| Midterm | `30` | Mid examination score |
| Final | `60` | Final examination score |
| Total | `100` | Course maximum marks |

Is default mein `Attendance + Midterm = 40` aur `Final = 60` hai. Lekin **yeh values hard-code nahi hongi**: har course ke liye admin/academic staff apni approved marking scheme set kar sakega.

> Attendance ko Midterm ke 40 marks mein hidden/manual mix nahi karna. Attendance, Midterm aur Final teen separate components rahenge; unka sum course total banayega. Is se result sheet, audit aur future attendance automation clear rehti hai.

## 5A.1 Academic policy decisions

`config/academic.php` mein configurable rules add honge:

- `attendance`, `midterm`, aur `final` default maxima.
- Har course component total ka validation rule.
- Kya publish ke liye teeno component marks required hain.
- Overall course passing percentage (current baseline: `50%`).
- Optional final-exam minimum rule, for example `final ke 60 marks mein minimum 30`. Yeh rule default mein disabled hoga jab tak university policy confirm na kare.
- Attendance score manual entry hai ya attendance module se auto-calculate hota hai.

## 5A.2 Course assessment scheme

`courses` table mein course-level configuration fields add honge:

| Field | Example | Purpose |
|---|---:|---|
| `attendance_marks` | `10` | Attendance component maximum |
| `mid_marks` | `30` | Midterm component maximum |
| `final_marks` | `60` | Final component maximum |
| `total_marks` | `100` | Existing course total |

### Course validation

```text
attendance_marks + mid_marks + final_marks = total_marks
```

- Har component zero ya positive numeric value hoga.
- `total_marks` se zyada component marks allow nahi honge.
- Course create aur edit form mein live total/check shown hoga.
- Invalid distribution save nahi hogi.
- Default scheme initially `10 + 30 + 60 = 100` hogi, lekin course-wise editable rahegi.

## 5A.3 Historical snapshots are mandatory

Future course configuration change se old semester result kabhi change nahi hona chahiye. Isliye new scheme enroll karte waqt snapshot hogi:

| Table | New snapshot fields |
|---|---|
| `student_enrollment_courses` | `attendance_marks`, `mid_marks`, `final_marks` |
| `semester_result_items` | component maxima aur obtained component marks |

`semester_result_items` mein yeh values store hongi:

| Field | Purpose |
|---|---|
| `attendance_marks`, `mid_marks`, `final_marks` | Maximum marks snapshot |
| `attendance_obtained_marks` | Attendance score entered/calculated for that result |
| `mid_obtained_marks` | Midterm score |
| `final_obtained_marks` | Final score |
| `obtained_marks` | Server-derived sum of the three components |

Existing `obtained_marks`, percentage, grade, GP aur status fields remain rahenge; koi old result overwrite/delete nahi hoga. Old Phase-5 results ke component fields nullable rahenge unless a verified manual migration assigns a breakdown.

## 5A.4 Calculation changes

Per-course total server-side derive hoga:

```text
obtained_marks = attendance_obtained_marks + mid_obtained_marks + final_obtained_marks
percentage = (obtained_marks / total_marks) × 100
```

Uske baad existing grade scale, grade point, SGPA aur CGPA rules use honge.

### Pass/Fail rule

Default rule:

```text
overall course percentage >= configured passing percentage → Pass
otherwise → Fail
```

Optional policy enabled hone par additional rule:

```text
final_obtained_marks >= configured final minimum marks
```

Yani overall `50%` hone ke bawajood final-minimum policy fail ho to course `Fail` hoga. Is decision ko official university policy confirm karegi; UI aur calculator hard-code nahi karenge.

### Component validation

- Attendance obtained marks `0` se `attendance_marks` ke darmiyan.
- Midterm obtained marks `0` se `mid_marks` ke darmiyan.
- Final obtained marks `0` se `final_marks` ke darmiyan.
- Published result ke liye all required components entered hon.
- Draft mein blank components allowed hon, lekin result status `Draft` aur final metrics incomplete rahenge.
- Browser se bheja hua total, percentage, grade, GP, SGPA aur CGPA ignore hoga; backend dobara calculate karega.

## 5A.5 Attendance scope

Is phase mein attendance component score result workflow mein separate field hoga. Proper lecture/day-wise attendance module future enhancement ke liye ready rakha jayega:

1. Attendance records per class/session store honge.
2. Attendance percentage calculate hogi.
3. Approved conversion formula se `attendance_obtained_marks` auto-calculate honge.
4. Manual override only authorized staff ke liye audit trail ke saath hoga.

Pehle iteration mein score direct enter karna allowed hoga agar `attendance_mode = manual` ho. Automated module add hone par result calculation structure change nahi karna padega.

## 5A.6 Add Result drawer update

Phase 5 ka right drawer same clean layout retain karega, lekin marks table ki row yeh ho jayegi:

| # | Course | Code | Cr. Hrs | Attendance | Mid | Final | Obtained | % | Grade | GP | Status |
|---:|---|---|---:|---:|---:|---:|---:|---:|---|---:|---|

- `Attendance`, `Mid`, aur `Final` hi editable inputs honge.
- Input labels mein maximum clearly show hoga, for example `Mid / 30`.
- `Obtained`, percentage, grade, GP aur status live calculated/read-only honge.
- Bottom summary mein total earned/possible marks, percentage, SGPA, CGPA aur overall status existing design ke mutabiq live update honge.
- Existing result drawer ko Phase 6 ke edit workflow mein same component values prefilled milengi.

## 5A.7 Backend/API and data safety

- Enrollment course API component maxima snapshots return karegi.
- Save Draft / Publish API nested component marks accept karegi, not a trusted combined total.
- DB transaction header result aur all course items save karegi.
- Duplicate result, student/enrollment/course ownership, published-result policy aur all current Phase 4 protections remain rahengi.
- Component configuration ya grade policy change ke baad historical published item snapshots unchanged rahenge.

## 5A.8 Tests required

- Course scheme accepts `10 + 30 + 60 = 100`.
- Course scheme rejects a distribution whose sum differs from total marks.
- Enrollment preserves component maxima after course config later changes.
- Result item preserves component maxima and obtained component marks.
- Calculator derives total/percentage/grade correctly from all three components.
- Component boundary validation rejects marks greater than their individual maximum.
- Draft/publish completeness behaves correctly.
- Optional final-minimum policy Pass/Fail test.
- Existing legacy and old single-total result records remain readable.

### Phase 5A completion checklist

- [x] Assessment policy configuration created
- [x] Course assessment-component fields and validation added
- [x] Enrollment component snapshots added
- [x] Result-item component snapshots and obtained fields added
- [x] Calculator updated for Attendance + Midterm + Final
- [x] Course add/edit/list UI updated
- [x] Add Result drawer updated with component inputs
- [x] Save Draft / Publish API updated
- [x] Historical-data compatibility preserved
- [x] Assessment calculation and validation tests written

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

### Implemented edit and audit policy

- Draft results can be edited by authenticated academic users.
- Published results are protected by default. Since Phase 9A, their correction requires the database-backed `results.edit-published` permission; the `SemesterResultPolicy` remains the single authorization checkpoint for both prefill and update requests.
- Every update creates a `semester_result_audits` record containing the editor, action, and before/after calculated result snapshots. Published academic records are therefore corrected through an auditable update rather than direct deletion.
- Editing a published earlier semester rebuilds CGPA for that student in academic semester order, including every later published semester.

- [x] Edit drawer prefill implemented
- [x] Permission policy added
- [x] Result update transaction added
- [x] Later semester CGPA recalculation added
- [x] Update audit/history policy decided

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

- [x] Semester-aware filters added
- [x] Result list API updated
- [x] View result details updated
- [x] Student academic history view added
- [x] Direct delete disabled/removed

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

- [x] Promote student action created
- [x] Next semester enrollment creation implemented
- [x] Course assignment flow connected
- [x] Failure/repeat policy implemented
- [x] Academic history verified

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
7. **Phase 5A:** Attendance, Midterm and Final assessment breakdown.
8. **Phase 6:** Edit Semester Result right drawer.
9. **Phase 7:** Filters, listing, view and academic history.
10. **Phase 8:** Student promotion to next semester.
11. **Phase 9 and 10:** Permissions, data safety and full testing.

---

# First Work Item When Implementation Starts

Start with **Phase 0 and Phase 1** only:

1. Confirm grade scale, passing marks, total marks, and credit-hour policy.
2. Add `credit_hours`, `total_marks`, and `is_active` to courses.
3. Update Course add/edit/list UI.

Uske baad semester enrollment ka foundation build kiya jayega. Result drawer tab implement hoga jab enrollment history aur course assignments semester-wise available honge.
