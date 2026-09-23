
document.addEventListener('DOMContentLoaded', function () {

    const dropdownBtn = document.getElementById('courseDropdownBtn');
    const dropdownMenu = document.getElementById('courseDropdownMenu');
    const dropdownText = document.getElementById('courseDropdownText');
    const checkboxes = document.querySelectorAll('input[name="course_ids[]"]');

    function updateCourseText() {
        const selected = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => {
                return checkbox.closest('.course-option')
                    .querySelector('.course-name').textContent.trim();
            });

        if (selected.length === 0) {
            dropdownText.textContent = 'Select Courses';
        } else if (selected.length === 1) {
            dropdownText.textContent = selected[0];
        } else {
            dropdownText.textContent = selected.length + ' Courses Selected';
        }
    }

    dropdownBtn.addEventListener('click', function (event) {
        event.stopPropagation();

        dropdownMenu.classList.toggle('show');
        dropdownBtn.classList.toggle('active');
    });

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateCourseText();
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.course-dropdown')) {
            dropdownMenu.classList.remove('show');
            dropdownBtn.classList.remove('active');
        }
    });

    updateCourseText();
});

