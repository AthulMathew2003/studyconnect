<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Get student ID from request
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$teacher_userid = $_SESSION['userid'];

if ($student_id <= 0) {
    echo json_encode(['error' => 'Invalid student ID']);
    exit;
}

// Get student information
$student_query = "SELECT s.*, u.userid, u.username, u.email 
                  FROM tbl_student s 
                  JOIN users u ON s.userid = u.userid 
                  WHERE s.student_id = $student_id";
$student_result = $conn->query($student_query);

if (!$student_result || $student_result->num_rows == 0) {
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $student_result->fetch_assoc();

// Get messages between the teacher and student
$messages_query = "SELECT m.* 
                  FROM tbl_messages m 
                  WHERE (m.sender_id = $teacher_userid AND m.receiver_id = {$student['userid']})
                  OR (m.sender_id = {$student['userid']} AND m.receiver_id = $teacher_userid)
                  ORDER BY m.sent_time ASC";
$messages_result = $conn->query($messages_query);

$messages = [];
if ($messages_result && $messages_result->num_rows > 0) {
    while ($message = $messages_result->fetch_assoc()) {
        $messages[] = $message;
        
        // Mark messages as read if they were sent to the teacher
        if ($message['receiver_id'] == $teacher_userid && $message['is_read'] == 0) {
            $update_query = "UPDATE tbl_messages SET is_read = 1 WHERE message_id = {$message['message_id']}";
            $conn->query($update_query);
        }
    }
}

// Prepare and return the response
$response = [
    'student' => [
        'student_id' => $student['student_id'],
        'userid' => $student['userid'],
        'username' => $student['username'],
        'email' => $student['email'],
    ],
    'messages' => $messages
];

echo json_encode($response);
?> 