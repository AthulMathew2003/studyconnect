<?php
session_start();
require_once 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get student_id
$userid = (int)$_SESSION['userid'];
$result = $conn->query("SELECT student_id FROM tbl_student WHERE userid = $userid");

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['student_id'];

// Check if user has enough coins
$coinQuery = "SELECT coin_balance FROM tbl_coinwallet WHERE userid = $userid";
$coinResult = $conn->query($coinQuery);

if ($coinResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Wallet not found']);
    exit();
}

$wallet = $coinResult->fetch_assoc();
$coinBalance = (int)$wallet['coin_balance'];

if ($coinBalance < 70) {
    echo json_encode(['success' => false, 'message' => 'Insufficient coins. You need 70 coins to post a request.']);
    exit();
}

// Sanitize inputs
$subject = $conn->real_escape_string($_POST['subject']);
$mode_of_learning = $conn->real_escape_string($_POST['learningMode']);
$start_date = $conn->real_escape_string($_POST['startDate']);
$end_date = $conn->real_escape_string($_POST['endDate']);
if (!in_array($mode_of_learning, ['online', 'offline', 'both'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid learning mode']);
    exit();
}
$fee_rate = (float)$_POST['budget'];
$description = $conn->real_escape_string($_POST['details']);

// Start transaction
$conn->begin_transaction();

try {
    // Insert into tbl_request
    $query = "INSERT INTO tbl_request (student_id, subject, mode_of_learning, fee_rate, description, start_date, end_date, status, created_at) 
              VALUES ($student_id, '$subject', '$mode_of_learning', $fee_rate, '$description', '$start_date', '$end_date', 'open', NOW())";
    
    if (!$conn->query($query)) {
        throw new Exception("Error inserting request: " . $conn->error);
    }
    
    // Deduct coins from wallet
    $updateWallet = "UPDATE tbl_coinwallet SET coin_balance = coin_balance - 70 WHERE userid = $userid";
    
    if (!$conn->query($updateWallet)) {
        throw new Exception("Error updating wallet: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Request created successfully. 70 coins deducted from your wallet.']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 