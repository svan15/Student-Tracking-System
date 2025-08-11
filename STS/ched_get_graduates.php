<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['hei_id'])) {
    echo json_encode(["success" => false, "message" => "No HEI selected"]);
    exit();
}

$hei_id = $_SESSION['hei_id']; // Get selected HEI ID
require 'connect.php'; // Ensure database connection is included

$query = "SELECT * FROM Graduates WHERE hei_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $hei_id);
$stmt->execute();
$result = $stmt->get_result();
$graduates = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(["success" => true, "graduates" => $graduates]);
?>
