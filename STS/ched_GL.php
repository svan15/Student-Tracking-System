<?php
include 'connect.php';
session_start();
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "ched") {
    header("Location: index.php");
    exit();
}

// Ensure an HEI is selected
if (!isset($_SESSION['hei_id'])) {
    header("Location: ched_HEI.php"); // Redirect if no HEI is set
    exit();
}
$hei_id = $_SESSION['hei_id']; // Get the stored HEI ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduate List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    </style>
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
    <div class="container-fluid content-container">
        <h2 class="mb-4 text-center">Graduate List</h2>

        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div class="d-flex">
                <input type="text" class="form-control me-2" id="searchInput" placeholder="Search...">
                <select class="form-select me-2" id="searchField" style="width: auto;">
                    <option value="all">All Fields</option>
                    <option value="academicYearStarted">Academic Year Started</option>
                    <option value="semesterStarted">Semester Started</option>
                    <option value="academicYearEnded">Academic Year Ended</option>
                    <option value="semesterEnded">Semester Ended</option>
                    <option value="programName">Program Name</option>
                    <option value="majorName">Major Name</option>
                    <!-- Add more options as needed -->
                </select>
            </div>
            <div>
            <button class="btn btn-outline-info me-2" onclick="showAddFieldModal()">Add Field</button>
            <button id="leftButton2" onclick="window.location.href='ched_dashboard.php'" class="btn btn-outline-warning me-2"><i class="fa fa-list"></i> Programs</button>
                <button class="btn btn-outline-success me-2" onclick="printTable()">Print</button>
                <button id="leftButton1" onclick="window.location.href='ched_EL.php'" class="btn btn-outline-warning me-2"><i class="fa fa-book"></i> Enrollment List</button>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-bordered table-striped student-table">
                <thead class="table-dark">
                    <tr>
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
                    </tr>
                </thead>
                <tbody id="programTableBody">
                    <!-- Add more rows here as needed -->
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
    <script src="ched_GL.js"></script>
</body>
</html>