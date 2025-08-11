<?php

session_start();
require 'connect.php';

if (!isset($_SESSION['hei_id'])) {
    die(json_encode(["success" => false, "message" => "User not logged in"]));
}

$hei_id = $_SESSION['hei_id'];

if (!$connection) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]));
}

$sql = "SELECT * FROM Graduates WHERE hei_id = '$hei_id'";
$result = mysqli_query($connection, $sql);

$graduates = [];
while ($row = mysqli_fetch_assoc($result)) {
    $graduates[] = $row;
}

mysqli_close($connection);
echo json_encode(["success" => true, "graduates" => $graduates]);
?>
