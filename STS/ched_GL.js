
//new
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    performSearch();
});

document.getElementById('searchField').addEventListener('change', function() {
    performSearch();
});

function performSearch() {
    const searchField = document.getElementById('searchField').value;
    const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();

    const rows = document.getElementById('programTableBody').querySelectorAll('tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let foundMatch = false;

        if (searchField === 'all') {
            // Search all fields
            Array.from(cells).forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchQuery)) {
                    foundMatch = true;
                    return; // Exit inner loop if a match is found
                }
            });
        } else {
            // Search specific field
            let fieldIndex = -1;
            switch (searchField) {
                case 'academicYearStarted': fieldIndex = 0; break;
                case 'semesterStarted': fieldIndex = 1; break;
                case 'academicYearEnded': fieldIndex = 2; break;
                case 'semesterEnded': fieldIndex = 3; break;
                case 'programName': fieldIndex = 4; break;
                case 'majorName': fieldIndex = 5; break;
                // Add cases for other searchable fields
            }

            if (fieldIndex !== -1 && cells[fieldIndex].textContent.toLowerCase().includes(searchQuery)) {
                foundMatch = true;
            }
        }

        row.style.display = foundMatch ? '' : 'none';
    });
}

// Print functionality
function printTable() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Graduate List</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>body { padding: 20px; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2 class="text-center mb-4">CHED Students Tracking System - Graduate List</h2>');
    printWindow.document.write(document.querySelector('.table-container').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Show Add Field Modal
function showAddFieldModal() {
    // Populate existing fields
    const existingFieldsContainer = document.getElementById('existingFields');
    existingFieldsContainer.innerHTML = '';

    const tableHeader = document.querySelector('.student-table thead tr'); // Select the correct thead
    const headers = tableHeader.querySelectorAll('th');

    headers.forEach((header, index) => {
        const fieldItem = document.createElement('div');
        fieldItem.className = 'list-group-item d-flex justify-content-between align-items-center';
        fieldItem.innerHTML = `
            <span>${header.textContent}</span>
            <div>
                <button class="btn btn-sm btn-primary me-2" onclick="editField(${index})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteField(${index})">Delete</button>
            </div>
        `;
        existingFieldsContainer.appendChild(fieldItem);
    });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addFieldModal'));
    modal.show();
}

// Add New Field
function addNewField() {
    const newFieldName = document.getElementById('newFieldName').value.trim();
    if (!newFieldName) {
        alert('Please enter a field name');
        return;
    }

    // Add to table header
    const tableHeader = document.querySelector('.student-table thead tr'); // Select the correct thead
    const newHeaderCell = document.createElement('th');
    newHeaderCell.textContent = newFieldName;
    tableHeader.appendChild(newHeaderCell);

    // Add empty cell to each row
    const rows = document.getElementById('programTableBody').querySelectorAll('tr');
    rows.forEach(row => {
        const newCell = document.createElement('td');
        newCell.textContent = '';
        row.appendChild(newCell);
    });

    // Reset and close modal
    document.getElementById('newFieldName').value = '';
    alert('New field added successfully');

    // Refresh the modal content
    showAddFieldModal();
}

// Edit Field
function editField(index) {
    const tableHeader = document.querySelector('.student-table thead tr'); // Select the correct thead
    const headers = tableHeader.querySelectorAll('th');

    const newName = prompt('Enter new name for field:', headers[index].textContent);
    if (newName !== null && newName.trim() !== '') {
        headers[index].textContent = newName.trim();
        alert('Field updated successfully');
        showAddFieldModal();
    }
}

// Delete Field
function deleteField(index) {
    if (confirm('Are you sure you want to delete this field? This action cannot be undone.')) {
        const tableHeader = document.querySelector('.student-table thead tr'); // Select the correct thead
        const headers = tableHeader.querySelectorAll('th');

        // Remove header
        headers[index].remove();

        // Remove corresponding cell from each row
        const rows = document.getElementById('programTableBody').querySelectorAll('tr');
        rows.forEach(row => {
            row.querySelectorAll('td')[index].remove();
        });

        alert('Field deleted successfully');
        showAddFieldModal();
    }
}

function loadGraduatesForHEI() {
    fetch('ched_get_graduates.php')
    .then(response => response.json())
    .then(data => {
        console.log("HEI Data Response:", data); // Debugging step

        const tableBody = document.getElementById('programTableBody');
        tableBody.innerHTML = '';

        if (!data.success) {
            if (data.message === 'No HEI selected') {
                tableBody.innerHTML = `<tr><td colspan="16" class="text-center">Please select an institution first</td></tr>`;
                return;
            }
        }

        if (data.graduates.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="16" class="text-center">No graduates found for this institution</td></tr>`;
            return;
        }

        // Populate table with graduates data
        data.graduates.forEach(graduate => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${graduate.academic_year_started || ''}</td>
                <td>${graduate.semester_started || ''}</td>
                <td>${graduate.academic_year_ended || ''}</td>
                <td>${graduate.semester_ended || ''}</td>
                <td>${graduate.program_name || ''}</td>
                <td>${graduate.major_name || ''}</td>
                <td>${graduate.student_id_no || ''}</td>
                <td>${graduate.last_name || ''}</td>
                <td>${graduate.first_name || ''}</td>
                <td>${graduate.middle_name || ''}</td>
                <td>${graduate.suffix || ''}</td>
                <td>${formatDate(graduate.date_of_birth)}</td>
                <td>${graduate.sex || ''}</td>
                <td>${graduate.student_type || ''}</td>
                <td>${graduate.if_others || ''}</td>
                <td>${graduate.special_order_no || ''}</td>
            `;
            tableBody.appendChild(row);
        });
    })
    .catch(error => console.error("Fetch Error:", error));
}

// Ensure graduates load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGraduatesForHEI();
});

// Format date function
function formatDate(dateString) {
    if (!dateString) return '';
    
    // Assuming dateString is in YYYY-MM-DD format from the database
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid
    
    return date.toISOString().split('T')[0]; // Format as YYYY-MM-DD for display
}

// Call the function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadGraduatesForHEI();
});

// Format date function
function formatDate(dateString) {
    if (!dateString) return '';
    
    // Assuming dateString is in YYYY-MM-DD format from the database
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid
    
    return date.toISOString().split('T')[0]; // Format as YYYY-MM-DD for display
}

// Call the function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadGraduatesForHEI();
});