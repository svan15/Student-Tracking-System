<?php
include 'connect.php';
session_start();
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "ched") {
    header("Location: index.php");
    exit();
}

// Check if hei_id is passed in the URL and store it in session
if (isset($_GET['hei_id']) && !empty($_GET['hei_id'])) {
    $_SESSION['hei_id'] = $_GET['hei_id']; // Store HEI ID in session
}

// If no HEI is selected, redirect to selection page
if (!isset($_SESSION['hei_id'])) {
    header("Location: ched_HEI.php"); // Redirect user back to selection
    exit();
}

$hei_id = $_SESSION['hei_id'];
$hei_id = isset($_GET['hei_id']) ? intval($_GET['hei_id']) : 0;

// Get HEI name
$hei_name = '';
if ($hei_id > 0) {
    $stmt = $connection->prepare("SELECT hei_name FROM tbl_heis WHERE hei_id = ?");
    $stmt->bind_param("i", $hei_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $hei_name = $row['hei_name'];
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=\, initial-scale=1.0">
    <title>Ched Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-4">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
        <div class="ms-auto d-flex">
            <a href="ched_HEI.php" class="btn btn-outline-light me-2">HEI's</a>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
        
    </header>
    
    <div class="container mt-5 pt-4">
        
        <h2 class="mb-4 text-center">List of Programs</h2>
       
        <?php if (!empty($hei_name)): ?>
            <h4 class="mb-3 text-center"><?= htmlspecialchars($hei_name) ?></h4>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div class="d-flex">
                <input type="text" class="form-control me-2" id="searchInput" placeholder="Search programs...">
                <select class="form-select me-2" id="searchField" style="width: auto;">
                    <option value="all">All Fields</option>
                    <option value="program">Program Name</option>
                    <option value="major">Major</option>
                    <option value="modality">Learning Modality</option>
                    <option value="year">Academic Year</option>
                    <option value="category">Category</option>
                </select>
            </div>
            <div>
                <button class="btn btn-outline-info me-2" onclick="showAddFieldModal()">Add Field</button>
                <button class="btn btn-outline-success me-2" onclick="printTable()">Print</button>
                <button id="leftButton1" onclick="window.location.href='ched_GL.php'" class="btn btn-outline-primary me-2"><i class="fa fa-book"></i> Graduate List</button>
                <button id="leftButton2" onclick="window.location.href='ched_EL.php'" class="btn btn-outline-warning"><i class="fa fa-book"></i> Enrollment List</button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr id="tableHeader">
                        <th style="width: 10%;">No.</th>
                        <th>Program Name (Do not Abbreviate)</th>
                        <th>Major (Do not Abbreviate)</th>
                        <th>Learning Modality</th>
                        <th>Academic Year Started</th>
                        <th>Other Remarks</th>
                        <th>Category</th>
                        <th>Others</th>
                        <th>Serial</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody id="programTableBody">
                    <!-- school program list here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Field Modal -->
    <div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFieldModalLabel">Manage Table Fields</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newFieldName" class="form-label">New Field Name:</label>
                        <input type="text" class="form-control" id="newFieldName" placeholder="Enter field name">
                    </div>
                    <button class="btn btn-success mb-3" onclick="addNewField()">Add New Field</button>
                    
                    <h6 class="mt-4">Edit/Delete Existing Fields</h6>
                    <div id="existingFields" class="list-group">
                        <!-- Existing fields will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="ched_listPrograms.js"></script>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Get HEI ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const heiId = urlParams.get('hei_id');
    
    if (heiId) {
        // Fetch programs for this HEI
        fetch(`ched_get_programs.php?hei_id=${heiId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById('programTableBody');
                tableBody.innerHTML = ''; // Clear existing content
                
                // Populate table with programs
                data.programs.forEach((program, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${program.program_name || ''}</td>
                        <td>${program.major || ''}</td>
                        <td>${program.learning_modality || ''}</td>
                        <td>${program.academic_year_started || ''}</td>
                        <td>${program.other_remarks || ''}</td>
                        <td>${program.category || ''}</td>
                        <td>${program.others || ''}</td>
                        <td>${program.serial || ''}</td>
                        <td>${program.year || ''}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                console.error('Failed to load programs:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>

</html>