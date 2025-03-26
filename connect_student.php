<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Add this at the beginning of your connect_student.php file
if (isset($_POST['deduct_coins'])) {
    $coins_to_deduct = 50;
    
    // Check current balance
    $check_balance = $conn->prepare("SELECT coin_balance FROM tbl_coinwallet WHERE userid = ?");
    $check_balance->bind_param("i", $_SESSION['userid']);
    $check_balance->execute();
    $result = $check_balance->get_result();
    
    if ($result->num_rows > 0) {
        $current_balance = $result->fetch_assoc()['coin_balance'];
        if ($current_balance < $coins_to_deduct) {
            echo json_encode(['success' => false, 'message' => 'Insufficient coins']);
            exit;
        }
        
        // Deduct coins
        $update_balance = $conn->prepare("UPDATE tbl_coinwallet SET coin_balance = coin_balance - ? WHERE userid = ?");
        $update_balance->bind_param("ii", $coins_to_deduct, $_SESSION['userid']);
        if (!$update_balance->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to deduct coins']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No coin wallet found']);
        exit;
    }
}

// Get POST data
$request_id = $_POST['request_id'];
$tutor_id = $_POST['tutor_id'];
$message = $_POST['message'];

// Check if tutor has already responded to this request
$check_query = $conn->prepare("SELECT response_id FROM tbl_response WHERE request_id = $request_id AND tutor_id = $tutor_id");
$check_query->execute();
$result = $check_query->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already responded to this request']);
    exit();
}

// Insert new response
$insert_query = $conn->prepare("INSERT INTO tbl_response (request_id, tutor_id, message) VALUES ($request_id, $tutor_id, '$message')");

if ($insert_query->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$insert_query->close();
$conn->close();
?> 