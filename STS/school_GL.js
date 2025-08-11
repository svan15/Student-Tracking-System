// Select table body and buttons
const programTableBody = document.getElementById("programTableBody");
const addProgramBtn = document.getElementById("addProgramBtn");
const saveContainer = document.getElementById("saveContainer");
const saveBtn = document.getElementById("saveBtn");
const searchInput = document.getElementById("searchInput");

// Make sure this is at the top level of your script, not inside any other function
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, calling loadGraduates()");
    loadGraduates();
    
    // Initialize other DOM elements
    // ...rest of your initialization code
});

function formatDate(dateString) {
    if (!dateString) return '';
    
    // Assuming dateString is in YYYY-MM-DD format from the database
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid
    
    return date.toISOString().split('T')[0]; // Format as YYYY-MM-DD for display
}

function loadGraduates() {
    console.log("Loading graduates data from server...");
    fetch('school_load_graduates.php')
    .then(response => response.json())
    .then(data => {
        console.log("Loaded Graduates:", data);
        if (data.success) {
            programTableBody.innerHTML = '';
            if (data.graduates.length === 0) {
                console.warn("No graduates found.");
                return;
            }
            data.graduates.forEach(graduate => {
                const row = document.createElement("tr");
                row.setAttribute("data-id", graduate.graduate_id);
                
                row.innerHTML = `
                    <td></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="academic_year_started" value="${graduate.academic_year_started}" disabled></td>
                    <td>
                        <select class="form-select bg-transparent border-0 custom-select" name="semester_started" disabled>
                            <option value="">Select Semester</option>
                            <option value="1st" ${graduate.semester_started === '1st' ? 'selected' : ''}>1st</option>
                            <option value="2nd" ${graduate.semester_started === '2nd' ? 'selected' : ''}>2nd</option>
                            <option value="3rd" ${graduate.semester_started === '3rd' ? 'selected' : ''}>3rd</option>
                            <option value="Summer" ${graduate.semester_started === 'Summer' ? 'selected' : ''}>Summer</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="academic_year_ended" value="${graduate.academic_year_ended}" disabled></td>
                    <td>
                        <select class="form-select bg-transparent border-0 custom-select" name="semester_ended" disabled>
                            <option value="">Select Semester</option>
                            <option value="1st" ${graduate.semester_ended === '1st' ? 'selected' : ''}>1st</option>
                            <option value="2nd" ${graduate.semester_ended === '2nd' ? 'selected' : ''}>2nd</option>
                            <option value="3rd" ${graduate.semester_ended === '3rd' ? 'selected' : ''}>3rd</option>
                            <option value="Summer" ${graduate.semester_ended === 'Summer' ? 'selected' : ''}>Summer</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="program_name" value="${graduate.program_name}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="major_name" value="${graduate.major_name || ''}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="student_id_no" value="${graduate.student_id_no}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="last_name" value="${graduate.last_name}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="first_name" value="${graduate.first_name}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="middle_name" value="${graduate.middle_name || ''}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="suffix" value="${graduate.suffix || ''}" disabled></td>
                    <td><input type="date" class="form-control bg-transparent border-0" name="date_of_birth" value="${formatDate(graduate.date_of_birth)}" disabled></td>
                    <td>
                        <select class="form-select bg-transparent border-0 custom-select" name="sex" disabled>
                            <option value="">Select</option>
                            <option value="F" ${graduate.sex === 'F' ? 'selected' : ''}>F</option>
                            <option value="M" ${graduate.sex === 'M' ? 'selected' : ''}>M</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select bg-transparent border-0 custom-select" name="student_type" disabled>
                            <option value="">Select Type</option>
                            <option value="Foreign" ${graduate.student_type === 'Foreign' ? 'selected' : ''}>Foreign</option>
                            <option value="Regular" ${graduate.student_type === 'Regular' ? 'selected' : ''}>Regular</option>
                            <option value="Exchange" ${graduate.student_type === 'Exchange' ? 'selected' : ''}>Exchange</option>
                            <option value="LEP" ${graduate.student_type === 'LEP' ? 'selected' : ''}>LEP</option>
                            <option value="ETEEAP" ${graduate.student_type === 'ETEEAP' ? 'selected' : ''}>ETEEAP</option>
                            <option value="DE" ${graduate.student_type === 'DE' ? 'selected' : ''}>DE</option>
                            <option value="TNE" ${graduate.student_type === 'TNE' ? 'selected' : ''}>TNE</option>
                            <option value="Others" ${graduate.student_type === 'Others' ? 'selected' : ''}>Others</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="if_others" value="${graduate.if_others || ''}" disabled></td>
                    <td><input type="text" class="form-control bg-transparent border-0" name="special_order_no" value="${graduate.special_order_no || ''}" disabled></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-btn"><i class="fa fa-edit"></i></button>
                    </td>
                `;
                programTableBody.appendChild(row);
            });

            updateRowNumbers();
        } else {
            alert('Error loading graduates: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}


// Function to update the row numbers
function updateRowNumbers() {
    const rows = programTableBody.querySelectorAll("tr:not(.d-none)");
    rows.forEach((row, index) => {
        // Get the first cell (row number cell) and update its text
        const numberCell = row.cells[0];
        numberCell.textContent = index + 1;
    });
}

// Search functionality
searchInput.addEventListener("input", function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = programTableBody.querySelectorAll("tr");
    
    rows.forEach(row => {
        let found = false;
        // Search through all input and select fields in the row
        const inputs = row.querySelectorAll("input, select");
        
        inputs.forEach(input => {
            let value = "";
            
            if (input.tagName === "SELECT" && input.selectedIndex !== -1) {
                value = input.options[input.selectedIndex].text;
            } else {
                value = input.value;
            }
            
            if (value.toLowerCase().includes(searchTerm)) {
                found = true;
            }
        });
        
        // Also search through non-input/select cells (for read-only data)
        const textCells = row.querySelectorAll("td:not(:first-child):not(:last-child)");
        textCells.forEach(cell => {
            // Skip cells that contain inputs
            if (cell.querySelector("input, select") === null) {
                if (cell.textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                }
            }
        });
        
        // Hide/show the row based on search result
        if (found || searchTerm === "") {
            row.classList.remove("d-none");
        } else {
            row.classList.add("d-none");
        }
    });
    
    // Update row numbers for visible rows
    updateRowNumbers();
});

// Function to add a new row to the table
addProgramBtn.addEventListener("click", () => {
    // Create table row
    const row = document.createElement("tr");
    row.innerHTML = `
        <td></td> <!-- Will be filled by updateRowNumbers -->
        <td><input type="text" class="form-control" name="academic_year_started" required></td>
        <td>
            <select class="form-select" name="semester_started">
                <option value="">Select Semester</option>
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
                <option value="3rd">3rd</option>
                <option value="Summer">Summer</option>
            </select>
        </td>
        <td><input type="text" class="form-control" name="academic_year_ended" required></td>
        <td>
            <select class="form-select" name="semester_ended">
                <option value="">Select Semester</option>
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
                <option value="3rd">3rd</option>
                <option value="Summer">Summer</option>
            </select>
        </td>
        <td><input type="text" class="form-control" name="program_name" required></td>
        <td><input type="text" class="form-control" name="major_name"></td>
        <td><input type="text" class="form-control" name="student_id_no" required></td>
        <td><input type="text" class="form-control" name="last_name" required></td>
        <td><input type="text" class="form-control" name="first_name" required></td>
        <td><input type="text" class="form-control" name="middle_name"></td>
        <td><input type="text" class="form-control" name="suffix"></td>
        <td><input type="date" class="form-control" name="date_of_birth" required></td>
        <td>
            <select class="form-select" name="sex" required>
                <option value="">Select</option>
                <option value="F">F</option>
                <option value="M">M</option>
            </select>        
        </td>
        <td>
            <select class="form-select" name="student_type" required>
                <option value="">Select Type</option>
                <option value="Foreign">Foreign</option>
                <option value="Regular">Regular</option>
                <option value="Exchange">Exchange</option>
                <option value="LEP">LEP</option>
                <option value="ETEEAP">ETEEAP</option>
                <option value="DE">DE</option>
                <option value="TNE">TNE</option>
                <option value="Others">Others</option>
            </select>
        </td>
        <td><input type="text" class="form-control" name="if_others"></td>
        <td><input type="text" class="form-control" name="special_order_no"></td>
        <td>
            <button class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
        </td>
    `;

    // Append row to table
    programTableBody.appendChild(row);
    
    // Update row numbers
    updateRowNumbers();
    
    // Show save button
    saveContainer.classList.remove("d-none");
});

// Save button event listener
// Update your save button event listener with this improved version
saveBtn.addEventListener("click", () => {
    let graduates = [];
    const rows = programTableBody.querySelectorAll("tr");

    rows.forEach(row => {
        // Skip rows that don't have input fields (loaded records)
        const inputFields = row.querySelectorAll("input, select");
        if (inputFields.length === 0) return;
        
        let graduate = {};
        // Check if this is an existing graduate (has an ID)
        const graduateId = row.getAttribute("data-id");
        if (graduateId) {
            graduate.graduate_id = graduateId;
        }

        inputFields.forEach(input => {
            if (input.name) {
                graduate[input.name] = input.value;
            }
        });
        
        // Only add to the array if we have some data
        if (Object.keys(graduate).length > 0) {
            graduates.push(graduate);
        }
    });

    // Don't send if there's no data
    if (graduates.length === 0) {
        alert("No new data to save");
        return;
    }

    console.log("Sending data:", JSON.stringify(graduates));

    // Show a loading indicator or disable the save button
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    // In the save button event listener, modify the fetch response handling
    fetch("school_save_graduates.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(graduates),
        credentials: "same-origin"
    })
    .then(response => {
        return response.text().then(text => {
            console.log("Raw server response:", text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Failed to parse JSON response:", e);
                // Look for specific error patterns in the raw HTML response
                if (text.includes("Duplicate entry") && text.includes("student_id_no")) {
                    throw new Error("A student with this ID already exists in the database.");
                } else {
                    throw new Error("Server error: " + text.replace(/<[^>]*>?/gm, '').substring(0, 100));
                }
            }
        });
    })
    .then(data => {
        console.log("Processed response:", data);
        if (data.success) {
            alert("Graduates saved successfully!");
            loadGraduates();
            saveContainer.classList.add("d-none");
        } else {
            if (data.message === "User not logged in") {
                alert("Your session has expired. Please login again.");
                window.location.href = "index.php";
            } else {
                alert("Error: " + data.message);
            }
        }
    })
    .catch(error => {
        console.error("Fetch Error:", error);
        if (error.message.includes("A student with this ID already exists")) {
            alert(error.message);
        } else {
            alert("An error occurred while saving data: " + error.message);
        }
    })
    .finally(() => {
        // Re-enable the save button
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Changes';
        
    });
});


// Show Edit button on hover, hide when mouse leaves
programTableBody.addEventListener("mouseover", (e) => {
    let row = e.target.closest("tr");
    if (row) {
        let editBtn = row.querySelector(".edit-btn");
        if (editBtn) editBtn.classList.remove("d-none");
    }
});

programTableBody.addEventListener("mouseout", (e) => {
    let row = e.target.closest("tr");
    if (row) {
        let editBtn = row.querySelector(".edit-btn");
        if (editBtn) editBtn.classList.add("d-none");
    }
});

// Event delegation for edit button (enable inputs when clicked)
programTableBody.addEventListener("click", (e) => {
    if (e.target.closest(".edit-btn")) {
        let row = e.target.closest("tr");

        // Enable all inputs in the row
        row.querySelectorAll("input, select").forEach(input => {
            input.removeAttribute("disabled");
        });

        // Replace edit button with trash button
        let actionCell = row.lastElementChild;
        actionCell.innerHTML = `
            <button class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
        `;

        // Show save button
        saveContainer.classList.remove("d-none");
    }
});


// Delete button functionality
programTableBody.addEventListener("click", (e) => {
    if (e.target.closest(".delete-btn")) {
        const row = e.target.closest("tr");
        const graduateId = row.getAttribute("data-id");
        
        // If it's a new row that hasn't been saved yet (no ID)
        if (!graduateId) {
            row.remove();
            updateRowNumbers();
            
            // If no rows left, hide save button
            if (programTableBody.querySelectorAll("tr").length === 0) {
                saveContainer.classList.add("d-none");
            }
            return;
        }
        
        // Confirm deletion
        if (confirm("Are you sure you want to delete this record?")) {
            // Send delete request to the server using the existing save_graduates.php
            fetch("school_save_graduates.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ 
                    operation: 'delete',
                    id: graduateId 
                }),
                credentials: "same-origin"
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    row.remove();
                    updateRowNumbers();
                    alert("Record deleted successfully");
                    
                    // If no rows left, hide save button
                    if (programTableBody.querySelectorAll("tr").length === 0) {
                        saveContainer.classList.add("d-none");
                    }
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                alert("An error occurred while deleting the record");
            });
        }
    }
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
            if (file.name.endsWith('.pdf')) {
                icon = 'fa-file-pdf';
            } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                icon = 'fa-file-word';
            } else if (file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) {
                icon = 'fa-file-excel';
            } else if (file.name.endsWith('.jpg') || file.name.endsWith('.jpeg') || file.name.endsWith('.png')) {
                icon = 'fa-file-image';
            } else if (file.name.endsWith('.csv')) {
                icon = 'fa-file-csv';
            }

            fileItem.innerHTML = `
                <i class="fa ${icon}"></i>
                <span>${file.name}</span>
                <button class="btn btn-danger btn-sm remove-btn" data-index="${index}"><i class="fa fa-trash"></i></button>
            `;

            fileItemsContainer.appendChild(fileItem);
        });

        // Add event listeners to remove buttons
        const removeButtons = fileItemsContainer.querySelectorAll('.remove-btn');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index'); // Use 'this' instead of e.target
                files.splice(index, 1);
                displayFiles();
            });
        });
    }

    // Handle import button click
    importBtn.addEventListener('click', () => {
        if (files.length === 0) {
            alert('No files to import');
            return;
        }
        
        // Disable import button and show loading state
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Importing...';
        
        const formData = new FormData();
        if (files.length > 0) {
            formData.append('file', files[0]); // Send only the first file as 'file'
        }

        console.log("Starting file import...");
        fetch('school_import_graduates.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            console.log("Got response from import:", response);
            return response.json();
        })
        .then(data => {
            console.log("Import response data:", data);
            
            if (data.success) {
                // Close the import modal
                const importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                if (importModal) {
                    importModal.hide();
                }
                
                // Reset the result modal content
                document.getElementById('importSuccessAlert').style.display = 'block';
                document.getElementById('importSkippedAlert').style.display = 'none';
                
                // Display success message
                const successMessage = document.getElementById('importSuccessMessage');
                successMessage.textContent = `${data.imported_count} records were imported successfully.`;
                
                // Handle duplicate graduates
                const duplicatesContainer = document.getElementById('importDuplicatesContainer');
                const duplicatesList = document.getElementById('duplicateProgramsList');
                duplicatesList.innerHTML = ''; // Clear previous entries
                
                if (data.duplicate_graduates && data.duplicate_graduates.length > 0) {
                    // Show the container
                    duplicatesContainer.style.display = 'block';
                    
                    // Add duplicate records to the list
                    data.duplicate_graduates.forEach(duplicate => {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item';
                        listItem.innerHTML = `
                            <strong>Student ID:</strong> ${duplicate.student_id_no}<br>
                            <strong>Name:</strong> ${duplicate.name}<br>
                            <strong>Program:</strong> ${duplicate.program_name}
                        `;
                        duplicatesList.appendChild(listItem);
                    });
                    
                    // Update skipped message
                    const skippedMessage = document.getElementById('importSkippedMessage');
                    skippedMessage.textContent = `${data.duplicate_graduates.length} duplicate records were not imported.`;
                    document.getElementById('importSkippedAlert').style.display = 'block';
                } else {
                    duplicatesContainer.style.display = 'none';
                }
                
                // Handle invalid middle names
                // First, remove any existing invalid middle names container
                const existingInvalidContainer = document.getElementById('invalidMiddleNamesContainer');
                if (existingInvalidContainer) {
                    existingInvalidContainer.remove();
                }
                
                // Check if we have invalid middle names to display
                if (data.invalid_middle_names && data.invalid_middle_names.length > 0) {
                    // Create a new container for invalid middle names
                    const modalBody = document.querySelector('#importResultModal .modal-body');
                    
                    const invalidNamesContainer = document.createElement('div');
                    invalidNamesContainer.id = 'invalidMiddleNamesContainer';
                    
                    invalidNamesContainer.innerHTML = `
                        <h6>Records with Invalid Middle Names:</h6>
                        <ul id="invalidMiddleNamesList" class="list-group mb-3"></ul>
                        <div class="alert alert-warning">
                            <small>Note: Please review these middle names and ensure they are valid.</small>
                        </div>
                    `;
                    
                    modalBody.appendChild(invalidNamesContainer);
                    
                    const invalidList = document.getElementById('invalidMiddleNamesList');
                    
                    // Add invalid middle name records to the list
                    data.invalid_middle_names.forEach(invalid => {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item';
                        listItem.innerHTML = `
                            <strong>Student ID:</strong> ${invalid.student_id_no}<br>
                            <strong>Name:</strong> ${invalid.name}<br>
                            <strong>Middle Name:</strong> <span class="text-danger">${invalid.middle_name}</span>
                        `;
                        invalidList.appendChild(listItem);
                    });
                }
                
                // Show the result modal
                const resultModal = new bootstrap.Modal(document.getElementById('importResultModal'));
                resultModal.show();
                
                files = [];
                displayFiles();
                
                console.log("Reloading graduates data after successful import...");
                // Reload the graduates list
                loadGraduates();
            } else {
                alert('Error importing files: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error during import:', error);
            alert('An error occurred while importing files: ' + error.message);
        })
        .finally(() => {
            // Re-enable import button
            importBtn.disabled = false;
            importBtn.innerHTML = 'Import';
        }); 
    });
});