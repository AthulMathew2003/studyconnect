<?php
session_start();
require_once 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get student_id
$userid = (int)$_SESSION['userid'];
$result = $conn->query("SELECT student_id FROM tbl_student WHERE userid = $userid");

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['student_id'];

// Sanitize inputs
$subject = $conn->real_escape_string($_POST['subject']);
$mode_of_learning = $conn->real_escape_string($_POST['learningMode']);
if (!in_array($mode_of_learning, ['online', 'offline', 'both'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid learning mode']);
    exit();
}
$fee_rate = (float)$_POST['budget'];
$description = $conn->real_escape_string($_POST['details']);

// Insert into tbl_request
$query = "INSERT INTO tbl_request (student_id, subject, mode_of_learning, fee_rate, description, status, created_at) 
          VALUES ($student_id, '$subject', '$mode_of_learning', $fee_rate, '$description', 'open', NOW())";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?> 