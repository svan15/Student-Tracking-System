// Select DOM elements
const programListContainer = document.getElementById("programListContainer");
const programFormContainer = document.getElementById("programFormContainer");
const addProgramBtn = document.getElementById("addProgramBtn");
const closeFormBtn = document.getElementById("closeFormBtn");
const cancelFormBtn = document.getElementById("cancelFormBtn");
const programForm = document.getElementById("programForm");

// Current program being edited (if any)
let currentProgramId = null;

// Load programs when the page loads
document.addEventListener('DOMContentLoaded', loadPrograms);

// Function to load programs from the database
function loadPrograms() {
    fetch('school_save_programs.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.programs) {
                // Clear existing programs
                programListContainer.innerHTML = '';
                
                if (data.programs.length === 0) {
                    programListContainer.innerHTML = `
                        <div class="alert alert-info text-center">
                            No programs added yet. Click the "Add Program" button to add your first program.
                        </div>
                    `;
                    return;
                }
                
                // Add cards for each program
                data.programs.forEach(program => {
                    addProgramCard(program);
                });
            } else {
                console.error('Failed to load programs:', data.message);
                programListContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load programs: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading programs:', error);
            programListContainer.innerHTML = `
                <div class="alert alert-danger">
                    Error loading programs. Check console for details.
                </div>
            `;
        });
}

// Function to add a program card to the list
function addProgramCard(program) {
    const card = document.createElement("div");
    card.className = "program-card";
    card.setAttribute('data-id', program.program_id);
    
    card.innerHTML = `
        <div class="program-card-header" role="button">
            <h5 class="mb-0">${program.program_name || 'Unnamed Program'}</h5>
            <div>
                <button class="btn btn-sm btn-outline-warning edit-btn me-2">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-btn">
                    <i class="fa fa-trash"></i>
                </button>
                <button class="btn btn-sm toggle-btn ms-2">
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="program-card-body" style="display: none;">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Major:</strong> ${program.major || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Learning Modality:</strong> ${program.learning_modality || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Academic Year Started:</strong> ${program.academic_year_started || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Category:</strong> ${program.category || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Others:</strong> ${program.others || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Other Remarks:</strong> ${program.other_remarks || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Serial:</strong> ${program.serial || 'N/A'}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Year:</strong> ${program.year || 'N/A'}
                </div>
            </div>
        </div>
    `;
    
    // Add the card to the container
    programListContainer.appendChild(card);
    
    // Add event listeners for edit and delete buttons
    const editBtn = card.querySelector('.edit-btn');
    const deleteBtn = card.querySelector('.delete-btn');
    const toggleBtn = card.querySelector('.toggle-btn');
    const cardHeader = card.querySelector('.program-card-header');
    const cardBody = card.querySelector('.program-card-body');
    
    // Function to toggle the card body visibility
    const toggleCardBody = (e) => {
        // Prevent toggle when clicking edit or delete buttons
        if (e.target.closest('.edit-btn') || e.target.closest('.delete-btn')) {
            return;
        }
        
        const isVisible = cardBody.style.display !== 'none';
        cardBody.style.display = isVisible ? 'none' : 'block';
        
        // Change the toggle button icon
        const icon = toggleBtn.querySelector('i');
        icon.className = isVisible ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
    };
    
    // Add click event to card header and toggle button
    cardHeader.addEventListener('click', toggleCardBody);
    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the header click event
        toggleCardBody(e);
    });
    
    editBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the header click event
        openProgramForm(program);
    });
    
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the header click event
        deleteProgram(program.program_id);
    });
}

