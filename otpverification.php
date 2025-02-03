<?php
session_start();

// Only process form if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['otp']) && isset($_SESSION['otp_timestamp'])) {
        $entered_otp = $_POST['otp'];
        $stored_otp = $_SESSION['otp'];
        $otp_timestamp = $_SESSION['otp_timestamp'];
        
        // Check if OTP has expired (10 minutes = 600 seconds)
        if (time() - $otp_timestamp > 600) {
            $_SESSION['otpError'] = "OTP has expired. Please request a new one.";
            header("Location: otpverification.php");
            exit();
        }
        
        // Verify OTP
        if ($entered_otp === $stored_otp) {
            unset($_SESSION['otp']);
            unset($_SESSION['otp_timestamp']);
            header("Location: resetpassword.php");
            exit();
        } else {
            $_SESSION['otpError'] = "Invalid OTP. Please try again.";
            header("Location: otpverification.php");
            exit();
        }
    } else {
        $_SESSION['otpError'] = "Invalid request or session expired.";
        header("Location: otpverification.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification</title>
    <link rel="stylesheet" href="style.css" />
   
  </head>
  <body>
    <div class="wrapper">
      <h1>OTP Verification</h1>
      <?php 
        if (isset($_SESSION['otpError'])) {
            echo "<p class='error-message'>" . $_SESSION['otpError'] . "</p>";
            unset($_SESSION['otpError']);
        }
      ?>
 <form id="form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">        <div class="input-group">
          <div class="input-wrapper">
            <label for="otp-input">
              <span>#</span>
            </label>
            <input
              type="text"
              name="otp"
              id="otp-input"
              placeholder="Enter 6-digit OTP"
              maxlength="6"
              pattern="\d{6}"
              required
            />
          </div>
          <p id="otp-error" class="error-message">
            <?php if (isset($otpError)) echo $otpError; ?>
          </p>
        </div>
        <button type="submit">Verify OTP</button>
      </form>
      <div class="login-footer">
        <p><a href="send_otp.php">Resend OTP </a></p>
      </div>
    </div>
  </body>
</html>