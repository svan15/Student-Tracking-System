<?php
include 'connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a school user
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
$requiredFields = ['semester', 'academicYear', 'programLevel', 'programName', 'studentIdNo', 'lastName', 'firstName'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Get the current HEI ID for the logged-in school user
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

// Prepare and execute enrollment insertion
$stmt = $connection->prepare("
    INSERT INTO Enrollment (
        hei_id, semester, academic_year, program_level, 
        program_name, major_name, year_level, student_id_no, 
        last_name, first_name, middle_name, suffix, 
        sex, sector, sector_details, nationality, 
        student_type, if_others
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$semester = $data['semester'];
$academic_year = $data['academicYear'];
$program_level = $data['programLevel'];
$program_name = $data['programName'];
$major_name = $data['majorName'] ?? null;
$student_id_no = $data['studentIdNo'];
$last_name = $data['lastName'];
$first_name = $data['firstName'];
$middle_name = $data['middleName'] ?? null;
$year_level = $data['yearLevel'];
$suffix = $data['suffix'] ?? null;
$sex = $data['sex'];
$sector = $data['sector'] ?? null;
$sector_details = $data['sectorDetails'] ?? null;
$nationality = $data['nationality'];
$student_type = $data['studentType'];
$student_type_others = $data['studentType'] === 'Others' ? $data['studentTypeOthers'] : null;


$stmt->bind_param(
    "isssssssssssssssss", 
    $hei_id, $semester, $academic_year, $program_level, 
    $program_name, $major_name, $year_level, $student_id_no, 
    $last_name, $first_name, $middle_name, $suffix, 
    $sex, $sector, $sector_details, $nationality, 
    $student_type, $student_type_others
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
    $stmt->close();
    exit();
}

// Get the last inserted enrollment ID
$enrollment_id = $connection->insert_id;

// Insert subjects if any
if (!empty($data['subjects'])) {
    $subject_stmt = $connection->prepare("
        INSERT INTO EnrolledSubjects (enrollment_id, subject_code, units) 
        VALUES (?, ?, ?)
    ");

    foreach ($data['subjects'] as $subject) {
        $subject_code = $subject['subjectCode'];
        $units = $subject['units'];
        $subject_stmt->bind_param("isd", $enrollment_id, $subject_code, $units);
        $subject_stmt->execute();
    }
    $subject_stmt->close();
}

$stmt->close();
$connection->close();

echo json_encode(['success' => true, 'message' => 'Enrollment saved successfully']);
exit();
?>