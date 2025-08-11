document.addEventListener('DOMContentLoaded', function() {
    
    console.log('DOM fully loaded and parsed');

    // Element Selection
    const addEnrollmentBtn = document.getElementById('addEnrollmentBtn');
    const enrollmentFormContainer = document.getElementById('enrollmentFormContainer');
    const closeFormBtn = document.getElementById('closeFormBtn');
    const cancelFormBtn = document.getElementById('cancelFormBtn');
    const enrollmentForm = document.getElementById('enrollmentForm');
    const searchInput = document.getElementById('searchInput');
    const importFileBtn = document.getElementById('importFileBtn');
    const dragArea = document.getElementById('dragArea');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const fileItemsContainer = document.getElementById('fileItemsContainer');
    const importBtn = document.getElementById('importBtn');
    const subjectsContainer = document.getElementById('subjectsContainer');
    const addSubjectRowBtn = document.getElementById('addSubjectRowBtn');
    
    // Add Subject Row Dynamically
    function addSubjectRow() {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-2');
        newRow.innerHTML = `
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Subject Code">
            </div>
            <div class="col-md-6">
                <input type="number" class="form-control" placeholder="Units" min="0" step="0.5">
                <button type="button" class="btn btn-sm btn-danger mt-1 remove-subject-row">Remove</button>
            </div>
        `;
        
        // Add event listener to remove button
        const removeBtn = newRow.querySelector('.remove-subject-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                // Prevent removing the last row
                if (subjectsContainer.children.length > 1) {
                    newRow.remove();
                }
            });
        }

        if (subjectsContainer) {
            subjectsContainer.appendChild(newRow);
        }
    }

    // Event Listeners
    if (addEnrollmentBtn) {
        addEnrollmentBtn.addEventListener('click', function () {
            enrollmentFormContainer.classList.remove("d-none");
            enrollmentListContainer.classList.add("d-none");
            
            // Add first subject row if not exists
            if (subjectsContainer.children.length === 0) {
                addSubjectRow();
            }
        });
    }

    if (closeFormBtn) {
        closeFormBtn.addEventListener('click', function () {
            enrollmentFormContainer.classList.add("d-none");
            enrollmentListContainer.classList.remove("d-none");
            
            // Reset form and clear extra subject rows
            if (enrollmentForm) {
                enrollmentForm.reset();
            }
            
            while (subjectsContainer.children.length > 1) {
                subjectsContainer.removeChild(subjectsContainer.lastChild);
            }
        });
    }

    if (cancelFormBtn) {
        cancelFormBtn.addEventListener('click', function () {
            enrollmentFormContainer.classList.add("d-none");
            enrollmentListContainer.classList.remove("d-none");
            
            // Reset form and clear extra subject rows
            if (enrollmentForm) {
                enrollmentForm.reset();
            }
            
            while (subjectsContainer.children.length > 1) {
                subjectsContainer.removeChild(subjectsContainer.lastChild);
            }
        });
    }

    // Add Subject Row Button
    if (addSubjectRowBtn) {
        addSubjectRowBtn.addEventListener('click', addSubjectRow);
    }

    // Handle Enrollment Form Submission
    function collectEnrollmentFormData() {
        return {
            semester: document.getElementById('semester').value,
            academicYear: document.getElementById('academicYear').value,
            programLevel: document.getElementById('programLevel').value,
            programName: document.getElementById('programName').value,
            majorName: document.getElementById('majorName').value || '',
            studentIdNo: document.getElementById('studentIdNo').value,
            lastName: document.getElementById('lastName').value,
            firstName: document.getElementById('firstName').value,
            middleName: document.getElementById('middleName')?.value || '',
            yearLevel: document.getElementById('yearLevel').value,
            suffix: document.getElementById('suffix').value || null,
            sex: document.getElementById('sex').value,
            sector: document.getElementById('sector').value || null,
            sectorDetails: document.getElementById('sectorDetails').value || null,
            nationality: document.getElementById('nationality').value,
            studentType: document.getElementById('studentType').value,
            studentTypeOthers: document.getElementById('studentType').value === 'Others' 
                ? document.getElementById('studentTypeOthers').value 
                : null,
            
            // Existing subjects collection remains the same
            subjects: []
        };
    }
    
    // Update the form submission event listener to use this new function
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = collectEnrollmentFormData();
    
            // Collect subjects 
            const subjectRows = document.querySelectorAll('#subjectsContainer .row');
            subjectRows.forEach(row => {
                const subjectCode = row.querySelector('input[placeholder="Subject Code"]').value;
                const units = row.querySelector('input[placeholder="Units"]').value;
                
                if (subjectCode && units) {
                    formData.subjects.push({
                        subjectCode,
                        units: parseFloat(units)
                    });
                }
            });
    
            // Send data to server (rest of the code remains the same)
            fetch('school_save_enrollment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Enrollment saved successfully!');
                    closeEnrollmentForm();
                    loadEnrollments();
                } else {
                    alert('Error saving enrollment: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Duplicate Student ID Number.');
            });
        });
    }
    // Search Functionality
    // Current search function
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const enrollmentCards = document.querySelectorAll('.enrollment-card');
        
        enrollmentCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            card.style.display = cardText.includes(searchTerm) ? 'block' : 'none';
        });
    });
}

        // Search function
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const enrollmentCards = document.querySelectorAll('.enrollment-card');
                
                console.log(`Search term: "${searchTerm}"`); // Debug log
                console.log(`Total enrollment cards: ${enrollmentCards.length}`); // Debug log
                
                let matchedCards = 0;
                enrollmentCards.forEach(card => {
                    const cardText = card.textContent.toLowerCase();
                    const isMatch = cardText.includes(searchTerm);
                    
                    card.style.display = isMatch ? 'block' : 'none';
                    
                    if (isMatch) {
                        matchedCards++;
                        console.log('Matched card:', card); // Debug log
                    }
                });
                
                console.log(`Matched cards: ${matchedCards}`); // Debug log
                
                // Optional: Show a message if no cards match
                const noResultsMessage = document.getElementById('noResultsMessage');
                if (noResultsMessage) {
                    noResultsMessage.style.display = matchedCards === 0 ? 'block' : 'none';
                }
            });
        }

    // File Import Functionality
    if (dragArea) {
        dragArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dragArea.classList.add('active');
        });

        dragArea.addEventListener('dragleave', () => {
            dragArea.classList.remove('active');
        });

        dragArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dragArea.classList.remove('active');
            handleFiles(e.dataTransfer.files);
        });
    }

    if (browseBtn) {
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    function handleFiles(files) {
        if (fileItemsContainer) {
            fileItemsContainer.innerHTML = '';
            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');
                fileItem.innerHTML = `
                    <span>${file.name}</span>
                    <button class="btn btn-sm btn-danger" onclick="this.closest('.file-item').remove()">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
                fileItemsContainer.appendChild(fileItem);
            });
        }
    }

    // Import Button Handler
    if (importBtn) {
        importBtn.addEventListener('click', () => {
            const files = fileInput.files;
            if (files.length === 0) {
                alert('Please select files to import');
                return;
            }

            const formData = new FormData();
            for (let file of files) {
                formData.append('importFiles[]', file);
            }

            fetch('school_import_enrollments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Enrollments imported successfully!');
                    // Refresh enrollment list
                    loadEnrollments();
                    // Close import modal
                    const importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                    if (importModal) importModal.hide();
                } else {
                    alert('Error importing enrollments: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while importing enrollments.');
            });
        });
    }

    function closeEnrollmentForm() {
        enrollmentFormContainer.classList.add("d-none");
        enrollmentListContainer.classList.remove("d-none");
        
        // Reset form and clear extra subject rows
        if (enrollmentForm) {
            enrollmentForm.reset();
        }
        
        while (subjectsContainer.children.length > 1) {
            subjectsContainer.removeChild(subjectsContainer.lastChild);
        }
    }
    

    function loadEnrollments() {
        fetch('school_get_enrollments.php')
        .then(response => response.json())
        .then(data => {
            const enrollmentListContainer = document.getElementById('enrollmentListContainer');
            if (enrollmentListContainer) {
                enrollmentListContainer.innerHTML = '';
            
                data.forEach(enrollment => {
                    // Create a wrapper div for the scrollable table
                    let subjectsHTML = '';
                    
                    if (enrollment.subjects && Array.isArray(enrollment.subjects) && enrollment.subjects.length > 0) {
                        // Improved styling with explicit table layout control
                        subjectsHTML = `
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Enrolled Subjects</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0" style="table-layout: fixed; width: 100%;">
                                        <colgroup>
                                            <col style="width: 75%;">
                                            <col style="width: 25%;">
                                        </colgroup>
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="px-3">Subject Code</th>
                                                <th class="text-center">Units</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                        
                        // Add each subject as a table row with improved styling
                        enrollment.subjects.forEach(subject => {
                            subjectsHTML += `
                                <tr>
                                    <td class="px-3 py-2">${subject.subject_code}</td>
                                    <td class="text-center py-2">${subject.units}</td>
                                </tr>
                            `;
                        });
                        
                        // Calculate total units
                        const totalUnits = enrollment.subjects.reduce((sum, subject) => sum + parseInt(subject.units || 0), 0);
                        
                        // Add total units row
                        subjectsHTML += `
                                <tr class="table-primary">
                                    <th class="px-3 py-2">Total Units</th>
                                    <th class="text-center py-2">${totalUnits}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    </div>
                        `;
                    } else {
                        // Improved styling for no subjects message
                        subjectsHTML = `
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Enrolled Subjects</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>No subjects enrolled
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    const card = document.createElement('div');
                    card.classList.add('enrollment-card', 'card', 'mb-3', 'border-0');
                    card.innerHTML = `
                        <div class="enrollment-card-header toggleable-header d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                            <div class="header-content flex-grow-1">
                                <p class="mb-0 fw-bold">${enrollment.first_name} ${enrollment.last_name} <span class="text-muted fw-normal">| ID: ${enrollment.student_id_no}</span></p>
                            </div>
                            <div class="header-actions d-flex align-items-center">
                                <span class="badge bg-primary me-2">${enrollment.semester} Semester ${enrollment.academic_year}</span>
                                <button class="btn btn-sm btn-outline-warning me-2 view-enrollment" data-id="${enrollment.student_id}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-enrollment" data-id="${enrollment.student_id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-light toggle-btn ms-2">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="enrollment-card-body collapse">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Personal Information Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 border-0 ">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">Personal Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Last Name:</strong></p>
                                                        <p class="text-muted">${enrollment.last_name}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>First Name:</strong></p>
                                                        <p class="text-muted">${enrollment.first_name}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Middle Name:</strong></p>
                                                        <p class="text-muted">${enrollment.middle_name ? enrollment.middle_name : 'N/A'}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Suffix:</strong></p>
                                                        <p class="text-muted">${enrollment.suffix ? enrollment.suffix : 'N/A'}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Sex:</strong></p>
                                                        <p class="text-muted">${enrollment.sex}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Nationality:</strong></p>
                                                        <p class="text-muted">${enrollment.nationality}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Sector:</strong></p>
                                                        <p class="text-muted">${enrollment.sector ? enrollment.sector : 'N/A'}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Sector Details:</strong></p>
                                                        <p class="text-muted">${enrollment.sector_details ? enrollment.sector_details : 'N/A'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Enrollment Information Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 border-0">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Enrollment Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Student ID:</strong></p>
                                                        <p class="text-muted">${enrollment.student_id_no}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Student Type:</strong></p>
                                                        <p class="text-muted">${enrollment.student_type}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>If Others:</strong></p>
                                                        <p class="text-muted">${enrollment.if_others ? enrollment.if_others : 'N/A'}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Semester:</strong></p>
                                                        <p class="text-muted">${enrollment.semester}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Academic Year:</strong></p>
                                                        <p class="text-muted">${enrollment.academic_year}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Program Level:</strong></p>
                                                        <p class="text-muted">${enrollment.program_level}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Program:</strong></p>
                                                        <p class="text-muted">${enrollment.program_name}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Major:</strong></p>
                                                        <p class="text-muted">${enrollment.major_name}</p>
                                                    </div>
                                                    <div class="col-sm-6 mb-2">
                                                        <p class="mb-1"><strong>Year Level:</strong></p>
                                                        <p class="text-muted">${enrollment.year_level}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Enrolled Subjects Section -->
                                    <div class="col-12">
                                        ${subjectsHTML}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Toggle functionality
                    const cardHeader = card.querySelector('.toggleable-header');
                    const toggleBtn = card.querySelector('.toggle-btn');
                    const cardBody = card.querySelector('.enrollment-card-body');
                    const toggleIcon = toggleBtn.querySelector('i');
                    
                    // Function to toggle collapse
                    function toggleCardBody() {
                        $(cardBody).collapse('toggle');
                        
                        // Rotate the icon
                        toggleIcon.classList.toggle('fa-chevron-down');
                        toggleIcon.classList.toggle('fa-chevron-up');
                    }
                    
                    // Add click event to the entire header
                    cardHeader.addEventListener('click', function(e) {
                        // Prevent toggle if clicking on buttons
                        if (e.target.closest('.btn, a')) return;
                        toggleCardBody();
                    });
                    
                    // Add click event to toggle button
                    toggleBtn.addEventListener('click', toggleCardBody);
                    
                    // View and Delete button event listeners
                    const viewBtn = card.querySelector('.view-enrollment');
                    const deleteBtn = card.querySelector('.delete-enrollment');
                    
                    viewBtn.addEventListener('click', function() {
                        const enrollmentId = this.getAttribute('data-id');
                        viewEnrollmentDetails(enrollmentId);
                    });
                    
                    deleteBtn.addEventListener('click', function() {
                        const enrollmentId = this.getAttribute('data-id');
                        deleteEnrollment(enrollmentId);
                    });
                    
                    enrollmentListContainer.appendChild(card);
                });
            }
        })
        .catch(error => {
            console.error('Error loading enrollments:', error);
        });
    }

    // View Enrollment Details Function
    function viewEnrollmentDetails(enrollmentId) {
        fetch(`school_get_enrollment_details.php?id=${enrollmentId}`)
        .then(response => response.json())
        .then(details => {
            // Create a modal to display and edit enrollment details
            const modalHtml = `
                <div class="modal fade" id="enrollmentDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Enrollment Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editEnrollmentForm">
                                    <input type="hidden" id="editEnrollmentId" value="${details.student_id}">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="editSemester" class="form-label">Semester <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editSemester" required>
                                                <option value="1st" ${details.semester === '1st' ? 'selected' : ''}>First Semester</option>
                                                <option value="2nd" ${details.semester === '2nd' ? 'selected' : ''}>Second Semester</option>
                                                <option value="3rd" ${details.semester === '3rd' ? 'selected' : ''}>Third Semester</option>
                                                <option value="Summer" ${details.semester === 'Summer' ? 'selected' : ''}>Summer</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editAcademicYear" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editAcademicYear" value="${details.academic_year}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editProgramLevel" class="form-label">Program Level <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editProgramLevel" required>
                                                <option value="Pre-Baccalaureate" ${details.program_level === 'Pre-Baccalaureate' ? 'selected' : ''}>Pre-Baccalaureate</option>
                                                <option value="Baccalaureate" ${details.program_level === 'Baccalaureate' ? 'selected' : ''}>Baccalaureate</option>
                                                <option value="Post Baccalaureate" ${details.program_level === 'Post Baccalaureate' ? 'selected' : ''}>Post Baccalaureate</option>
                                                <option value="Masteral" ${details.program_level === 'Masteral' ? 'selected' : ''}>Masteral</option>
                                                <option value="Doctorate" ${details.program_level === 'Doctorate' ? 'selected' : ''}>Doctorate</option>
                                            </select>
                                        </div>
                                    </div>
    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="editProgramName" class="form-label">Program Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editProgramName" value="${details.program_name}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="editMajorName" class="form-label">Major Name</label>
                                            <input type="text" class="form-control" id="editMajorName" value="${details.major_name || ''}" placeholder="Optional">
                                        </div>
                                    </div>
    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="editStudentIdNo" class="form-label">Student ID No. <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editStudentIdNo" value="${details.student_id_no}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editLastName" value="${details.last_name}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editFirstName" value="${details.first_name}" required>
                                        </div>
                                    </div>
    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="editMiddleName" class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" id="editMiddleName" value="${details.middle_name || ''}" placeholder="Optional">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editSuffix" class="form-label">Suffix</label>
                                            <input type="text" class="form-control" id="editSuffix" value="${details.suffix || ''}" placeholder="Jr., Sr., III, etc.">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editYearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editYearLevel" required>
                                                <option value="1 Old" ${details.year_level === '1 Old' ? 'selected' : ''}>1st Year (Old Curriculum)</option>
                                                <option value="1 New" ${details.year_level === '1 New' ? 'selected' : ''}>1st Year (New Curriculum)</option>
                                                <option value="2" ${details.year_level === '2' ? 'selected' : ''}>2nd Year</option>
                                                <option value="3" ${details.year_level === '3' ? 'selected' : ''}>3rd Year</option>
                                                <option value="4" ${details.year_level === '4' ? 'selected' : ''}>4th Year</option>
                                                <option value="5" ${details.year_level === '5' ? 'selected' : ''}>5th Year</option>
                                                <option value="6" ${details.year_level === '6' ? 'selected' : ''}>6th Year</option>
                                                <option value="7" ${details.year_level === '7' ? 'selected' : ''}>7th Year</option>
                                            </select>
                                        </div>
                                    </div>
    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="editSex" class="form-label">Sex <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editSex" required>
                                                <option value="M" ${details.sex === 'M' ? 'selected' : ''}>Male</option>
                                                <option value="F" ${details.sex === 'F' ? 'selected' : ''}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editNationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editNationality" value="${details.nationality}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="editStudentType" class="form-label">Student Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editStudentType" required>
                                                <option value="Foreign" ${details.student_type === 'Foreign' ? 'selected' : ''}>Foreign</option>
                                                <option value="Regular" ${details.student_type === 'Regular' ? 'selected' : ''}>Regular</option>
                                                <option value="Exchange" ${details.student_type === 'Exchange' ? 'selected' : ''}>Exchange</option>
                                                <option value="LEP" ${details.student_type === 'LEP' ? 'selected' : ''}>LEP</option>
                                                <option value="ETEEAP" ${details.student_type === 'ETEEAP' ? 'selected' : ''}>ETEEAP</option>
                                                <option value="DE" ${details.student_type === 'DE' ? 'selected' : ''}>DE</option>
                                                <option value="TNE" ${details.student_type === 'TNE' ? 'selected' : ''}>TNE</option>
                                                <option value="Others" ${details.student_type === 'Others' ? 'selected' : ''}>Others</option>
                                            </select>
                                        </div>
                                    </div>
    
                                    <h5 class="mt-4 mb-3">Enrolled Subjects</h5>
                                    <div id="editSubjectsContainer">
                                        ${details.subjects.map((subject, index) => `
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control subject-code" placeholder="Subject Code" value="${subject.subject_code}">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="number" class="form-control subject-units" placeholder="Units" min="0" step="0.5" value="${subject.units}">
                                                    ${index === details.subjects.length - 1 ? 
                                                        '<button type="button" class="btn btn-sm btn-success mt-2 add-subject-row">Add Subject</button>' : 
                                                        '<button type="button" class="btn btn-sm btn-danger mt-2 remove-subject-row">Remove</button>'}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
    
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    
            // Create the modal dynamically
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer);
    
            // Initialize and show the modal
            const modal = new bootstrap.Modal(document.getElementById('enrollmentDetailsModal'));
            modal.show();
    
            // Add event listeners for modal interactions
            const editForm = document.getElementById('editEnrollmentForm');
            editForm.addEventListener('submit', handleEnrollmentUpdate);
    
            // Add subject row functionality
            const editSubjectsContainer = document.getElementById('editSubjectsContainer');
            editSubjectsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-subject-row')) {
                    addEditSubjectRow();
                } else if (e.target.classList.contains('remove-subject-row')) {
                    e.target.closest('.row').remove();
                }
            });
    
            // Remove modal from DOM when closed
            document.getElementById('enrollmentDetailsModal').addEventListener('hidden.bs.modal', function () {
                this.remove();
            });
        })
        .catch(error => {
            console.error('Error fetching enrollment details:', error);
            alert('Failed to retrieve enrollment details.');
        });
    }
    
    function addEditSubjectRow() {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-2');
        newRow.innerHTML = `
            <div class="col-md-6">
                <input type="text" class="form-control subject-code" placeholder="Subject Code">
            </div>
            <div class="col-md-6">
                <input type="number" class="form-control subject-units" placeholder="Units" min="0" step="0.5">
                <button type="button" class="btn btn-sm btn-danger mt-2 remove-subject-row">Remove</button>
            </div>
        `;
        document.getElementById('editSubjectsContainer').appendChild(newRow);
    }
    
    function handleEnrollmentUpdate(e) {
        e.preventDefault();
    
        // Collect form data
        const formData = {
            id: document.getElementById('editEnrollmentId').value,
            semester: document.getElementById('editSemester').value,
            academicYear: document.getElementById('editAcademicYear').value,
            programLevel: document.getElementById('editProgramLevel').value,
            programName: document.getElementById('editProgramName').value,
            majorName: document.getElementById('editMajorName').value || null,
            studentIdNo: document.getElementById('editStudentIdNo').value,
            lastName: document.getElementById('editLastName').value,
            firstName: document.getElementById('editFirstName').value,
            middleName: document.getElementById('editMiddleName').value || null,
            suffix: document.getElementById('editSuffix').value || null,
            yearLevel: document.getElementById('editYearLevel').value,
            sex: document.getElementById('editSex').value,
            nationality: document.getElementById('editNationality').value,
            studentType: document.getElementById('editStudentType').value,
            subjects: []
        };
    
        // Collect subjects
        const subjectRows = document.querySelectorAll('#editSubjectsContainer .row');
        subjectRows.forEach(row => {
            const subjectCode = row.querySelector('.subject-code').value;
            const units = row.querySelector('.subject-units').value;
            
            if (subjectCode && units) {
                formData.subjects.push({
                    subjectCode,
                    units: parseFloat(units)
                });
            }
        });
    
        // Add client-side validation
        const validateForm = (data) => {
            const errors = [];
            if (!data.studentIdNo) errors.push('Student ID is required');
            if (!data.lastName) errors.push('Last Name is required');
            if (!data.firstName) errors.push('First Name is required');
            
            return errors;
        };
    
        const validationErrors = validateForm(formData);
        if (validationErrors.length > 0) {
            alert('Validation Errors:\n' + validationErrors.join('\n'));
            return;
        }
    
        // Log form data for debugging
        console.log('Sending enrollment update:', formData);
    
        // Send update request
        fetch('school_update_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            // Log the raw response for debugging
            console.log('Raw response:', response);
            
            // Check response status
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            
            if (data.success) {
                alert('Enrollment updated successfully!');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('enrollmentDetailsModal'));
                if (modal) modal.hide();
                
                // Refresh the enrollment list
                loadEnrollments();
            } else {
                alert('Error updating enrollment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Full error details:', error);
            
            // More detailed error logging
            if (error.response) {
                // The request was made and the server responded with a status code
                console.error('Response error data:', error.response.data);
                console.error('Response error status:', error.response.status);
                console.error('Response error headers:', error.response.headers);
            } else if (error.request) {
                // The request was made but no response was received
                console.error('No response received:', error.request);
            } else {
                // Something happened in setting up the request
                console.error('Error setting up request:', error.message);
            }
            
            const errorMessage = error.message || 'An unexpected error occurred';
            alert(`Error updating enrollment: ${errorMessage}\n\nPlease check the console for more details.`);
        });
    }

    // Delete Enrollment Function
    function deleteEnrollment(enrollmentId) {
        if (!confirm('Are you sure you want to delete this enrollment record? This action cannot be undone.')) {
            return;
        }

        fetch('school_delete_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: enrollmentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Enrollment record deleted successfully.');
                loadEnrollments(); // Refresh the enrollment list
            } else {
                alert('Error deleting enrollment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the enrollment.');
        });
    }

    // Initial load of enrollments
    loadEnrollments();

});