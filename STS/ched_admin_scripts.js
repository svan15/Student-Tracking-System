document.addEventListener('DOMContentLoaded', function() {
    const editChedModal = document.getElementById('editChedModal');
    if (editChedModal) {
        editChedModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            
            // Set the user ID in the hidden field
            document.getElementById('edit_ched_id').value = userId;
            
            // Clear previous values to avoid displaying old data
            document.getElementById('edit_username').value = '';
            document.getElementById('edit_first_name').value = '';
            document.getElementById('edit_middle_name').value = '';
            document.getElementById('edit_last_name').value = '';
            document.getElementById('edit_email').value = '';
            document.getElementById('edit_phone').value = '';
            document.getElementById('edit_position').value = '';
            document.getElementById('edit_address').value = '';
            
            // Show a loading indicator
            const modalBody = document.querySelector('#editChedModal .modal-body');
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-indicator';
            loadingDiv.className = 'text-center my-3';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            modalBody.insertBefore(loadingDiv, modalBody.firstChild);
            
            // Fetch user data via AJAX
            fetch('ched_admin_get_ched_user.php?id=' + userId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    // Remove loading indicator safely
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (loadingIndicator && loadingIndicator.parentNode) {
                        loadingIndicator.parentNode.removeChild(loadingIndicator);
                    }
                    
                    // Log data for debugging
                    console.log('CHED user data received:', data);
                    
                    // Check if there's an error message in the response
                    if (data.error) {
                        console.error('Error from server:', data.error);
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    // Populate the form fields with null checks
                    const fields = {
                        'edit_username': data.username,
                        'edit_first_name': data.first_name,
                        'edit_middle_name': data.middle_name,
                        'edit_last_name': data.last_name,
                        'edit_email': data.email,
                        'edit_phone': data.phone,
                        'edit_position': data.position,
                        'edit_address': data.address
                    };
                    
                    // Set values safely with checks
                    Object.keys(fields).forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.value = fields[id] || '';
                        }
                    });
                })
                .catch(error => {
                    // Remove loading indicator safely
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (loadingIndicator && loadingIndicator.parentNode) {
                        loadingIndicator.parentNode.removeChild(loadingIndicator);
                    }
                    
                    console.error('Error fetching CHED user data:', error);
                    alert('Failed to load user data: ' + error.message);
                });
        });
    }
    
    // Handle Delete CHED Account Modal
    const deleteChedModal = document.getElementById('deleteChedModal');
    if (deleteChedModal) {
        deleteChedModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            
            // Set the user ID in the hidden field
            document.getElementById('delete_ched_id').value = userId;
            
            // Fetch user data to display name
            fetch('ched_admin_get_ched_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    const nameElement = document.getElementById('delete_ched_name');
                    if (nameElement) {
                        nameElement.textContent = `${data.first_name} ${data.middle_name || ''} ${data.last_name}`;
                    }
                })
                .catch(error => console.error('Error fetching CHED user data:', error));
        });
    }
    
    // Handle Edit HEI Account Modal
    const editHeiModal = document.getElementById('editHeiModal');
    if (editHeiModal) {
        editHeiModal.addEventListener('show.bs.modal', function(event) {
            try {
                const button = event.relatedTarget;
                const heiId = button.getAttribute('data-id');
                
                console.log('Opening edit modal for HEI ID:', heiId);
                
                // Set the HEI ID in the hidden field
                const heiIdField = document.getElementById('edit_hei_id');
                if (heiIdField) {
                    heiIdField.value = heiId;
                } else {
                    console.error('Hidden HEI ID field not found');
                }
                
                // Clear form fields before populating
                const formFields = [
                    'edit_hei_username', 'edit_hei_name', 'edit_hei_type', 
                    'edit_semester_type', 'edit_year_established', 'edit_head', 
                    'edit_designation', 'edit_registrar', 'edit_hei_logo',
                    'edit_street_barangay', 'edit_municipality', 'edit_province', 
                    'edit_zip_code', 'edit_contact_no', 'edit_email_address', 
                    'edit_website', 'edit_calendar', 'edit_land_area', 
                    'edit_x_coordinate', 'edit_y_coordinate'
                ];
                
                formFields.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.value = '';
                    } else {
                        console.warn(`Field ${id} not found in the DOM`);
                    }
                });
                
                // Create and append loading indicator
                const modalBody = document.querySelector('#editHeiModal .modal-body');
                if (modalBody) {
                    // Check if indicator already exists and remove it
                    const existingIndicator = document.getElementById('hei-loading-indicator');
                    if (existingIndicator) {
                        modalBody.removeChild(existingIndicator);
                    }
                    
                    const loadingDiv = document.createElement('div');
                    loadingDiv.id = 'hei-loading-indicator';
                    loadingDiv.className = 'text-center my-3';
                    loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                    modalBody.insertBefore(loadingDiv, modalBody.firstChild);
                } else {
                    console.error('Modal body not found');
                }
                
                // Fetch HEI data
                console.log('Fetching data for HEI ID:', heiId);
                fetch('ched_admin_get_hei_data.php?id=' + heiId)
                    .then(response => {
                        console.log('Fetch response received:', response);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('HEI data received:', data);
                        
                        // Remove loading indicator safely
                        const loadingIndicator = document.getElementById('hei-loading-indicator');
                        if (loadingIndicator && loadingIndicator.parentNode) {
                            loadingIndicator.parentNode.removeChild(loadingIndicator);
                        } else {
                            console.warn('Loading indicator not found or already removed');
                        }
                        
                        // Check for error in response
                        if (data.error) {
                            console.error('Error from server:', data.error);
                            alert('Error: ' + data.error);
                            return;
                        }
                        
                        // Map of field IDs to data properties
                        const fieldMap = {
                            'edit_hei_username': 'username',
                            'edit_hei_name': 'hei_name',
                            'edit_hei_type': 'hei_type',
                            'edit_semester_type': 'semester_type',
                            'edit_year_established': 'year_established',
                            'edit_head': 'head',
                            'edit_designation': 'designation',
                            'edit_registrar': 'registrar',
                            'edit_hei_logo': 'hei_logo',
                            'edit_street_barangay': 'street_barangay',
                            'edit_municipality': 'municipality',
                            'edit_province': 'province',
                            'edit_zip_code': 'zip_code',
                            'edit_contact_no': 'contact_no',
                            'edit_email_address': 'email_address',
                            'edit_website': 'website',
                            'edit_calendar': 'calendar',
                            'edit_land_area': 'land_area',
                            'edit_x_coordinate': 'x_coordinate',
                            'edit_y_coordinate': 'y_coordinate'
                        };
                        
                        // Populate form fields safely
                        Object.keys(fieldMap).forEach(id => {
                            const element = document.getElementById(id);
                            if (element) {
                                const dataProperty = fieldMap[id];
                                element.value = data[dataProperty] || '';
                                console.log(`Set ${id} to "${data[dataProperty] || ''}"`);
                            } else {
                                console.warn(`Field ${id} not found when populating data`);
                            }
                        });
                        
                        // Specific check for logo field
                        const logoField = document.getElementById('edit_hei_logo');
                        if (logoField) {
                            console.log('Logo field found, value set to:', data.hei_logo || '');
                        } else {
                            console.error('Logo field not found in DOM');
                        }
                    })
                    .catch(error => {
                        console.error('Error in fetch operation:', error);
                        
                        // Remove loading indicator safely
                        const loadingIndicator = document.getElementById('hei-loading-indicator');
                        if (loadingIndicator && loadingIndicator.parentNode) {
                            loadingIndicator.parentNode.removeChild(loadingIndicator);
                        }
                        
                        alert('Failed to load HEI data: ' + error.message);
                    });
            } catch (error) {
                console.error('Error in edit HEI modal event handler:', error);
                alert('An unexpected error occurred: ' + error.message);
            }
        });
    }
    
    // Handle Delete HEI Account Modal
    const deleteHeiModal = document.getElementById('deleteHeiModal');
    if (deleteHeiModal) {
        deleteHeiModal.addEventListener('show.bs.modal', function(event) {
            try {
                const button = event.relatedTarget;
                const heiId = button.getAttribute('data-id');
                
                // Set the HEI ID in the hidden field
                const hiddenField = document.getElementById('delete_hei_id');
                if (hiddenField) {
                    hiddenField.value = heiId;
                }
                
                // Fetch HEI data to display name
                fetch('ched_admin_get_hei_data.php?id=' + heiId)
                    .then(response => response.json())
                    .then(data => {
                        const nameElement = document.getElementById('delete_hei_name');
                        if (nameElement && data && data.hei_name) {
                            nameElement.textContent = data.hei_name;
                        }
                    })
                    .catch(error => console.error('Error fetching HEI data:', error));
            } catch (error) {
                console.error('Error in delete HEI modal event handler:', error);
            }
        });
    }
});