// Function to open the program form (for add or edit)
function openProgramForm(program = null) {
    // Clear form
    programForm.reset();
    
    // If program is provided, populate form with program data
    if (program) {
        document.getElementById('programName').value = program.program_name || '';
        document.getElementById('major').value = program.major || '';
        document.getElementById('learningModality').value = program.learning_modality || '';
        document.getElementById('academicYearStarted').value = program.academic_year_started || '';
        document.getElementById('category').value = program.category || '';
        document.getElementById('others').value = program.others || '';
        document.getElementById('serial').value = program.serial || '';
        document.getElementById('year').value = program.year || '';
        document.getElementById('otherRemarks').value = program.other_remarks || '';
        
        // Set current program ID
        currentProgramId = program.program_id;
        
        // Change form title to "Edit Program"
        document.querySelector('.card-header h5').textContent = 'Edit Program';
    } else {
        // Set current program ID to null (adding new program)
        currentProgramId = null;
        
        // Change form title to "Add New Program"
        document.querySelector('.card-header h5').textContent = 'Add New Program';
    }
    
    // Show form, hide program list
    programListContainer.classList.add('d-none');
    programFormContainer.classList.remove('d-none');
}

// Function to close the program form
function closeProgramForm() {
    programFormContainer.classList.add('d-none');
    programListContainer.classList.remove('d-none');
    
    // Reset current program ID
    currentProgramId = null;
}

