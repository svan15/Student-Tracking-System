<?php
include 'connect.php';
session_start();

// Check if the user is logged in and has appropriate permissions
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
//     header('Content-Type: application/json');
//     echo json_encode(['error' => 'Unauthorized access']);
//     exit();
// }

// Get user ID from the request
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid user ID']);
    exit();
}

// Query to get user data
$sql = "SELECT u.id, u.username, c.first_name, c.middle_name, c.last_name, c.position, c.email, c.phone, c.address 
        FROM User u 
        JOIN ChedDetails c ON u.id = c.user_id 
        WHERE u.id = ? AND u.user_type = 'ched'";

try {
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $userData = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($userData);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'User not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

$connection->close();
?>