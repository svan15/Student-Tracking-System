
window.onerror = function(message, source, lineno, colno, error) {
    console.error(`Error: ${message} at ${source}:${lineno}:${colno}`);
    alert(`A JavaScript error occurred: ${message}. Check console for details.`);
    return true;
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');

    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const searchField = document.getElementById('searchField');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            performSearch();
        });
    } else {
        console.error('Search input element not found');
    }
    
    if (searchField) {
        searchField.addEventListener('change', function() {
            performSearch();
        });
    } else {
        console.error('Search field element not found');
    }
    
    function performSearch() {
        if (!searchInput || !searchField) return;
        
        const searchTerm = searchInput.value.toLowerCase().trim();
        const searchFieldValue = searchField.value;
        
        const enrollmentCards = document.querySelectorAll('.enrollment-card');
        console.log(`Found ${enrollmentCards.length} enrollment cards to search through`);
        
        let matchedCards = 0;
        enrollmentCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            let isMatch = false;
            
            if (searchFieldValue === 'all') {
                isMatch = cardText.includes(searchTerm);
            } else {
                // Search specific data attributes based on the selected field
                const fieldContent = card.getAttribute('data-' + searchFieldValue);
                if (fieldContent) {
                    isMatch = fieldContent.toLowerCase().includes(searchTerm);
                }
            }
            
            card.style.display = isMatch ? 'block' : 'none';
            
            if (isMatch) {
                matchedCards++;
            }
        });
        
        // Show/hide "no results" message
        const noResultsMessage = document.getElementById('noResultsMessage');
        if (noResultsMessage) {
            noResultsMessage.style.display = matchedCards === 0 ? 'block' : 'none';
        }
        
        console.log(`Search complete. Matched ${matchedCards} cards.`);
    }

    // Print functionality
    window.printTable = function() {
        console.log('Print function called');
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Enrollment List</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body { padding: 20px; } .card { break-inside: avoid; margin-bottom: 15px; border: 1px solid #ddd; } .enrollment-card-body { padding: 15px; } .subject-container { margin-top: 10px; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 class="text-center mb-4">CHED Students Tracking System - Enrollment List</h2>');
        
        // Get only visible cards
        const visibleCards = Array.from(document.querySelectorAll('.enrollment-card'))
            .filter(card => card.style.display !== 'none');
        
        // Create a printable version of each visible card
        const printContainer = document.createElement('div');
        printContainer.className = 'container';
        
        visibleCards.forEach(card => {
            const printCard = card.cloneNode(true);
            // Make sure all card bodies are expanded for printing
            const cardBody = printCard.querySelector('.enrollment-card-body');
            if (cardBody) {
                cardBody.classList.remove('collapse');
                cardBody.style.display = 'block';
            }
            // Remove toggle buttons and other interactive elements
            const toggleBtns = printCard.querySelectorAll('.toggle-btn');
            toggleBtns.forEach(btn => btn.remove());
            
            printContainer.appendChild(printCard);
        });
        
        printWindow.document.write(printContainer.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    };

    // Load and display enrollments
    function loadEnrollments() {
        console.log('Starting to load enrollments...');
        const enrollmentListContainer = document.getElementById('enrollmentListContainer');
        
        if (!enrollmentListContainer) {
            console.error('Error: enrollmentListContainer not found in the DOM');
            return;
        }
        
        enrollmentListContainer.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading enrollment data...</div>';
        
        fetch('ched_get_enrollments.php')
            .then(response => {
                console.log('Fetch response received:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data received:', typeof data, Array.isArray(data) ? data.length : 'not an array');
                
                // Clear any existing content including potential test cards
                enrollmentListContainer.innerHTML = '';
                
                if (!Array.isArray(data)) {
                    console.error("Invalid response format:", data);
                    enrollmentListContainer.innerHTML = '<div class="alert alert-danger">Error: Received invalid data format from server.</div>';
                    return;
                }
                
                if (data.length === 0) {
                    console.log('No enrollment data found');
                    enrollmentListContainer.innerHTML = '<div class="alert alert-info">No enrollment records found for this HEI.</div>';
                    return;
                }
                
                console.log(`Processing ${data.length} enrollment records...`);
                
                // Process and display ALL enrollments...
                data.forEach((enrollment, index) => {
                    try {
                        console.log(`Creating card for enrollment #${index + 1} with ID: ${enrollment.student_id}`);
                        // Skip validation for now to troubleshoot
                        const card = createEnrollmentCard(enrollment);
                        enrollmentListContainer.appendChild(card);
                        console.log(`Successfully added card #${index + 1} to container`);
                    } catch (error) {
                        console.error(`Error creating card for enrollment #${index + 1}:`, error);
                        console.log('Problematic enrollment data:', enrollment);
                    }
                });
                
                console.log('All enrollment cards created successfully');
                console.log('Final number of cards in container:', enrollmentListContainer.querySelectorAll('.enrollment-card').length);
            })
            .catch(error => {
                console.error('Error loading enrollments:', error);
                enrollmentListContainer.innerHTML = `<div class="alert alert-danger">An error occurred while loading enrollment data: ${error.message}</div>`;
            });
    }
    
    function validateEnrollmentData(enrollment) {
        console.log('Validating enrollment data:', enrollment);
        // Just log warnings but don't filter out the enrollment
        const requiredFields = ['student_id', 'first_name', 'last_name', 'semester', 'academic_year'];
        
        for (const field of requiredFields) {
            if (!enrollment[field]) {
                console.warn(`Missing field in enrollment data: ${field}`);
            }
        }
        
        return enrollment; // Always return the enrollment regardless
    }

    function createEnrollmentCard(enrollment) {
        console.log('Creating card for student:', enrollment.student_id);
        
        // Create data attributes for searchable fields
        const dataAttributes = {
            'data-hei': enrollment.hei_name || '',
            'data-semester': enrollment.semester || '',
            'data-year': enrollment.academic_year || '',
            'data-programLevel': enrollment.program_level || '',
            'data-programName': enrollment.program_name || '',
            'data-majorName': enrollment.major_name || '',
            'data-studentId': enrollment.student_id_no || '',
            'data-studentName': `${enrollment.first_name || ''} ${enrollment.middle_name || ''} ${enrollment.last_name || ''}`
        };
        
        // Create the card element
        const card = document.createElement('div');
        card.className = 'enrollment-card card mb-3';
        for (const [key, value] of Object.entries(dataAttributes)) {
            card.setAttribute(key, value);
        }
        
        // Safely get enrollment properties with fallbacks
        const studentId = enrollment.student_id_no || 'Unknown ID';
        const firstName = enrollment.first_name || '';
        const middleName = enrollment.middle_name || '';
        const lastName = enrollment.last_name || '';
        const semester = enrollment.semester || '';
        const academicYear = enrollment.academic_year || '';
        
        // Create card header with student information
        const cardHeader = document.createElement('div');
        cardHeader.className = 'enrollment-card-header toggleable-header d-flex justify-content-between align-items-center';
        cardHeader.innerHTML = `
            <div class="header-content flex-grow-1">
                <p class="mb-0">Student ID: ${studentId} | <strong>${firstName} ${middleName ? middleName + ' ' : ''}${lastName}</strong></p>
            </div>
            <div class="header-actions d-flex align-items-center">
                <span class="badge bg-primary me-2">${semester} Semester ${academicYear}</span>
                <button class="btn btn-sm btn-link toggle-btn ms-2">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        `;
        
        // Create card body with enrollment details
        const cardBody = document.createElement('div');
        cardBody.className = 'enrollment-card-body collapse';
        
        // Format the subject data
        let subjectsHtml = '';
        if (enrollment.subjects && Array.isArray(enrollment.subjects) && enrollment.subjects.length > 0) {
            subjectsHtml = `
                <div class="subject-container mt-3">
                    <h6>Enrolled Subjects</h6>
                    <div class="row fw-bold mb-2">
                        <div class="col-md-6">Subject Code</div>
                        <div class="col-md-6">Units</div>
                    </div>
                    ${enrollment.subjects.map(subject => `
                        <div class="row subject-row">
                            <div class="col-md-6">${subject.subject_code || 'N/A'}</div>
                            <div class="col-md-6">${subject.units || 'N/A'}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        // Populate card body with enrollment details
        cardBody.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Program:</strong> ${enrollment.program_name || 'N/A'}</p>
                        <p><strong>Major:</strong> ${enrollment.major_name || 'N/A'}</p>
                        <p><strong>Program Level:</strong> ${enrollment.program_level || 'N/A'}</p>
                        <p><strong>Year Level:</strong> ${enrollment.year_level || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Student:</strong> ${firstName} ${middleName ? middleName + ' ' : ''}${lastName} ${enrollment.suffix || ''}</p>
                        <p><strong>Sex:</strong> ${enrollment.sex === 'M' ? 'Male' : 'Female'}</p>
                        <p><strong>Nationality:</strong> ${enrollment.nationality || 'N/A'}</p>
                        <p><strong>Student Type:</strong> ${enrollment.student_type || 'N/A'}</p>
                    </div>
                </div>
                ${subjectsHtml}
            </div>
        `;
        
        // Append header and body to card
        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        
        // Add toggle functionality
        const toggleBtn = cardHeader.querySelector('.toggle-btn');
        const toggleIcon = toggleBtn.querySelector('i');
        
        function toggleCardBody() {
            $(cardBody).collapse('toggle');
            toggleIcon.classList.toggle('fa-chevron-down');
            toggleIcon.classList.toggle('fa-chevron-up');
        }
        
        cardHeader.addEventListener('click', function(e) {
            // Prevent toggle if clicking on buttons or links
            if (e.target.closest('.btn:not(.toggle-btn), a')) return;
            toggleCardBody();
        });
        
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleCardBody();
        });
        
        return card;
    }
    
    // Initial load of enrollments
    console.log('Initializing enrollment list...');
    loadEnrollments();
});

console.log('Checking Bootstrap initialization...');
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap JS is not loaded properly!');
} else {
    console.log('Bootstrap JS is loaded correctly');
}