<?php
// Start with error handling and proper headers
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

    // Check if the notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'tbl_notifications'");
    if ($table_check->num_rows == 0) {
        // Table doesn't exist, create it
        $create_table = "CREATE TABLE IF NOT EXISTS tbl_notifications (
            notification_id INT PRIMARY KEY AUTO_INCREMENT,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(20) NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reference_id INT DEFAULT NULL,
            FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
        )";
        $conn->query($create_table);
    }

    // Get recent notifications for the user
    $query = $conn->prepare("
        SELECT notification_id, userid, title, message, type, is_read, created_at 
        FROM tbl_notifications 
        WHERE userid = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");

    $query->bind_param("i", $userid);
    $query->execute();
    $result = $query->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['type'],
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at']
        ];
    }

    // Count unread notifications
    $unread_query = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM tbl_notifications 
        WHERE userid = ? AND is_read = 0
    ");

    $unread_query->bind_param("i", $userid);
    $unread_query->execute();
    $unread_result = $unread_query->get_result();
    $unread_row = $unread_result->fetch_assoc();
    $unread_count = $unread_row['unread_count'];

    echo json_encode([
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    
} catch (Exception $e) {
    // Return a proper JSON error response
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 