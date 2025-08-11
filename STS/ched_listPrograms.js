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
                }
            });
        } else {
            // Search specific field
            let fieldIndex = 0;
            switch(searchField) {
                case 'program': fieldIndex = 1; break;
                case 'major': fieldIndex = 2; break;
                case 'modality': fieldIndex = 3; break;
                case 'year': fieldIndex = 4; break;
                case 'category': fieldIndex = 6; break;
            }
            
            if (cells[fieldIndex].textContent.toLowerCase().includes(searchQuery)) {
                foundMatch = true;
            }
        }
        
        row.style.display = foundMatch ? '' : 'none';
    });
}

// Print functionality
function printTable() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>CHED Programs List</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>body { padding: 20px; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2 class="text-center mb-4">CHED Students Tracking System - Programs List</h2>');
    printWindow.document.write(document.querySelector('.table-responsive').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Show Add Field Modal
function showAddFieldModal() {
    // Populate existing fields
    const existingFieldsContainer = document.getElementById('existingFields');
    existingFieldsContainer.innerHTML = '';
    
    const tableHeader = document.getElementById('tableHeader');
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
    const tableHeader = document.getElementById('tableHeader');
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
    const tableHeader = document.getElementById('tableHeader');
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
        const tableHeader = document.getElementById('tableHeader');
        const headers = tableHeader.querySelectorAll('th');
        
        // Don't allow deleting the No. field
        if (index === 0) {
            alert('Cannot delete the No. field');
            return;
        }
        
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
