document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('section_id');
    const program = document.getElementById('program_id');
    const course = document.getElementById('curriculum_course_id');
    const scheme = document.getElementById('offeringScheme');
    if (!section || !program || !course) return;

    function showScheme() {
        scheme.textContent = course.selectedOptions[0]?.dataset.scheme || 'Choose a curriculum course to preview the assessment scheme.';
    }
    function filterCourses() {
        const department = section.selectedOptions[0]?.dataset.departmentId;
        const programId = program.value;
        for (const option of course.options) {
            if (!option.value) continue;
            option.hidden = option.disabled = option.dataset.programId !== programId || program.selectedOptions[0]?.dataset.departmentId !== department;
            if (option.disabled && option.selected) course.value = '';
        }
        showScheme();
    }
    section.addEventListener('change', filterCourses);
    program.addEventListener('change', filterCourses);
    course.addEventListener('change', showScheme);
    filterCourses();
});
