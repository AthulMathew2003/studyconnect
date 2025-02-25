<?php
include 'connectdb.php';
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // name validastion
    $firstname = trim($_POST['firstname']);
    if (empty($firstname)) {
        $errors['firstname'] = "Firstname is required";
    } elseif (strlen($firstname) < 2) {
        $errors['firstname'] = "Firstname must be at least 2 characters";
    }
    
    // email validation
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address";
    } else {
        // Check if email already exists in database
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $check_query = "SELECT email FROM users WHERE email = '$email_escaped'";
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = "This email is already registered";
        }
        mysqli_free_result($result);
    }
    
    // password validation
    $password = $_POST['password'];
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }
    
    // confirm password
    $repeat_password = $_POST['repeat-password'];
    if ($password !== $repeat_password) {
        $errors['repeat-password'] = "Passwords do not match";
    }
    
    // role validation
    $role = $_POST['role'] ?? '';
    if (empty($role)) {
        $errors['role'] = "Please select a role";
    } elseif (!in_array($role, ['student', 'teacher'])) {
        $errors['role'] = "Invalid role selected";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and execute database insertion
        $firstname_escaped = mysqli_real_escape_string($conn, $firstname);
        $insert_query = "INSERT INTO users (username, email, password, role) 
                        VALUES ('$firstname_escaped', '$email_escaped', '$hash_password', '$role')";
        
        if (mysqli_query($conn, $insert_query)) {
            // Successful registration
            
            // Insert initial coin wallet entry
            $user_id = mysqli_insert_id($conn); // Get the last inserted user ID
            $insert_wallet_query = "INSERT INTO tbl_coinwallet (userid, coin_balance) VALUES ('$user_id', 0)";
            mysqli_query($conn, $insert_wallet_query); // Execute the wallet insertion
            
            header("Location: login.php");
            exit();
        } else {
            $errors['db_error'] = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Signup</title>
   <link rel="stylesheet" href="style.css">
   <script type="text/javascript" src="v1.js" defer></script>
</head>
<body>
   <div class="wrapper">
     <h2 class="site-header">StudyConnect</h2>
     <h1>Signup</h1>
     <form id="form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
       <div class="input-group">
         <div class="input-wrapper">
           <label for="firstname-input">
             <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/></svg>
           </label>
           <input type="text" name="firstname" id="firstname-input" placeholder="Firstname">
         </div>
         <p id="firstname-error" class="error-message">
           <?php if (isset($errors['firstname'])) echo $errors['firstname']; ?>
         </p>
       </div>
       <div class="input-group">
         <div class="input-wrapper">
           <label for="email-input">
             <span>@</span>
           </label>
           <input type="email" name="email" id="email-input" placeholder="Email">
         </div>
         <p id="email-error" class="error-message">
           <?php if (isset($errors['email'])) echo $errors['email']; ?>
         </p>
       </div>
       <div class="input-group">
         <div class="input-wrapper">
           <label for="password-input">
             <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
           </label>
           <input type="password" name="password" id="password-input" placeholder="Password">
         </div>
         <p id="password-error" class="error-message">
           <?php if (isset($errors['password'])) echo $errors['password']; ?>
         </p>
       </div>
       <div class="input-group">
         <div class="input-wrapper">
           <label for="repeat-password-input">
             <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
           </label>
           <input type="password" name="repeat-password" id="repeat-password-input" placeholder="Repeat Password">
         </div>
         <p id="repeat-password-error" class="error-message">
           <?php if (isset($errors['repeat-password'])) echo $errors['repeat-password']; ?>
         </p>
       </div>
       <div class="input-group">
         <div class="input-wrapper">
           <label for="role-input">
             <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-120 200-272v-240L40-600l440-240 440 240v320h-80v-276l-80 44v240L480-120Zm0-332 274-148-274-148-274 148 274 148Zm0 241 200-108v-151L480-360 280-470v151l200 108Zm0-241Zm0 90Zm0 0Z"/></svg>
           </label>
           <select name="role" id="role-input" placeholder="Select Role" style="background-color: var(--input-color);">
             <option value="" disabled selected>Select Role</option>
             <option value="student">Student</option>
             <option value="teacher">Teacher</option>
           </select>
         </div>
         <p id="role-error" class="error-message">
           <?php if (isset($errors['role'])) echo $errors['role']; ?>
         </p>
       </div>
       <button type="submit">Signup</button>
     </form>
     <p>Already have an Account? <a href="login.php">login</a> </p>
   </div>

</body>
</html>