<?php
session_start();
include 'connectdb.php';

// Check if email session exists
if (!isset($_SESSION['email'])) {
    header("Location: admindashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $error = false;
    
    // Validate current password
    if (empty($current_password)) {
        $currentPasswordError = "Current password is required";
        $error = true;
    } else {
        // Verify current password from database
        $email = $_SESSION['email'];
        $sql = "SELECT password FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if (!password_verify($current_password, $row['password'])) {
                $currentPasswordError = "Incorrect current password";
                $error = true;
            }
        } else {
            $currentPasswordError = "User not found";
            $error = true;
        }
    }
    
    if (!$error) {
        // Redirect to change password page if password is correct
        header("Location: changepassword.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Password</title>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="v2.js" defer></script>
  </head>
  <body>
    <div class="wrapper">
      <h1>Verify Current Password</h1>
      <form id="form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input-group">
          <div class="input-wrapper">
            <label for="current-password-input">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </label>
            <input
              type="password"
              name="current_password"
              id="current-password-input"
              placeholder="Enter Current Password"
            />
          </div>
          <p id="current-password-error" class="error-message">
            <?php if (isset($currentPasswordError)) echo $currentPasswordError; ?>
          </p>
        </div>
        <button type="submit">Verify Password</button>
      </form>
      <div class="login-footer">
        <p>Back to <a href="<?php echo isset($_SESSION['back_view']) ? $_SESSION['back_view'] : 'admindashboard.php'; ?>">dashboard</a></p>
      </div>
    </div>
  </body>
</html>