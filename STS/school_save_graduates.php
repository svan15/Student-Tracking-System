<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json');
require 'connect.php';

error_log("Session variables in school_save_graduates.php: " . json_encode($_SESSION));

if (!isset($_SESSION['hei_id'])) {
    die(json_encode([
        "success" => false, 
        "message" => "User not logged in", 
        "debug" => "Missing hei_id in session"
    ]));
}

$hei_id = $_SESSION['hei_id'];
$data = json_decode(file_get_contents("php://input"), true);

error_log("Received data: " . json_encode($data));
if (empty($data)) {
    die(json_encode(["success" => false, "message" => "No data received"]));
}

if (!$connection) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]));
}

// Check if this is a delete operation
if (isset($data['operation']) && $data['operation'] === 'delete') {
    if (!isset($data['id'])) {
        die(json_encode(["success" => false, "message" => "No graduate ID provided for deletion"]));
    }
    
    $graduate_id = $data['id'];
    
    // Prepare the SQL statement for deletion
    $sql = "DELETE FROM Graduates WHERE graduate_id = ? AND hei_id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    
    if (!$stmt) {
        die(json_encode(["success" => false, "message" => "Prepare statement failed: " . mysqli_error($connection)]));
    }
    
    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "ii", $graduate_id, $hei_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        die(json_encode(["success" => false, "message" => "SQL Error: " . $error]));
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    
    echo json_encode(["success" => true, "message" => "Graduate record deleted successfully"]);
    exit;
}

// If not a delete operation, proceed with the save operation
$response = ["success" => true, "newIds" => [], "updatedIds" => []];

foreach ($data as $graduate) {
    // Check if graduate_id exists (editing existing record)
    if (isset($graduate['graduate_id']) && !empty($graduate['graduate_id'])) {
        // UPDATE existing record
        $sql = "UPDATE Graduates SET 
                academic_year_started = ?, 
                semester_started = ?, 
                academic_year_ended = ?, 
                semester_ended = ?, 
                program_name = ?, 
                major_name = ?, 
                student_id_no = ?, 
                last_name = ?, 
                first_name = ?, 
                middle_name = ?, 
                suffix = ?, 
                date_of_birth = ?, 
                sex = ?, 
                student_type = ?, 
                if_others = ?, 
                special_order_no = ? 
                WHERE graduate_id = ? AND hei_id = ?";
        
        $stmt = mysqli_prepare($connection, $sql);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . mysqli_error($connection));
            die(json_encode(["success" => false, "message" => "Prepare statement failed: " . mysqli_error($connection)]));
        }
        
        // Bind parameters for UPDATE
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssii", 
            $graduate['academic_year_started'],
            $graduate['semester_started'],
            $graduate['academic_year_ended'],
            $graduate['semester_ended'],
            $graduate['program_name'],
            $graduate['major_name'],
            $graduate['student_id_no'],
            $graduate['last_name'],
            $graduate['first_name'],
            $graduate['middle_name'],
            $graduate['suffix'],
            $graduate['date_of_birth'],
            $graduate['sex'],
            $graduate['student_type'],
            $graduate['if_others'],
            $graduate['special_order_no'],
            $graduate['graduate_id'],
            $hei_id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            error_log("SQL Error: " . $error);
            mysqli_stmt_close($stmt);
            die(json_encode(["success" => false, "message" => "SQL Error: " . $error]));
        }
        
        $response["updatedIds"][] = $graduate['graduate_id'];
        
    } else {
        // INSERT new record
        $sql = "INSERT INTO Graduates 
                (hei_id, academic_year_started, semester_started, academic_year_ended, semester_ended, 
                program_name, major_name, student_id_no, last_name, first_name, middle_name, suffix, 
                date_of_birth, sex, student_type, if_others, special_order_no) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($connection, $sql);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . mysqli_error($connection));
            die(json_encode(["success" => false, "message" => "Prepare statement failed: " . mysqli_error($connection)]));
        }
        
        // Bind parameters for INSERT
        mysqli_stmt_bind_param($stmt, "issssssssssssssss", 
            $hei_id, 
            $graduate['academic_year_started'],
            $graduate['semester_started'],
            $graduate['academic_year_ended'],
            $graduate['semester_ended'],
            $graduate['program_name'],
            $graduate['major_name'],
            $graduate['student_id_no'],
            $graduate['last_name'],
            $graduate['first_name'],
            $graduate['middle_name'],
            $graduate['suffix'],
            $graduate['date_of_birth'],
            $graduate['sex'],
            $graduate['student_type'],
            $graduate['if_others'],
            $graduate['special_order_no']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            error_log("SQL Error: " . $error);
            mysqli_stmt_close($stmt);
            die(json_encode(["success" => false, "message" => "SQL Error: " . $error]));
        }
        
        // Get the ID of the inserted row
        $newId = mysqli_insert_id($connection);
        $response["newIds"][] = $newId;
    }
    
    // Close the statement
    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
echo json_encode($response);
?>