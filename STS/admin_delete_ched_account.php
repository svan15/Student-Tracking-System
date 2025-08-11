<?php
session_start();
include 'connect.php';

// Check if user is logged in and has appropriate permissions
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
//     header("Location: login.php");
//     exit();
// }

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Cannot delete own account
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account!";
        header("Location: ched_admin.php");
        exit();
    }
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Check if user exists and is of type 'ched'
        $check_sql = "SELECT user_type FROM User WHERE id = $user_id";
        $check_result = $connection->query($check_sql);
        
        if ($check_result->num_rows == 0) {
            throw new Exception("User not found.");
        }
        
        $user_type = $check_result->fetch_assoc()['user_type'];
        if ($user_type !== 'ched') {
            throw new Exception("Can only delete CHED accounts from this page.");
        }
        
        // Delete from User table (will cascade to ChedDetails due to foreign key constraints)
        $delete_sql = "DELETE FROM User WHERE id = $user_id";
        
        if (!$connection->query($delete_sql)) {
            throw new Exception("Error deleting account: " . $connection->error);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "CHED account deleted successfully!";
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