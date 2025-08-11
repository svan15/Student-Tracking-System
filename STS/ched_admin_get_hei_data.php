<?php
include 'connect.php';
session_start();

// Check if the user is logged in and has appropriate permissions
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
//     header('Content-Type: application/json');
//     echo json_encode(['error' => 'Unauthorized access']);
//     exit();
// }

// Get HEI ID from the request
$hei_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hei_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid HEI ID']);
    exit();
}

// Query to get HEI data
$sql = "SELECT h.*, u.username 
        FROM tbl_heis h 
        JOIN User u ON h.user_id = u.id 
        WHERE h.hei_id = ? AND u.user_type = 'school'";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $hei_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $heiData = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($heiData);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'HEI not found']);
}

$stmt->close();
$connection->close();
?>