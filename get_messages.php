<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode([]);
    exit;
}

// Get tutor ID from request
$tutor_userid = isset($_GET['tutor_id']) ? (int)$_GET['tutor_id'] : 0;
$student_userid = $_SESSION['userid'];

if ($tutor_userid <= 0) {
    echo json_encode([]);
    exit;
}

// Get messages between the student and tutor
$query = "SELECT * FROM tbl_messages 
          WHERE (sender_id = $student_userid AND receiver_id = $tutor_userid) 
          OR (sender_id = $tutor_userid AND receiver_id = $student_userid) 
          ORDER BY sent_time ASC";
$result = $conn->query($query);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
    
    // Mark messages as read if they were sent to the student
    if ($row['receiver_id'] == $student_userid && $row['is_read'] == 0) {
        $update_query = "UPDATE tbl_messages SET is_read = 1 WHERE message_id = " . $row['message_id'];
        $conn->query($update_query);
    }
}

echo json_encode($messages);
?>