<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userid = (int)$_SESSION['userid'];

// Check coin balance first
$coinQuery = "SELECT coin_balance FROM tbl_coinwallet WHERE userid = $userid";
$coinResult = $conn->query($coinQuery);

if ($coinResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Wallet not found']);
    exit();
}

$wallet = $coinResult->fetch_assoc();
if ($wallet['coin_balance'] < 50) {
    echo json_encode(['success' => false, 'message' => 'Insufficient coins']);
    exit();
}

// Get student_id
$studentQuery = "SELECT student_id FROM tbl_student WHERE userid = $userid";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();
$student_id = (int)$student['student_id'];

// Get form data
$tutor_id = (int)$_POST['tutorId'];
$description = $conn->real_escape_string($_POST['subject']);

// Start transaction
$conn->begin_transaction();

try {
    // Insert tutor request
    $insertQuery = "INSERT INTO tbl_tutorrequest (student_id, tutor_id, description, feerate, status) 
                   VALUES ($student_id, $tutor_id, '$description', 0, 'created')";
    
    if (!$conn->query($insertQuery)) {
        throw new Exception("Error creating request: " . $conn->error);
    }
    
    // Deduct coins from wallet
    $updateWallet = "UPDATE tbl_coinwallet 
                    SET coin_balance = coin_balance - 50 
                    WHERE userid = $userid";
    
    if (!$conn->query($updateWallet)) {
        throw new Exception("Error updating wallet: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Request sent successfully! 50 coins deducted from your wallet.']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 