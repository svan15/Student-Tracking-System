<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include 'connect.php';
session_start();

// Check authentication
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
    error_log("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get HEI ID for the current user
$stmt = $connection->prepare("SELECT hei_id FROM tbl_heis WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("HEI not found for user ID: $user_id");
    echo json_encode(['success' => false, 'message' => 'HEI not found for this user']);
    exit();
}

$heiData = $result->fetch_assoc();
$hei_id = $heiData['hei_id'];
$stmt->close();

// Function to process CSV file
function processCSV($file, $hei_id, $connection) {
    // Open the file
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        error_log("Could not open file: " . $file['name']);
        return ['success' => false, 'message' => 'Could not open file'];
    }
    
    // Get headers (first row)
    $headers = fgetcsv($handle);
    if (!$headers) {
        error_log("Empty file or invalid format: " . $file['name']);
        fclose($handle);
        return ['success' => false, 'message' => 'Empty file or invalid format'];
    }
    
    // Normalize headers: trim spaces and remove newlines
    $headers = array_map(function($header) {
        return trim(preg_replace('/\s+/', ' ', $header));
    }, $headers);
    
    // Debug - log the normalized headers
    error_log("Normalized headers: " . implode(", ", $headers));
    
    // Define header keywords for enrollment CSV columns
    $headerKeywords = [
        'hei_name' => 'Name of HEI',
        'semester' => 'Semester',
        'academic_year' => 'Academic Year',
        'program_level' => 'Program Level',
        'program_name' => 'Program Name',
        'major_name' => 'Major Name',
        'year_level' => 'Year Level',
        'student_id_no' => 'Student ID Number',
        'last_name' => 'Last Name',
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'suffix' => 'Suffix',
        'sex' => 'Sex',
        'sector' => 'Sector',
        'if_sector_is' => 'If sector is',
        'nationality' => 'Nationality',
        'student_type' => 'Student Type',
        'if_others' => 'If Others'
    ];

    // Map headers to database columns using partial matching
    $columnMap = [];
    foreach ($headerKeywords as $dbColumn => $keyword) {
        $columnMap[$dbColumn] = -1;
        foreach ($headers as $index => $header) {
            if (stripos($header, $keyword) !== false) {
                $columnMap[$dbColumn] = $index;
                break;
            }
        }
    }
    
    // Check if required columns exist
    $requiredColumns = ['semester', 'academic_year', 'program_name', 'student_id_no', 
                       'last_name', 'first_name'];
    
    $missingColumns = [];
    foreach ($requiredColumns as $column) {
        if ($columnMap[$column] === -1) {
            $missingColumns[] = str_replace('_', ' ', ucfirst($column));
        }
    }
    
    if (!empty($missingColumns)) {
        error_log("Missing required columns in file " . $file['name'] . ": " . implode(', ', $missingColumns));
        fclose($handle);
        return ['success' => false, 'message' => 'Missing required columns: ' . implode(', ', $missingColumns)];
    }
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        // Prepare statement to check existing enrollments
        $checkStmt = $connection->prepare("
            SELECT student_id FROM Enrollment 
            WHERE hei_id = ? AND student_id_no = ? AND semester = ? AND academic_year = ?
        ");
        
        // Prepare insert statement
        $insertStmt = $connection->prepare("
            INSERT INTO Enrollment 
            (hei_id, semester, academic_year, program_level, program_name, 
            major_name, year_level, student_id_no, last_name, first_name, 
            middle_name, suffix, sex, nationality, student_type, if_others)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $rowCount = 0;
        $skippedRows = 0;
        $duplicateEnrollments = [];
        $rowNumber = 1; // Header is row 1, data starts at row 2
        
        // Process data rows
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }
            
            // Extract data using the column mapping
            $semester = $columnMap['semester'] !== -1 && isset($row[$columnMap['semester']]) ? $row[$columnMap['semester']] : '';
            $academic_year = $columnMap['academic_year'] !== -1 && isset($row[$columnMap['academic_year']]) ? $row[$columnMap['academic_year']] : '';
            $program_level = $columnMap['program_level'] !== -1 && isset($row[$columnMap['program_level']]) ? $row[$columnMap['program_level']] : 'Baccalaureate';
            $program_name = $columnMap['program_name'] !== -1 && isset($row[$columnMap['program_name']]) ? $row[$columnMap['program_name']] : '';
            $major_name = $columnMap['major_name'] !== -1 && isset($row[$columnMap['major_name']]) ? $row[$columnMap['major_name']] : '';
            $year_level = $columnMap['year_level'] !== -1 && isset($row[$columnMap['year_level']]) ? $row[$columnMap['year_level']] : '1';
            $student_id_no = $columnMap['student_id_no'] !== -1 && isset($row[$columnMap['student_id_no']]) ? $row[$columnMap['student_id_no']] : '';
            $last_name = $columnMap['last_name'] !== -1 && isset($row[$columnMap['last_name']]) ? $row[$columnMap['last_name']] : '';
            $first_name = $columnMap['first_name'] !== -1 && isset($row[$columnMap['first_name']]) ? $row[$columnMap['first_name']] : '';
            $middle_name = $columnMap['middle_name'] !== -1 && isset($row[$columnMap['middle_name']]) ? $row[$columnMap['middle_name']] : '';
            $suffix = $columnMap['suffix'] !== -1 && isset($row[$columnMap['suffix']]) ? $row[$columnMap['suffix']] : '';
            $sex = $columnMap['sex'] !== -1 && isset($row[$columnMap['sex']]) ? $row[$columnMap['sex']] : '';
            $nationality = $columnMap['nationality'] !== -1 && isset($row[$columnMap['nationality']]) ? $row[$columnMap['nationality']] : 'Filipino';
            $student_type = $columnMap['student_type'] !== -1 && isset($row[$columnMap['student_type']]) ? $row[$columnMap['student_type']] : 'Regular';
            $if_others = $columnMap['if_others'] !== -1 && isset($row[$columnMap['if_others']]) ? $row[$columnMap['if_others']] : '';
            
            // Skip rows without required fields
            if (empty($student_id_no) || empty($program_name) || empty($last_name) || empty($first_name)) {
                error_log("Row $rowNumber skipped: Missing required fields. ID: $student_id_no, Program: $program_name, Name: $last_name, $first_name");
                $skippedRows++;
                continue;
            }
            
            // Check for existing enrollment
            $checkStmt->bind_param("isss", $hei_id, $student_id_no, $semester, $academic_year);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                error_log("Duplicate enrollment found: Student ID $student_id_no, $last_name, $first_name, $academic_year, $semester");
                $duplicateEnrollments[] = [
                    'student_id_no' => $student_id_no,
                    'program_name' => $program_name,
                    'name' => "$last_name, $first_name",
                    'academic_year' => $academic_year,
                    'semester' => $semester
                ];
                $skippedRows++;
                continue;
            }
            
            // Validate sex field
            if (!empty($sex) && !in_array(strtoupper($sex), ['M', 'F'])) {
                error_log("Invalid sex value '$sex' for student $student_id_no, defaulting to empty");
                $sex = '';
            } else {
                $sex = strtoupper($sex);
            }
            
            // Validate student type
            $validStudentTypes = ['Foreign', 'Regular', 'Exchange', 'LEP', 'ETEEAP', 'DE', 'TNE', 'Others'];
            if (!empty($student_type) && !in_array($student_type, $validStudentTypes)) {
                error_log("Invalid student type '$student_type' for student $student_id_no, defaulting to Regular");
                $student_type = 'Regular';
            }
            
            try {
                // Insert into database
                $insertStmt->bind_param(
                    "isssssssssssssss",
                    $hei_id,
                    $semester,
                    $academic_year,
                    $program_level,
                    $program_name,
                    $major_name,
                    $year_level,
                    $student_id_no,
                    $last_name,
                    $first_name,
                    $middle_name,
                    $suffix,
                    $sex,
                    $nationality,
                    $student_type,
                    $if_others
                );
                
                $insertStmt->execute();
                $studentId = $connection->insert_id;
                $rowCount++;
                error_log("Successfully inserted enrollment for student $student_id_no: $last_name, $first_name");
                
                // Process subject enrollments
                $subjectColumns = [];
                $unitsColumns = [];
                
                // Find all subject code and units columns
                foreach ($headers as $index => $header) {
                    if (preg_match('/Subject\s*Code\s*\d*/i', $header)) {
                        $subjectColumns[] = $index;
                    } elseif (preg_match('/Units\s*-\s*Subject\s*\d*/i', $header)) {
                        // Find the subject number from the units header
                        if (preg_match('/Subject\s*(\d+)/i', $header, $matches)) {
                            $subjectNum = $matches[1];
                            $unitsColumns[$subjectNum] = $index;
                        }
                    }
                }
                
                // If subject columns found, process subjects
                if (!empty($subjectColumns)) {
                    $subjectStmt = $connection->prepare("
                        INSERT INTO EnrolledSubjects (enrollment_id, subject_code, units)
                        VALUES (?, ?, ?)
                    ");
                    
                    foreach ($subjectColumns as $subjectIndex) {
                        if (!isset($row[$subjectIndex]) || empty(trim($row[$subjectIndex]))) {
                            continue;
                        }
                        
                        $subjectCode = trim($row[$subjectIndex]);
                        
                        // Find corresponding units column
                        $subjectNum = array_search($subjectIndex, array_flip($subjectColumns)) + 1;
                        $units = 0;
                        
                        if (isset($unitsColumns[$subjectNum]) && isset($row[$unitsColumns[$subjectNum]])) {
                            $unitsValue = trim($row[$unitsColumns[$subjectNum]]);
                            $units = is_numeric($unitsValue) ? (float)$unitsValue : 0;
                        }
                        
                        try {
                            $subjectStmt->bind_param("isd", $studentId, $subjectCode, $units);
                            $subjectStmt->execute();
                            error_log("Added subject $subjectCode ($units units) for student $student_id_no");
                        } catch (mysqli_sql_exception $e) {
                            error_log("Error inserting subject $subjectCode for student $student_id_no: " . $e->getMessage());
                        }
                    }
                    
                    $subjectStmt->close();
                }
                
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    error_log("Duplicate key error for student $student_id_no: " . $e->getMessage());
                    $duplicateEnrollments[] = [
                        'student_id_no' => $student_id_no,
                        'program_name' => $program_name,
                        'name' => "$last_name, $first_name",
                        'academic_year' => $academic_year,
                        'semester' => $semester
                    ];
                    $skippedRows++;
                } else {
                    error_log("SQL error on row $rowNumber: " . $e->getMessage());
                    throw $e;
                }
            }
        }
        
        $checkStmt->close();
        $insertStmt->close();
        fclose($handle);
        
        // Commit the transaction
        $connection->commit();
        error_log("Transaction committed: $rowCount records imported, $skippedRows skipped");
        
        // Format response
        $duplicateMessage = '';
        if (!empty($duplicateEnrollments)) {
            $duplicateMessage = "The following enrollments were not imported due to duplicates:\n";
            foreach ($duplicateEnrollments as $duplicate) {
                $duplicateMessage .= "- ID: {$duplicate['student_id_no']}, {$duplicate['name']} ({$duplicate['program_name']}, {$duplicate['academic_year']}, {$duplicate['semester']})\n";
            }
        }
        
        $message = "Successfully imported $rowCount enrollment records.";
        if ($skippedRows > 0) {
            $message .= " $skippedRows records were skipped.";
        }
        if (!empty($duplicateMessage)) {
            $message .= "\n\n" . $duplicateMessage;
        }
        
        return [
            'success' => true, 
            'message' => $message,
            'imported_count' => $rowCount,
            'skipped_rows' => $skippedRows,
            'duplicate_enrollments' => $duplicateEnrollments
        ];
    } catch (Exception $e) {
        $connection->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
        fclose($handle);
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        // Single file upload
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'File upload error: ' . $file['error'];
            error_log($errorMessage);
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit();
        }
        
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileType !== 'csv') {
            $errorMessage = 'Only CSV files are supported';
            error_log($errorMessage . ': ' . $file['name']);
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit();
        }
        
        error_log("Processing single file: " . $file['name']);
        $result = processCSV($file, $hei_id, $connection);
        echo json_encode($result);
    } elseif (isset($_FILES['importFiles'])) {
        // Multiple files upload
        $totalImported = 0;
        $totalSkipped = 0;
        $allDuplicates = [];
        $fileResults = [];
        $errors = [];
        
        error_log("Processing multiple files upload: " . count($_FILES['importFiles']['tmp_name']) . " files");
        
        foreach ($_FILES['importFiles']['tmp_name'] as $index => $tmpFile) {
            $fileName = $_FILES['importFiles']['name'][$index];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if ($fileType !== 'csv') {
                $errorMessage = "File '$fileName' is not a CSV file";
                error_log($errorMessage);
                $errors[] = $errorMessage;
                continue;
            }
            
            $fileData = [
                'name' => $fileName,
                'type' => $_FILES['importFiles']['type'][$index],
                'tmp_name' => $tmpFile,
                'error' => $_FILES['importFiles']['error'][$index],
                'size' => $_FILES['importFiles']['size'][$index]
            ];
            
            error_log("Processing file: " . $fileName);
            $result = processCSV($fileData, $hei_id, $connection);
            
            if ($result['success']) {
                error_log("Successfully processed file: " . $fileName . " - Imported: " . $result['imported_count'] . ", Skipped: " . $result['skipped_rows']);
                $totalImported += $result['imported_count'];
                $totalSkipped += $result['skipped_rows'];
                
                if (!empty($result['duplicate_enrollments'])) {
                    $allDuplicates = array_merge($allDuplicates, $result['duplicate_enrollments']);
                }
                
                $fileResults[] = [
                    'file' => $fileName,
                    'imported' => $result['imported_count'],
                    'skipped' => $result['skipped_rows']
                ];
            } else {
                $errorMessage = "Error processing '$fileName': " . $result['message'];
                error_log($errorMessage);
                $errors[] = $errorMessage;
            }
        }
        
        if ($totalImported > 0) {
            $message = "Successfully imported $totalImported enrollment records from " . count($fileResults) . " file(s).";
            if ($totalSkipped > 0) {
                $message .= " $totalSkipped records were skipped.";
            }
            
            error_log($message);
            if (!empty($errors)) {
                error_log("Some files had errors: " . implode('; ', $errors));
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'imported' => $totalImported,
                'skipped' => $totalSkipped,
                'file_results' => $fileResults,
                'duplicate_enrollments' => $allDuplicates,
                'errors' => $errors
            ]);
        } else {
            $errorMessage = "No enrollments were imported. \nErrors: " . implode('; ', $errors);
            error_log($errorMessage);
            echo json_encode([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $errors
            ]);
        }
    } else {
        $errorMessage = 'No file uploaded or invalid request';
        error_log($errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }
}

$connection->close();
?>