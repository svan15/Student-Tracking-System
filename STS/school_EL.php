<?php
    include 'connect.php';
    session_start();
    if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
        header("Location: index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch HEI details
    $stmt = $connection->prepare("SELECT hei_name FROM tbl_heis WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userDetails = $result->fetch_assoc();
    $stmt->close();
    $connection->close();

    // Get HEI name
    $hei_name = $userDetails['hei_name'] ?? 'Unknown HEI';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .enrollment-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }

        .enrollment-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .enrollment-card-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .enrollment-card-header:hover {
            background-color: #e9ecef;
        }

        .enrollment-card-body {
            padding: 20px;
            border-top: none;
            background-color: #ffffff;
        }

        .toggle-btn {
            transition: transform 0.2s;
        }

        .toggle-btn.rotated i {
            transform: rotate(180deg);
        }
        
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
        
        .form-label {
            font-weight: 500;
        }
        
        .search-container {
            width: 300px;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .toggle-btn i.fa-chevron-up {
            transform: rotate(180deg);
        }

        .enrollment-card-body.collapse:not(.show) {
            display: none;
        }

        .toggleable-header {
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .toggleable-header:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .enrollment-card-header .header-content {
            pointer-events: none;
        }

        .enrollment-card-header .header-actions .btn {
            pointer-events: auto;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .toggle-btn i.fa-chevron-up {
            transform: rotate(180deg);
        }

        .enrollment-card-body.collapse:not(.show) {
            display: none;
        }

        /*card layout*/
        .enrollment-card-body {
            overflow: visible;  /* Make sure it is not hidden */
            height: auto;  /* Allow the card to expand as needed */
        }

        /* Section Titles */
        .Ph4 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
        }

        .Eh4 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
            color:rgb(0, 255, 60);
        }

        .ESh4 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
            color:rgb(255, 242, 0);
        }

        .card-body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        /* Grouping Sections Side by Side */
        .card-body {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;  /* Add space between sections */
        }

        .personal-info,
        .enrollment-info {
            flex: 1 1 45%;  /* Allow both sections to take 45% of the width, and grow/shrink if needed */
            min-width: 300px;  /* Ensure each section has a minimum width */
        }

        /* Section Grouping */
        .section {
            margin-bottom: 30px;
        }

        /* Styling for each field within the sections */
        .section p {
            font-size: 1rem;
            margin: 5px 0;
        }

        .section p strong {
            font-weight: bold;
            color: #333;
        }

        .subjects-list {
            display: flex;
            flex-direction: row;
            gap: 10px;
        }

        .subject-row {
            padding: 10px;
            border-radius: 5px;
            min-width: 120px;
            flex-shrink: 0;
        }

        /* Responsive Design for smaller screens */
        @media (max-width: 768px) {
            .personal-info,
            .enrollment-info {
                flex: 1 1 100%;  /* Stack sections on top of each other on smaller screens */
            }

            .section p {
                font-size: 0.9rem;
            }
        }


    </style>
</head>
<body class="p-4">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
    </header>
    <div class="container mt-5 pt-4">
        <h4 class="mb-2 text-center">Enrollment List Management</h4>
        <p class="text-center fs-2"><?= htmlspecialchars($hei_name) ?></p>
        
        <div class="d-flex justify-content-between mb-3">
            <!-- Left-side buttons -->
            <div>
                <button onclick="window.location.href='school_dashboard.php'" class="btn btn-outline-info me-2"><i class="fa fa-university"></i> Programs</button>
                <button onclick="window.location.href='school_GL.php'" class="btn btn-outline-warning"><i class="fa fa-user-graduate"></i> Graduate List</button>
            </div>

            <!-- Search bar in the middle -->
            <div class="search-container">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search enrollments...">
                </div>
            </div>

            <!-- Right-side buttons -->
            <div>
                <button id="addEnrollmentBtn" class="btn btn-outline-primary me-2"><i class="fa fa-plus"></i> Add Enrollment</button>
                <button id="importFileBtn" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa fa-file-import"></i> Import File
                </button>
            </div>
        </div>
        
        <!-- Enrollment list container -->
        <div id="enrollmentListContainer" class="mt-4">
            <!-- Enrollment records will be added dynamically here -->
        </div>
        
        <!-- Enrollment Form (initially hidden) -->
        <div id="enrollmentFormContainer" class="mt-4 d-none">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Enrollment Record</h5>
                    <button id="closeFormBtn" class="btn btn-sm btn-outline-light"><i class="fa fa-times"></i></button>
                </div>
                <div class="card-body">
                    <form id="enrollmentForm">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="First Semester">First Semester</option>
                                    <option value="Second Semester">Second Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="academicYear" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="academicYear" required placeholder="e.g. 2022-2023">
                            </div>
                            <div class="col-md-4">
                                <label for="programLevel" class="form-label">Program Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="programLevel" required>
                                    <option value="">Select Program Level</option>
                                    <option value="Undergraduate">Undergraduate</option>
                                    <option value="Graduate">Graduate</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="programName" class="form-label">Program Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="programName" required placeholder="Do not abbreviate">
                            </div>
                            <div class="col-md-6">
                                <label for="majorName" class="form-label">Major Name</label>
                                <input type="text" class="form-control" id="majorName" placeholder="Do not abbreviate">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="studentIdNo" class="form-label">Student ID No. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentIdNo" required>
                            </div>
                            <div class="col-md-4">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" required>
                            </div>
                            <div class="col-md-4">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="middleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middleName" placeholder="Optional">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="yearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="yearLevel" required>
                                    <option value="">Select Year Level</option>
                                    <option value="1 Old">1st Year (Old Curriculum)</option>
                                    <option value="1 New">1st Year (New Curriculum)</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                    <option value="6">6th Year</option>
                                    <option value="7">7th Year</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="suffix" class="form-label">Suffix</label>
                                <input type="text" class="form-control" id="suffix" placeholder="Jr., Sr., III, etc.">
                            </div>
                            <div class="col-md-4">
                                <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                                <select class="form-select" id="sex" required>
                                    <option value="">Select Sex</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="sector" class="form-label">Sector</label>
                                <select class="form-select" id="sector">
                                    <option value="">Select Sector (Optional)</option>
                                    <option value="Indigenous People">Indigenous People</option>
                                    <option value="Solo Parent">Solo Parent</option>
                                    <option value="PWD">PWD</option>
                                    <option value="Senior Citizen">Senior Citizen</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sectorDetails" class="form-label">Sector Details</label>
                                <input type="text" class="form-control" id="sectorDetails" placeholder="Details for Indigenous People or PWD">
                            </div>
                            <div class="col-md-4">
                                <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nationality" required placeholder="e.g., Filipino">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="studentType" class="form-label">Student Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="studentType" required>
                                    <option value="">Select Student Type</option>
                                    <option value="Foreign">Foreign</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Exchange">Exchange</option>
                                    <option value="LEP">LEP</option>
                                    <option value="ETEEAP">ETEEAP</option>
                                    <option value="DE">DE</option>
                                    <option value="TNE">TNE</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="studentTypeOthers" class="form-label">If Others, Please Specify</label>
                                <input type="text" class="form-control" id="studentTypeOthers" placeholder="Specify student type if 'Others' is selected">
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Enrolled Subjects</h5>
                        <div id="subjectsContainer">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Subject Code">
                                </div>
                                <div class="col-md-6">
                                    <input type="number" class="form-control" placeholder="Units" min="0" step="0.5">
                                    <button type="button" id="addSubjectRowBtn" class="btn btn-sm btn-success mt-2">Add Subject</button>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" id="cancelFormBtn" class="btn btn-secondary me-2">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Enrollment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Enrollment Files</h5>
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
    </div>

    <div id="noResultsMessage" style="display:none;" class="alert alert-info text-center">
        No enrollment records match your search.
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="school_EL.js"></script>
</body>
</html>