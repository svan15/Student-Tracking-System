<?php
include 'connect.php';
session_start();

// Check authentication
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "ched") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get HEI ID from request
$hei_id = isset($_GET['hei_id']) ? intval($_GET['hei_id']) : 0;

if ($hei_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid HEI ID']);
    exit();
}

// Fetch programs for the specified HEI
$stmt = $connection->prepare("SELECT * FROM Programs WHERE hei_id = ? ORDER BY program_name");
$stmt->bind_param("i", $hei_id);
$stmt->execute();
$result = $stmt->get_result();

$programs = [];
while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
}

$stmt->close();
$connection->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'programs' => $programs]);
?>