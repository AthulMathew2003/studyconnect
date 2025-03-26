<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$request_id = $_POST['request_id'] ?? '';
$status = $_POST['status'] ?? '';
$userid = $_SESSION['userid'];

// If approving, check and deduct coins
if ($status === 'approved' && isset($_POST['deduct_coins'])) {
    // Check current balance
    $check_balance = $conn->prepare("SELECT coin_balance FROM tbl_coinwallet WHERE userid = ?");
    $check_balance->bind_param("i", $userid);
    $check_balance->execute();
    $result = $check_balance->get_result();
    
    if ($result->num_rows > 0) {
        $current_balance = $result->fetch_assoc()['coin_balance'];
        
        if ($current_balance < 50) {
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient coins',
                'current_balance' => $current_balance
            ]);
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Deduct coins
            $update_balance = $conn->prepare("UPDATE tbl_coinwallet SET coin_balance = coin_balance - 50 WHERE userid = ?");
            $update_balance->bind_param("i", $userid);
            $update_balance->execute();
            
            // Update request status
            $update_request = $conn->prepare("UPDATE tbl_tutorrequest SET status = ? WHERE tutorrequestid = ?");
            $update_request->bind_param("si", $status, $request_id);
            $update_request->execute();
            
            $conn->commit();
            
            // Get new balance
            $check_balance->execute();
            $new_balance = $check_balance->get_result()->fetch_assoc()['coin_balance'];
            
            echo json_encode([
                'success' => true,
                'new_balance' => $new_balance
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No coin wallet found']);
    }
} else {
    // For rejection, simply update the status
    $update_request = $conn->prepare("UPDATE tbl_tutorrequest SET status = ? WHERE tutorrequestid = ?");
    $update_request->bind_param("si", $status, $request_id);
    
    if ($update_request->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
}

$conn->close();
?> 