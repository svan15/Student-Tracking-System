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

// Handle POST request to save programs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['programs']) || !is_array($data['programs'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid data format']);
        exit();
    }
    
    // Begin transaction
    $connection->begin_transaction();
    
    try {
        $programs = $data['programs'];
        $response = ['success' => true, 'message' => 'Programs saved successfully', 'updated' => 0, 'created' => 0];
        
        foreach ($programs as $program) {
            // Check if program_id exists and is not empty
            if (isset($program['program_id']) && !empty($program['program_id'])) {
                // Update existing program
                $stmt = $connection->prepare("
                    UPDATE Programs 
                    SET program_name = ?, major = ?, learning_modality = ?, 
                        academic_year_started = ?, other_remarks = ?, 
                        category = ?, others = ?, serial = ?, year = ?
                    WHERE program_id = ? AND hei_id = ?
                ");
                
                $stmt->bind_param(
                    "sssssssssis",
                    $program['program_name'],
                    $program['major'],
                    $program['learning_modality'],
                    $program['academic_year_started'],
                    $program['other_remarks'],
                    $program['category'],
                    $program['others'],
                    $program['serial'],
                    $program['year'],
                    $program['program_id'],
                    $hei_id
                );
                
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $response['updated']++;
                }
                
                $stmt->close();
            } else {
                // Insert new program
                $stmt = $connection->prepare("
                    INSERT INTO Programs 
                    (hei_id, program_name, major, learning_modality, 
                     academic_year_started, other_remarks, category, 
                     others, serial, year)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param(
                    "isssssssss",
                    $hei_id,
                    $program['program_name'],
                    $program['major'],
                    $program['learning_modality'],
                    $program['academic_year_started'],
                    $program['other_remarks'],
                    $program['category'],
                    $program['others'],
                    $program['serial'],
                    $program['year']
                );
                
                $stmt->execute();
                
                if ($stmt->insert_id) {
                    $response['created']++;
                }
                
                $stmt->close();
            }
        }
        
        // Commit the transaction
        $connection->commit();
        
        // Send success response
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $connection->rollback();
        
        // Send error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['program_id'])) {
    // Handle program deletion
    $program_id = intval($_GET['program_id']);
    
    $stmt = $connection->prepare("DELETE FROM Programs WHERE program_id = ? AND hei_id = ?");
    $stmt->bind_param("ii", $program_id, $hei_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Program deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete program or program not found']);
    }
    
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle retrieving all programs for the HEI
    $stmt = $connection->prepare("SELECT * FROM Programs WHERE hei_id = ? ORDER BY program_id");
    $stmt->bind_param("i", $hei_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $programs = [];
    
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    $stmt->close();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'programs' => $programs]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$connection->close();
?>