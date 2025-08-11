<?php
include 'connect.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

// Ensure it's a POST request with JSON body
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit();
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid enrollment ID"]);
    exit();
}

$enrollmentId = intval($input['id']);
$userId = $_SESSION['user_id'];

try {
    // Begin transaction
    $connection->begin_transaction();

    // First, verify the enrollment belongs to the user's HEI
    $verifyStmt = $connection->prepare("
        SELECT e.student_id 
        FROM Enrollment e
        JOIN tbl_heis h ON e.hei_id = h.hei_id
        WHERE e.student_id = ? AND h.user_id = ?
    ");
    $verifyStmt->bind_param("ii", $enrollmentId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();

    if ($verifyResult->num_rows === 0) {
        throw new Exception("Enrollment record not found or unauthorized");
    }
    $verifyStmt->close();

    // Delete associated subjects first
    $deleteSubjectsStmt = $connection->prepare("
        DELETE FROM EnrolledSubjects 
        WHERE enrollment_id = ?
    ");
    $deleteSubjectsStmt->bind_param("i", $enrollmentId);
    $deleteSubjectsStmt->execute();
    $deleteSubjectsStmt->close();

    // Then delete the main enrollment record
    $deleteEnrollmentStmt = $connection->prepare("
        DELETE FROM Enrollment 
        WHERE student_id = ?
    ");
    $deleteEnrollmentStmt->bind_param("i", $enrollmentId);
    $deleteEnrollmentStmt->execute();
    $deleteEnrollmentStmt->close();

    // Commit transaction
    $connection->commit();

    // Return success response
    echo json_encode([
        "success" => true, 
        "message" => "Enrollment record deleted successfully"
    ]);

} catch (Exception $e) {
    // Rollback transaction in case of error
    $connection->rollback();

    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Error deleting enrollment: " . $e->getMessage()
    ]);
} finally {
    $connection->close();
}
?>