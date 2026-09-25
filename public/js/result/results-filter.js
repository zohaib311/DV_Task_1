document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department_id');
    const sectionSelect = document.getElementById('section_id');
    const initialStateMsg = document.getElementById('initial_state_msg');
    const loadingSpinner = document.getElementById('loading_spinner');
    const studentsTableWrapper = document.getElementById('students_table_wrapper');
    const studentsTableBody = document.getElementById('students_table_body');
    const noStudentsMsg = document.getElementById('no_students_msg');

    // 1. Department Change -> Fetch Sections via AJAX
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            const departmentId = this.value;

            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            sectionSelect.disabled = true;
            resetStudentViews();

            if (!departmentId) {
                sectionSelect.innerHTML = '<option value="">-- Select Department First --</option>';
                return;
            }

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
                        sectionSelect.innerHTML = '<option value="">No Sections in this Department</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching sections:', error);
                    alert('Error loading sections!');
                });
        });
    }

    // 2. Section Change -> Fetch Students with Results via AJAX
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function () {
            const sectionId = this.value;

            resetStudentViews();

            if (!sectionId) {
                if (initialStateMsg) initialStateMsg.style.display = 'block';
                return;
            }

            if (loadingSpinner) loadingSpinner.style.display = 'block';

            fetch(`/result/get-students/${sectionId}`)
                .then(response => response.json())
                .then(students => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';

                    if (students.length > 0) {
                        studentsTableBody.innerHTML = '';
                        students.forEach((student, index) => {
                            const tr = document.createElement('tr');
                            const hasResults = student.results && student.results.length > 0;
                            const latestResult = hasResults ? student.results[student.results.length - 1] : null;

                            let courseStatusHtml = '';
                            if (hasResults && latestResult) {
                                const passClass = (latestResult.status || '').toLowerCase() === 'pass' ? 'bg-success' : 'bg-danger';
                                courseStatusHtml = `
                                    <div>
                                        <div class="fw-bold text-dark">${latestResult.course ? latestResult.course.name : 'N/A'}</div>
                                        <small class="text-muted">Grade: <strong>${latestResult.grade || 'N/A'}</strong> | GPA: <strong>${latestResult.gpa || '0'}</strong></small>
                                        <span class="badge ${passClass} ms-1">${latestResult.status || 'N/A'}</span>
                                    </div>
                                `;
                            } else {
                                courseStatusHtml = `<span class="badge bg-secondary">No Result</span>`;
                            }

                            // Build action buttons
                            let actionButtonsHtml = '';
                            if (hasResults && latestResult) {
                                actionButtonsHtml = `
                                    <div class="action__buttons">
                                        <!-- View Result -->
                                        <button type="button" class="action__btn action__info btn-view-result"
                                            title="View Details"
                                            data-bs-toggle="modal" data-bs-target="#viewResultModal"
                                            data-result-id="${latestResult.id}"
                                            data-student-name="${student.name}"
                                            data-student-email="${student.email || 'N/A'}"
                                            data-student-phone="${student.phone || 'N/A'}"
                                            data-student-image="${student.image || 'default-user.png'}"
                                            data-percentage="${latestResult.percentage}"
                                            data-gpa="${latestResult.gpa}"
                                            data-cgpa="${latestResult.cgpa}"
                                            data-grade="${latestResult.grade}"
                                            data-status="${latestResult.status}"
                                            data-course-name="${latestResult.course ? latestResult.course.name : 'N/A'}"
                                            data-course-code="${latestResult.course ? latestResult.course.code : ''}"
                                            data-section-name="${student.section ? student.section.name : ''}">
                                            <i class="bi bi-info-circle"></i>
                                        </button>

                                        <!-- Add Result -->
                                        <button type="button" class="action__btn action__add btn-add-result"
                                            title="Add New Result"
                                            data-bs-toggle="modal" data-bs-target="#addResultModal"
                                            data-student-id="${student.id}"
                                            data-section-id="${sectionId}"
                                            data-student-name="${student.name}"
                                            data-student-email="${student.email || 'N/A'}">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>

                                        <!-- Edit Result -->
                                        <button type="button" class="action__btn action__edit btn-edit-result"
                                            title="Edit Result"
                                            data-bs-toggle="modal" data-bs-target="#editResultModal"
                                            data-result-id="${latestResult.id}"
                                            data-student-id="${student.id}"
                                            data-section-id="${sectionId}"
                                            data-course-id="${latestResult.course_id}"
                                            data-percentage="${latestResult.percentage}"
                                            data-gpa="${latestResult.gpa}"
                                            data-cgpa="${latestResult.cgpa}"
                                            data-grade="${latestResult.grade}"
                                            data-status="${latestResult.status}"
                                            data-student-name="${student.name}"
                                            data-student-email="${student.email || 'N/A'}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <!-- Delete Result -->
                                        <button type="button" class="action__btn action__delete btn-delete-result"
                                            title="Delete Result"
                                            data-bs-toggle="modal" data-bs-target="#deleteResultModal"
                                            data-result-id="${latestResult.id}"
                                            data-student-name="${student.name}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                `;
                            } else {
                                actionButtonsHtml = `
                                    <div class="action__buttons">
                                        <!-- Add Result -->
                                        <button type="button" class="action__btn action__add btn-add-result"
                                            title="Add Result"
                                            data-bs-toggle="modal" data-bs-target="#addResultModal"
                                            data-student-id="${student.id}"
                                            data-section-id="${sectionId}"
                                            data-student-name="${student.name}"
                                            data-student-email="${student.email || 'N/A'}">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                `;
                            }

                            tr.innerHTML = `
                                <td>${index + 1}</td>
                                <td><span class="student-id-badge">STD-${student.id}</span></td>
                                <td class="fw-bold text-dark">${student.name}</td>
                                <td class="text-muted">${student.email || 'N/A'}</td>
                                <td>${courseStatusHtml}</td>
                                <td class="text-center">${actionButtonsHtml}</td>
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
                    alert('Error loading students!');
                });
        });
    }

    function resetStudentViews() {
        if (initialStateMsg) initialStateMsg.style.display = 'none';
        if (loadingSpinner) loadingSpinner.style.display = 'none';
        if (studentsTableWrapper) studentsTableWrapper.style.display = 'none';
        if (noStudentsMsg) noStudentsMsg.style.display = 'none';
        if (studentsTableBody) studentsTableBody.innerHTML = '';
    }

    // 3. Dynamic Modal Event Delegation
    document.addEventListener('click', function (e) {

        // A. View Result Modal Click
        const viewBtn = e.target.closest('.btn-view-result');
        if (viewBtn) {
            document.getElementById('view_student_name').textContent = viewBtn.getAttribute('data-student-name');
            document.getElementById('view_student_email').textContent = viewBtn.getAttribute('data-student-email');
            document.getElementById('view_student_phone').textContent = viewBtn.getAttribute('data-student-phone');
            document.getElementById('view_student_image').src = `/storage/images/${viewBtn.getAttribute('data-student-image')}`;
            
            document.getElementById('view_percentage').textContent = viewBtn.getAttribute('data-percentage') + '%';
            document.getElementById('view_gpa').textContent = viewBtn.getAttribute('data-gpa');
            document.getElementById('view_cgpa').textContent = viewBtn.getAttribute('data-cgpa');
            document.getElementById('view_grade').textContent = viewBtn.getAttribute('data-grade');

            document.getElementById('view_record_id').textContent = '#' + viewBtn.getAttribute('data-result-id');
            document.getElementById('view_course_name').textContent = viewBtn.getAttribute('data-course-name');
            document.getElementById('view_course_code').textContent = viewBtn.getAttribute('data-course-code') ? `(${viewBtn.getAttribute('data-course-code')})` : '';
            document.getElementById('view_section_name').textContent = viewBtn.getAttribute('data-section-name');

            const status = viewBtn.getAttribute('data-status');
            const statusBadge = document.getElementById('view_status_badge');
            if (statusBadge) {
                statusBadge.textContent = status;
                statusBadge.className = `status-badge ${status.toLowerCase() === 'pass' ? 'pass' : 'fail'}`;
            }
        }

        // B. Add Result Modal Click
        const addBtn = e.target.closest('.btn-add-result');
        if (addBtn) {
            document.getElementById('add_modal_student_id').value = addBtn.getAttribute('data-student-id');
            document.getElementById('add_modal_section_id').value = addBtn.getAttribute('data-section-id');
            document.getElementById('add_modal_student_name').textContent = addBtn.getAttribute('data-student-name');
            document.getElementById('add_modal_student_email').textContent = addBtn.getAttribute('data-student-email');
        }

        // C. Edit Result Modal Click
        const editBtn = e.target.closest('.btn-edit-result');
        if (editBtn) {
            const resultId = editBtn.getAttribute('data-result-id');
            const editForm = document.getElementById('editResultForm');
            if (editForm) {
                editForm.action = `/result/update/${resultId}`;
            }

            document.getElementById('edit_modal_student_id').value = editBtn.getAttribute('data-student-id');
            document.getElementById('edit_modal_section_id').value = editBtn.getAttribute('data-section-id');
            document.getElementById('edit_modal_student_name').textContent = editBtn.getAttribute('data-student-name');
            document.getElementById('edit_modal_student_email').textContent = editBtn.getAttribute('data-student-email');
            
            document.getElementById('edit_course_id').value = editBtn.getAttribute('data-course-id');
            document.getElementById('edit_percentage').value = editBtn.getAttribute('data-percentage');
            document.getElementById('edit_gpa').value = editBtn.getAttribute('data-gpa');
            document.getElementById('edit_cgpa').value = editBtn.getAttribute('data-cgpa');
            document.getElementById('edit_grade').value = editBtn.getAttribute('data-grade');
            document.getElementById('edit_status').value = editBtn.getAttribute('data-status');
        }

        // D. Delete Result Modal Click
        const deleteBtn = e.target.closest('.btn-delete-result');
        if (deleteBtn) {
            const resultId = deleteBtn.getAttribute('data-result-id');
            const studentName = deleteBtn.getAttribute('data-student-name');

            document.getElementById('delete_student_name').textContent = studentName;
            const deleteForm = document.getElementById('deleteResultForm');
            if (deleteForm) {
                deleteForm.action = `/result/delete/${resultId}`;
            }
        }
    });
});
