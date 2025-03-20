<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$request_id = $_POST['request_id'];
$status = $_POST['status'];

// Update the request status
$update_query = "UPDATE tbl_tutorrequest 
                SET status = '$status' 
                WHERE tutorrequestid = $request_id";

if ($conn->query($update_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$conn->close();
?> 