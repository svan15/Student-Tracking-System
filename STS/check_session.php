<?php
ini_set('session.save_path', '/tmp');
session_start();
header('Content-Type: application/json');

// Return current session data
echo json_encode([
    "success" => true,
    "session" => $_SESSION,
    "session_id" => session_id()
]);
?>