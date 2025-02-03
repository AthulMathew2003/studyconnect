<?php
include 'connectdb.php';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css" />
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
        <button type="submit">Reset Password</button>
      </form>
      <div class="login-footer">
        <p>Remember your password? <a href="login.php">Login</a></p>
      </div>
    </div>
  </body>
</html>