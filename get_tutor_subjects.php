<?php
session_start();
require_once 'connectdb.php';

header('Content-Type: application/json');

if (!isset($_GET['tutor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tutor ID not provided']);
    exit();
}

$tutor_id = (int)$_GET['tutor_id'];

// Get subjects taught by this tutor
$query = "SELECT s.subject_id, s.subject 
          FROM tbl_tutorsubject ts 
          JOIN tbl_subject s ON ts.subject_id = s.subject_id 
          WHERE ts.tutor_id = $tutor_id";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = [
        'subject_id' => $row['subject_id'],
        'subject' => $row['subject']
    ];
}

echo json_encode(['success' => true, 'subjects' => $subjects]);

$conn->close();
?> 