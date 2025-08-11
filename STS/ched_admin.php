<?php
include 'connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ched Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            padding-top: 80px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .action-btn {
            margin-right: 5px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>

<body class="bg-light">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
        <div class="ms-auto d-flex">
            <a href="logout.php" class="btn btn-outline-light me-2">Logout</a>
        </div>
    </header>
    
    <div class="acc_container mt-5 pt-4 container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-3">Account Management</h2>
                
                <!-- Nav tabs -->
                <ul class="nav nav-pills mb-3" id="accountTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ched-tab" data-bs-toggle="tab" data-bs-target="#ched-accounts" type="button" role="tab" aria-controls="ched-accounts" aria-selected="true">CHED Accounts</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="hei-tab" data-bs-toggle="tab" data-bs-target="#hei-accounts" type="button" role="tab" aria-controls="hei-accounts" aria-selected="false">HEI Accounts</button>
                    </li>
                </ul>
                
                <!-- Tab content -->
                <div class="tab-content" id="accountTabsContent">
                    <!-- CHED Accounts Tab -->
                    <div class="tab-pane fade show active" id="ched-accounts" role="tabpanel" aria-labelledby="ched-tab">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">CHED Personnel Accounts</h5>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addChedModal">
                                    <i class="bi bi-plus-circle"></i> Add CHED Account
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- PHP will populate this -->
                                            <?php
                                            // Sample code to fetch CHED accounts
                                            $sql = "SELECT u.id, u.username, c.first_name, c.middle_name, c.last_name, c.position, c.email, c.phone 
                                                    FROM User u 
                                                    JOIN ChedDetails c ON u.id = c.user_id 
                                                    WHERE u.user_type = 'ched'";
                                            $result = $connection->query($sql);
                                            
                                            if ($result->num_rows > 0) {
                                                while($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row["id"] . "</td>";
                                                    echo "<td>" . $row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"] . "</td>";
                                                    echo "<td>" . $row["position"] . "</td>";
                                                    echo "<td>" . $row["email"] . "</td>";
                                                    echo "<td>" . $row["phone"] . "</td>";
                                                    echo "<td>
                                                        <button class='btn btn-sm btn-primary action-btn' data-bs-toggle='modal' data-bs-target='#editChedModal' data-id='" . $row["id"] . "'>
                                                            <i class='bi bi-pencil'></i> Edit
                                                        </button>
                                                        <button class='btn btn-sm btn-danger action-btn' data-bs-toggle='modal' data-bs-target='#deleteChedModal' data-id='" . $row["id"] . "'>
                                                            <i class='bi bi-trash'></i> Delete
                                                        </button>
                                                    </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center'>No CHED accounts found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- HEI Accounts Tab -->
                    <div class="tab-pane fade" id="hei-accounts" role="tabpanel" aria-labelledby="hei-tab">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Higher Education Institution Accounts</h5>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addHeiModal">
                                    <i class="bi bi-plus-circle"></i> Add HEI Account
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>HEI Name</th>
                                                <th>Type</th>
                                                <th>Location</th>
                                                <th>Contact</th>
                                                <th>Head</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- PHP will populate this -->
                                            <?php
                                            // Sample code to fetch HEI accounts
                                            $sql = "SELECT h.hei_id, h.hei_name, h.hei_type, h.municipality, h.province, 
                                                    h.contact_no, h.email_address, h.head 
                                                    FROM tbl_heis h 
                                                    JOIN User u ON h.user_id = u.id 
                                                    WHERE u.user_type = 'school'";
                                            $result = $connection->query($sql);
                                            
                                            if ($result->num_rows > 0) {
                                                while($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row["hei_id"] . "</td>";
                                                    echo "<td>" . $row["hei_name"] . "</td>";
                                                    echo "<td>" . $row["hei_type"] . "</td>";
                                                    echo "<td>" . $row["municipality"] . ", " . $row["province"] . "</td>";
                                                    echo "<td>" . $row["contact_no"] . "<br>" . $row["email_address"] . "</td>";
                                                    echo "<td>" . $row["head"] . "</td>";
                                                    echo "<td>
                                                        <button class='btn btn-sm btn-primary action-btn' data-bs-toggle='modal' data-bs-target='#editHeiModal' data-id='" . $row["hei_id"] . "'>
                                                            <i class='bi bi-pencil'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-danger action-btn' data-bs-toggle='modal' data-bs-target='#deleteHeiModal' data-id='" . $row["hei_id"] . "'>
                                                            <i class='bi bi-trash'></i>
                                                        </button>
                                                    </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>No HEI accounts found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add CHED Account Modal -->
    <div class="modal fade" id="addChedModal" tabindex="-1" aria-labelledby="addChedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addChedModalLabel">Add CHED Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_add_ched_account.php" method="post">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit CHED Account Modal -->
    <div class="modal fade" id="editChedModal" tabindex="-1" aria-labelledby="editChedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editChedModalLabel">Edit CHED Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_update_ched_account.php" method="post">
                    <input type="hidden" id="edit_ched_id" name="user_id">
                    <div class="modal-body">
                        <!-- Form fields similar to the add modal but will be populated with existing data via JavaScript -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="edit_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="edit_position" name="position" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_address" class="form-label">Address</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete CHED Account Modal -->
    <div class="modal fade" id="deleteChedModal" tabindex="-1" aria-labelledby="deleteChedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteChedModalLabel">Delete CHED Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_delete_ched_account.php" method="post">
                    <input type="hidden" id="delete_ched_id" name="user_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this CHED account? This action cannot be undone.</p>
                        <p class="fw-bold">Account: <span id="delete_ched_name"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add HEI Account Modal -->
    <div class="modal fade" id="addHeiModal" tabindex="-1" aria-labelledby="addHeiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addHeiModalLabel">Add HEI Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_add_hei_account.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-3" id="heiTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-info" type="button" role="tab">Login Information</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">Basic Information</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location-info" type="button" role="tab">Location & Contact</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other-info" type="button" role="tab">Other Details</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="heiTabContent">
                            <div class="tab-pane fade show active" id="login-info" role="tabpanel" aria-labelledby="login-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="hei_username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="hei_username" name="username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hei_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="hei_password" name="password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="basic-info" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="hei_name" class="form-label">HEI Name</label>
                                        <input type="text" class="form-control" id="hei_name" name="hei_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hei_type" class="form-label">HEI Type</label>
                                        <select class="form-select" id="hei_type" name="hei_type" required>
                                            <option value="">Select Type</option>
                                            <option value="Public">Public</option>
                                            <option value="Private">Private</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="semester_type" class="form-label">Semester Type</label>
                                        <select class="form-select" id="semester_type" name="semester_type" required>
                                            <option value="Regular">Regular</option>
                                            <option value="Trimester">Trimester</option>
                                            <option value="Quarterly">Quarterly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year_established" class="form-label">Year Established</label>
                                        <input type="text" class="form-control" id="year_established" name="year_established" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="head" class="form-label">Institution Head</label>
                                        <input type="text" class="form-control" id="head" name="head" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control" id="designation" name="designation" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="registrar" class="form-label">Registrar</label>
                                        <input type="text" class="form-control" id="registrar" name="registrar">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hei_logo" class="form-label">Institution Logo</label>
                                        <input type="file" class="form-control" id="hei_logo" name="hei_logo">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="location-info" role="tabpanel" aria-labelledby="location-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="street_barangay" class="form-label">Street/Barangay</label>
                                        <input type="text" class="form-control" id="street_barangay" name="street_barangay" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="municipality" class="form-label">Municipality/City</label>
                                        <input type="text" class="form-control" id="municipality" name="municipality" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="province" class="form-label">Province</label>
                                        <input type="text" class="form-control" id="province" name="province" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contact_no" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="contact_no" name="contact_no">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email_address" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email_address" name="email_address" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="text" class="form-control" id="website" name="website" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="other-info" role="tabpanel" aria-labelledby="other-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="calendar" class="form-label">Academic Calendar</label>
                                        <input type="text" class="form-control" id="calendar" name="calendar">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="land_area" class="form-label">Land Area (sqm)</label>
                                        <input type="text" class="form-control" id="land_area" name="land_area" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="x_coordinate" class="form-label">X Coordinate</label>
                                        <input type="text" class="form-control" id="x_coordinate" name="x_coordinate" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="y_coordinate" class="form-label">Y Coordinate</label>
                                        <input type="text" class="form-control" id="y_coordinate" name="y_coordinate" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save HEI Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete HEI Account Modal -->
<div class="modal fade" id="deleteHeiModal" tabindex="-1" aria-labelledby="deleteHeiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteHeiModalLabel">Delete HEI Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="admin_ched_delete_hei_account.php" method="post">
                <input type="hidden" id="delete_hei_id" name="hei_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete this HEI account? This action cannot be undone.</p>
                    <p class="fw-bold">Institution: <span id="delete_hei_name"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
    <!-- Edit HEI Account Modal -->
<div class="modal fade" id="editHeiModal" tabindex="-1" aria-labelledby="editHeiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editHeiModalLabel">Edit HEI Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="admin_ched_update_hei_account.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="edit_hei_id" name="hei_id">
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="editHeiTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="edit-login-tab" data-bs-toggle="tab" data-bs-target="#edit-login-info" type="button" role="tab">Login Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-basic-tab" data-bs-toggle="tab" data-bs-target="#edit-basic-info" type="button" role="tab">Basic Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-location-tab" data-bs-toggle="tab" data-bs-target="#edit-location-info" type="button" role="tab">Location & Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-other-tab" data-bs-toggle="tab" data-bs-target="#edit-other-info" type="button" role="tab">Other Details</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="editHeiTabContent">
                        <div class="tab-pane fade show active" id="edit-login-info" role="tabpanel" aria-labelledby="edit-login-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_hei_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="edit_hei_username" name="username" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_hei_password" class="form-label">Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control" id="edit_hei_password" name="password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="edit-basic-info" role="tabpanel" aria-labelledby="edit-basic-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_hei_name" class="form-label">HEI Name</label>
                                    <input type="text" class="form-control" id="edit_hei_name" name="hei_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_hei_type" class="form-label">HEI type</label>
                                    <input type="text" class="form-control" id="edit_hei_type" name="hei_type" required>
                                </div>
                            </div>
                            <div class="row mb-3">

                                <div class="col-md-6">
                                    <label for="edit_semester_type" class="form-label">Semester Type</label>
                                    <input type="text" class="form-control" id="edit_semester_type" name="semester_type" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="edit_year_established" class="form-label">Year Established</label>
                                    <input type="text" class="form-control" id="edit_year_established" name="year_established" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_head" class="form-label">Institution Head</label>
                                    <input type="text" class="form-control" id="edit_head" name="head" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" id="edit_designation" name="designation" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_registrar" class="form-label">Registrar</label>
                                    <input type="text" class="form-control" id="edit_registrar" name="registrar">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_hei_logo" class="form-label">Institution Logo</label>
                                    <input type="text" class="form-control" id="edit_hei_logo" name="hei_logo">
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="edit-location-info" role="tabpanel" aria-labelledby="edit-location-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_street_barangay" class="form-label">Street/Barangay</label>
                                    <input type="text" class="form-control" id="edit_street_barangay" name="street_barangay" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_municipality" class="form-label">Municipality/City</label>
                                    <input type="text" class="form-control" id="edit_municipality" name="municipality" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_province" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="edit_province" name="province" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_zip_code" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="edit_zip_code" name="zip_code" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_contact_no" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="edit_contact_no" name="contact_no">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_email_address" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="edit_email_address" name="email_address" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="edit_website" class="form-label">Website</label>
                                    <input type="text" class="form-control" id="edit_website" name="website" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="edit-other-info" role="tabpanel" aria-labelledby="edit-other-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_calendar" class="form-label">Academic Calendar</label>
                                    <input type="text" class="form-control" id="edit_calendar" name="calendar" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_land_area" class="form-label">Land Area (sqm)</label>
                                    <input type="text" class="form-control" id="edit_land_area" name="land_area" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_x_coordinate" class="form-label">X Coordinate</label>
                                    <input type="text" class="form-control" id="edit_x_coordinate" name="x_coordinate" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_y_coordinate" class="form-label">Y Coordinate</label>
                                    <input type="text" class="form-control" id="edit_y_coordinate" name="y_coordinate" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update HEI Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="ched_admin_scripts.js"></script>
</body>
</html>