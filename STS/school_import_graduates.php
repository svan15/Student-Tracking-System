<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    header('Content-Type: application/json');
    include 'connect.php';
    session_start();

    // Check authentication
    if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] !== "school") {
        header('Content-Type: application/json');
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
        header('Content-Type: application/json');
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
            return ['success' => false, 'message' => 'Could not open file'];
        }
        
        // Get headers (first row)
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['success' => false, 'message' => 'Empty file or invalid format'];
        }
        
        // Normalize headers: trim spaces and remove newlines
        $headers = array_map(function($header) {
            return trim(preg_replace('/\s+/', ' ', $header)); // Replace newlines and extra spaces
        }, $headers);
        
        // Debug - log the normalized headers
        error_log("Normalized headers: " . implode(", ", $headers));
        
        // Normalize header keywords for comparison
        $headerKeywords = [
            'academic_year_started' => 'Academic Year Started',
            'semester_started' => 'Semester Started',
            'academic_year_ended' => 'Academic Year Ended', 
            'semester_ended' => 'Semester Ended',
            'program_name' => 'Program Name',
            'major_name' => 'Major Name',
            'student_id_no' => 'Student ID No.',
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'suffix' => 'Suffix',
            'date_of_birth' => 'Date of Birth',
            'sex' => 'Sex',
            'student_type' => 'Student Type',
            'if_others' => 'If Others',
            'special_order_no' => 'Special Order No. Issued'
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
        
        // Debug - log the column mapping
        error_log("Column mapping: " . print_r($columnMap, true));
        
        // Check if required columns exist
        $requiredColumns = ['academic_year_started', 'semester_started', 'academic_year_ended', 'semester_ended', 
                           'program_name', 'student_id_no', 'last_name', 'first_name', 'date_of_birth', 'sex', 
                           'student_type'];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if ($columnMap[$column] === -1) {
                $missingColumns[] = str_replace('_', ' ', ucfirst($column));
            }
        }
        
        if (!empty($missingColumns)) {
            fclose($handle);
            return ['success' => false, 'message' => 'Missing required columns: ' . implode(', ', $missingColumns)];
        }
        
        // Begin transaction
        $connection->begin_transaction();
        
        try {
            // Prepare statement to check existing graduates
            $checkStmt = $connection->prepare("
                SELECT graduate_id FROM Graduates 
                WHERE hei_id = ? AND student_id_no = ? AND program_name = ?
            ");
            
            // Prepare insert statement
            $insertStmt = $connection->prepare("
                INSERT INTO Graduates 
                (hei_id, academic_year_started, semester_started, academic_year_ended, 
                semester_ended, program_name, major_name, student_id_no, last_name, 
                first_name, middle_name, suffix, date_of_birth, sex, 
                student_type, if_others, special_order_no)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $rowCount = 0;
            $skippedRows = 0;
            $duplicateGraduates = []; // Track duplicate graduates
            $invalidMiddleNames = []; // Track entries with single-letter middle names
            
            // Process data rows
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }
                
                // Extract data using the column mapping
                $academic_year_started = $columnMap['academic_year_started'] !== -1 && isset($row[$columnMap['academic_year_started']]) ? $row[$columnMap['academic_year_started']] : '';
                $semester_started = $columnMap['semester_started'] !== -1 && isset($row[$columnMap['semester_started']]) ? $row[$columnMap['semester_started']] : '';
                $academic_year_ended = $columnMap['academic_year_ended'] !== -1 && isset($row[$columnMap['academic_year_ended']]) ? $row[$columnMap['academic_year_ended']] : '';
                $semester_ended = $columnMap['semester_ended'] !== -1 && isset($row[$columnMap['semester_ended']]) ? $row[$columnMap['semester_ended']] : '';
                $program_name = $columnMap['program_name'] !== -1 && isset($row[$columnMap['program_name']]) ? $row[$columnMap['program_name']] : '';
                $major_name = $columnMap['major_name'] !== -1 && isset($row[$columnMap['major_name']]) ? $row[$columnMap['major_name']] : '';
                $student_id_no = $columnMap['student_id_no'] !== -1 && isset($row[$columnMap['student_id_no']]) ? $row[$columnMap['student_id_no']] : '';
                $last_name = $columnMap['last_name'] !== -1 && isset($row[$columnMap['last_name']]) ? $row[$columnMap['last_name']] : '';
                $first_name = $columnMap['first_name'] !== -1 && isset($row[$columnMap['first_name']]) ? $row[$columnMap['first_name']] : '';
                $middle_name = $columnMap['middle_name'] !== -1 && isset($row[$columnMap['middle_name']]) ? $row[$columnMap['middle_name']] : '';
                $suffix = $columnMap['suffix'] !== -1 && isset($row[$columnMap['suffix']]) ? $row[$columnMap['suffix']] : '';
                $date_of_birth = $columnMap['date_of_birth'] !== -1 && isset($row[$columnMap['date_of_birth']]) ? $row[$columnMap['date_of_birth']] : '';
                $sex = $columnMap['sex'] !== -1 && isset($row[$columnMap['sex']]) ? $row[$columnMap['sex']] : '';
                $student_type = $columnMap['student_type'] !== -1 && isset($row[$columnMap['student_type']]) ? $row[$columnMap['student_type']] : '';
                $if_others = $columnMap['if_others'] !== -1 && isset($row[$columnMap['if_others']]) ? $row[$columnMap['if_others']] : '';
                $special_order_no = $columnMap['special_order_no'] !== -1 && isset($row[$columnMap['special_order_no']]) ? $row[$columnMap['special_order_no']] : '';
                
                // Skip rows without required fields
                if (empty($student_id_no) || empty($program_name) || empty($last_name) || empty($first_name)) {
                    $skippedRows++;
                    continue;
                }
                
                // Middle name validation - skip if it's only one letter
                if (!empty($middle_name) && strlen(trim($middle_name)) === 1) {
                    $invalidMiddleNames[] = [
                        'student_id_no' => $student_id_no,
                        'program_name' => $program_name,
                        'name' => "$last_name, $first_name",
                        'middle_name' => $middle_name
                    ];
                    $skippedRows++;
                    continue;
                }
                
                // Format date of birth for database
                if (!empty($date_of_birth)) {
                    // Check if date is in Excel format (m/d/Y)
                    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_of_birth)) {
                        $date = DateTime::createFromFormat('m/d/Y', $date_of_birth);
                        if ($date) {
                            $date_of_birth = $date->format('Y-m-d');
                        }
                    }
                }
                
                // Check for existing graduate with same student ID and program
                $checkStmt->bind_param("iss", $hei_id, $student_id_no, $program_name);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                // If graduate with same ID and program exists, track it
                if ($checkResult->num_rows > 0) {
                    $duplicateGraduates[] = [
                        'student_id_no' => $student_id_no,
                        'program_name' => $program_name,
                        'name' => "$last_name, $first_name"
                    ];
                    $skippedRows++;
                    continue;
                }
                
                // Validate sex field
                if (!empty($sex) && !in_array(strtoupper($sex), ['M', 'F'])) {
                    $sex = ''; // Clear invalid value
                } else {
                    $sex = strtoupper($sex); // Ensure uppercase
                }
                
                // Validate student type
                $validStudentTypes = ['Foreign', 'Regular', 'Exchange', 'LEP', 'ETEEAP', 'DE', 'TNE', 'Others'];
                if (!empty($student_type) && !in_array($student_type, $validStudentTypes)) {
                    $student_type = ''; // Clear invalid value
                }
                
                try {
                    // Insert into database
                    $insertStmt->bind_param(
                        "issssssssssssssss",
                        $hei_id,
                        $academic_year_started,
                        $semester_started,
                        $academic_year_ended,
                        $semester_ended,
                        $program_name,
                        $major_name,
                        $student_id_no,
                        $last_name,
                        $first_name,
                        $middle_name,
                        $suffix,
                        $date_of_birth,
                        $sex,
                        $student_type,
                        $if_others,
                        $special_order_no
                    );
                
                    $insertStmt->execute();
                    $rowCount++;
                } catch (mysqli_sql_exception $e) {
                    // Check if the error is a duplicate entry error
                    if ($e->getCode() === 1062) { // 1062 is the MySQL error code for duplicate entry
                        $duplicateGraduates[] = [
                            'student_id_no' => $student_id_no,
                            'program_name' => $program_name,
                            'name' => "$last_name, $first_name"
                        ];
                        $skippedRows++;
                    } else {
                        throw $e; // Re-throw other exceptions
                    }
                }
            }
            
            $checkStmt->close();
            $insertStmt->close();
            fclose($handle);
            
            // Commit the transaction
            $connection->commit();
            
            // Format duplicate graduates for alert message
            $duplicateMessage = '';
            if (!empty($duplicateGraduates)) {
                $duplicateMessage = "The following graduates were not imported due to duplicates:\n";
                foreach ($duplicateGraduates as $duplicate) {
                    $duplicateMessage .= "- ID: {$duplicate['student_id_no']}, {$duplicate['name']} ({$duplicate['program_name']})\n";
                }
            }
            
            // Format invalid middle names for alert message
            $middleNameMessage = '';
            if (!empty($invalidMiddleNames)) {
                $middleNameMessage = "The following graduates were not imported due to single-letter middle names:\n";
                foreach ($invalidMiddleNames as $invalid) {
                    $middleNameMessage .= "- ID: {$invalid['student_id_no']}, {$invalid['name']} ({$invalid['program_name']})\n";
                }
            }
            
            // Combine success message with duplicate and middle name info
            $message = "Successfully imported $rowCount graduates.";
            if ($skippedRows > 0) {
                $message .= " $skippedRows records were skipped.";
            }
            if (!empty($duplicateMessage)) {
                $message .= "\n\n" . $duplicateMessage;
            }
            if (!empty($middleNameMessage)) {
                $message .= "\n\n" . $middleNameMessage;
            }
            
            return [
                'success' => true, 
                'message' => $message,
                'imported_count' => $rowCount,
                'skipped_rows' => $skippedRows,
                'duplicate_graduates' => $duplicateGraduates,
                'invalid_middle_names' => $invalidMiddleNames
            ];
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $connection->rollback();
            fclose($handle);
            
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Handle file upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
            exit();
        }
        
        // Validate file type
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileType !== 'csv') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Only CSV files are supported']);
            exit();
        }
        
        // Process the file
        $result = processCSV($file, $hei_id, $connection);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No file uploaded or invalid request']);
    }

    $connection->close();
?>