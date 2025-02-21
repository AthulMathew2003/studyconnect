<?php
session_start();
require_once 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request_id is provided
if (!isset($_POST['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID not provided']);
    exit();
}

$request_id = $_POST['request_id'];
$userid = $_SESSION['userid'];

// First verify that this request belongs to the current user
$query = "SELECT r.* FROM tbl_request r 
          INNER JOIN tbl_student s ON r.student_id = s.student_id 
          WHERE r.request_id = $request_id AND s.userid = $userid";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized to delete this request']);
    exit();
}

// Delete the request
$delete_query = "DELETE FROM tbl_request WHERE request_id = $request_id";
if ($conn->query($delete_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting request']);
}

$conn->close();
?> 