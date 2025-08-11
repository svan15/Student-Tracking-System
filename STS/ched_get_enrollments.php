<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

session_start();
require_once 'connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function debug_log($message) {
    error_log("[DEBUG] " . $message);
}

debug_log("Start of script");

// Check database connection
if (!$connection) {
    debug_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// Check if HEI ID is available in session
if (!isset($_SESSION['hei_id'])) {
    debug_log("No HEI ID in session");
    http_response_code(400);
    echo json_encode(["error" => "No HEI selected"]);
    exit;
}

$hei_id = $_SESSION['hei_id'];
debug_log("HEI ID: " . $hei_id);

try {
    // Prepare statement to prevent SQL injection
    $stmt = mysqli_prepare($connection, "SELECT e.*, h.hei_name 
                                   FROM Enrollment e 
                                   JOIN tbl_heis h ON e.hei_id = h.hei_id 
                                   WHERE e.hei_id = ?");

    if (!$stmt) {
        debug_log("Query preparation failed: " . mysqli_error($connection));
        throw new Exception("Query preparation failed: " . mysqli_error($connection));
    }

    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "i", $hei_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        debug_log("Query execution failed: " . mysqli_stmt_error($stmt));
        throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        debug_log("Failed to get result set: " . mysqli_error($connection));
        throw new Exception("Failed to get result set: " . mysqli_error($connection));
    }

    debug_log("Found " . mysqli_num_rows($result) . " enrollment records");

    // Fetch enrollment data
    $enrollments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // For each enrollment, fetch their enrolled subjects
        $enrollment_id = $row['student_id'];
        
        $subjects_stmt = mysqli_prepare($connection, "SELECT * FROM EnrolledSubjects WHERE enrollment_id = ?");
        if (!$subjects_stmt) {
            debug_log("Subjects query preparation failed for enrollment ID " . $enrollment_id . ": " . mysqli_error($connection));
            continue;
        }
        
        mysqli_stmt_bind_param($subjects_stmt, "i", $enrollment_id);
        
        if (!mysqli_stmt_execute($subjects_stmt)) {
            debug_log("Subjects query execution failed for enrollment ID " . $enrollment_id . ": " . mysqli_stmt_error($subjects_stmt));
            mysqli_stmt_close($subjects_stmt);
            continue;
        }
        
        $subjects_result = mysqli_stmt_get_result($subjects_stmt);
        
        if (!$subjects_result) {
            debug_log("Failed to get subjects result set for enrollment ID " . $enrollment_id . ": " . mysqli_error($connection));
            mysqli_stmt_close($subjects_stmt);
            continue;
        }
        
        $subjects = [];
        while ($subject_row = mysqli_fetch_assoc($subjects_result)) {
            $subjects[] = $subject_row;
        }
        
        debug_log("Found " . count($subjects) . " subjects for enrollment ID " . $enrollment_id);
        
        $row['subjects'] = $subjects;
        $enrollments[] = $row;
        
        mysqli_stmt_close($subjects_stmt);
    }

    mysqli_stmt_close($stmt);

    // Check if we have any data before returning
    if (empty($enrollments)) {
        debug_log("No enrollment records found for HEI ID: " . $hei_id);
    }
    
    // Ensure proper encoding of the JSON response
    echo json_encode($enrollments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    debug_log("Exception caught: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    // Close connection regardless of success or failure
    if (isset($connection) && $connection) {
        mysqli_close($connection);
    }
}
?>