<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['userid'])) {
    // Check if it's an AJAX request or direct form submission
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    } else {
        // For direct form submissions
        $_SESSION['error_message'] = 'You must be logged in to perform this action';
        header('Location: teacherdashboard.php');
    }
    exit;
}

$request_id = $_POST['request_id'] ?? '';
$status = $_POST['status'] ?? '';
$userid = $_SESSION['userid'];

// Get the referring page for later redirect
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$redirect_url = 'teacherdashboard.php';

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
            if ($is_ajax) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Insufficient coins',
                    'current_balance' => $current_balance
                ]);
            } else {
                $_SESSION['error_message'] = 'Insufficient coins. You need at least 50 coins to approve this request.';
                header("Location: $redirect_url");
            }
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
            
            // Add notification for student
            include_once 'notification_helper.php';
            
            // Get student info from the request
            $student_query = $conn->prepare("
                SELECT s.student_id, s.userid as student_userid, u.username as student_name,
                       t.userid as tutor_userid, ut.username as tutor_name 
                FROM tbl_tutorrequest tr
                JOIN tbl_student s ON tr.student_id = s.student_id
                JOIN users u ON s.userid = u.userid
                JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                JOIN users ut ON t.userid = ut.userid
                WHERE tr.tutorrequestid = ?
            ");
            $student_query->bind_param("i", $request_id);
            $student_query->execute();
            $student_result = $student_query->get_result();
            $request_data = $student_result->fetch_assoc();
            
            $student_userid = $request_data['student_userid'];
            $tutor_name = $request_data['tutor_name'];
            
            // Create notification for student
            $title = "Request Approved";
            $message = "$tutor_name has approved your tutoring request!";
            addNotification($conn, $student_userid, $title, $message, "request", $request_id);
            
            if ($is_ajax) {
                echo json_encode([
                    'success' => true,
                    'new_balance' => $new_balance
                ]);
            } else {
                $_SESSION['success_message'] = 'Request approved successfully!';
                header("Location: $redirect_url");
            }
        } catch (Exception $e) {
            $conn->rollback();
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Database error occurred']);
            } else {
                $_SESSION['error_message'] = 'Database error occurred. Please try again.';
                header("Location: $redirect_url");
            }
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'No coin wallet found']);
        } else {
            $_SESSION['error_message'] = 'Wallet not found. Please contact support.';
            header("Location: $redirect_url");
        }
    }
} else {
    // For rejection, simply update the status
    $update_request = $conn->prepare("UPDATE tbl_tutorrequest SET status = ? WHERE tutorrequestid = ?");
    $update_request->bind_param("si", $status, $request_id);
    
    if ($update_request->execute()) {
        // Add notification for student when request is rejected
        include_once 'notification_helper.php';
        
        // Get student info from the request
        $student_query = $conn->prepare("
            SELECT s.student_id, s.userid as student_userid, u.username as student_name,
                   t.userid as tutor_userid, ut.username as tutor_name 
            FROM tbl_tutorrequest tr
            JOIN tbl_student s ON tr.student_id = s.student_id
            JOIN users u ON s.userid = u.userid
            JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
            JOIN users ut ON t.userid = ut.userid
            WHERE tr.tutorrequestid = ?
        ");
        $student_query->bind_param("i", $request_id);
        $student_query->execute();
        $student_result = $student_query->get_result();
        $request_data = $student_result->fetch_assoc();
        
        $student_userid = $request_data['student_userid'];
        $tutor_name = $request_data['tutor_name'];
        
        // Create notification for student
        $title = "Request Declined";
        $message = "$tutor_name has declined your tutoring request.";
        addNotification($conn, $student_userid, $title, $message, "request", $request_id);
        
        if ($is_ajax) {
            echo json_encode(['success' => true]);
        } else {
            $_SESSION['success_message'] = 'Request declined successfully!';
            header("Location: $redirect_url");
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        } else {
            $_SESSION['error_message'] = 'Failed to update request status.';
            header("Location: $redirect_url");
        }
    }
}

$conn->close();
?> 