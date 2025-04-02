<?php
// Only declare the function if it doesn't already exist
if (!function_exists('addNotification')) {
    // Function to add a notification
    function addNotification($conn, $userid, $title, $message, $type, $reference_id = null) {
        // Check if reference_id column exists
        $check_column = $conn->query("SHOW COLUMNS FROM tbl_notifications LIKE 'reference_id'");
        
        if ($check_column->num_rows > 0) {
            // If reference_id column exists, use it
            $query = $conn->prepare("
                INSERT INTO tbl_notifications (userid, title, message, type, reference_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $query->bind_param("isssi", $userid, $title, $message, $type, $reference_id);
        } else {
            // If reference_id column doesn't exist, don't use it
            $query = $conn->prepare("
                INSERT INTO tbl_notifications (userid, title, message, type) 
                VALUES (?, ?, ?, ?)
            ");
            $query->bind_param("isss", $userid, $title, $message, $type);
        }
        
        return $query->execute();
    }
}
?> 