// Function to delete a program
function deleteProgram(programId) {
    // Confirm deletion
    if (confirm('Are you sure you want to delete this program?')) {
        // Send delete request to server
        fetch(`school_save_programs.php?program_id=${programId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove program card from DOM
                const card = document.querySelector(`.program-card[data-id="${programId}"]`);
                if (card) {
                    card.remove();
                }
                
                // If no programs left, show message
                if (programListContainer.children.length === 0) {
                    programListContainer.innerHTML = `
                        <div class="alert alert-info text-center">
                            No programs added yet. Click the "Add Program" button to add your first program.
                        </div>
                    `;
                }
                
                // Show success message
                alert(data.message || 'Program deleted successfully');
            } else {
                alert('Error: ' + (data.message || 'Failed to delete program'));
            }
        })
        .catch(error => {
            console.error('Error deleting program:', error);
            alert('Failed to delete program. Check console for details.');
        });
    }
}

// Event listener for add program button
addProgramBtn.addEventListener('click', () => {
    openProgramForm();
});

// Event listeners for closing form
closeFormBtn.addEventListener('click', closeProgramForm);
cancelFormBtn.addEventListener('click', closeProgramForm);

// Event listener for form submission
programForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Get form data
    const programData = {
        program_name: document.getElementById('programName').value,
        major: document.getElementById('major').value,
        learning_modality: document.getElementById('learningModality').value,
        academic_year_started: document.getElementById('academicYearStarted').value,
        category: document.getElementById('category').value,
        others: document.getElementById('others').value,
        serial: document.getElementById('serial').value,
        year: document.getElementById('year').value,
        other_remarks: document.getElementById('otherRemarks').value
    };
    
    // If currentProgramId is set, we're editing an existing program
    if (currentProgramId) {
        programData.program_id = currentProgramId;
    }
    
    // Send data to server
    fetch('school_save_programs.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ programs: [programData] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close form
            closeProgramForm();
            
            // Reload programs to get updated data
            loadPrograms();
            
            // Show success message
            alert(data.message || 'Program saved successfully');
        } else {
            alert('Error: ' + (data.message || 'Failed to save program'));
        }
    })
    .catch(error => {
        console.error('Error saving program:', error);
        alert('Failed to save program. Check console for details.');
    });
});

// File Import Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const dragArea = document.getElementById('dragArea');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const fileItemsContainer = document.getElementById('fileItemsContainer');
    const importBtn = document.getElementById('importBtn');
    
    // Store the selected files
    let files = [];

    // Click browse button to open file input
    browseBtn.addEventListener('click', () => {
        fileInput.click();
    });

    // Handle selected files from file input
    fileInput.addEventListener('change', () => {
        const selectedFiles = Array.from(fileInput.files);
        handleFiles(selectedFiles);
    });

    // Handle drag events
    ['dragover', 'dragleave', 'drop'].forEach(eventName => {
        dragArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Add/remove active class for visual feedback
    dragArea.addEventListener('dragover', () => {
        dragArea.classList.add('active');
    });

    dragArea.addEventListener('dragleave', () => {
        dragArea.classList.remove('active');
    });

    // Handle dropped files
    dragArea.addEventListener('drop', (e) => {
        dragArea.classList.remove('active');
        const droppedFiles = Array.from(e.dataTransfer.files);
        handleFiles(droppedFiles);
    });

    // Process the files
    function handleFiles(selectedFiles) {
        // Add new files to the array
        files = [...files, ...selectedFiles];
        
        // Display files in the list
        displayFiles();
    }

    // Display files in the file list
    function displayFiles() {
        fileItemsContainer.innerHTML = '';
        
        if (files.length === 0) {
            fileItemsContainer.innerHTML = '<p class="text-muted">No files selected</p>';
            return;
        }

        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            // Determine icon based on file type
            let icon = 'fa-file';
            if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
                icon = 'fa-file-excel';
            } else if (file.name.endsWith('.csv')) {
                icon = 'fa-file-csv';
            }
            
            fileItem.innerHTML = `
                <div>
                    <i class="fas ${icon} me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                </div>
                <button class="btn btn-sm btn-outline-danger remove-file" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            fileItemsContainer.appendChild(fileItem);
        });

        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-file').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                files.splice(index, 1);
                displayFiles();
            });
        });
    }

    // Format file size for display
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Import button event
    importBtn.addEventListener('click', () => {
        if (files.length === 0) {
            alert('Please select at least one file to import');
            return;
        }

        // Process each file
        files.forEach(file => {
            // Only process CSV files
            if (!file.name.endsWith('.csv')) {
                alert(`File ${file.name} is not a CSV file and will be skipped.`);
                return;
            }
            
            // Create form data to send the file
            const formData = new FormData();
            formData.append('file', file);
            
            // Send the file to the server
            fetch('school_import_programs.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show the results in the modal instead of alert
                    showImportResultModal(data);
                    
                    // Reload programs to get updated data
                    loadPrograms();
                    
                    // Close the import modal
                    const importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                    importModal.hide();
                    
                    // Clear the file list
                    files = [];
                    displayFiles();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error importing file:', error);
                alert('Failed to import file. Check console for details.');
            });
        });
    });

    // Function to display import results in the modal
    function showImportResultModal(data) {
        // Set the success message
        const successMessage = document.getElementById('importSuccessMessage');
        successMessage.textContent = `Successfully imported ${data.message.split(' Imported ')[1].split(' programs.')[0]} programs.`;
        
        // Handle skipped rows
        const skippedAlert = document.getElementById('importSkippedAlert');
        const skippedMessage = document.getElementById('importSkippedMessage');
        
        if (data.skipped_rows > 0) {
            skippedMessage.textContent = `${data.skipped_rows} programs were skipped.`;
            skippedAlert.style.display = 'block';
        } else {
            skippedAlert.style.display = 'none';
        }
        
        // Handle duplicate programs
        const duplicatesContainer = document.getElementById('importDuplicatesContainer');
        const duplicatesList = document.getElementById('duplicateProgramsList');
        
        if (data.duplicate_programs && data.duplicate_programs.length > 0) {
            // Clear previous items
            duplicatesList.innerHTML = '';
            
            // Add each duplicate program to the list
            data.duplicate_programs.forEach(program => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.textContent = `${program.program_name} ${program.major ? '(' + program.major + ')' : ''}`;
                duplicatesList.appendChild(listItem);
            });
            
            duplicatesContainer.style.display = 'block';
        } else {
            duplicatesContainer.style.display = 'none';
        }
        
        // Show the modal
        const resultModal = new bootstrap.Modal(document.getElementById('importResultModal'));
        resultModal.show();
    }
});