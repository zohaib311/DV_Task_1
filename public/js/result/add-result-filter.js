
document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department_id');
    const sectionSelect = document.getElementById('section_id');
    const initialStateMsg = document.getElementById('initial_state_msg');
    const loadingSpinner = document.getElementById('loading_spinner');
    const studentsTableWrapper = document.getElementById('students_table_wrapper');
    const studentsTableBody = document.getElementById('students_table_body');
    const noStudentsMsg = document.getElementById('no_students_msg');

    // 1. When Department changes -> fetch Sections via AJAX
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
                        '<option value="">There is No Section in This Department</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching sections:', error);
                alert('Sections loading Error');
            });
    });

    // 2. When Section changes -> fetch Students via AJAX
    sectionSelect.addEventListener('change', function () {
        const sectionId = this.value;

        resetStudentViews();

        if (!sectionId) {
            initialStateMsg.style.display = 'block';
            return;
        }

        // Show spinner
        loadingSpinner.style.display = 'block';

        // AJAX Request to fetch students by section ID
        fetch(`/result/get-students/${sectionId}`)
            .then(response => response.json())
            .then(students => {
                loadingSpinner.style.display = 'none';

                if (students.length > 0) {
                    studentsTableBody.innerHTML = '';
                    students.forEach((student, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td><span class="badge bg-secondary">STD-${student.id}</span></td>
                                    <td class="fw-semibold">${student.name}</td>
                                    <td class="text-muted">${student.email || 'N/A'}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary add-result-btn"
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

                    studentsTableWrapper.style.display = 'block';
                } else {
                    noStudentsMsg.style.display = 'block';
                }
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Error fetching students:', error);
                alert('Students loading Error');
            });
    });

    // Helper function to hide table, spinner, and messages
    function resetStudentViews() {
        initialStateMsg.style.display = 'none';
        loadingSpinner.style.display = 'none';
        studentsTableWrapper.style.display = 'none';
        noStudentsMsg.style.display = 'none';
        studentsTableBody.innerHTML = '';
    }

    // 3. Event Listener for "Add Result" button to populate and open Modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.add-result-btn');
        if (btn) {
            const studentId = btn.getAttribute('data-id');
            const studentName = btn.getAttribute('data-name');
            const studentEmail = btn.getAttribute('data-email');
            const sectionId = btn.getAttribute('data-section');

            document.getElementById('modal_student_id').value = studentId;
            document.getElementById('modal_section_id').value = sectionId;
            document.getElementById('modal_student_name').textContent = studentName;
            document.getElementById('modal_student_email').textContent = studentEmail;

            // Open Bootstrap Modal
            const addResultModal = new bootstrap.Modal(document.getElementById(
                'addResultModal'));
            addResultModal.show();
        }
    });
});
