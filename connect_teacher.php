<?php
session_start();
require_once 'connectdb.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || !isset($_GET['tutor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get student_id for the current user
$userid = (int)$_SESSION['userid'];
$query = "SELECT student_id FROM tbl_student WHERE userid = $userid";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}

$student = $result->fetch_assoc();
$student_id = (int)$student['student_id'];
$tutor_id = (int)$_GET['tutor_id'];

// Check if a request already exists
$check_query = "SELECT tutorrequestid FROM tbl_tutorrequest 
                WHERE student_id = $student_id 
                AND tutor_id = $tutor_id 
                AND status = 'created'";
$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A connection request already exists with this tutor']);
    exit();
}

// Get tutor's hourly rate
$rate_query = "SELECT hourly_rate FROM tbl_tutors WHERE tutor_id = $tutor_id";
$rate_result = $conn->query($rate_query);

if (!$rate_result || $rate_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Tutor not found']);
    exit();
}

$tutor = $rate_result->fetch_assoc();
$hourly_rate = $tutor['hourly_rate'];

// Insert the connection request
$insert_query = "INSERT INTO tbl_tutorrequest (student_id, tutor_id, description, feerate, status, created_at) 
                 VALUES ($student_id, $tutor_id, 'New connection request', $hourly_rate, 'created', CURRENT_TIMESTAMP)";

if ($conn->query($insert_query)) {
    echo json_encode(['success' => true, 'message' => 'Connection request sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating connection request: ' . $conn->error]);
}

$conn->close();
?> 