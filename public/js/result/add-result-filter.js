
document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department_id');
    const sectionSelect = document.getElementById('section_id');
    const initialStateMsg = document.getElementById('initial_state_msg');
    const loadingSpinner = document.getElementById('loading_spinner');
    const studentsTableWrapper = document.getElementById('students_table_wrapper');
    const studentsTableBody = document.getElementById('students_table_body');
    const noStudentsMsg = document.getElementById('no_students_msg');

    // 1. When Department changes -> fetch Sections via AJAX
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            const departmentId = this.value;

            // Reset section dropdown & views
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            sectionSelect.disabled = true;
            resetStudentViews();

            if (!departmentId) {
                sectionSelect.innerHTML = '<option value="">-- Select Department First --</option>';
                return;
            }

            // AJAX Request to fetch sections by department ID
            fetch(`/result/get-sections/${departmentId}`)
                .then(response => response.json())
                .then(sections => {
                    if (sections.length > 0) {
                        sectionSelect.disabled = false;
                        sections.forEach(section => {
                            const option = document.createElement('option');
                            option.value = section.id;
                            option.textContent = section.name;
                            sectionSelect.appendChild(option);
                        });
                    } else {
                        sectionSelect.innerHTML =
                            '<option value="">There Are No Sections in This Department</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching sections:', error);
                    alert('Sections loading Error');
                });
        });
    }

    // 2. When Section changes -> fetch Students via AJAX
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function () {
            const sectionId = this.value;

            resetStudentViews();

            if (!sectionId) {
                if (initialStateMsg) initialStateMsg.style.display = 'block';
                return;
            }

            // Show spinner
            if (loadingSpinner) loadingSpinner.style.display = 'block';

            // AJAX Request to fetch students by section ID
            fetch(`/result/get-students/${sectionId}`)
                .then(response => response.json())
                .then(students => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';

                    if (students.length > 0) {
                        studentsTableBody.innerHTML = '';
                        students.forEach((student, index) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${index + 1}</td>
                                <td><span class="student-id-badge">STD-${student.id}</span></td>
                                <td class="fw-bold">${student.name}</td>
                                <td class="text-muted">${student.email || 'N/A'}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-add-result add-result-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addResultModal"
                                        data-id="${student.id}"
                                        data-name="${student.name}"
                                        data-email="${student.email || 'N/A'}"
                                        data-section="${sectionId}">
                                        <i class="bi bi-plus-circle me-1"></i> Add Result
                                    </button>
                                </td>
                            `;
                            studentsTableBody.appendChild(tr);
                        });

                        if (studentsTableWrapper) studentsTableWrapper.style.display = 'block';
                    } else {
                        if (noStudentsMsg) noStudentsMsg.style.display = 'block';
                    }
                })
                .catch(error => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                    console.error('Error fetching students:', error);
                    alert('Students loading Error');
                });
        });
    }

    // Helper function to hide table, spinner, and messages
    function resetStudentViews() {
        if (initialStateMsg) initialStateMsg.style.display = 'none';
        if (loadingSpinner) loadingSpinner.style.display = 'none';
        if (studentsTableWrapper) studentsTableWrapper.style.display = 'none';
        if (noStudentsMsg) noStudentsMsg.style.display = 'none';
        if (studentsTableBody) studentsTableBody.innerHTML = '';
    }

    // 3. Event Listener for "Add Result" button to populate Modal fields
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.add-result-btn');
        if (btn) {
            const studentId = btn.getAttribute('data-id');
            const studentName = btn.getAttribute('data-name');
            const studentEmail = btn.getAttribute('data-email');
            const sectionId = btn.getAttribute('data-section');

            const modalStudentId = document.getElementById('modal_student_id');
            const modalSectionId = document.getElementById('modal_section_id');
            const modalStudentName = document.getElementById('modal_student_name');
            const modalStudentEmail = document.getElementById('modal_student_email');

            if (modalStudentId) modalStudentId.value = studentId;
            if (modalSectionId) modalSectionId.value = sectionId;
            if (modalStudentName) modalStudentName.textContent = studentName;
            if (modalStudentEmail) modalStudentEmail.textContent = studentEmail;

            // Fallback modal open if data-bs-toggle doesn't trigger automatically
            const modalElement = document.getElementById('addResultModal');
            if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalInstance.show();
            }
        }
    });
});

