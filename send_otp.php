<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'PHPMailer-master\src\Exception.php';
require 'PHPMailer-master\src\PHPMailer.php';
require 'PHPMailer-master\src\SMTP.php';


function sendVerificationEmail($recipientEmail, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mathewathul03@gmail.com';  
        $mail->Password   = 'twvp urma derx nqcf';     
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('mathewathul03@gmail.com', 'name');
        $mail->addAddress($recipientEmail);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: $otp\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        echo "Failed to send email. Error: " . $mail->ErrorInfo;
        return false;
    }
}


try {
    session_start();

    // Check if email exists in session
    if (!isset($_SESSION['reset_email'])) {
        header("Location: otppage.php");
        exit();
    }

    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));

    // Store OTP and timestamp in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_timestamp'] = time(); // Store timestamp for expiry check

    $email = $_SESSION['reset_email'];
   

    if (sendVerificationEmail($email, $otp)) {
        header("Location: otpverification.php");
        exit();
    } else {
        echo "Failed to send verification code.";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>