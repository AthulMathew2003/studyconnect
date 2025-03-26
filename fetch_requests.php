<?php
session_start();
include 'connectdb.php';

header('Content-Type: application/json');

try {
    // Build the WHERE clause based on filters
    $where_conditions = ["r.status = 'open'"]; // Base condition
    $params = [];
    $types = "";

    // Add tutor_id as first parameter (for checking if tutor has applied)
    if (!empty($_POST['tutor_id'])) {
        array_unshift($params, $_POST['tutor_id']);
        $types .= "i";
    } else {
        throw new Exception('Tutor ID is required');
    }

    // Add subject filter
    if (!empty($_POST['subject']) && $_POST['subject'] !== 'all') {
        $where_conditions[] = "r.subject = ?";
        $params[] = $_POST['subject'];
        $types .= "s";
    }

    // Add mode filter
    if (!empty($_POST['mode']) && $_POST['mode'] !== 'all') {
        $where_conditions[] = "r.mode_of_learning = ?";
        $params[] = $_POST['mode'];
        $types .= "s";
    }

    if (!empty($_POST['rate'])) {
        $where_conditions[] = "r.fee_rate <= ?";
        $params[] = $_POST['rate'];
        $types .= "d";
    }

    if (!empty($_POST['location'])) {
        $location_parts = explode(', ', $_POST['location']);
        if (count($location_parts) == 2) {
            $where_conditions[] = "(sl.city = ? AND sl.state = ?)";
            $params[] = $location_parts[0];
            $params[] = $location_parts[1];
            $types .= "ss";
        }
    }

    // Construct the query
    $query = "SELECT r.*, s.student_id, u.username, sl.city, sl.state, sl.country,
              (SELECT COUNT(*) FROM tbl_response WHERE request_id = r.request_id) as response_count,
              (SELECT COUNT(*) FROM tbl_response WHERE request_id = r.request_id AND tutor_id = ?) as has_applied
              FROM tbl_request r
              JOIN tbl_student s ON r.student_id = s.student_id
              JOIN users u ON s.userid = u.userid
              JOIN tbl_studentlocation sl ON s.student_id = sl.student_id";

    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];

    while ($row = $result->fetch_assoc()) {
        $row['location'] = $row['city'] . ', ' . $row['state'] . ', ' . $row['country'];
        $row['has_applied'] = (bool)$row['has_applied'];
        $requests[] = $row;
    }

    echo json_encode($requests);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 