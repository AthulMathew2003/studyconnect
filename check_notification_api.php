<?php
session_start();
include 'connectdb.php';

echo "<h1>Notification API Debug</h1>";

if (!isset($_SESSION['userid'])) {
    echo "<p>Not logged in</p>";
    exit;
}

echo "<p>User ID: " . $_SESSION['userid'] . "</p>";

// Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'tbl_notifications'");
if ($table_check->num_rows == 0) {
    echo "<p>Table 'tbl_notifications' does not exist.</p>";
} else {
    echo "<p>Table 'tbl_notifications' exists.</p>";
    
    // Show structure
    $structure = $conn->query("DESCRIBE tbl_notifications");
    echo "<h2>Table Structure:</h2>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $query = $conn->prepare("
        SELECT * FROM tbl_notifications 
        WHERE userid = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $query->bind_param("i", $_SESSION['userid']);
    $query->execute();
    $result = $query->get_result();
    
    echo "<h2>Sample Data:</h2>";
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Title</th><th>Message</th><th>Type</th><th>Is Read</th><th>Created At</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['notification_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['message']) . "</td>";
            echo "<td>" . $row['type'] . "</td>";
            echo "<td>" . ($row['is_read'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No notifications found for this user.</p>";
    }
}

// Test API response
echo "<h2>API Response Test:</h2>";
echo "<p>This shows what the API would return:</p>";
echo "<pre>";
$userid = $_SESSION['userid'];

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

// Get notifications
$notifications = [];
$query = $conn->prepare("
    SELECT notification_id, userid, title, message, type, is_read, created_at, reference_id
    FROM tbl_notifications 
    WHERE userid = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");

$query->bind_param("i", $userid);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['notification_id'],
        'title' => $row['title'],
        'message' => $row['message'],
        'type' => $row['type'],
        'is_read' => (bool)$row['is_read'],
        'created_at' => $row['created_at'],
        'reference_id' => $row['reference_id']
    ];
}

$response = [
    'notifications' => $notifications,
    'unread_count' => $unread_count
];

echo json_encode($response, JSON_PRETTY_PRINT);
echo "</pre>";
?> 