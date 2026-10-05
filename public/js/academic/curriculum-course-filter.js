document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('curriculumCoursePlan');
    const usageElement = document.getElementById('curriculumCourseUsage');
    if (!form || !usageElement) return;

    const program = document.getElementById('program_id');
    const semester = document.getElementById('semester');
    const notice = document.getElementById('courseAvailabilityNotice');
    const usage = JSON.parse(usageElement.textContent);
    const courseRows = [...form.querySelectorAll('[data-course-row]')];

    function refreshCourses() {
        const programUsage = usage[program.value] || {};
        let hiddenCount = 0;

        courseRows.forEach((row) => {
            const usedSemesters = programUsage[row.dataset.courseId] || [];
            const usedElsewhere = usedSemesters.some((usedSemester) => usedSemester !== semester.value);
            row.hidden = usedElsewhere;

            const include = row.querySelector('input[name="course_ids[]"]');
            const elective = row.querySelector('input[name="elective_ids[]"]');
            if (usedElsewhere) {
                hiddenCount += 1;
                include.checked = false;
                elective.checked = false;
            }
            include.disabled = usedElsewhere;
            elective.disabled = usedElsewhere;
        });

        notice.hidden = !program.value;
        if (!program.value) {
            notice.textContent = '';
        } else if (hiddenCount > 0) {
            notice.textContent = `${hiddenCount} ${hiddenCount === 1 ? 'course is' : 'courses are'} already assigned to another semester in this program and ${hiddenCount === 1 ? 'is' : 'are'} hidden.`;
        } else {
            notice.textContent = 'All active catalog courses are available for this program and semester.';
        }
    }

    program.addEventListener('change', refreshCourses);
    semester.addEventListener('change', refreshCourses);
    refreshCourses();
});
