document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('academicEnrollmentForm');
    if (!form) return;
    const student = document.getElementById('student_id');
    const term = document.getElementById('academic_term_id');
    const curriculum = document.getElementById('semester_curriculum_id');
    const rows = document.getElementById('enrollmentOfferingRows');
    const status = document.getElementById('offeringStatus');
    const save = document.getElementById('saveEnrollment');
    let requestNumber = 0;
    let ready = false;

    function updateSave() {
        save.disabled = !ready || form.dataset.blocked === 'true';
    }
    function filterCurricula() {
        const option = student.selectedOptions[0];
        document.getElementById('studentPlacementSummary').textContent = option?.dataset.placement || 'Select a student to see their current placement.';
        for (const item of curriculum.options) {
            if (!item.value) continue;
            item.hidden = item.disabled = item.dataset.departmentId !== option?.dataset.departmentId || item.dataset.programId !== option?.dataset.programId || (form.dataset.nextSemester && item.dataset.semester !== form.dataset.nextSemester);
            if (item.disabled && item.selected) curriculum.value = '';
        }
        const visiblePlans = [...curriculum.options].filter((item) => item.value && !item.disabled);
        if (!curriculum.value) {
            const defaultPlan = form.dataset.nextSemester
                ? visiblePlans.find((item) => item.dataset.semester === form.dataset.nextSemester)
                : visiblePlans.find((item) => item.dataset.semester === 'Semester 1');
            curriculum.value = defaultPlan?.value || (visiblePlans.length === 1 ? visiblePlans[0].value : '');
        }
    }
    function cell(text) {
        const td = document.createElement('td');
        td.textContent = text;
        return td;
    }
    async function loadOfferings() {
        const currentRequest = ++requestNumber;
        ready = false;
        rows.replaceChildren();
        updateSave();
        status.className = 'text-muted small';
        if (!student.value || !term.value || !curriculum.value) {
            status.textContent = 'Select a student, term, and curriculum to load the course plan.';
            return;
        }
        status.textContent = 'Loading the automatic semester course plan…';
        try {
            const url = new URL(form.dataset.offeringsUrl, window.location.origin);
            url.search = new URLSearchParams({student_id: student.value, academic_term_id: term.value, semester_curriculum_id: curriculum.value});
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (currentRequest !== requestNumber) return;
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to load course offerings.');
            for (const offering of data.offerings) {
                const row = document.createElement('tr');
                const courseCell = cell('');
                const label = document.createElement('label');
                label.textContent = `${offering.course_code} — ${offering.course_name}`;
                const teacher = document.createElement('small');
                teacher.textContent = offering.teachers;
                courseCell.append(label, teacher);
                row.append(courseCell, cell('Required'), cell(offering.credit_hours), cell(`${offering.attendance_marks} / ${offering.mid_marks} / ${offering.final_marks} (${offering.total_marks})`));
                rows.append(row);
            }
            ready = data.offerings.length > 0 && data.missing_required.length === 0;
            if (data.missing_required.length) {
                status.className = 'text-danger small';
                status.textContent = `Semester teaching setup is incomplete for: ${data.missing_required.join(', ')}. Academic Administration must prepare these classes and assign teachers.`;
            } else {
                status.textContent = data.offerings.length ? `${data.offerings.length} required courses will be assigned automatically when you create this enrollment.` : 'No required courses are configured in this approved semester plan.';
            }
            updateSave();
        } catch (error) {
            if (currentRequest !== requestNumber) return;
            status.className = 'text-danger small';
            status.textContent = error instanceof SyntaxError ? 'Unable to load course offerings. Refresh the page and try again.' : error.message;
        }
    }
    student.addEventListener('change', () => { filterCurricula(); loadOfferings(); });
    term.addEventListener('change', () => loadOfferings());
    curriculum.addEventListener('change', () => loadOfferings());
    form.addEventListener('submit', () => { save.disabled = true; });
    filterCurricula();
    const activeTerms = [...term.options].filter((item) => item.value);
    if (!term.value && activeTerms.length === 1) term.value = activeTerms[0].value;
    loadOfferings();
});
