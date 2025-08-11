<?php
    include 'connect.php';
    session_start();  

    if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school" || !isset($_SESSION["hei_id"])) {
        session_destroy();
        header("Location: index.php?error=session_invalid");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduate List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .drag-area {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: border 0.3s;
        }
        .drag-area.active {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .table-container {
            overflow-x: auto;
        }
        th, td {
            white-space: nowrap;
            padding: 8px;
            font-size: 14px;
        }
        .student-table th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: white;
            z-index: 1;
        }
        .content-container {
            margin-top: 80px;
        }
        .search-container {
            width: 300px;
        }

        /* Row selection */
        /* Custom selection styling */
        .table-row {
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .table-row:hover {
            background-color: rgba(0,0,0,0.075);
        }
        .table-row.selected {
            background-color: #0d6efd; /* Bootstrap primary color */
            color: white;
        }

    </style>

</head>
<body class="bg-light p-4">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
        <div class="ms-auto d-flex">
            <a href="school_dashboard.php" class="btn btn-outline-light me-2">Back</a>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
        
    </header>
    <div class="container-fluid content-container">
        <h2 class="mb-4 text-center">Graduate List</h2>

        <div class="d-flex justify-content-between mb-3">
            <div>
                <button onclick="window.location.href='school_dashboard.php'" class="btn btn-outline-info me-2"><i class="fa fa-university"></i> Programs</button>
                <button onclick="window.location.href='school_EL.php'" class="btn btn-outline-warning"><i class="bi bi-person-lines-fill"></i> Enrollment List</button>
            </div>

            <!-- Search bar on the left -->
            <div class="search-container">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search programs...">
                </div>
            </div>
            
            <!-- Buttons on the right -->
            <div>
                <button id="addProgramBtn" class="btn btn-outline-success me-2"><i class="fa fa-plus"></i> Add Record</button>
                <button id="importFileBtn" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa fa-file-import"></i> Import File
                </button>
            </div>
        </div>

        <div class="table-container">
    <table class="table table-bordered table-hover table-sm student-table align-middle">
        <thead class="table-dark">
            <tr class="table-row" data-bs-toggle="row-select">
                <th>No.</th>
                <th>Academic Year Started</th>
                <th>Semester Started</th>
                <th>Academic Year Ended</th>
                <th>Semester Ended</th>
                <th>Program Name</th>
                <th>Major Name</th>
                <th>Student ID No.</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Suffix</th>
                <th>Date of Birth</th>
                <th>Sex</th>
                <th>Student Type</th>
                <th>If Others</th>
                <th>Special Order No. Issued</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="programTableBody">
            <!-- Add more rows here as needed -->
        </tbody>
    </table>
</div>
        <div id="saveContainer" class="text-end mt-3 d-none">
            <button id="saveBtn" class="btn btn-primary">Save</button>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dragArea" class="drag-area">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                        <h5>Drag & Drop files here</h5>
                        <p>or</p>
                        <button class="btn btn-primary" id="browseBtn">Browse Files</button>
                        <input type="file" id="fileInput" multiple style="display: none;">
                    </div>
                    <div id="fileList" class="mt-4">
                        <h6 class="mb-3">Selected Files:</h6>
                        <div id="fileItemsContainer">
                            <!-- File items will be added here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="importBtn">Import</button>
                </div>
            </div>
        </div>
    </div>

   <!-- Import Result Modal (for displaying results after import) -->
    <div class="modal fade" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importResultModalLabel">Import Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="importSuccessAlert" class="alert alert-success mb-3">
                        <span id="importSuccessMessage"></span>
                    </div>
                    <div id="importSkippedAlert" class="alert alert-warning mb-3" style="display: none;">
                        <span id="importSkippedMessage"></span>
                    </div>
                    <div id="importDuplicatesContainer" style="display: none;">
                        <h6>Duplicate Records (Not Imported):</h6>
                        <ul id="duplicateProgramsList" class="list-group mb-3">
                            <!-- Duplicate graduates will be listed here -->
                        </ul>
                    </div>
                    <div id="invalidMiddleNamesContainer" style="display: none;">
                        <h6>Records with Invalid Middle Names:</h6>
                        <ul id="invalidMiddleNamesList" class="list-group mb-3">
                            <!-- Invalid middle names will be listed here -->
                        </ul>
                        <div class="alert alert-warning">
                            <small>Note: Please review these middle names and ensure they are valid.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="school_GL.js"></script>

    <script>
    function adjustInputWidths() {
        const inputs = document.querySelectorAll('.form-control, .form-select');
        
        inputs.forEach(input => {
            // Reset width
            input.style.width = 'auto';
            
            // Measure content width
            const tempSpan = document.createElement('span');
            tempSpan.style.visibility = 'hidden';
            tempSpan.style.position = 'absolute';
            tempSpan.style.whiteSpace = 'nowrap';
            tempSpan.style.font = window.getComputedStyle(input).font;
            
            // Use value or selected text
            tempSpan.textContent = input.tagName.toLowerCase() === 'select' 
                ? input.options[input.selectedIndex].text 
                : input.value;
            
            document.body.appendChild(tempSpan);
            
            // Calculate and set width
            const contentWidth = tempSpan.offsetWidth;
            const paddingWidth = parseInt(window.getComputedStyle(input).paddingLeft) + 
                                  parseInt(window.getComputedStyle(input).paddingRight);
            
            // Set width with some extra padding
            input.style.width = `${Math.min(contentWidth + paddingWidth + 20, 400)}px`;
            
            // Clean up
            document.body.removeChild(tempSpan);
        });
    }

    // Run on page load and after data is populated
    document.addEventListener('DOMContentLoaded', () => {
        // Delay to ensure table is populated
        setTimeout(adjustInputWidths, 500);
    });

    // Optional: Re-adjust on window resize
    window.addEventListener('resize', adjustInputWidths);

    // Optional JavaScript for row selection
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.table-row');
        
        rows.forEach(row => {
            row.addEventListener('click', function() {
                // Remove selected class from all rows
                rows.forEach(r => r.classList.remove('selected'));
                
                // Add selected class to clicked row
                this.classList.add('selected');
            });
        });
    });
</script>
</body>
</html>