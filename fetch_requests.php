<?php
session_start();
include 'connectdb.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$tutor_id = $_SESSION['userid'];

// Get tutor ID from tbl_tutors
$tutor_query = $conn->prepare("SELECT tutor_id FROM tbl_tutors WHERE userid = ?");
$tutor_query->bind_param("i", $tutor_id);
$tutor_query->execute();
$tutor_result = $tutor_query->get_result();
$tutor = $tutor_result->fetch_assoc();
$tutor_id = $tutor['tutor_id'];
$tutor_query->close();

// Fetch all requests with student information and response count
$query = "SELECT r.*, u.username, sl.city, sl.state, sl.country,
          (SELECT COUNT(*) FROM tbl_response WHERE request_id = r.request_id) as response_count,
          (SELECT COUNT(*) FROM tbl_response WHERE request_id = r.request_id AND tutor_id = ?) as has_applied
          FROM tbl_request r
          JOIN tbl_student s ON r.student_id = s.student_id
          JOIN users u ON s.userid = u.userid
          JOIN tbl_studentlocation sl ON s.student_id = sl.student_id
          WHERE r.status = 'open'
          ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $row['location'] = $row['city'] . ', ' . $row['state'] . ', ' . $row['country'];
    $row['has_applied'] = (bool)$row['has_applied'];
    // Convert any null values to empty strings to avoid JSON encoding issues
    array_walk($row, function(&$value) {
        $value = $value ?? '';
    });
    $requests[] = $row;
}

echo json_encode($requests);
$stmt->close();
$conn->close();
?> 