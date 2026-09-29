document.addEventListener('DOMContentLoaded', () => {
    const departmentSelect = document.getElementById('department_id');
    const sectionSelect = document.getElementById('section_id');
    const initialStateMsg = document.getElementById('initial_state_msg');
    const loadingSpinner = document.getElementById('loading_spinner');
    const studentsTableWrapper = document.getElementById('students_table_wrapper');
    const studentsTableBody = document.getElementById('students_table_body');
    const noStudentsMsg = document.getElementById('no_students_msg');
    const drawer = document.getElementById('semesterResultDrawer');
    let currentSectionId = null;

    const drawerState = drawer ? {
        form: document.getElementById('semesterResultForm'), studentId: document.getElementById('semester_result_student_id'),
        enrollmentId: document.getElementById('semester_result_enrollment_id'), enrollmentSelect: document.getElementById('semester_result_enrollment_select'),
        loading: document.getElementById('semester_result_loading'), content: document.getElementById('semester_result_content'),
        errors: document.getElementById('semester_result_errors'), locked: document.getElementById('semester_result_locked'),
        coursesBody: document.getElementById('semester_result_courses_body'), courseCount: document.getElementById('semester_result_course_count'),
        actionState: document.getElementById('semester_result_action_state'), status: document.getElementById('semester_result_summary_status'),
        total: document.getElementById('semester_result_total_marks'), percentage: document.getElementById('semester_result_percentage'),
        sgpa: document.getElementById('semester_result_sgpa'), cgpa: document.getElementById('semester_result_cgpa'),
        studentName: document.getElementById('semester_result_student_name'), registrationNo: document.getElementById('semester_result_registration_no'),
        department: document.getElementById('semester_result_department'), section: document.getElementById('semester_result_section'),
        academicYear: document.getElementById('semester_result_academic_year'), gradeScale: JSON.parse(drawer.dataset.gradeScale || '[]'),
        finalMinimumEnabled: drawer.dataset.finalMinimumEnabled === 'true', finalMinimumPercentage: Number(drawer.dataset.finalMinimumPercentage || 0),
        student: null, enrollment: null, courses: [], priorItems: [],
    } : null;

    departmentSelect?.addEventListener('change', async () => {
        const departmentId = departmentSelect.value;
        sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
        sectionSelect.disabled = true;
        resetStudentViews();
        if (!departmentId) { sectionSelect.innerHTML = '<option value="">-- Select Department First --</option>'; return; }
        try {
            const response = await fetch(`/result/get-sections/${departmentId}`);
            const sections = await response.json();
            if (sections.length) {
                sectionSelect.disabled = false;
                sections.forEach((section) => sectionSelect.add(new Option(section.name, section.id)));
            } else sectionSelect.innerHTML = '<option value="">No Sections in this Department</option>';
        } catch (error) {
            console.error('Error fetching sections:', error);
            showPageNotice('Sections load nahi ho sakin. Please try again.', 'danger');
        }
    });

    sectionSelect?.addEventListener('change', () => {
        currentSectionId = sectionSelect.value || null;
        if (!currentSectionId) { resetStudentViews(); initialStateMsg.style.display = 'block'; return; }
        loadStudents(currentSectionId);
    });

    async function loadStudents(sectionId) {
        resetStudentViews();
        loadingSpinner.style.display = 'block';
        try {
            const response = await fetch(`/result/get-students/${sectionId}`);
            if (!response.ok) throw new Error('Students request failed');
            const students = await response.json();
            loadingSpinner.style.display = 'none';
            if (!students.length) { noStudentsMsg.style.display = 'flex'; return; }
            studentsTableBody.innerHTML = students.map((student, index) => renderStudentRow(student, index)).join('');
            studentsTableWrapper.style.display = 'block';
        } catch (error) {
            loadingSpinner.style.display = 'none';
            console.error('Error fetching students:', error);
            showPageNotice('Students load nahi ho sakay. Please try again.', 'danger');
        }
    }

    function renderStudentRow(student, index) {
        const latestResult = student.results?.[student.results.length - 1];
        const activeEnrollment = student.active_semester_enrollment;
        const semesterResult = activeEnrollment?.semester_result;
        const resultHtml = semesterResult ? `<div class="semester-result-list-state"><span class="semester-result-list-label"><i class="bi bi-journal-check"></i> Semester result added</span><strong>${escapeHtml(activeEnrollment.semester)} · ${escapeHtml(activeEnrollment.academic_year)}</strong><small>${semesterResult.published_at ? 'Published' : 'Draft saved'} <span class="semester-result-list-status is-${semesterResult.status.toLowerCase()}">${escapeHtml(semesterResult.status)}</span></small></div>` : latestResult ? `<div><div class="fw-bold text-dark">${escapeHtml(latestResult.course?.name || 'N/A')}</div><small class="text-muted">Grade: <strong>${escapeHtml(latestResult.grade || 'N/A')}</strong> | GPA: <strong>${escapeHtml(latestResult.gpa || '0')}</strong></small><span class="badge ${(latestResult.status || '').toLowerCase() === 'pass' ? 'bg-success' : 'bg-danger'} ms-1">${escapeHtml(latestResult.status || 'N/A')}</span></div>` : '<span class="badge bg-secondary">No semester result</span>';
        const viewEditButtons = latestResult ? `<button type="button" class="action__btn action__info btn-view-result" title="View legacy result" data-bs-toggle="modal" data-bs-target="#viewResultModal" data-result-id="${latestResult.id}" data-student-name="${escapeAttribute(student.name)}" data-student-email="${escapeAttribute(student.email || 'N/A')}" data-student-phone="${escapeAttribute(student.phone || 'N/A')}" data-student-image="${escapeAttribute(student.image || 'default-user.png')}" data-percentage="${latestResult.percentage}" data-gpa="${latestResult.gpa}" data-cgpa="${latestResult.cgpa}" data-grade="${escapeAttribute(latestResult.grade)}" data-status="${escapeAttribute(latestResult.status)}" data-course-name="${escapeAttribute(latestResult.course?.name || 'N/A')}" data-course-code="${escapeAttribute(latestResult.course?.code || '')}" data-section-name="${escapeAttribute(student.section?.name || '')}"><i class="bi bi-info-circle"></i></button><button type="button" class="action__btn action__edit btn-edit-result" title="Edit legacy result" data-bs-toggle="offcanvas" data-bs-target="#editResultModal" data-result-id="${latestResult.id}" data-student-id="${student.id}" data-section-id="${currentSectionId}" data-course-id="${latestResult.course_id}" data-percentage="${latestResult.percentage}" data-gpa="${latestResult.gpa}" data-cgpa="${latestResult.cgpa}" data-grade="${escapeAttribute(latestResult.grade)}" data-status="${escapeAttribute(latestResult.status)}" data-student-name="${escapeAttribute(student.name)}" data-student-email="${escapeAttribute(student.email || 'N/A')}"><i class="bi bi-pencil-square"></i></button>` : '';
        const addActionClass = semesterResult ? 'action__info' : 'action__add';
        const addActionTitle = semesterResult ? 'Semester result already added' : 'Add semester result';
        const addActionIcon = semesterResult ? 'bi-journal-check' : 'bi-plus-circle';
        return `<tr><td>${index + 1}</td><td><span class="student-id-badge">${escapeHtml(student.registration_no || '—')}</span></td><td class="fw-bold text-dark">${escapeHtml(student.name)}</td><td class="text-muted">${escapeHtml(student.email || 'N/A')}</td><td>${resultHtml}</td><td class="text-center"><div class="action__buttons">${viewEditButtons}<button type="button" class="action__btn ${addActionClass} btn-add-semester-result" title="${addActionTitle}" data-bs-toggle="offcanvas" data-bs-target="#semesterResultDrawer" data-student-id="${student.id}" data-existing-result="${semesterResult ? 'true' : 'false'}"><i class="bi ${addActionIcon}"></i></button></div></td></tr>`;
    }

    function resetStudentViews() {
        initialStateMsg.style.display = 'none'; loadingSpinner.style.display = 'none'; studentsTableWrapper.style.display = 'none';
        noStudentsMsg.style.display = 'none'; studentsTableBody.innerHTML = '';
    }

    document.addEventListener('click', (event) => {
        const viewBtn = event.target.closest('.btn-view-result'); if (viewBtn) fillLegacyView(viewBtn);
        const addBtn = event.target.closest('.btn-add-semester-result'); if (addBtn && drawerState) openSemesterDrawer(addBtn.dataset.studentId, addBtn.dataset.existingResult === 'true');
        const editBtn = event.target.closest('.btn-edit-result'); if (editBtn) fillLegacyEdit(editBtn);
    });

    function fillLegacyView(button) {
        setText('view_student_name', button.dataset.studentName); setText('view_student_email', button.dataset.studentEmail); setText('view_student_phone', button.dataset.studentPhone);
        const image = document.getElementById('view_student_image'); if (image) image.src = `/storage/images/${button.dataset.studentImage}`;
        setText('view_percentage', `${button.dataset.percentage}%`); setText('view_gpa', button.dataset.gpa); setText('view_cgpa', button.dataset.cgpa); setText('view_grade', button.dataset.grade);
        setText('view_record_id', `#${button.dataset.resultId}`); setText('view_course_name', button.dataset.courseName); setText('view_course_code', button.dataset.courseCode ? `(${button.dataset.courseCode})` : ''); setText('view_section_name', button.dataset.sectionName);
        const badge = document.getElementById('view_status_badge'); if (badge) { badge.textContent = button.dataset.status; badge.className = `status-badge ${button.dataset.status.toLowerCase() === 'pass' ? 'pass' : 'fail'}`; }
    }

    function fillLegacyEdit(button) {
        const form = document.getElementById('editResultForm'); if (form) form.action = `/result/update/${button.dataset.resultId}`;
        const fields = { edit_modal_student_id: button.dataset.studentId, edit_modal_section_id: button.dataset.sectionId, edit_modal_student_name: button.dataset.studentName, edit_modal_student_email: button.dataset.studentEmail, edit_course_id: button.dataset.courseId, edit_percentage: button.dataset.percentage, edit_gpa: button.dataset.gpa, edit_cgpa: button.dataset.cgpa, edit_grade: button.dataset.grade, edit_status: button.dataset.status };
        Object.entries(fields).forEach(([id, value]) => { const element = document.getElementById(id); if (!element) return; if (['INPUT', 'SELECT'].includes(element.tagName)) element.value = value; else element.textContent = value; });
    }

    async function openSemesterDrawer(studentId, knownExistingResult = false) {
        resetDrawer(); drawerState.loading.hidden = false; drawerState.content.hidden = true;
        if (knownExistingResult) drawerState.actionState.textContent = 'Result already added';
        try {
            const response = await fetch(`${drawer.dataset.studentEnrollmentsUrl}/${studentId}/semester-enrollments`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Student enrollments request failed');
            const data = await response.json();
            drawerState.student = data.student; drawerState.studentId.value = data.student.id; drawerState.studentName.textContent = data.student.name; drawerState.registrationNo.textContent = data.student.registration_no || '—';
            populateEnrollmentChoices(data.enrollments); drawerState.loading.hidden = true; drawerState.content.hidden = false;
            if (drawerState.enrollmentSelect.value) await loadEnrollment(drawerState.enrollmentSelect.value);
            else showDrawerError('This student has no semester enrollment yet. Create an enrollment before adding a result.');
        } catch (error) { drawerState.loading.hidden = true; showDrawerError('Student academic record load nahi ho saka. Please try again.'); console.error(error); }
    }

    function populateEnrollmentChoices(enrollments) {
        drawerState.enrollmentSelect.innerHTML = '';
        enrollments.forEach((enrollment) => {
            const resultLabel = enrollment.existing_result ? ` — ${enrollment.existing_result.published_at ? 'Published' : 'Draft saved'}` : ' — Ready for result';
            drawerState.enrollmentSelect.add(new Option(`${enrollment.semester} · ${enrollment.academic_year}${resultLabel}`, enrollment.id));
        });
        const activeEnrollment = enrollments.find((enrollment) => enrollment.enrollment_status === 'active');
        if (activeEnrollment) drawerState.enrollmentSelect.value = activeEnrollment.id; else if (enrollments[0]) drawerState.enrollmentSelect.value = enrollments[0].id;
    }

    drawerState?.enrollmentSelect.addEventListener('change', () => loadEnrollment(drawerState.enrollmentSelect.value));

    async function loadEnrollment(enrollmentId) {
        clearDrawerError(); drawerState.coursesBody.innerHTML = ''; drawerState.enrollmentId.value = enrollmentId; setDrawerLocked(false);
        try {
            const response = await fetch(`${drawer.dataset.enrollmentUrl}/${enrollmentId}/data`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Enrollment courses request failed');
            const data = await response.json();
            drawerState.enrollment = data.enrollment; drawerState.courses = data.courses; drawerState.priorItems = data.prior_result_items || [];
            drawerState.department.textContent = data.enrollment.department_name || '—'; drawerState.section.textContent = data.enrollment.section_name || '—'; drawerState.academicYear.textContent = data.enrollment.academic_year || '—';
            drawerState.courseCount.textContent = `${data.courses.length} ${data.courses.length === 1 ? 'course' : 'courses'}`; renderCourseRows(data.courses);
            if (data.enrollment.existing_result) {
                const result = data.enrollment.existing_result;
                setDrawerLocked(true, result.published_at ? 'This semester result is already published. An authorized edit workflow will be available in Phase 6.' : 'A draft already exists for this semester. It will be editable in the Phase 6 edit flow.');
            }
            updateLiveSummary();
        } catch (error) { showDrawerError('Enrollment courses load nahi ho sakay. Please select the semester again.'); console.error(error); }
    }

    function renderCourseRows(courses) {
        drawerState.coursesBody.innerHTML = courses.map((course, index) => `<tr data-course-id="${course.student_enrollment_course_id}">
            <td>${index + 1}</td><td><strong>${escapeHtml(course.course_name)}</strong></td><td>${escapeHtml(course.course_code)}</td><td>${formatNumber(course.credit_hours, 1)}</td>
            ${renderAssessmentInput(course, 'attendance', 'Attendance')}${renderAssessmentInput(course, 'mid', 'Midterm')}${renderAssessmentInput(course, 'final', 'Final')}
            <td class="result-row-obtained">—</td><td class="result-row-percentage">—</td><td class="result-row-grade">—</td><td class="result-row-gp">—</td><td class="result-row-status"><span class="course-status is-draft">Draft</span></td>
        </tr>`).join('');
        drawerState.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => input.addEventListener('input', updateLiveSummary));
    }

    function renderAssessmentInput(course, component, label) {
        const maximum = Number(course[`${component}_marks`]);
        const field = `${component}_obtained_marks`;
        const disabled = maximum === 0 ? 'disabled value="0"' : '';
        return `<td><div class="assessment-mark-entry"><small>${label} / ${formatNumber(maximum, 0)}</small><input class="form-control assessment-mark-input" type="number" inputmode="decimal" min="0" max="${maximum}" step="0.01" ${disabled} aria-label="${label} marks for ${escapeAttribute(course.course_name)}" data-component="${field}" data-enrollment-course-id="${course.student_enrollment_course_id}"></div></td>`;
    }

    function updateLiveSummary() {
        if (!drawerState?.courses.length) return;
        let earned = 0; let total = 0; let totalCredits = 0; let qualityPoints = 0; let complete = true; let hasFail = false; const currentItems = [];
        drawerState.courses.forEach((course) => {
            const row = drawerState.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`); const componentMarks = {}; let courseComplete = true; total += Number(course.total_marks);
            ['attendance', 'mid', 'final'].forEach((component) => {
                const maximum = Number(course[`${component}_marks`]);
                const input = row.querySelector(`[data-component="${component}_obtained_marks"]`);
                const rawValue = input.value.trim(); input.classList.remove('is-invalid');
                if (maximum === 0) { componentMarks[component] = 0; return; }
                if (rawValue === '') { courseComplete = false; return; }
                const marks = Number(rawValue);
                if (!Number.isFinite(marks) || marks < 0 || marks > maximum) { courseComplete = false; input.classList.add('is-invalid'); return; }
                componentMarks[component] = marks;
            });
            if (!courseComplete) { complete = false; setCoursePreview(row, null); return; }
            const marks = componentMarks.attendance + componentMarks.mid + componentMarks.final;
            const outcome = calculateCoursePreview(marks, Number(course.total_marks), componentMarks.final, Number(course.final_marks)); earned += marks; totalCredits += Number(course.credit_hours); qualityPoints += outcome.gradePoint * Number(course.credit_hours); hasFail = hasFail || outcome.status === 'Fail';
            currentItems.push({ course_id: course.course_id, course_code: course.course_code, credit_hours: Number(course.credit_hours), grade_point: outcome.gradePoint, attempted_at: new Date().toISOString(), result_id: Number.MAX_SAFE_INTEGER, item_id: course.student_enrollment_course_id }); setCoursePreview(row, outcome);
        });
        drawerState.total.textContent = complete ? `${formatNumber(earned, 2)} / ${formatNumber(total, 0)}` : `— / ${formatNumber(total, 0)}`;
        if (!complete) { setSummaryStatus('Draft'); drawerState.percentage.textContent = '—'; drawerState.sgpa.textContent = '—'; drawerState.cgpa.textContent = '—'; return; }
        drawerState.percentage.textContent = `${formatNumber((earned / total) * 100, 2)}%`; drawerState.sgpa.textContent = formatNumber(qualityPoints / totalCredits, 2); drawerState.cgpa.textContent = formatCgpa([...drawerState.priorItems, ...currentItems]); setSummaryStatus(hasFail ? 'Fail' : 'Pass');
    }

    function calculateCoursePreview(marks, totalMarks, finalMarks, finalMaximum) {
        const percentage = (marks / totalMarks) * 100; const grade = drawerState.gradeScale.find((item) => percentage >= Number(item.minimum_percentage));
        const finalMinimumFailed = drawerState.finalMinimumEnabled && finalMaximum > 0 && ((finalMarks / finalMaximum) * 100) < drawerState.finalMinimumPercentage;
        return finalMinimumFailed ? { obtainedMarks: marks, percentage, grade: 'F', gradePoint: 0, status: 'Fail' } : { obtainedMarks: marks, percentage, grade: grade.grade, gradePoint: Number(grade.grade_point), status: grade.status };
    }
    function setCoursePreview(row, outcome) {
        const obtained = row.querySelector('.result-row-obtained'); const percentage = row.querySelector('.result-row-percentage'); const grade = row.querySelector('.result-row-grade'); const gp = row.querySelector('.result-row-gp'); const status = row.querySelector('.result-row-status');
        if (!outcome) { obtained.textContent = percentage.textContent = grade.textContent = gp.textContent = '—'; status.innerHTML = '<span class="course-status is-draft">Draft</span>'; return; }
        obtained.textContent = formatNumber(outcome.obtainedMarks, 2); percentage.textContent = `${formatNumber(outcome.percentage, 2)}%`; grade.textContent = outcome.grade; gp.textContent = formatNumber(outcome.gradePoint, 2); status.innerHTML = `<span class="course-status is-${outcome.status.toLowerCase()}">${outcome.status}</span>`;
    }
    function formatCgpa(items) {
        const latestAttempts = new Map(); [...items].sort((a, b) => new Date(a.attempted_at || 0) - new Date(b.attempted_at || 0)).forEach((item) => latestAttempts.set(item.course_id ? `id:${item.course_id}` : `code:${item.course_code}`, item));
        let credits = 0; let points = 0; latestAttempts.forEach((item) => { credits += Number(item.credit_hours); points += Number(item.grade_point) * Number(item.credit_hours); }); return credits ? formatNumber(points / credits, 2) : '—';
    }
    function setSummaryStatus(status) { drawerState.status.textContent = status; drawerState.status.className = `semester-status is-${status.toLowerCase()}`; drawerState.actionState.textContent = status === 'Draft' ? 'Draft' : 'Ready to publish'; }
    function setDrawerLocked(locked, message = '') {
        drawerState.locked.hidden = !locked;
        drawerState.locked.innerHTML = locked ? `<i class="bi bi-shield-check"></i><div><strong>Result already added</strong><span>${escapeHtml(message)}</span></div>` : '';
        drawerState.form.querySelectorAll('[data-result-action]').forEach((button) => { button.disabled = locked; }); drawerState.coursesBody.querySelectorAll('.assessment-mark-input').forEach((input) => { input.disabled = locked; });
    }

    drawerState?.form.addEventListener('submit', async (event) => {
        event.preventDefault(); const action = event.submitter?.dataset.resultAction; if (!action || !drawerState.enrollment?.id) return;
        clearDrawerError(); const submitButtons = drawerState.form.querySelectorAll('[data-result-action]'); submitButtons.forEach((button) => { button.disabled = true; });
        const courses = drawerState.courses.map((course) => {
            const row = drawerState.coursesBody.querySelector(`[data-course-id="${course.student_enrollment_course_id}"]`);
            const valueFor = (component) => {
                const input = row.querySelector(`[data-component="${component}_obtained_marks"]`);
                return input.value.trim() === '' ? null : Number(input.value);
            };
            return {
                student_enrollment_course_id: course.student_enrollment_course_id,
                attendance_obtained_marks: valueFor('attendance'),
                mid_obtained_marks: valueFor('mid'),
                final_obtained_marks: valueFor('final'),
            };
        });
        try {
            const response = await fetch(drawer.dataset.storeUrl, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': drawerState.form.querySelector('[name="_token"]').value }, body: JSON.stringify({ student_id: Number(drawerState.studentId.value), enrollment_id: Number(drawerState.enrollmentId.value), action, courses }) });
            const data = await response.json(); if (!response.ok) {
                const duplicateMessage = data.errors?.result?.find((message) => message.toLowerCase().includes('already exists'));
                if (duplicateMessage) setDrawerLocked(true, duplicateMessage);
                displayServerErrors(data.errors || { result: data.message || 'Result save nahi ho saka.' }); return;
            }
            bootstrap.Offcanvas.getOrCreateInstance(drawer).hide(); showPageNotice(data.message, 'success'); if (currentSectionId) loadStudents(currentSectionId);
        } catch (error) { console.error(error); showDrawerError('Network issue ki wajah se result save nahi ho saka. Please try again.'); }
        finally { if (!drawerState.enrollment?.existing_result) submitButtons.forEach((button) => { button.disabled = false; }); }
    });

    function resetDrawer() {
        clearDrawerError(); drawerState.form.reset(); drawerState.enrollmentId.value = ''; drawerState.courses = []; drawerState.priorItems = []; drawerState.coursesBody.innerHTML = ''; drawerState.courseCount.textContent = '0 courses';
        ['department', 'section', 'academicYear'].forEach((key) => { drawerState[key].textContent = '—'; }); drawerState.total.textContent = '— / —'; drawerState.percentage.textContent = drawerState.sgpa.textContent = drawerState.cgpa.textContent = '—'; setSummaryStatus('Draft'); setDrawerLocked(false);
    }
    function displayServerErrors(errors) { const messages = Object.values(errors).flat().map(escapeHtml); drawerState.errors.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i><div><strong>Please review the result entry.</strong><ul>${messages.map((message) => `<li>${message}</li>`).join('')}</ul></div>`; drawerState.errors.hidden = false; }
    function showDrawerError(message) { displayServerErrors({ result: message }); }
    function clearDrawerError() { if (drawerState) { drawerState.errors.hidden = true; drawerState.errors.innerHTML = ''; } }
    function showPageNotice(message, type) { document.querySelector('.results-page .results-alert.dynamic-notice')?.remove(); const notice = document.createElement('div'); notice.className = `alert alert-${type} results-alert dynamic-notice`; notice.setAttribute('role', 'status'); notice.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'} me-2"></i>${escapeHtml(message)}`; document.querySelector('.results-main-card-body').prepend(notice); window.setTimeout(() => notice.remove(), 4500); }
    function setText(id, value) { const element = document.getElementById(id); if (element) element.textContent = value || '—'; }
    function formatNumber(value, fractionDigits) { return Number(value).toFixed(fractionDigits); }
    function escapeHtml(value) { const element = document.createElement('div'); element.textContent = value ?? ''; return element.innerHTML; }
    function escapeAttribute(value) { return escapeHtml(value).replace(/"/g, '&quot;'); }
});
