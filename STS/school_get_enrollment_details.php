<?php
include 'connect.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Check if an ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid enrollment ID"]);
    exit();
}

$enrollmentId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

try {
    // Fetch enrollment details
    $stmt = $connection->prepare("
        SELECT 
            e.student_id, 
            e.student_id_no, 
            e.first_name, 
            e.middle_name, 
            e.last_name, 
            e.suffix,
            e.sex,
            e.nationality,
            e.semester,
            e.academic_year,
            e.program_name,
            e.major_name,
            e.year_level,
            e.student_type,
            e.if_others
        FROM 
            Enrollment e
        JOIN 
            tbl_heis h ON e.hei_id = h.hei_id
        WHERE 
            e.student_id = ? AND h.user_id = ?
    ");
    $stmt->bind_param("ii", $enrollmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Enrollment record not found"]);
        exit();
    }
    
    $enrollmentDetails = $result->fetch_assoc();
    
    // Fetch subjects for this enrollment
    $subjectStmt = $connection->prepare("
        SELECT 
            subject_code, 
            units 
        FROM 
            EnrolledSubjects 
        WHERE 
            enrollment_id = ?
    ");
    $subjectStmt->bind_param("i", $enrollmentId);
    $subjectStmt->execute();
    $subjectsResult = $subjectStmt->get_result();
    
    $subjects = [];
    while ($subject = $subjectsResult->fetch_assoc()) {
        $subjects[] = $subject;
    }
    
    // Combine enrollment details with subjects
    $enrollmentDetails['subjects'] = $subjects;
    
    // Return the complete enrollment details
    echo json_encode($enrollmentDetails);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
} finally {
    $stmt->close();
    $subjectStmt->close();
    $connection->close();
}
?>