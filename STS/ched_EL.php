<?php
include 'connect.php';
session_start();
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "ched") {
    header("Location: index.php");
    exit();
}

// Check if HEI ID exists in session
if(!isset($_SESSION['hei_id'])) {
    header("Location: ched_HEI.php");
    exit();
}

// Get HEI name for display
$hei_id = $_SESSION['hei_id'];
$hei_name = "";
$query = "SELECT hei_name FROM tbl_heis WHERE hei_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $hei_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if($row = mysqli_fetch_assoc($result)) {
    $hei_name = $row['hei_name'];
}

// echo "<script>console.log('HEI ID: " . $_SESSION['hei_id'] . "');</script>";

// $test_query = "SELECT COUNT(*) as count FROM Enrollment WHERE hei_id = ?";
// $test_stmt = mysqli_prepare($connection, $test_query);
// mysqli_stmt_bind_param($test_stmt, "i", $hei_id);
// mysqli_stmt_execute($test_stmt);
// $test_result = mysqli_stmt_get_result($test_stmt);
// $count_row = mysqli_fetch_assoc($test_result);
// echo "<script>console.log('Enrollment count: " . $count_row['count'] . "');</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment List - <?php echo htmlspecialchars($hei_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .content-container {
            margin-top: 80px;
        }
        .enrollment-card-header {
            padding: 12px 15px;
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .enrollment-card-header:hover {
            background-color: #e9ecef;
        }
        .enrollment-card-body {
            border-top: 1px solid #dee2e6;
        }
        .badge {
            font-size: 0.85rem;
        }
        .subject-row {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .subject-row:last-child {
            border-bottom: none;
        }
        .toggle-btn {
            background: none;
            border: none;
            color: #0d6efd;
        }
        .toggle-btn:focus {
            outline: none;
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
        <h2 class="mb-4 text-center">Enrollment List - <?php echo htmlspecialchars($hei_name); ?></h2>

        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div class="d-flex">
                <input type="text" class="form-control me-2" id="searchInput" placeholder="Search...">
                <select class="form-select me-2" id="searchField" style="width: auto;">
                    <option value="all">All Fields</option>
                    <option value="hei">Name of HEI</option>
                    <option value="semester">Semester</option>
                    <option value="year">Academic Year</option>
                    <option value="programLevel">Program Level</option>
                    <option value="programName">Program Name</option>
                    <option value="majorName">Major Name</option>
                    <option value="studentId">Student ID</option>
                    <option value="studentName">Student Name</option>
                </select>
            </div>
            <div>
                <button class="btn btn-outline-info me-2">Add Field(not yet working)</button>
                <button id="leftButton2" onclick="window.location.href='ched_dashboard.php'" class="btn btn-outline-warning me-2"><i class="fa fa-list"></i> Programs</button>
                <button class="btn btn-outline-success" onclick="printTable()">Print</button>
                <button id="leftButton1" onclick="window.location.href='ched_GL.php'" class="btn btn-outline-primary ms-2"><i class="fa fa-book"></i> Graduate List</button>
            </div>
        </div>

        <!-- Enrollment List Container -->
        <div id="enrollmentListContainer" class="mb-3">
            <!-- Enrollment cards will be populated dynamically by JavaScript -->
            <div id="noResultsMessage" class="alert alert-info text-center" style="display: none;">
                No enrollment records found matching your search criteria.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="ched_EL.js"></script>
    <script>
        // Add HEI ID to any later fetch requests
        const heiId = <?php echo $hei_id; ?>;
    </script>
</body>
</html>