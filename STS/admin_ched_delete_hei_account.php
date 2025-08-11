<?php
session_start();
include 'connect.php';

// Check if user is logged in and has appropriate permissions
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
//     header("Location: login.php");
//     exit();
// }

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hei_id'])) {
    $hei_id = (int)$_POST['hei_id'];
    
    // Get user_id and logo path associated with the HEI
    $get_hei_info_sql = "SELECT user_id, hei_logo FROM tbl_heis WHERE hei_id = $hei_id";
    $hei_info_result = $connection->query($get_hei_info_sql);
    
    if ($hei_info_result->num_rows == 0) {
        $_SESSION['error'] = "HEI not found.";
        header("Location: ched_admin.php");
        exit();
    }
    
    $hei_info = $hei_info_result->fetch_assoc();
    $user_id = $hei_info['user_id'];
    $hei_logo = $hei_info['hei_logo'];
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Check if user exists and is of type 'school'
        $check_sql = "SELECT user_type FROM User WHERE id = $user_id";
        $check_result = $connection->query($check_sql);
        
        if ($check_result->num_rows == 0) {
            throw new Exception("User not found.");
        }
        
        $user_type = $check_result->fetch_assoc()['user_type'];
        if ($user_type !== 'school') {
            throw new Exception("Can only delete HEI accounts from this page.");
        }
        
        // Delete from User table (will cascade to tbl_heis due to foreign key constraints)
        $delete_sql = "DELETE FROM User WHERE id = $user_id";
        
        if (!$connection->query($delete_sql)) {
            throw new Exception("Error deleting account: " . $connection->error);
        }
        
        // Delete the logo file if it exists
        if (!empty($hei_logo) && file_exists($hei_logo)) {
            unlink($hei_logo);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "HEI account deleted successfully!";
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