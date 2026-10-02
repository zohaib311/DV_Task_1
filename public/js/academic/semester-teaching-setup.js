document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('semesterTeachingSetup');
    if (!form) return;
    const section = document.getElementById('section_id');
    const program = document.getElementById('program_id');
    const curriculum = document.getElementById('semester_curriculum_id');
    const rows = document.getElementById('teachingSetupCourses');
    const save = document.getElementById('createTeachingSetup');
    const plans = JSON.parse(document.getElementById('teachingSetupData').textContent);
    const teachers = JSON.parse(document.getElementById('teachingSetupTeachers').textContent);

    function filterSections() {
        const departmentId = program.selectedOptions[0]?.dataset.departmentId;
        let hasAvailableSection = false;
        [...section.options].forEach((option) => {
            if (!option.value) return;
            option.hidden = option.disabled = !departmentId || option.dataset.departmentId !== departmentId;
            if (!option.disabled) hasAvailableSection = true;
        });
        if (section.selectedOptions[0]?.disabled) section.value = '';
        section.disabled = !departmentId || !hasAvailableSection;
        section.options[0].textContent = !departmentId
            ? 'Select program first'
            : hasAvailableSection ? 'Select section' : 'No sections exist for this program department';
    }

    function filterPlans() {
        const departmentId = section.selectedOptions[0]?.dataset.departmentId;
        const programId = program.value;
        [...curriculum.options].forEach((option) => {
            if (!option.value) return;
            option.hidden = option.disabled = option.dataset.programId !== programId || program.selectedOptions[0]?.dataset.departmentId !== departmentId;
            if (option.disabled && option.selected) curriculum.value = '';
        });
        curriculum.options[0].textContent = !programId
            ? 'Select program first'
            : !section.value ? 'Select a compatible section first' : 'Select approved semester plan';
    }
    function cell(value) { const item = document.createElement('td'); item.textContent = value; return item; }
    function render() {
        rows.replaceChildren();
        const courses = plans[curriculum.value] || [];
        if (!courses.length) {
            rows.innerHTML = '<tr><td colspan="5" class="academic-empty">Select a department section and semester plan to load its courses.</td></tr>';
            save.disabled = true;
            return;
        }
        courses.forEach((course) => {
            const row = document.createElement('tr');
            const courseCell = cell(`${course.course_code} — ${course.course_name}`);
            const teacherCell = document.createElement('td');
            const teacherSelect = document.createElement('select');
            teacherSelect.name = `teacher_ids[${course.id}]`;
            teacherSelect.required = true;
            teacherSelect.className = 'form-select form-select-sm';
            teacherSelect.innerHTML = '<option value="">Assign teacher</option>';
            teachers.forEach((teacher) => teacherSelect.add(new Option(teacher.name, teacher.id)));
            teacherCell.append(teacherSelect);
            row.append(courseCell, cell(course.type === 'required' ? 'Required' : 'Elective'), cell(course.credit_hours), cell(`${course.attendance_marks} / ${course.mid_marks} / ${course.final_marks} (${course.total_marks})`), teacherCell);
            rows.append(row);
        });
        save.disabled = false;
    }
    section.addEventListener('change', () => { filterPlans(); render(); });
    program.addEventListener('change', () => { filterSections(); filterPlans(); render(); });
    curriculum.addEventListener('change', render);
    filterSections(); filterPlans(); render();
});
