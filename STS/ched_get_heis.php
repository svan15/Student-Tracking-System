<?php
include 'connect.php';
session_start();

// Check authentication
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "ched") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Fetch all HEIs
$stmt = $connection->prepare("SELECT hei_id, hei_name FROM tbl_heis ORDER BY hei_name"); //where user_type = 'school'
$stmt->execute();
$result = $stmt->get_result();

$heis = [];
while ($row = $result->fetch_assoc()) {
    $heis[] = $row;
}

$stmt->close();
$connection->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'heis' => $heis]);
?>