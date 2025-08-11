<?php
session_start();
include 'connect.php';

// Check if user is logged in and has appropriate permissions
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
//     header("Location: login.php");
//     exit();
// }

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize user table data
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    
    // Validate and sanitize HEI data
    $hei_name = mysqli_real_escape_string($connection, $_POST['hei_name']);
    $hei_type = mysqli_real_escape_string($connection, $_POST['hei_type']);
    $semester_type = mysqli_real_escape_string($connection, $_POST['semester_type']);
    $street_barangay = mysqli_real_escape_string($connection, $_POST['street_barangay']);
    $municipality = mysqli_real_escape_string($connection, $_POST['municipality']);
    $province = mysqli_real_escape_string($connection, $_POST['province']);
    $zip_code = mysqli_real_escape_string($connection, $_POST['zip_code']);
    $contact_no = mysqli_real_escape_string($connection, $_POST['contact_no'] ?? '');
    $email_address = mysqli_real_escape_string($connection, $_POST['email_address']);
    $website = mysqli_real_escape_string($connection, $_POST['website']);
    $year_established = mysqli_real_escape_string($connection, $_POST['year_established']);
    $head = mysqli_real_escape_string($connection, $_POST['head']);
    $designation = mysqli_real_escape_string($connection, $_POST['designation']);
    $calendar = mysqli_real_escape_string($connection, $_POST['calendar']);
    $land_area = mysqli_real_escape_string($connection, $_POST['land_area']);
    $x_coordinate = mysqli_real_escape_string($connection, $_POST['x_coordinate']);
    $y_coordinate = mysqli_real_escape_string($connection, $_POST['y_coordinate']);
    $registrar = mysqli_real_escape_string($connection, $_POST['registrar'] ?? '');
    
    // Handle logo upload if provided
    $hei_logo = "";
    if (isset($_FILES['hei_logo']) && $_FILES['hei_logo']['error'] == 0) {
        $uploads_dir = 'uploads/logos/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }
        
        $tmp_name = $_FILES['hei_logo']['tmp_name'];
        $name = basename($_FILES['hei_logo']['name']);
        $file_extension = pathinfo($name, PATHINFO_EXTENSION);
        $new_filename = 'hei_logo_' . time() . '_' . uniqid() . '.' . $file_extension;
        
        // Only allow certain file types
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            if (move_uploaded_file($tmp_name, $uploads_dir . $new_filename)) {
                $hei_logo = $uploads_dir . $new_filename;
            }
        }
    }
    
    // Check if username already exists
    $check_query = "SELECT id FROM User WHERE username = '$username'";
    $check_result = $connection->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists. Please choose another username.";
        header("Location: ched_admin.php");
        exit();
    }
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Insert into User table
        $user_sql = "INSERT INTO User (username, password, user_type) VALUES ('$username', '$password', 'school')";
        
        if (!$connection->query($user_sql)) {
            throw new Exception("Error creating user account: " . $connection->error);
        }
        
        $user_id = $connection->insert_id;
        
        // Insert into tbl_heis table
        $hei_sql = "INSERT INTO tbl_heis (
                    user_id, hei_name, hei_type, semester_type, street_barangay, 
                    municipality, province, zip_code, contact_no, email_address, 
                    website, year_established, head, designation, calendar, 
                    land_area, x_coordinate, y_coordinate, registrar, hei_logo
                ) VALUES (
                    $user_id, '$hei_name', '$hei_type', '$semester_type', '$street_barangay', 
                    '$municipality', '$province', '$zip_code', '$contact_no', '$email_address', 
                    '$website', '$year_established', '$head', '$designation', '$calendar', 
                    '$land_area', '$x_coordinate', '$y_coordinate', '$registrar', '$hei_logo'
                )";
        
        if (!$connection->query($hei_sql)) {
            throw new Exception("Error creating HEI details: " . $connection->error);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "HEI account created successfully!";
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $connection->rollback();
        $_SESSION['error'] = $e->getMessage();
        
        // Delete the uploaded file if it exists
        if (!empty($hei_logo) && file_exists($hei_logo)) {
            unlink($hei_logo);
        }
    }
    
    // Redirect back to admin page
    header("Location: ched_admin.php");
    exit();
}
?>