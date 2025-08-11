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

    // Get user name
    $hei_name = $userDetails['hei_name'] ?? 'Unknown HEI';
    $head_name = $userDetails['head'] ?? 'Unknown Head';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Add these styles to the existing CSS in your school_dashboard.php file */
        .program-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }

        .program-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .program-card-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .program-card-header:hover {
            background-color: #e9ecef;
        }

        .program-card-body {
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
        .program-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .program-card-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .program-card-body {
            padding: 20px;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light p-4">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
    </header>
    <div class="container mt-5 pt-4">
        <h4 class="mb-2 text-center">Program Management</h4>
        <p class="text-center fs-2"><?= htmlspecialchars($hei_name) ?></p>
        
        <div class="d-flex justify-content-between mb-3">
            <!-- Left-side buttons -->
            <div>
            <button onclick="window.location.href='school_GL.php'" class="btn btn-outline-primary"><i class="fa fa-user-graduate"></i> Graduate List</button>
            <button onclick="window.location.href='school_EL.php'" class="btn btn-outline-warning"><i class="bi bi-person-lines-fill"></i> Enrollment List</button>
            </div>

            <!-- Right-side buttons -->
            <div>
                <button id="addProgramBtn" class="btn btn-outline-primary me-2"><i class="fa fa-plus"></i> Add Program</button>
                <button id="importFileBtn" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa fa-file-import"></i> Import File
                </button>
            </div>
        </div>
        
        <!-- Program list container -->
        <div id="programListContainer" class="mt-4">
            <!-- Programs will be added here -->
        </div>
        
        <!-- Program form (initially hidden) -->
        <div id="programFormContainer" class="mt-4 d-none">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Program</h5>
                    <button id="closeFormBtn" class="btn btn-sm btn-outline-light"><i class="fa fa-times"></i></button>
                </div>
                <div class="card-body">
                    <form id="programForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="programName" class="form-label">Program Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="programName" required placeholder="Do not abbreviate">
                            </div>
                            <div class="col-md-6">
                                <label for="major" class="form-label">Major</label>
                                <input type="text" class="form-control" id="major" placeholder="Do not abbreviate">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="learningModality" class="form-label">Learning Modality <span class="text-danger">*</span></label>
                                <select class="form-select" id="learningModality" required>
                                    <option value="">Select Modality</option>
                                    <option value="Face to Face">Face to Face</option>
                                    <option value="Hybrid">Hybrid</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="academicYearStarted" class="form-label">Academic Year Started <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="academicYearStarted" required placeholder="e.g. 2022-2023">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" required>
                                    <option value="">Select Category</option>
                                    <option value="GP">GP</option>
                                    <option value="GR">GR</option>
                                    <option value="BR">BR</option>
                                    <option value="COPC">COPC</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="others" class="form-label">Others</label>
                                <input type="text" class="form-control" id="others">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="serial" class="form-label">Serial</label>
                                <input type="text" class="form-control" id="serial">
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Year</label>
                                <input type="text" class="form-control" id="year">
                            </div>
                            <div class="col-md-4">
                                <label for="otherRemarks" class="form-label">Other Remarks</label>
                                <input type="text" class="form-control" id="otherRemarks">
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" id="cancelFormBtn" class="btn btn-secondary me-2">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal for Uploading Files -->
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
                        <h6>Duplicate Programs (Not Imported):</h6>
                        <ul id="duplicateProgramsList" class="list-group mb-3">
                            <!-- Duplicate programs will be listed here -->
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="schoolDashboard.js"></script>
</body>
</html>