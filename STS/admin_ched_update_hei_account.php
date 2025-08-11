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
    // Validate and sanitize input
    $hei_id = (int)$_POST['hei_id'];
    
    // Get user_id linked to this HEI
    $get_user_id_sql = "SELECT user_id FROM tbl_heis WHERE hei_id = $hei_id";
    $user_id_result = $connection->query($get_user_id_sql);
    
    if ($user_id_result->num_rows == 0) {
        $_SESSION['error'] = "HEI not found.";
        header("Location: ched_admin.php");
        exit();
    }
    
    $user_id = $user_id_result->fetch_assoc()['user_id'];
    
    // Sanitize all input fields
    $username = mysqli_real_escape_string($connection, $_POST['username']);
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
    $hei_logo = mysqli_real_escape_string($connection, $_POST['hei_logo'] ?? '');
    
    // Check if password is being updated
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$password'";
    }
    
    // Check if username already exists (for other users)
    $check_query = "SELECT id FROM User WHERE username = '$username' AND id != $user_id";
    $check_result = $connection->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists. Please choose another username.";
        header("Location: ched_admin.php");
        exit();
    }
    
    // Handle logo upload if provided
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
                // Get the old logo to delete later
                $old_logo_query = "SELECT hei_logo FROM tbl_heis WHERE hei_id = $hei_id";
                $old_logo_result = $connection->query($old_logo_query);
                $old_logo = '';
                
                if ($old_logo_result->num_rows > 0) {
                    $old_logo = $old_logo_result->fetch_assoc()['hei_logo'];
                }
                
                $hei_logo = $uploads_dir . $new_filename;
                
                // Delete old logo file if it exists
                if (!empty($old_logo) && file_exists($old_logo) && $old_logo != $hei_logo) {
                    unlink($old_logo);
                }
            }
        }
    }
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Update User table
        $user_sql = "UPDATE User SET username = '$username'$password_sql WHERE id = $user_id";
        
        if (!$connection->query($user_sql)) {
            throw new Exception("Error updating user account: " . $connection->error);
        }
        
        // Update tbl_heis table
        $hei_sql = "UPDATE tbl_heis SET 
                    hei_name = '$hei_name', 
                    hei_type = '$hei_type', 
                    semester_type = '$semester_type', 
                    street_barangay = '$street_barangay', 
                    municipality = '$municipality', 
                    province = '$province', 
                    zip_code = '$zip_code', 
                    contact_no = '$contact_no', 
                    email_address = '$email_address', 
                    website = '$website', 
                    year_established = '$year_established', 
                    head = '$head', 
                    designation = '$designation', 
                    calendar = '$calendar', 
                    land_area = '$land_area', 
                    x_coordinate = '$x_coordinate', 
                    y_coordinate = '$y_coordinate', 
                    registrar = '$registrar'";
        
        // Only add logo to update query if it has changed
        if (!empty($hei_logo)) {
            $hei_sql .= ", hei_logo = '$hei_logo'";
        }
        
        $hei_sql .= " WHERE hei_id = $hei_id";
        
        if (!$connection->query($hei_sql)) {
            throw new Exception("Error updating HEI details: " . $connection->error);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "HEI account updated successfully!";
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $connection->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    // Redirect back to admin page
    header("Location: ched_admin.php");
    exit();
}
?>