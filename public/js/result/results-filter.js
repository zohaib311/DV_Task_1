document.addEventListener('DOMContentLoaded', () => {
    const department = document.getElementById('department_id');
    const section = document.getElementById('section_id');
    const year = document.getElementById('academic_year_filter');
    const semester = document.getElementById('semester_filter');
    const resultStatus = document.getElementById('result_status_filter');
    const initial = document.getElementById('initial_state_msg');
    const loading = document.getElementById('loading_spinner');
    const table = document.getElementById('students_table_wrapper');
    const tbody = document.getElementById('students_table_body');
    const empty = document.getElementById('no_students_msg');
    const drawer = document.getElementById('semesterResultDrawer');
    const sheetModal = document.getElementById('semesterResultSheetModal');
    let filterOptions = [];
    let currentSectionId = null;

    const s = drawer ? {
        form: document.getElementById('semesterResultForm'), studentId: document.getElementById('semester_result_student_id'), enrollmentId: document.getElementById('semester_result_enrollment_id'), enrollmentSelect: document.getElementById('semester_result_enrollment_select'),
        loading: document.getElementById('semester_result_loading'), content: document.getElementById('semester_result_content'), errors: document.getElementById('semester_result_errors'), locked: document.getElementById('semester_result_locked'), coursesBody: document.getElementById('semester_result_courses_body'), courseCount: document.getElementById('semester_result_course_count'),
        actionState: document.getElementById('semester_result_action_state'), status: document.getElementById('semester_result_summary_status'), total: document.getElementById('semester_result_total_marks'), percentage: document.getElementById('semester_result_percentage'), sgpa: document.getElementById('semester_result_sgpa'), cgpa: document.getElementById('semester_result_cgpa'),
        studentName: document.getElementById('semester_result_student_name'), registrationNo: document.getElementById('semester_result_registration_no'), department: document.getElementById('semester_result_department'), section: document.getElementById('semester_result_section'), academicYear: document.getElementById('semester_result_academic_year'), title: document.getElementById('semesterResultDrawerLabel'), draftButton: document.getElementById('semester_result_draft_button'), publishButton: document.getElementById('semester_result_publish_button'), savedSummary: document.getElementById('semester_result_saved_summary'), savedStatus: document.getElementById('semester_result_saved_status'), savedNote: document.getElementById('semester_result_saved_note'),
        gradeScale: JSON.parse(drawer.dataset.gradeScale || '[]'), finalMinimumEnabled: drawer.dataset.finalMinimumEnabled === 'true', finalMinimumPercentage: Number(drawer.dataset.finalMinimumPercentage || 0), allowPublishedEdits: drawer.dataset.allowPublishedEdits === 'true',
        student: null, enrollment: null, courses: [], priorItems: [], mode: 'create', resultId: null, isLocked: false,
    } : null;

    department?.addEventListener('change', async () => {
        section.innerHTML = '<option value="">-- Select Section --</option>'; section.disabled = true; resetAcademicFilters(); resetStudentViews();
        if (!department.value) { section.innerHTML = '<option value="">-- Select Department First --</option>'; return; }
        try {
            const response = await fetch(`/result/get-sections/${department.value}`); const sections = await response.json();
            if (sections.length) { section.disabled = false; sections.forEach((item) => section.add(new Option(item.name, item.id))); }
            else section.innerHTML = '<option value="">No Sections in this Department</option>';
        } catch (error) { console.error(error); pageNotice('Unable to load sections. Please try again.', 'danger'); }
    });
    section?.addEventListener('change', async () => {
        currentSectionId = section.value || null; resetAcademicFilters(); resetStudentViews();
        if (!currentSectionId) { initial.style.display = 'block'; return; }
        await loadAcademicFilters(); loadStudents();
    });
    year?.addEventListener('change', () => { populateSemesters(); loadStudents(); });
    semester?.addEventListener('change', loadStudents);
    resultStatus?.addEventListener('change', loadStudents);

    async function loadAcademicFilters() {
        try {
            const response = await fetch(`/result/get-filter-options/${currentSectionId}`); if (!response.ok) throw new Error('Filter options request failed');
            const data = await response.json(); filterOptions = data.enrollments || [];
            year.innerHTML = '<option value="">All Academic Years</option>'; (data.academic_years || []).forEach((value) => year.add(new Option(value, value)));
            year.disabled = semester.disabled = resultStatus.disabled = false; populateSemesters();
        } catch (error) { console.error(error); pageNotice('Unable to load academic filters. Please try again.', 'danger'); }
    }
    function resetAcademicFilters() {
        filterOptions = []; year.innerHTML = '<option value="">All Academic Years</option>'; semester.innerHTML = '<option value="">All Semesters</option>'; resultStatus.value = 'all';
        year.disabled = semester.disabled = resultStatus.disabled = true;
    }
    function populateSemesters() {
        const selected = semester.value; semester.innerHTML = '<option value="">All Semesters</option>';
        [...new Set(filterOptions.filter((item) => !year.value || item.academic_year === year.value).map((item) => item.semester))].forEach((value) => semester.add(new Option(value, value)));
        semester.value = [...semester.options].some((option) => option.value === selected) ? selected : '';
    }
    async function loadStudents() {
        if (!currentSectionId) return;
        resetStudentViews(); loading.style.display = 'block';
        const query = new URLSearchParams({ department_id: department.value, academic_year: year.value, semester: semester.value, status: resultStatus.value });
        try {
            const response = await fetch(`/result/get-students/${currentSectionId}?${query.toString()}`); if (!response.ok) throw new Error('Result rows request failed');
            const rows = await response.json(); loading.style.display = 'none';
            if (!rows.length) { empty.querySelector('strong').textContent = 'No academic records found'; empty.querySelector('span').textContent = 'Try another semester, academic year, or result status.'; empty.style.display = 'flex'; return; }
            tbody.innerHTML = rows.map(renderStudentRow).join(''); table.style.display = 'block';
        } catch (error) { loading.style.display = 'none'; console.error(error); pageNotice('Unable to load student results. Please try again.', 'danger'); }
    }
    function renderStudentRow(student, index) {
        const enrollment = student.listed_enrollment; const result = enrollment.semester_result;
        const canEdit = !enrollment.moderation_url && result && (!result.published_at || s?.allowPublishedEdits);
        const add = enrollment.moderation_url ? `<a class="action__btn action__add" href="${escapeAttribute(enrollment.moderation_url)}" title="${result ? 'Result already added — open saved moderation sheet' : 'Review teacher marks'}"><i class="bi bi-journal-check"></i></a>` : `<button type="button" class="action__btn ${result ? 'action__info' : 'action__add'} btn-add-semester-result" title="${result ? 'Semester result already added' : 'Add semester result'}" data-bs-toggle="offcanvas" data-bs-target="#semesterResultDrawer" data-student-id="${student.id}" data-enrollment-id="${enrollment.id}" data-existing-result="${result ? 'true' : 'false'}"><i class="bi ${result ? 'bi-journal-check' : 'bi-plus-circle'}"></i></button>`;
        const view = result ? `<button type="button" class="action__btn action__info btn-view-semester-result" title="View semester result sheet" data-bs-toggle="modal" data-bs-target="#semesterResultSheetModal" data-result-id="${result.id}"><i class="bi bi-eye"></i></button>` : '';
        const edit = canEdit ? `<button type="button" class="action__btn action__edit btn-edit-semester-result" title="Edit semester result" data-bs-toggle="offcanvas" data-bs-target="#semesterResultDrawer" data-result-id="${result.id}"><i class="bi bi-pencil-square"></i></button>` : '';
        const history = `<button type="button" class="action__btn action__info btn-view-academic-history" title="View academic history" data-bs-toggle="modal" data-bs-target="#semesterResultSheetModal" data-student-id="${student.id}"><i class="bi bi-clock-history"></i></button>`;
        const state = !result ? '<span class="result-list-empty">Not entered</span>' : `<div class="semester-result-list-state"><span class="semester-result-list-label">${result.published_at ? 'Published' : 'Draft saved'}</span><span class="semester-result-list-status is-${escapeAttribute(result.status.toLowerCase())}">${escapeHtml(result.status)}</span></div>`;
        return `<tr><td>${index + 1}</td><td><span class="student-id-badge">${escapeHtml(student.registration_no || '—')}</span></td><td class="fw-bold text-dark">${escapeHtml(student.name)}</td><td class="text-muted">${escapeHtml(student.email || 'N/A')}</td><td><div class="result-list-semester"><strong>${escapeHtml(enrollment.semester)}</strong><small>${escapeHtml(enrollment.academic_year)}</small></div></td><td class="text-center result-list-number">${enrollment.course_count}</td><td class="text-center result-list-number">${result?.semester_percentage !== null && result ? `${format(result.semester_percentage, 2)}%` : '—'}</td><td class="text-center result-list-number">${result?.sgpa !== null && result ? format(result.sgpa, 2) : '—'}</td><td class="text-center result-list-number">${result?.cgpa !== null && result ? format(result.cgpa, 2) : '—'}</td><td>${state}</td><td class="text-center"><div class="action__buttons">${view}${add}${edit}${history}</div></td></tr>`;
    }
    function resetStudentViews() { initial.style.display = 'none'; loading.style.display = 'none'; table.style.display = 'none'; empty.style.display = 'none'; tbody.innerHTML = ''; }

    document.addEventListener('click', (event) => {
        const add = event.target.closest('.btn-add-semester-result'); if (add && s) openCreateDrawer(add.dataset.studentId, add.dataset.enrollmentId, add.dataset.existingResult === 'true');
        const edit = event.target.closest('.btn-edit-semester-result'); if (edit && s) openEditDrawer(edit.dataset.resultId);
        const view = event.target.closest('.btn-view-semester-result'); if (view) loadResultSheet(view.dataset.resultId);
        const history = event.target.closest('.btn-view-academic-history'); if (history) loadAcademicHistory(history.dataset.studentId);
        const historySheet = event.target.closest('.btn-history-sheet'); if (historySheet) loadResultSheet(historySheet.dataset.resultId);
    });

    function showSheetLoading() { sheetModal.querySelector('#semester_sheet_loading').hidden = false; sheetModal.querySelector('#semester_sheet_content').hidden = true; }
    async function loadResultSheet(resultId) {
        showSheetLoading();
        try { const response = await fetch(`/result/semester-result/${resultId}/sheet`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Sheet request failed'); renderSheet(await response.json()); }
        catch (error) { console.error(error); pageNotice('Unable to load the semester result sheet.', 'danger'); }
    }
    async function loadAcademicHistory(studentId) {
        showSheetLoading();
        try { const response = await fetch(`/result/student/${studentId}/academic-history`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('History request failed'); const data = await response.json(); renderSheet({ ...data, sheet: null }); }
        catch (error) { console.error(error); pageNotice('Unable to load academic history.', 'danger'); }
    }
    function renderSheet(data) {
        const student = data.student; const sheet = data.sheet;
        setText('sheet_student_name', student.name); setText('sheet_registration_no', student.registration_no); setText('sheet_department', student.department_name); setText('sheet_section', student.section_name);
        const record = document.getElementById('semester_sheet_record'); record.hidden = !sheet;
        if (sheet) {
            setText('sheet_semester', sheet.semester); setText('sheet_academic_year', sheet.academic_year); setText('sheet_percentage', sheet.semester_percentage !== null ? `${format(sheet.semester_percentage, 2)}%` : '—'); setText('sheet_sgpa', optionalNumber(sheet.sgpa)); setText('sheet_cgpa', optionalNumber(sheet.cgpa)); setText('sheet_record_state', sheet.published_at ? 'Published' : 'Draft');
            const status = document.getElementById('sheet_status'); status.textContent = sheet.status; status.className = `semester-status is-${sheet.status.toLowerCase()}`;
            document.getElementById('sheet_course_rows').innerHTML = sheet.items.map((item) => `<tr><td><strong>${escapeHtml(item.course_name)}</strong><small>${escapeHtml(item.course_code)}</small></td><td>${format(item.credit_hours, 1)}</td><td>${component(item.attendance_obtained_marks, item.attendance_marks)}</td><td>${component(item.mid_obtained_marks, item.mid_marks)}</td><td>${component(item.final_obtained_marks, item.final_marks)}</td><td>${optionalNumber(item.obtained_marks)} / ${format(item.total_marks, 0)}</td><td>${item.percentage !== null ? `${format(item.percentage, 2)}%` : '—'}</td><td>${escapeHtml(item.grade || '—')}</td><td>${optionalNumber(item.grade_point)}</td><td><span class="course-status is-${escapeAttribute((item.status || 'Draft').toLowerCase())}">${escapeHtml(item.status || 'Draft')}</span></td></tr>`).join('');
        }
        const sheetBody = document.getElementById('sheet_course_rows');
        const sheetHeader = sheetBody.closest('table').querySelector('thead tr');
        if (!sheetHeader.dataset.legacyHtml) sheetHeader.dataset.legacyHtml = sheetHeader.innerHTML;
        sheetHeader.innerHTML = sheetHeader.dataset.legacyHtml;
        if (sheet?.items.some((item) => item.assessment_snapshot)) {
            sheetHeader.innerHTML = '<th>Course</th><th>Cr.</th><th>Assessment breakdown</th><th>Obtained</th><th>%</th><th>Grade</th><th>GP</th><th>Status</th>';
            sheetBody.innerHTML = sheet.items.map((item) => {
                const evidence = item.assessment_snapshot;
                const breakdown = evidence ? evidence.scheme.map((part) => `${escapeHtml(part.code)}: ${format(evidence.student.components[part.code], 2)} / ${format(part.allocation, 2)}`).join('<br>') : 'Legacy result';
                return `<tr><td><strong>${escapeHtml(item.course_name)}</strong><small>${escapeHtml(item.course_code)}</small></td><td>${format(item.credit_hours, 1)}</td><td>${breakdown}</td><td>${format(item.obtained_marks, 2)} / ${format(item.total_marks, 0)}</td><td>${format(item.percentage, 2)}%</td><td>${escapeHtml(item.grade)}</td><td>${format(item.grade_point, 2)}</td><td>${escapeHtml(item.status)}</td></tr>`;
            }).join('');
        }
        document.getElementById('sheet_history_heading').textContent = sheet ? 'Academic history' : 'All semester records';
        document.getElementById('sheet_history_count').textContent = `${data.history.length} ${data.history.length === 1 ? 'semester' : 'semesters'}`;
        document.getElementById('sheet_history_rows').innerHTML = data.history.map((item) => `<tr><td><strong>${escapeHtml(item.semester)}</strong></td><td>${escapeHtml(item.academic_year)}</td><td>${optionalNumber(item.sgpa)}</td><td>${optionalNumber(item.cgpa)}</td><td>${historyState(item)}</td><td>${item.result_id ? `<button type="button" class="action__btn action__info btn-history-sheet" title="View semester sheet" data-result-id="${item.result_id}"><i class="bi bi-eye"></i></button>` : '—'}</td></tr>`).join('');
        sheetModal.querySelector('#semester_sheet_loading').hidden = true; sheetModal.querySelector('#semester_sheet_content').hidden = false;
    }
    function component(value, maximum) { return value === null ? '—' : `${format(value, 2)} / ${format(maximum, 0)}`; }
    function historyState(item) { if (item.status === 'Not entered') return '<span class="result-list-empty">Not entered</span>'; return `<span class="semester-result-list-status is-${escapeAttribute(item.status.toLowerCase())}">${escapeHtml(item.published_at ? 'Published · ' : '')}${escapeHtml(item.status)}</span>`; }

    async function openCreateDrawer(studentId, preferredEnrollmentId, knownExisting) {
        resetDrawer(); s.loading.hidden = false; s.content.hidden = true; if (knownExisting) s.actionState.textContent = 'Result already added';
        try {
            const response = await fetch(`${drawer.dataset.studentEnrollmentsUrl}/${studentId}/semester-enrollments`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Student enrollments request failed');
            const data = await response.json(); s.student = data.student; s.studentId.value = data.student.id; s.studentName.textContent = data.student.name; s.registrationNo.textContent = data.student.registration_no || '—';
            s.enrollmentSelect.innerHTML = ''; data.enrollments.forEach((item) => { const label = item.existing_result ? ` — ${item.existing_result.published_at ? 'Published' : 'Draft saved'}` : ' — Ready for result'; s.enrollmentSelect.add(new Option(`${item.semester} · ${item.academic_year}${label}`, item.id)); });
            const active = data.enrollments.find((item) => item.enrollment_status === 'active'); s.enrollmentSelect.value = preferredEnrollmentId || active?.id || data.enrollments[0]?.id || '';
            s.loading.hidden = true; s.content.hidden = false; if (s.enrollmentSelect.value) await loadEnrollment(s.enrollmentSelect.value); else showDrawerError('This student has no semester enrollment yet. Create an enrollment before adding a result.');
        } catch (error) { s.loading.hidden = true; showDrawerError('Unable to load the student academic record. Please try again.'); console.error(error); }
    }
    async function openEditDrawer(resultId) {
        resetDrawer(); s.mode = 'edit'; s.resultId = Number(resultId); s.title.textContent = 'Edit Semester Result'; s.actionState.textContent = 'Loading edit'; setButtonLabels(); s.loading.hidden = false; s.content.hidden = true;
        try {
            const response = await fetch(`${drawer.dataset.resultUrl}/${resultId}/data`, { headers: { Accept: 'application/json' } }); const data = await response.json();
            if (!response.ok) { s.loading.hidden = true; s.content.hidden = false; showDrawerError(data.message || 'This result is not available for editing.'); if (response.status === 403) setDrawerLocked(true, 'Published result edits are disabled by academic policy.'); return; }
            s.student = data.student; s.enrollment = data.enrollment; s.courses = data.courses; s.priorItems = data.prior_result_items || []; s.studentId.value = data.student.id; s.enrollmentId.value = data.enrollment.id; s.studentName.textContent = data.student.name; s.registrationNo.textContent = data.student.registration_no || '—'; setEnrollmentInfo(data.enrollment);
            s.enrollmentSelect.innerHTML = ''; s.enrollmentSelect.add(new Option(`${data.enrollment.semester} · ${data.enrollment.academic_year}`, data.enrollment.id)); s.enrollmentSelect.disabled = true; renderCourses();
            Object.entries(data.result.items || {}).forEach(([courseId, marks]) => ['attendance', 'mid', 'final'].forEach((part) => { const input = s.coursesBody.querySelector(`[data-course-id="${courseId}"] [data-component="${part}_obtained_marks"]`); if (input && marks[`${part}_obtained_marks`] !== null) input.value = marks[`${part}_obtained_marks`]; }));
            s.savedSummary.hidden = false; s.savedStatus.textContent = data.result.published_at ? 'Published semester result' : 'Saved draft result'; s.savedNote.textContent = data.result.published_at ? `Saved: ${format(data.result.semester_percentage, 2)}% · SGPA ${format(data.result.sgpa, 2)} · CGPA ${format(data.result.cgpa, 2)}` : 'Saved component marks are loaded below. Complete or revise them before publishing.';
            s.actionState.textContent = data.result.published_at ? 'Editing published result' : 'Editing draft'; updateSummary(); s.loading.hidden = true; s.content.hidden = false;
        } catch (error) { s.loading.hidden = true; s.content.hidden = false; showDrawerError('Unable to load result details for editing. Please try again.'); console.error(error); }
    }
    s?.enrollmentSelect.addEventListener('change', () => { if (s.mode === 'create') loadEnrollment(s.enrollmentSelect.value); });
    async function loadEnrollment(id) {
        clearErrors(); s.coursesBody.innerHTML = ''; s.enrollmentId.value = id; setDrawerLocked(false);
        try { const response = await fetch(`${drawer.dataset.enrollmentUrl}/${id}/data`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Enrollment request failed'); const data = await response.json(); s.enrollment = data.enrollment; s.courses = data.courses; s.priorItems = data.prior_result_items || []; setEnrollmentInfo(data.enrollment); renderCourses(); updateSummary(); if (data.enrollment.existing_result) setDrawerLocked(true, data.enrollment.existing_result.published_at ? 'This semester result is already published.' : 'A draft already exists for this semester. Use the edit button in the student list.'); }
        catch (error) { showDrawerError('Unable to load enrollment courses. Please select the semester again.'); console.error(error); }
    }
    function setEnrollmentInfo(data) { s.department.textContent = data.department_name || '—'; s.section.textContent = data.section_name || '—'; s.academicYear.textContent = data.academic_year || '—'; }
    function renderCourses() {
        s.courseCount.textContent = `${s.courses.length} ${s.courses.length === 1 ? 'course' : 'courses'}`;
        s.coursesBody.innerHTML = s.courses.map((course, i) => `<tr data-course-id="${course.student_enrollment_course_id}"><td>${i + 1}</td><td><strong>${escapeHtml(course.course_name)}</strong></td><td>${escapeHtml(course.course_code)}</td><td>${format(course.credit_hours, 1)}</td>${markField(course, 'attendance', 'Attendance')}${markField(course, 'mid', 'Midterm')}${markField(course, 'final', 'Final')}<td class="result-row-obtained">—</td><td class="result-row-percentage">—</td><td class="result-row-grade">—</td><td class="result-row-gp">—</td><td class="result-row-status"><span class="course-status is-draft">Draft</span></td></tr>`).join('');
        s.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => input.addEventListener('input', updateSummary));
    }
    function markField(course, part, label) { const max = Number(course[`${part}_marks`]); return `<td><div class="assessment-mark-entry"><small>${label} / ${format(max, 0)}</small><input class="form-control assessment-mark-input" type="number" min="0" max="${max}" step="0.01" ${max === 0 ? 'disabled value="0"' : ''} data-component="${part}_obtained_marks"></div></td>`; }
    function updateSummary() {
        if (!s?.courses.length) return; let earned = 0, total = 0, credits = 0, points = 0, complete = true, fail = false; const current = [];
        s.courses.forEach((course) => { const row = s.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`); const marks = {}; let ready = true; total += Number(course.total_marks); ['attendance', 'mid', 'final'].forEach((part) => { const max = Number(course[`${part}_marks`]); const input = row.querySelector(`[data-component="${part}_obtained_marks"]`); const value = input.value.trim(); input.classList.remove('is-invalid'); if (!max) { marks[part] = 0; return; } if (value === '' || Number(value) < 0 || Number(value) > max) { ready = false; if (value !== '') input.classList.add('is-invalid'); return; } marks[part] = Number(value); }); if (!ready) { complete = false; preview(row, null); return; } const obtained = marks.attendance + marks.mid + marks.final; const out = outcome(obtained, Number(course.total_marks), marks.final, Number(course.final_marks)); earned += obtained; credits += Number(course.credit_hours); points += out.point * Number(course.credit_hours); fail ||= out.status === 'Fail'; current.push({ course_id: course.course_id, course_code: course.course_code, credit_hours: course.credit_hours, grade_point: out.point, attempted_at: new Date().toISOString() }); preview(row, out); });
        s.total.textContent = complete ? `${format(earned, 2)} / ${format(total, 0)}` : `— / ${format(total, 0)}`; if (!complete) { setSummaryStatus('Draft'); s.percentage.textContent = s.sgpa.textContent = s.cgpa.textContent = '—'; return; } s.percentage.textContent = `${format((earned / total) * 100, 2)}%`; s.sgpa.textContent = format(points / credits, 2); s.cgpa.textContent = cgpa([...s.priorItems, ...current]); setSummaryStatus(fail ? 'Fail' : 'Pass');
    }
    function outcome(obtained, total, final, finalMax) { const percentage = obtained / total * 100; const grade = s.gradeScale.find((item) => percentage >= Number(item.minimum_percentage)); const finalFail = s.finalMinimumEnabled && finalMax > 0 && final / finalMax * 100 < s.finalMinimumPercentage; return finalFail ? { obtained, percentage, grade: 'F', point: 0, status: 'Fail' } : { obtained, percentage, grade: grade.grade, point: Number(grade.grade_point), status: grade.status }; }
    function preview(row, data) { const cells = ['.result-row-obtained', '.result-row-percentage', '.result-row-grade', '.result-row-gp', '.result-row-status'].map((x) => row.querySelector(x)); if (!data) { cells.slice(0, 4).forEach((cell) => cell.textContent = '—'); cells[4].innerHTML = '<span class="course-status is-draft">Draft</span>'; return; } cells[0].textContent = format(data.obtained, 2); cells[1].textContent = `${format(data.percentage, 2)}%`; cells[2].textContent = data.grade; cells[3].textContent = format(data.point, 2); cells[4].innerHTML = `<span class="course-status is-${data.status.toLowerCase()}">${data.status}</span>`; }
    function cgpa(items) { const latest = new Map(); [...items].sort((a, b) => new Date(a.attempted_at || 0) - new Date(b.attempted_at || 0)).forEach((item) => latest.set(item.course_id ? `id:${item.course_id}` : `code:${item.course_code}`, item)); let credits = 0, points = 0; latest.forEach((item) => { credits += Number(item.credit_hours); points += Number(item.grade_point) * Number(item.credit_hours); }); return credits ? format(points / credits, 2) : '—'; }
    function setSummaryStatus(value) { s.status.textContent = value; s.status.className = `semester-status is-${value.toLowerCase()}`; if (s.mode === 'create') s.actionState.textContent = value === 'Draft' ? 'Draft' : 'Ready to publish'; }
    function setDrawerLocked(locked, message = '') { s.isLocked = locked; s.locked.hidden = !locked; s.locked.innerHTML = locked ? `<i class="bi bi-shield-check"></i><div><strong>Result already added</strong><span>${escapeHtml(message)}</span></div>` : ''; s.form.querySelectorAll('[data-result-action]').forEach((button) => button.disabled = locked); s.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => input.disabled = locked); }
    s?.form.addEventListener('submit', async (event) => {
        event.preventDefault(); const action = event.submitter?.dataset.resultAction; if (!action || !s.enrollment?.id || s.isLocked) return; clearErrors(); const buttons = s.form.querySelectorAll('[data-result-action]'); buttons.forEach((button) => button.disabled = true);
        const courses = s.courses.map((course) => { const row = s.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`); const mark = (part) => { const value = row.querySelector(`[data-component="${part}_obtained_marks"]`).value.trim(); return value === '' ? null : Number(value); }; return { student_enrollment_course_id: course.student_enrollment_course_id, attendance_obtained_marks: mark('attendance'), mid_obtained_marks: mark('mid'), final_obtained_marks: mark('final') }; });
        try { const url = s.mode === 'edit' ? `${drawer.dataset.resultUrl}/${s.resultId}` : drawer.dataset.storeUrl; const response = await fetch(url, { method: s.mode === 'edit' ? 'PUT' : 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': s.form.querySelector('[name="_token"]').value }, body: JSON.stringify({ student_id: Number(s.studentId.value), enrollment_id: Number(s.enrollmentId.value), action, courses }) }); const data = await response.json(); if (!response.ok) { displayErrors(data.errors || { result: data.message || 'Unable to save the result.' }); return; } bootstrap.Offcanvas.getOrCreateInstance(drawer).hide(); pageNotice(data.message, 'success'); loadStudents(); }
        catch (error) { console.error(error); showDrawerError('A network issue prevented the result from being saved. Please try again.'); } finally { if (!s.isLocked) buttons.forEach((button) => button.disabled = false); }
    });
    function resetDrawer() { clearErrors(); s.form.reset(); s.mode = 'create'; s.resultId = null; s.isLocked = false; s.title.textContent = 'Add Semester Result'; s.enrollmentSelect.disabled = false; s.enrollmentId.value = ''; s.courses = []; s.priorItems = []; s.coursesBody.innerHTML = ''; s.courseCount.textContent = '0 courses'; s.savedSummary.hidden = true; ['department', 'section', 'academicYear'].forEach((key) => s[key].textContent = '—'); s.total.textContent = '— / —'; s.percentage.textContent = s.sgpa.textContent = s.cgpa.textContent = '—'; setSummaryStatus('Draft'); setDrawerLocked(false); setButtonLabels(); }
    function setButtonLabels() { if (s.draftButton) s.draftButton.innerHTML = s.mode === 'edit' ? '<i class="bi bi-save2"></i> Update Draft' : '<i class="bi bi-save2"></i> Save Draft'; if (s.publishButton) s.publishButton.innerHTML = s.mode === 'edit' ? '<i class="bi bi-check2-circle"></i> Update &amp; Publish' : '<i class="bi bi-check2-circle"></i> Save &amp; Publish'; }
    function displayErrors(errors) { const messages = Object.values(errors).flat().map(escapeHtml); s.errors.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i><div><strong>Please review the result entry.</strong><ul>${messages.map((message) => `<li>${message}</li>`).join('')}</ul></div>`; s.errors.hidden = false; }
    function showDrawerError(message) { displayErrors({ result: message }); }
    function clearErrors() { if (s) { s.errors.hidden = true; s.errors.innerHTML = ''; } }
    function pageNotice(message, type) { document.querySelector('.results-page .results-alert.dynamic-notice')?.remove(); const notice = document.createElement('div'); notice.className = `alert alert-${type} results-alert dynamic-notice`; notice.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'} me-2"></i>${escapeHtml(message)}`; document.querySelector('.results-main-card-body')?.prepend(notice); window.setTimeout(() => notice.remove(), 4500); }
    function setText(id, value) { const el = document.getElementById(id); if (el) el.textContent = value || '—'; }
    function optionalNumber(value) { return value === null || value === undefined ? '—' : format(value, 2); }
    function format(value, decimals) { return Number(value).toFixed(decimals); }
    function escapeHtml(value) { const el = document.createElement('div'); el.textContent = value ?? ''; return el.innerHTML; }
    function escapeAttribute(value) { return escapeHtml(value).replace(/"/g, '&quot;'); }
});
