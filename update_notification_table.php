<?php
include 'connectdb.php';

// Add the reference_id column to the tbl_notifications table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM tbl_notifications LIKE 'reference_id'");
if ($check_column->num_rows == 0) {
    $sql = "ALTER TABLE tbl_notifications ADD COLUMN reference_id INT DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'reference_id' added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'reference_id' already exists";
}
?> 