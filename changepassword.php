<?php
session_start();
include 'connectdb.php';

// Check if reset_email session exists
if (!isset($_SESSION['email'])) {
    header("Location: confirmpassword.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $error = false;
    
    // Validate password
    if (empty($password)) {
        $passwordError = "Password is required";
        $error = true;
    } elseif (strlen($password) < 6) {
        $passwordError = "Password must be at least 6 characters";
        $error = true;
    }
    
    // Validate confirm password
    if (empty($confirm_password)) {
        $confirmPasswordError = "Confirm password is required";
        $error = true;
    } elseif ($password !== $confirm_password) {
        $confirmPasswordError = "Passwords do not match";
        $error = true;
    }
    
    if (!$error) {
        $email = $_SESSION['email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password='$hashed_password' WHERE email='$email'";
        if (mysqli_query($conn, $sql)) {
            // Destroy session
            session_unset();
            session_destroy();
            
            header("Location: login.php");
            exit();
        } else {
            $updateError = "Error updating password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="v2.js" defer></script>
  </head>
  <body>
    <div class="wrapper">
      <h1>Change Password</h1>
      <?php if (isset($updateError)) echo "<p class='error-message'>$updateError</p>"; ?>
      <form id="form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input-group">
          <div class="input-wrapper">
            <label for="password-input">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </label>
            <input
              type="password"
              name="password"
              id="password-input"
              placeholder="New Password"
            />
          </div>
          <p id="password-error" class="error-message">
            <?php if (isset($passwordError)) echo $passwordError; ?>
          </p>
        </div>
        <div class="input-group">
          <div class="input-wrapper">
            <label for="confirm-password-input">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </label>
            <input
              type="password"
              name="confirm_password"
              id="confirm-password-input"
              placeholder="Confirm Password"
            />
          </div>
          <p id="confirm-password-error" class="error-message">
            <?php if (isset($confirmPasswordError)) echo $confirmPasswordError; ?>
          </p>
        </div>
        <button type="submit">Change Password</button>
      </form>
      <div class="login-footer">
        <p>Dashboard <a href="admindashboard.php">Dashboard</a></p>
      </div>
    </div>
  </body>
</html>