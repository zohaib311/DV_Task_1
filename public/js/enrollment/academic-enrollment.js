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
    let maximumCredits = 21;

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
    function updateCourseLoad() {
        const selected = [...rows.querySelectorAll('input[name="offering_ids[]"]:checked')];
        const credits = selected.reduce((total, input) => total + Number(input.dataset.credits || 0), 0);
        const requiredMissing = rows.querySelectorAll('input[data-required="true"]:not(:checked)').length > 0;
        ready = selected.length > 0 && !requiredMissing && credits <= maximumCredits;
        status.className = credits > maximumCredits ? 'text-danger small' : 'text-muted small';
        status.textContent = credits > maximumCredits
            ? `Selected load is ${credits} credit hours; maximum allowed is ${maximumCredits}.`
            : `Selected course load: ${credits} / ${maximumCredits} credit hours. Required courses are locked; electives and backlog repeats are optional.`;
        updateSave();
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
            maximumCredits = Number(data.maximum_credit_hours || 21);
            for (const offering of data.offerings) {
                const row = document.createElement('tr');
                const selectionCell = cell('');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'offering_ids[]';
                checkbox.value = offering.id;
                checkbox.checked = offering.required;
                checkbox.disabled = offering.required;
                checkbox.dataset.required = offering.required ? 'true' : 'false';
                checkbox.dataset.credits = offering.credit_hours;
                checkbox.className = 'form-check-input';
                checkbox.setAttribute('aria-label', `Include ${offering.course_code}`);
                selectionCell.append(checkbox);
                if (offering.required) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'offering_ids[]';
                    hidden.value = offering.id;
                    selectionCell.append(hidden);
                }
                const courseCell = cell('');
                const label = document.createElement('label');
                label.textContent = `${offering.course_code} — ${offering.course_name}`;
                const teacher = document.createElement('small');
                teacher.textContent = offering.teachers;
                courseCell.append(label, teacher);
                row.append(selectionCell, courseCell, cell(offering.type === 'repeat' ? 'Backlog repeat' : offering.type[0].toUpperCase() + offering.type.slice(1)), cell(offering.credit_hours), cell(`${offering.attendance_marks} / ${offering.mid_marks} / ${offering.final_marks} (${offering.total_marks})`));
                rows.append(row);
            }
            rows.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach((input) => input.addEventListener('change', updateCourseLoad));
            ready = data.offerings.length > 0 && data.missing_required.length === 0;
            if (data.missing_required.length) {
                status.className = 'text-danger small';
                status.textContent = `Semester teaching setup is incomplete for: ${data.missing_required.join(', ')}. Academic Administration must prepare these classes and assign teachers.`;
            } else if (data.offerings.length) updateCourseLoad();
            else status.textContent = 'No courses are available for this semester plan and term.';
            if (data.missing_required.length) updateSave();
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
