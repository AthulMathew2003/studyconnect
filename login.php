<?php
session_start();
include 'connectdb.php';
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $error = false;
        
        if (empty($email)) {
          $emailError = "Email is required";
          $error = true;
        }
        
        if (empty($password)) {
          $passwordError = "Password is required";
          $error = true;
        }
        
        if (!$error) {
          // Verify user credentials from database
          $email = mysqli_real_escape_string($conn, $email);
          
          // First get the user by email only
          $sql = "SELECT * FROM users WHERE email='$email'";
          $result = mysqli_query($conn, $sql);
          
          if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            // Verify password using secure hash comparison
            if (password_verify($password, $user['password'])) {
              $_SESSION['userid'] = $user['userid'];
              $_SESSION['username'] = $user['username'];
              $_SESSION['user_role'] = $user['role'];
              $_SESSION['email'] = $user['email'];
              // Redirect based on user role
              if ($user['role'] === 'admin') {
                header("Location: admindashboard.php");
              } else if ($user['role'] === 'teacher') {
                header("Location: teacherdashboard.php");
              } else {
                header("Location: studentdashboard.php");
              }
              exit();
            } else {
              $loginError = "Invalid email or password";
            }
          } else {
            $loginError = "Invalid email or password";
          }
        }
      }
    ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="v1.js" defer></script>
  </head>
  <body>
    <div class="wrapper">
      <h2 class="site-header">StudyConnect</h2>
      <h1>Login</h1>
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
        <div class="input-group">
          <div class="input-wrapper">
            <label for="password-input">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                height="24"
                viewBox="0 -960 960 960"
                width="24"
              >
                <path
                  d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"
                />
              </svg>
            </label>
            <input
              type="password"
              name="password"
              id="password-input"
              placeholder="Password"
            />
          </div>
          <p id="password-error" class="error-message">
            <?php if (isset($passwordError)) echo $passwordError; ?>
          </p>
        </div>
        <button type="submit">Login</button>
      </form>
      <div class="login-footer">
        <a href="otppage.php" class="forgot-password">Forgot Password?</a>
        <p>New here? <a href="signup.php">Create an Account</a></p>
      </div>
    </div>
  </body>
</html>