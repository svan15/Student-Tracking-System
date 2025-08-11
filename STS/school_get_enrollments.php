<?php
include 'connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT hei_id FROM tbl_heis WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$hei_details = $result->fetch_assoc();
$stmt->close();

if (!$hei_details) {
    echo json_encode(['success' => false, 'message' => 'HEI not found']);
    exit();
}

$hei_id = $hei_details['hei_id'];

// Get all enrollments first
$stmt = $connection->prepare("
    SELECT * FROM Enrollment 
    WHERE hei_id = ? 
    ORDER BY last_name, first_name
");
$stmt->bind_param("i", $hei_id);
$stmt->execute();
$result = $stmt->get_result();

$enrollments = [];

while ($enrollment = $result->fetch_assoc()) {
    // Fetch subjects for this enrollment
    $subjectStmt = $connection->prepare("
        SELECT subject_code, units 
        FROM EnrolledSubjects 
        WHERE enrollment_id = ?
    ");
    $subjectStmt->bind_param("i", $enrollment['student_id']); // assuming student_id is same as enrollment_id
    $subjectStmt->execute();
    $subjectsResult = $subjectStmt->get_result();

    $subjects = [];
    while ($subject = $subjectsResult->fetch_assoc()) {
        $subjects[] = $subject;
    }

    $enrollment['subjects'] = $subjects;
    $enrollments[] = $enrollment;

    $subjectStmt->close();
}

$stmt->close();
$connection->close();

echo json_encode($enrollments);
exit();
