document.addEventListener('DOMContentLoaded', () => {
    const department = document.getElementById('department_id');
    const section = document.getElementById('section_id');
    const initial = document.getElementById('initial_state_msg');
    const loading = document.getElementById('loading_spinner');
    const table = document.getElementById('students_table_wrapper');
    const tbody = document.getElementById('students_table_body');
    const empty = document.getElementById('no_students_msg');
    const drawer = document.getElementById('semesterResultDrawer');
    let currentSectionId = null;

    const s = drawer ? {
        form: document.getElementById('semesterResultForm'), studentId: document.getElementById('semester_result_student_id'), enrollmentId: document.getElementById('semester_result_enrollment_id'), enrollmentSelect: document.getElementById('semester_result_enrollment_select'),
        loading: document.getElementById('semester_result_loading'), content: document.getElementById('semester_result_content'), errors: document.getElementById('semester_result_errors'), locked: document.getElementById('semester_result_locked'), coursesBody: document.getElementById('semester_result_courses_body'), courseCount: document.getElementById('semester_result_course_count'),
        actionState: document.getElementById('semester_result_action_state'), status: document.getElementById('semester_result_summary_status'), total: document.getElementById('semester_result_total_marks'), percentage: document.getElementById('semester_result_percentage'), sgpa: document.getElementById('semester_result_sgpa'), cgpa: document.getElementById('semester_result_cgpa'),
        studentName: document.getElementById('semester_result_student_name'), registrationNo: document.getElementById('semester_result_registration_no'), department: document.getElementById('semester_result_department'), section: document.getElementById('semester_result_section'), academicYear: document.getElementById('semester_result_academic_year'), title: document.getElementById('semesterResultDrawerLabel'), draftButton: document.getElementById('semester_result_draft_button'), publishButton: document.getElementById('semester_result_publish_button'),
        gradeScale: JSON.parse(drawer.dataset.gradeScale || '[]'), finalMinimumEnabled: drawer.dataset.finalMinimumEnabled === 'true', finalMinimumPercentage: Number(drawer.dataset.finalMinimumPercentage || 0), allowPublishedEdits: drawer.dataset.allowPublishedEdits === 'true',
        student: null, enrollment: null, courses: [], priorItems: [], mode: 'create', resultId: null, isLocked: false,
    } : null;

    department?.addEventListener('change', async () => {
        section.innerHTML = '<option value="">-- Select Section --</option>'; section.disabled = true; resetStudentViews();
        if (!department.value) { section.innerHTML = '<option value="">-- Select Department First --</option>'; return; }
        try {
            const response = await fetch(`/result/get-sections/${department.value}`); const sections = await response.json();
            if (sections.length) { section.disabled = false; sections.forEach((item) => section.add(new Option(item.name, item.id))); }
            else section.innerHTML = '<option value="">No Sections in this Department</option>';
        } catch (error) { console.error(error); showPageNotice('Sections load nahi ho sakin. Please try again.', 'danger'); }
    });
    section?.addEventListener('change', () => { currentSectionId = section.value || null; if (!currentSectionId) { resetStudentViews(); initial.style.display = 'block'; return; } loadStudents(currentSectionId); });

    async function loadStudents(sectionId) {
        resetStudentViews(); loading.style.display = 'block';
        try {
            const response = await fetch(`/result/get-students/${sectionId}`); if (!response.ok) throw new Error('Students request failed');
            const students = await response.json(); loading.style.display = 'none';
            if (!students.length) { empty.style.display = 'flex'; return; }
            tbody.innerHTML = students.map(renderStudentRow).join(''); table.style.display = 'block';
        } catch (error) { loading.style.display = 'none'; console.error(error); showPageNotice('Students load nahi ho sakay. Please try again.', 'danger'); }
    }

    function renderStudentRow(student, index) {
        const legacy = student.results?.[student.results.length - 1];
        const enrollment = student.active_semester_enrollment;
        const result = enrollment?.semester_result;
        const status = result ? `<div class="semester-result-list-state"><span class="semester-result-list-label"><i class="bi bi-journal-check"></i> Semester result added</span><strong>${escapeHtml(enrollment.semester)} &middot; ${escapeHtml(enrollment.academic_year)}</strong><small>${result.published_at ? 'Published' : 'Draft saved'} <span class="semester-result-list-status is-${escapeAttribute(result.status.toLowerCase())}">${escapeHtml(result.status)}</span></small></div>` : legacy ? `<div><div class="fw-bold text-dark">${escapeHtml(legacy.course?.name || 'N/A')}</div><small class="text-muted">Grade: <strong>${escapeHtml(legacy.grade || 'N/A')}</strong> | GPA: <strong>${escapeHtml(legacy.gpa || '0')}</strong></small></div>` : '<span class="badge bg-secondary">No semester result</span>';
        const legacyActions = legacy ? `<button type="button" class="action__btn action__info btn-view-result" title="View legacy result" data-bs-toggle="modal" data-bs-target="#viewResultModal" data-result-id="${legacy.id}" data-student-name="${escapeAttribute(student.name)}" data-student-email="${escapeAttribute(student.email || 'N/A')}" data-student-phone="${escapeAttribute(student.phone || 'N/A')}" data-student-image="${escapeAttribute(student.image || 'default-user.png')}" data-percentage="${legacy.percentage}" data-gpa="${legacy.gpa}" data-cgpa="${legacy.cgpa}" data-grade="${escapeAttribute(legacy.grade)}" data-status="${escapeAttribute(legacy.status)}" data-course-name="${escapeAttribute(legacy.course?.name || 'N/A')}" data-course-code="${escapeAttribute(legacy.course?.code || '')}" data-section-name="${escapeAttribute(student.section?.name || '')}"><i class="bi bi-info-circle"></i></button><button type="button" class="action__btn action__edit btn-edit-result" title="Edit legacy result" data-bs-toggle="offcanvas" data-bs-target="#editResultModal" data-result-id="${legacy.id}" data-student-id="${student.id}" data-section-id="${currentSectionId}" data-course-id="${legacy.course_id}" data-percentage="${legacy.percentage}" data-gpa="${legacy.gpa}" data-cgpa="${legacy.cgpa}" data-grade="${escapeAttribute(legacy.grade)}" data-status="${escapeAttribute(legacy.status)}" data-student-name="${escapeAttribute(student.name)}" data-student-email="${escapeAttribute(student.email || 'N/A')}"><i class="bi bi-pencil-square"></i></button>` : '';
        const canEdit = result && (!result.published_at || s?.allowPublishedEdits);
        const resultAction = canEdit ? `<button type="button" class="action__btn action__edit btn-edit-semester-result" title="Edit semester result" data-bs-toggle="offcanvas" data-bs-target="#semesterResultDrawer" data-result-id="${result.id}"><i class="bi bi-pencil-square"></i></button>` : `<button type="button" class="action__btn ${result ? 'action__info' : 'action__add'} btn-add-semester-result" title="${result ? 'Semester result already added' : 'Add semester result'}" data-bs-toggle="offcanvas" data-bs-target="#semesterResultDrawer" data-student-id="${student.id}" data-existing-result="${result ? 'true' : 'false'}"><i class="bi ${result ? 'bi-journal-check' : 'bi-plus-circle'}"></i></button>`;
        return `<tr><td>${index + 1}</td><td><span class="student-id-badge">${escapeHtml(student.registration_no || '—')}</span></td><td class="fw-bold text-dark">${escapeHtml(student.name)}</td><td class="text-muted">${escapeHtml(student.email || 'N/A')}</td><td>${status}</td><td class="text-center"><div class="action__buttons">${legacyActions}${resultAction}</div></td></tr>`;
    }

    function resetStudentViews() { initial.style.display = 'none'; loading.style.display = 'none'; table.style.display = 'none'; empty.style.display = 'none'; tbody.innerHTML = ''; }
    document.addEventListener('click', (event) => {
        const view = event.target.closest('.btn-view-result'); if (view) fillLegacyView(view);
        const add = event.target.closest('.btn-add-semester-result'); if (add && s) openCreateDrawer(add.dataset.studentId, add.dataset.existingResult === 'true');
        const editSemester = event.target.closest('.btn-edit-semester-result'); if (editSemester && s) openEditDrawer(editSemester.dataset.resultId);
        const editLegacy = event.target.closest('.btn-edit-result'); if (editLegacy) fillLegacyEdit(editLegacy);
    });
    function fillLegacyView(button) {
        setText('view_student_name', button.dataset.studentName); setText('view_student_email', button.dataset.studentEmail); setText('view_student_phone', button.dataset.studentPhone);
        const image = document.getElementById('view_student_image'); if (image) image.src = `/storage/images/${button.dataset.studentImage}`;
        setText('view_percentage', `${button.dataset.percentage}%`); setText('view_gpa', button.dataset.gpa); setText('view_cgpa', button.dataset.cgpa); setText('view_grade', button.dataset.grade); setText('view_record_id', `#${button.dataset.resultId}`); setText('view_course_name', button.dataset.courseName); setText('view_course_code', button.dataset.courseCode ? `(${button.dataset.courseCode})` : ''); setText('view_section_name', button.dataset.sectionName);
        const badge = document.getElementById('view_status_badge'); if (badge) { badge.textContent = button.dataset.status; badge.className = `status-badge ${button.dataset.status.toLowerCase() === 'pass' ? 'pass' : 'fail'}`; }
    }
    function fillLegacyEdit(button) {
        const form = document.getElementById('editResultForm'); if (form) form.action = `/result/update/${button.dataset.resultId}`;
        const values = { edit_modal_student_id: button.dataset.studentId, edit_modal_section_id: button.dataset.sectionId, edit_modal_student_name: button.dataset.studentName, edit_modal_student_email: button.dataset.studentEmail, edit_course_id: button.dataset.courseId, edit_percentage: button.dataset.percentage, edit_gpa: button.dataset.gpa, edit_cgpa: button.dataset.cgpa, edit_grade: button.dataset.grade, edit_status: button.dataset.status };
        Object.entries(values).forEach(([id, value]) => { const element = document.getElementById(id); if (!element) return; if (['INPUT', 'SELECT'].includes(element.tagName)) element.value = value; else element.textContent = value; });
    }

    async function openCreateDrawer(studentId, knownExisting) {
        resetDrawer(); s.loading.hidden = false; s.content.hidden = true; if (knownExisting) s.actionState.textContent = 'Result already added';
        try {
            const response = await fetch(`${drawer.dataset.studentEnrollmentsUrl}/${studentId}/semester-enrollments`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Student enrollments request failed');
            const data = await response.json(); s.student = data.student; s.studentId.value = data.student.id; s.studentName.textContent = data.student.name; s.registrationNo.textContent = data.student.registration_no || '—';
            s.enrollmentSelect.innerHTML = ''; data.enrollments.forEach((item) => { const label = item.existing_result ? ` — ${item.existing_result.published_at ? 'Published' : 'Draft saved'}` : ' — Ready for result'; s.enrollmentSelect.add(new Option(`${item.semester} · ${item.academic_year}${label}`, item.id)); });
            const active = data.enrollments.find((item) => item.enrollment_status === 'active'); if (active) s.enrollmentSelect.value = active.id; else if (data.enrollments[0]) s.enrollmentSelect.value = data.enrollments[0].id;
            s.loading.hidden = true; s.content.hidden = false; if (s.enrollmentSelect.value) await loadEnrollment(s.enrollmentSelect.value); else showDrawerError('This student has no semester enrollment yet. Create an enrollment before adding a result.');
        } catch (error) { s.loading.hidden = true; showDrawerError('Student academic record load nahi ho saka. Please try again.'); console.error(error); }
    }
    async function openEditDrawer(resultId) {
        resetDrawer(); s.mode = 'edit'; s.resultId = Number(resultId); s.title.textContent = 'Edit Semester Result'; s.actionState.textContent = 'Loading edit'; setButtonLabels(); s.loading.hidden = false; s.content.hidden = true;
        try {
            const response = await fetch(`${drawer.dataset.resultUrl}/${resultId}/data`, { headers: { Accept: 'application/json' } }); const data = await response.json();
            if (!response.ok) { s.loading.hidden = true; s.content.hidden = false; showDrawerError(data.message || 'Result edit ke liye available nahi hai.'); if (response.status === 403) setDrawerLocked(true, 'Published result edits are disabled by academic policy.'); return; }
            s.student = data.student; s.enrollment = data.enrollment; s.courses = data.courses; s.priorItems = data.prior_result_items || []; s.studentId.value = data.student.id; s.enrollmentId.value = data.enrollment.id;
            s.studentName.textContent = data.student.name; s.registrationNo.textContent = data.student.registration_no || '—'; setEnrollmentInfo(data.enrollment); s.enrollmentSelect.innerHTML = ''; s.enrollmentSelect.add(new Option(`${data.enrollment.semester} · ${data.enrollment.academic_year}`, data.enrollment.id)); s.enrollmentSelect.disabled = true;
            renderCourses(); Object.entries(data.result.items || {}).forEach(([courseId, marks]) => ['attendance', 'mid', 'final'].forEach((component) => { const input = s.coursesBody.querySelector(`[data-course-id="${courseId}"] [data-component="${component}_obtained_marks"]`); if (input && marks[`${component}_obtained_marks`] !== null) input.value = marks[`${component}_obtained_marks`]; }));
            s.actionState.textContent = data.result.published_at ? 'Editing published result' : 'Editing draft'; updateSummary(); s.loading.hidden = true; s.content.hidden = false;
        } catch (error) { s.loading.hidden = true; s.content.hidden = false; showDrawerError('Result edit data load nahi ho saka. Please try again.'); console.error(error); }
    }
    s?.enrollmentSelect.addEventListener('change', () => { if (s.mode === 'create') loadEnrollment(s.enrollmentSelect.value); });
    async function loadEnrollment(enrollmentId) {
        clearErrors(); s.coursesBody.innerHTML = ''; s.enrollmentId.value = enrollmentId; setDrawerLocked(false);
        try {
            const response = await fetch(`${drawer.dataset.enrollmentUrl}/${enrollmentId}/data`, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Enrollment courses request failed');
            const data = await response.json(); s.enrollment = data.enrollment; s.courses = data.courses; s.priorItems = data.prior_result_items || []; setEnrollmentInfo(data.enrollment); renderCourses(); updateSummary();
            if (data.enrollment.existing_result) setDrawerLocked(true, data.enrollment.existing_result.published_at ? 'This semester result is already published.' : 'A draft already exists for this semester. Use the edit button in the student list.');
        } catch (error) { showDrawerError('Enrollment courses load nahi ho sakay. Please select the semester again.'); console.error(error); }
    }
    function setEnrollmentInfo(enrollment) { s.department.textContent = enrollment.department_name || '—'; s.section.textContent = enrollment.section_name || '—'; s.academicYear.textContent = enrollment.academic_year || '—'; }
    function renderCourses() {
        s.courseCount.textContent = `${s.courses.length} ${s.courses.length === 1 ? 'course' : 'courses'}`;
        s.coursesBody.innerHTML = s.courses.map((course, index) => `<tr data-course-id="${course.student_enrollment_course_id}"><td>${index + 1}</td><td><strong>${escapeHtml(course.course_name)}</strong></td><td>${escapeHtml(course.course_code)}</td><td>${format(course.credit_hours, 1)}</td>${componentField(course, 'attendance', 'Attendance')}${componentField(course, 'mid', 'Midterm')}${componentField(course, 'final', 'Final')}<td class="result-row-obtained">—</td><td class="result-row-percentage">—</td><td class="result-row-grade">—</td><td class="result-row-gp">—</td><td class="result-row-status"><span class="course-status is-draft">Draft</span></td></tr>`).join('');
        s.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => input.addEventListener('input', updateSummary));
    }
    function componentField(course, component, label) { const maximum = Number(course[`${component}_marks`]); const disabled = maximum === 0 ? 'disabled value="0"' : ''; return `<td><div class="assessment-mark-entry"><small>${label} / ${format(maximum, 0)}</small><input class="form-control assessment-mark-input" type="number" inputmode="decimal" min="0" max="${maximum}" step="0.01" ${disabled} aria-label="${label} marks for ${escapeAttribute(course.course_name)}" data-component="${component}_obtained_marks"></div></td>`; }
    function updateSummary() {
        if (!s?.courses.length) return;
        let earned = 0, total = 0, credits = 0, points = 0, complete = true, failed = false; const items = [];
        s.courses.forEach((course) => {
            const row = s.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`); const marks = {}; let ready = true; total += Number(course.total_marks);
            ['attendance', 'mid', 'final'].forEach((component) => { const maximum = Number(course[`${component}_marks`]); const input = row.querySelector(`[data-component="${component}_obtained_marks"]`); const raw = input.value.trim(); input.classList.remove('is-invalid'); if (!maximum) { marks[component] = 0; return; } if (raw === '' || Number(raw) < 0 || Number(raw) > maximum || !Number.isFinite(Number(raw))) { ready = false; if (raw !== '') input.classList.add('is-invalid'); return; } marks[component] = Number(raw); });
            if (!ready) { complete = false; preview(row, null); return; }
            const obtained = marks.attendance + marks.mid + marks.final; const outcome = outcomeFor(obtained, Number(course.total_marks), marks.final, Number(course.final_marks)); earned += obtained; credits += Number(course.credit_hours); points += outcome.point * Number(course.credit_hours); failed ||= outcome.status === 'Fail'; items.push({ course_id: course.course_id, course_code: course.course_code, credit_hours: Number(course.credit_hours), grade_point: outcome.point, attempted_at: new Date().toISOString() }); preview(row, outcome);
        });
        s.total.textContent = complete ? `${format(earned, 2)} / ${format(total, 0)}` : `— / ${format(total, 0)}`;
        if (!complete) { setSummaryStatus('Draft'); s.percentage.textContent = s.sgpa.textContent = s.cgpa.textContent = '—'; return; }
        s.percentage.textContent = `${format((earned / total) * 100, 2)}%`; s.sgpa.textContent = format(points / credits, 2); s.cgpa.textContent = calculateCgpa([...s.priorItems, ...items]); setSummaryStatus(failed ? 'Fail' : 'Pass');
    }
    function outcomeFor(obtained, total, final, finalMaximum) { const percent = (obtained / total) * 100; const grade = s.gradeScale.find((item) => percent >= Number(item.minimum_percentage)); const failFinal = s.finalMinimumEnabled && finalMaximum > 0 && (final / finalMaximum) * 100 < s.finalMinimumPercentage; return failFinal ? { obtained, percent, grade: 'F', point: 0, status: 'Fail' } : { obtained, percent, grade: grade.grade, point: Number(grade.grade_point), status: grade.status }; }
    function preview(row, data) { const [obtained, percent, grade, point, status] = ['.result-row-obtained', '.result-row-percentage', '.result-row-grade', '.result-row-gp', '.result-row-status'].map((selector) => row.querySelector(selector)); if (!data) { obtained.textContent = percent.textContent = grade.textContent = point.textContent = '—'; status.innerHTML = '<span class="course-status is-draft">Draft</span>'; return; } obtained.textContent = format(data.obtained, 2); percent.textContent = `${format(data.percent, 2)}%`; grade.textContent = data.grade; point.textContent = format(data.point, 2); status.innerHTML = `<span class="course-status is-${data.status.toLowerCase()}">${data.status}</span>`; }
    function calculateCgpa(items) { const attempts = new Map(); [...items].sort((a, b) => new Date(a.attempted_at || 0) - new Date(b.attempted_at || 0)).forEach((item) => attempts.set(item.course_id ? `id:${item.course_id}` : `code:${item.course_code}`, item)); let credits = 0, points = 0; attempts.forEach((item) => { credits += Number(item.credit_hours); points += Number(item.grade_point) * Number(item.credit_hours); }); return credits ? format(points / credits, 2) : '—'; }
    function setSummaryStatus(value) { s.status.textContent = value; s.status.className = `semester-status is-${value.toLowerCase()}`; if (s.mode === 'create') s.actionState.textContent = value === 'Draft' ? 'Draft' : 'Ready to publish'; }
    function setDrawerLocked(locked, message = '') { s.isLocked = locked; s.locked.hidden = !locked; s.locked.innerHTML = locked ? `<i class="bi bi-shield-check"></i><div><strong>Result already added</strong><span>${escapeHtml(message)}</span></div>` : ''; s.form.querySelectorAll('[data-result-action]').forEach((button) => { button.disabled = locked; }); s.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => { input.disabled = locked; }); }
    s?.form.addEventListener('submit', async (event) => {
        event.preventDefault(); const action = event.submitter?.dataset.resultAction; if (!action || !s.enrollment?.id || s.isLocked) return;
        clearErrors(); const buttons = s.form.querySelectorAll('[data-result-action]'); buttons.forEach((button) => { button.disabled = true; });
        const courses = s.courses.map((course) => { const row = s.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`); const mark = (component) => { const value = row.querySelector(`[data-component="${component}_obtained_marks"]`).value.trim(); return value === '' ? null : Number(value); }; return { student_enrollment_course_id: course.student_enrollment_course_id, attendance_obtained_marks: mark('attendance'), mid_obtained_marks: mark('mid'), final_obtained_marks: mark('final') }; });
        try {
            const url = s.mode === 'edit' ? `${drawer.dataset.resultUrl}/${s.resultId}` : drawer.dataset.storeUrl; const response = await fetch(url, { method: s.mode === 'edit' ? 'PUT' : 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': s.form.querySelector('[name="_token"]').value }, body: JSON.stringify({ student_id: Number(s.studentId.value), enrollment_id: Number(s.enrollmentId.value), action, courses }) }); const data = await response.json();
            if (!response.ok) { const duplicate = data.errors?.result?.find((message) => message.toLowerCase().includes('already exists')); if (duplicate) setDrawerLocked(true, duplicate); displayErrors(data.errors || { result: data.message || 'Result save nahi ho saka.' }); return; }
            bootstrap.Offcanvas.getOrCreateInstance(drawer).hide(); showPageNotice(data.message, 'success'); if (currentSectionId) loadStudents(currentSectionId);
        } catch (error) { console.error(error); showDrawerError('Network issue ki wajah se result save nahi ho saka. Please try again.'); }
        finally { if (!s.isLocked) buttons.forEach((button) => { button.disabled = false; }); }
    });
    function resetDrawer() { clearErrors(); s.form.reset(); s.mode = 'create'; s.resultId = null; s.isLocked = false; s.title.textContent = 'Add Semester Result'; s.enrollmentSelect.disabled = false; s.enrollmentId.value = ''; s.courses = []; s.priorItems = []; s.coursesBody.innerHTML = ''; s.courseCount.textContent = '0 courses'; ['department', 'section', 'academicYear'].forEach((key) => { s[key].textContent = '—'; }); s.total.textContent = '— / —'; s.percentage.textContent = s.sgpa.textContent = s.cgpa.textContent = '—'; setSummaryStatus('Draft'); setDrawerLocked(false); setButtonLabels(); }
    function setButtonLabels() { if (s.draftButton) s.draftButton.innerHTML = s.mode === 'edit' ? '<i class="bi bi-save2"></i> Update Draft' : '<i class="bi bi-save2"></i> Save Draft'; if (s.publishButton) s.publishButton.innerHTML = s.mode === 'edit' ? '<i class="bi bi-check2-circle"></i> Update &amp; Publish' : '<i class="bi bi-check2-circle"></i> Save &amp; Publish'; }
    function displayErrors(errors) { const messages = Object.values(errors).flat().map(escapeHtml); s.errors.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i><div><strong>Please review the result entry.</strong><ul>${messages.map((message) => `<li>${message}</li>`).join('')}</ul></div>`; s.errors.hidden = false; }
    function showDrawerError(message) { displayErrors({ result: message }); }
    function clearErrors() { if (s) { s.errors.hidden = true; s.errors.innerHTML = ''; } }
    function showPageNotice(message, type) { document.querySelector('.results-page .results-alert.dynamic-notice')?.remove(); const notice = document.createElement('div'); notice.className = `alert alert-${type} results-alert dynamic-notice`; notice.setAttribute('role', 'status'); notice.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'} me-2"></i>${escapeHtml(message)}`; document.querySelector('.results-main-card-body')?.prepend(notice); window.setTimeout(() => notice.remove(), 4500); }
    function setText(id, value) { const element = document.getElementById(id); if (element) element.textContent = value || '—'; }
    function format(value, decimals) { return Number(value).toFixed(decimals); }
    function escapeHtml(value) { const element = document.createElement('div'); element.textContent = value ?? ''; return element.innerHTML; }
    function escapeAttribute(value) { return escapeHtml(value).replace(/"/g, '&quot;'); }
});
