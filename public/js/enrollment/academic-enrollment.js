document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('academicEnrollmentForm');
    if (!form) return;
    const student = document.getElementById('student_id');
    const term = document.getElementById('academic_term_id');
    const curriculum = document.getElementById('semester_curriculum_id');
    const rows = document.getElementById('enrollmentOfferingRows');
    const status = document.getElementById('offeringStatus');
    const save = document.getElementById('saveEnrollment');
    const previous = JSON.parse(document.getElementById('previousOfferingSelection').textContent).map(String);
    let requestNumber = 0;
    let ready = false;

    function updateSave() {
        save.disabled = !ready || form.dataset.blocked === 'true' || !rows.querySelector('input:checked');
    }
    function filterCurricula() {
        const option = student.selectedOptions[0];
        document.getElementById('studentPlacementSummary').textContent = option?.dataset.placement || 'Select a student to see their current placement.';
        for (const item of curriculum.options) {
            if (!item.value) continue;
            item.hidden = item.disabled = item.dataset.departmentId !== option?.dataset.departmentId || (form.dataset.nextSemester && item.dataset.semester !== form.dataset.nextSemester);
            if (item.disabled && item.selected) curriculum.value = '';
        }
    }
    function cell(text) {
        const td = document.createElement('td');
        td.textContent = text;
        return td;
    }
    async function loadOfferings(restore = false) {
        const currentRequest = ++requestNumber;
        ready = false;
        rows.replaceChildren();
        updateSave();
        status.className = 'text-muted small';
        if (!student.value || !term.value || !curriculum.value) {
            status.textContent = 'Select a student, term, and curriculum to load the course plan.';
            return;
        }
        status.textContent = 'Loading assigned course offerings…';
        try {
            const url = new URL(form.dataset.offeringsUrl, window.location.origin);
            url.search = new URLSearchParams({student_id: student.value, academic_term_id: term.value, semester_curriculum_id: curriculum.value});
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (currentRequest !== requestNumber) return;
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to load course offerings.');
            for (const offering of data.offerings) {
                const row = document.createElement('tr');
                const choice = document.createElement('input');
                choice.type = 'checkbox';
                choice.name = 'offering_ids[]';
                choice.value = offering.id;
                choice.className = 'academic-check';
                choice.setAttribute('aria-label', `Register ${offering.course_name}`);
                choice.checked = offering.type === 'required' || (restore && previous.includes(String(offering.id)));
                choice.addEventListener('change', updateSave);
                const choiceCell = cell('');
                choiceCell.append(choice);
                const courseCell = cell('');
                const label = document.createElement('label');
                choice.id = `enrollment-offering-${offering.id}`;
                label.htmlFor = choice.id;
                label.textContent = `${offering.course_code} — ${offering.course_name}`;
                const teacher = document.createElement('small');
                teacher.textContent = offering.teachers;
                courseCell.append(label, teacher);
                row.append(choiceCell, courseCell, cell(offering.type === 'repeat' ? 'Repeat / improvement' : offering.type === 'required' ? 'Required' : 'Elective'), cell(offering.credit_hours), cell(`${offering.attendance_marks} / ${offering.mid_marks} / ${offering.final_marks} (${offering.total_marks})`));
                rows.append(row);
            }
            ready = data.offerings.length > 0 && data.missing_required.length === 0;
            if (data.missing_required.length) {
                status.className = 'text-danger small';
                status.textContent = `Missing active offerings for required courses: ${data.missing_required.join(', ')}. Ask Academic Administration to complete the course plan.`;
            } else {
                status.textContent = data.offerings.length ? `${data.offerings.length} available courses. Confirm the selection below.` : 'No matching active offerings. Create and activate offerings for this term, section, and curriculum first.';
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
    loadOfferings(true);
});
