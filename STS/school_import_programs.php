<?php
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
        
        // Map headers to database columns
        $columnMap = [
            'program_name' => array_search('Program Name (Do not Abbreviate)', $headers) !== false ? array_search('Program Name (Do not Abbreviate)', $headers) : -1,
            'major' => array_search('Major (Do not Abbreviate)', $headers) !== false ? array_search('Major (Do not Abbreviate)', $headers) : -1,
            'learning_modality' => array_search('Learning Modality (Face to Face, Hybrid, Online)', $headers) !== false ? array_search('Learning Modality (Face to Face, Hybrid, Online)', $headers) : -1,
            'academic_year_started' => array_search('Academic Year Started', $headers) !== false ? array_search('Academic Year Started', $headers) : -1,
            'other_remarks' => array_search('Other Remarks', $headers) !== false ? array_search('Other Remarks', $headers) : -1,
            'category' => array_search('Category', $headers) !== false ? array_search('Category', $headers) : -1,
            'others' => array_search('Others', $headers) !== false ? array_search('Others', $headers) : -1,
            'serial' => array_search('Serial', $headers) !== false ? array_search('Serial', $headers) : -1,
            'year' => array_search('Year', $headers) !== false ? array_search('Year', $headers) : -1
        ];
        
        // Check if required columns exist
        if ($columnMap['program_name'] === -1) {
            fclose($handle);
            return ['success' => false, 'message' => 'lacking required column: Program Name'];
        }
        
        // Begin transaction
        $connection->begin_transaction();
        
        try {
            // Prepare statement to check existing programs
            $checkStmt = $connection->prepare("
                SELECT program_id, program_name, major FROM Programs 
                WHERE hei_id = ? AND program_name = ? AND major = ?
            ");
            
            // Prepare insert statement
            $insertStmt = $connection->prepare("
                INSERT INTO Programs 
                (hei_id, program_name, major, learning_modality, 
                academic_year_started, other_remarks, category, 
                others, serial, year)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $rowCount = 0;
            $skippedRows = 0;
            $duplicatePrograms = []; // Track duplicate programs
            
            // Process data rows
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }
                
                // Extract data using the column mapping
                $program_name = $columnMap['program_name'] !== -1 && isset($row[$columnMap['program_name']]) ? $row[$columnMap['program_name']] : '';
                $major = $columnMap['major'] !== -1 && isset($row[$columnMap['major']]) ? $row[$columnMap['major']] : '';
                $learning_modality = $columnMap['learning_modality'] !== -1 && isset($row[$columnMap['learning_modality']]) ? $row[$columnMap['learning_modality']] : '';
                $academic_year_started = $columnMap['academic_year_started'] !== -1 && isset($row[$columnMap['academic_year_started']]) ? $row[$columnMap['academic_year_started']] : '';
                $other_remarks = $columnMap['other_remarks'] !== -1 && isset($row[$columnMap['other_remarks']]) ? $row[$columnMap['other_remarks']] : '';
                $category = $columnMap['category'] !== -1 && isset($row[$columnMap['category']]) ? $row[$columnMap['category']] : '';
                $others = $columnMap['others'] !== -1 && isset($row[$columnMap['others']]) ? $row[$columnMap['others']] : '';
                $serial = $columnMap['serial'] !== -1 && isset($row[$columnMap['serial']]) ? $row[$columnMap['serial']] : '';
                $year = $columnMap['year'] !== -1 && isset($row[$columnMap['year']]) ? intval($row[$columnMap['year']]) : null;
                
                // Skip rows without a program name
                if (empty($program_name)) {
                    continue;
                }
                
                // Check for existing program with same name and major
                $checkStmt->bind_param("iss", $hei_id, $program_name, $major);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                // If program with same name and major exists, track it
                if ($checkResult->num_rows > 0) {
                    $duplicatePrograms[] = [
                        'program_name' => $program_name,
                        'major' => $major
                    ];
                    $skippedRows++;
                    continue;
                }
                
                // Validate learning modality
                if (!empty($learning_modality) && !in_array($learning_modality, ['Face to Face', 'Hybrid', 'Online'])) {
                    $learning_modality = ''; // Clear invalid value
                }
                
                // Insert into database
                $insertStmt->bind_param(
                    "isssssssis",
                    $hei_id,
                    $program_name,
                    $major,
                    $learning_modality,
                    $academic_year_started,
                    $other_remarks,
                    $category,
                    $others,
                    $serial,
                    $year
                );
                
                $insertStmt->execute();
                $rowCount++;
            }
            
            $checkStmt->close();
            $insertStmt->close();
            fclose($handle);
            
            // Commit the transaction
            $connection->commit();
            
            // Format duplicate programs for alert message
            $duplicateMessage = '';
            if (!empty($duplicatePrograms)) {
                $duplicateMessage = "The following programs were not imported due to duplicates:\n";
                foreach ($duplicatePrograms as $duplicate) {
                    $duplicateMessage .= "- {$duplicate['program_name']} ({$duplicate['major']})\n";
                }
            }
            
            // Combine success message with duplicate info
            $message = " Imported $rowCount programs.";
            if ($skippedRows > 0) {
                $message .= " $skippedRows programs were skipped.";
            }
            if (!empty($duplicateMessage)) {
                $message .= "\n\n" . $duplicateMessage;
            }
            
            return [
                'success' => true, 
                'message' => $message,
                'skipped_rows' => $skippedRows,
                'duplicate_programs' => $duplicatePrograms
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