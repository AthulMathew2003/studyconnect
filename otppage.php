<?php
session_start();
include 'connectdb.php';
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $error = false;
        
        if (empty($email)) {
          $emailError = "Email is required";
          $error = true;
        }
        
        if (!$error) {
          // Verify email exists in database
          $email = mysqli_real_escape_string($conn, $email);
          
          $sql = "SELECT * FROM users WHERE email='$email'";
          $result = mysqli_query($conn, $sql);
          
          if (mysqli_num_rows($result) === 1) {
            // Store email in session
            $_SESSION['reset_email'] = $email;
            
            // Redirect to OTP sending page
            header("Location: send_otp.php");
            exit();
          } else {
            $emailError = "Email address not found in our records";
          }
        }
      }
    ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="v1.js" defer></script>
  </head>
  <body>
    <div class="wrapper">
      <h1>Forgot Password</h1>
      <?php if (isset($loginError)) echo "<p class='error-message'>$loginError</p>"; ?>
      <form id="form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input-group">
          <div class="input-wrapper">
            <label for="email-input">
              <span>@</span>
            </label>
            <input
              type="email"
              name="email"
              id="email-input"
              placeholder="Email"
            />
          </div>
          <p id="email-error" class="error-message">
            <?php if (isset($emailError)) echo $emailError; ?>
          </p>
        </div>
        <button type="submit">Send OTP</button>
      </form>
      <div class="login-footer">
        <p>Remember your password? <a href="login.php">Login</a></p>
      </div>
    </div>
  </body>
</html>