<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Get message and receiver ID from request
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$sender_id = $_SESSION['userid'];

if (empty($message) || $receiver_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Sanitize message to prevent SQL injection
$message = $conn->real_escape_string($message);

// Insert message into database
$query = "INSERT INTO tbl_messages (sender_id, receiver_id, message_text, sent_time, is_read) 
          VALUES ($sender_id, $receiver_id, '$message', NOW(), 0)";
$result = $conn->query($query);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>