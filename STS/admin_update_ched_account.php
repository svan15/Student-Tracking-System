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
    $user_id = (int)$_POST['user_id'];
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $first_name = mysqli_real_escape_string($connection, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($connection, $_POST['middle_name'] ?? '');
    $last_name = mysqli_real_escape_string($connection, $_POST['last_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($connection, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($connection, $_POST['address'] ?? '');
    $position = mysqli_real_escape_string($connection, $_POST['position'] ?? '');
    
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
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Update User table
        $user_sql = "UPDATE User SET username = '$username'$password_sql WHERE id = $user_id";
        
        if (!$connection->query($user_sql)) {
            throw new Exception("Error updating user account: " . $connection->error);
        }
        
        // Update ChedDetails table
        $details_sql = "UPDATE ChedDetails SET 
                        first_name = '$first_name', 
                        middle_name = '$middle_name', 
                        last_name = '$last_name', 
                        email = '$email', 
                        phone = '$phone', 
                        address = '$address', 
                        position = '$position' 
                        WHERE user_id = $user_id";
        
        if (!$connection->query($details_sql)) {
            throw new Exception("Error updating CHED details: " . $connection->error);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "CHED account updated successfully!";
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