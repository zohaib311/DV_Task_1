document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department_id');
    const sectionSelect = document.getElementById('section_id');

    if (departmentSelect && sectionSelect) {
        const placeholderOption = sectionSelect.querySelector('option[value=""]');

        function filterSections(isInit = false) {
            const selectedDeptId = departmentSelect.value;
            const options = sectionSelect.querySelectorAll('option');

            if (!selectedDeptId) {
                sectionSelect.disabled = true;
                if (placeholderOption) placeholderOption.textContent = 'Select Department First';
                sectionSelect.value = '';
                options.forEach(option => {
                    if (option.value) {
                        option.hidden = true;
                        option.disabled = true;
                    }
                });
                return;
            }

            sectionSelect.disabled = false;
            if (placeholderOption) placeholderOption.textContent = 'Select Section';

            options.forEach(option => {
                if (!option.value) return;

                const deptId = option.getAttribute('data-department-id');
                if (deptId === selectedDeptId) {
                    option.hidden = false;
                    option.disabled = false;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });

            const currentOption = sectionSelect.options[sectionSelect.selectedIndex];
            if (currentOption && currentOption.disabled && !isInit) {
                sectionSelect.value = '';
            }
        }

        departmentSelect.addEventListener('change', function () {
            filterSections(false);
        });

        filterSections(true); // Run initially for pre-selected values
    }
});
