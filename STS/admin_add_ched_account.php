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
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $first_name = mysqli_real_escape_string($connection, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($connection, $_POST['middle_name'] ?? '');
    $last_name = mysqli_real_escape_string($connection, $_POST['last_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($connection, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($connection, $_POST['address'] ?? '');
    $position = mysqli_real_escape_string($connection, $_POST['position'] ?? '');
    
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
        $user_sql = "INSERT INTO User (username, password, user_type) VALUES ('$username', '$password', 'ched')";
        
        if (!$connection->query($user_sql)) {
            throw new Exception("Error creating user account: " . $connection->error);
        }
        
        $user_id = $connection->insert_id;
        
        // Insert into ChedDetails table
        $details_sql = "INSERT INTO ChedDetails (user_id, first_name, middle_name, last_name, email, phone, address, position) 
                       VALUES ($user_id, '$first_name', '$middle_name', '$last_name', '$email', '$phone', '$address', '$position')";
        
        if (!$connection->query($details_sql)) {
            throw new Exception("Error creating CHED details: " . $connection->error);
        }
        
        // Commit the transaction
        $connection->commit();
        
        $_SESSION['success'] = "CHED account created successfully!";
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