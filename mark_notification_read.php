<?php
error_reporting(0); // Disable error reporting in production
header('Content-Type: application/json');

try {
    session_start();
    include 'connectdb.php';

    if (!isset($_SESSION['userid'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    $userid = $_SESSION['userid'];

    if (isset($_POST['notification_id'])) {
        // Mark single notification as read
        $notification_id = (int)$_POST['notification_id'];
        
        $query = $conn->prepare("
            UPDATE tbl_notifications 
            SET is_read = 1 
            WHERE notification_id = ? AND userid = ?
        ");
        
        $query->bind_param("ii", $notification_id, $userid);
        $success = $query->execute();
        
        echo json_encode(['success' => $success]);
    } else {
        // Mark all notifications as read
        $query = $conn->prepare("
            UPDATE tbl_notifications 
            SET is_read = 1 
            WHERE userid = ?
        ");
        
        $query->bind_param("i", $userid);
        $success = $query->execute();
        
        echo json_encode(['success' => $success]);
    }
} catch (Exception $e) {
    // Return a proper JSON error response
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 