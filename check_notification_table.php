<?php
include 'connectdb.php';

echo "<h2>Checking Notifications Table Structure</h2>";

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'tbl_notifications'");
if ($table_check->num_rows == 0) {
    echo "<p>Table 'tbl_notifications' does not exist. Creating it now...</p>";
    
    // Create table
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
    
    if ($conn->query($create_table)) {
        echo "<p>Table created successfully!</p>";
    } else {
        echo "<p>Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Table 'tbl_notifications' exists.</p>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE tbl_notifications");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Insert test notification
    echo "<h3>Insert Test Notification:</h3>";
    if (isset($_GET['test'])) {
        $userid = 1; // Assuming user 1 exists
        $query = $conn->prepare("
            INSERT INTO tbl_notifications (userid, title, message, type) 
            VALUES (?, 'Test Notification', 'This is a test notification', 'system')
        ");
        $query->bind_param("i", $userid);
        
        if ($query->execute()) {
            echo "<p>Test notification added successfully!</p>";
        } else {
            echo "<p>Error adding test notification: " . $conn->error . "</p>";
        }
    } else {
        echo "<p><a href='?test=1'>Click here to insert a test notification</a></p>";
    }
    
    // Show sample data
    echo "<h3>Sample Data (up to 5 rows):</h3>";
    $sample = $conn->query("SELECT * FROM tbl_notifications LIMIT 5");
    if ($sample->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Title</th><th>Message</th><th>Type</th><th>Is Read</th><th>Created At</th></tr>";
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['notification_id'] . "</td>";
            echo "<td>" . $row['userid'] . "</td>";
            echo "<td>" . $row['title'] . "</td>";
            echo "<td>" . $row['message'] . "</td>";
            echo "<td>" . $row['type'] . "</td>";
            echo "<td>" . ($row['is_read'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in the table yet.</p>";
    }
}
?> 