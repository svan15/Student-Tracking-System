<?php
// Disable error reporting and clear output buffer
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean();

// Set JSON content type header
header('Content-Type: application/json');

include 'connect.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

// Get the raw POST data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Debugging: Log the raw input
error_log("Raw input received: " . print_r($data, true));

// Additional input validation
if ($data === null) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit();
}

// Validate required fields
$requiredFields = ['id', 'semester', 'academicYear', 'programLevel', 'programName', 
                   'studentIdNo', 'lastName', 'firstName', 'yearLevel', 'sex', 
                   'nationality', 'studentType'];

$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => "Missing required fields: " . implode(', ', $missingFields)
    ]);
    exit();
}

// Start a transaction
$connection->begin_transaction();

try {
    // Get the user's HEI ID using prepared statement
    $userStmt = $connection->prepare("SELECT hei_id FROM tbl_heis WHERE user_id = ?");
    $userStmt->bind_param("i", $_SESSION['user_id']);
    $userStmt->execute();
    $result = $userStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("HEI not found for the user");
    }
    
    $heiRow = $result->fetch_assoc();
    $heiId = $heiRow['hei_id'];
    $userStmt->close();

    // Verify the enrollment exists and belongs to the HEI
    $checkStmt = $connection->prepare("
        SELECT student_id FROM Enrollment 
        WHERE student_id = ? AND hei_id = ?
    ");
    $checkStmt->bind_param("ii", $data['id'], $heiId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception("Enrollment record not found or does not belong to your institution");
    }
    $checkStmt->close();

    // Update enrollment record
    $updateStmt = $connection->prepare("
        UPDATE Enrollment 
        SET 
            semester = ?, 
            academic_year = ?, 
            program_level = ?, 
            program_name = ?, 
            major_name = ?, 
            student_id_no = ?, 
            last_name = ?, 
            first_name = ?, 
            middle_name = ?, 
            suffix = ?, 
            year_level = ?, 
            sex = ?, 
            nationality = ?, 
            student_type = ?
        WHERE student_id = ? AND hei_id = ?
    ");
    
    $majorName = $data['majorName'] ?? null;
    $middleName = $data['middleName'] ?? null;
    $suffix = $data['suffix'] ?? null;
    
    $updateStmt->bind_param(
        "ssssssssssssssis", 
        $data['semester'], 
        $data['academicYear'], 
        $data['programLevel'], 
        $data['programName'], 
        $majorName, 
        $data['studentIdNo'], 
        $data['lastName'], 
        $data['firstName'], 
        $middleName, 
        $suffix, 
        $data['yearLevel'], 
        $data['sex'], 
        $data['nationality'], 
        $data['studentType'], 
        $data['id'], 
        $heiId
    );
    
    $updateResult = $updateStmt->execute();
    
    if (!$updateResult) {
        throw new Exception("Failed to update enrollment record: " . $updateStmt->error);
    }
    
    // Delete existing subjects
    $deleteSubjectsStmt = $connection->prepare("DELETE FROM EnrolledSubjects WHERE enrollment_id = ?");
    $deleteSubjectsStmt->bind_param("i", $data['id']);
    $deleteSubjectsStmt->execute();
    
    // Insert new subjects
    $insertSubjectStmt = $connection->prepare("
        INSERT INTO EnrolledSubjects (enrollment_id, subject_code, units) 
        VALUES (?, ?, ?)
    ");
    
    foreach ($data['subjects'] as $subject) {
        $insertSubjectStmt->bind_param(
            "isd", 
            $data['id'], 
            $subject['subjectCode'], 
            $subject['units']
        );
        $insertResult = $insertSubjectStmt->execute();
        
        if (!$insertResult) {
            throw new Exception("Failed to insert subject: " . $insertSubjectStmt->error);
        }
    }
    
    // Commit the transaction
    $connection->commit();
    
    // Return success response
    echo json_encode(["success" => true]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $connection->rollback();
    
    // Log the full error details
    error_log("Enrollment Update Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Error updating enrollment: " . $e->getMessage()
    ]);
} finally {
    // Close statements and connection
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($deleteSubjectsStmt)) $deleteSubjectsStmt->close();
    if (isset($insertSubjectStmt)) $insertSubjectStmt->close();
    $connection->close();
}
exit(); // Ensure no additional output
?>