<?php
$servername="localhost";
$username="root";
$password= "";
$db="studyconnect";
$conn=mysqli_connect($servername,$username,$password,$db);
if($conn->connect_error){
  die("connect failed".$conn->connect_error);
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    userid INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
// if ($conn->query($sql) === TRUE) {
//     echo "Users table created successfully";
// } else {
//     echo "Error creating table: " . $conn->error;
// }

// Create default admin user if not exists
$adminPassword = password_hash('123456', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, email, password, role) 
        VALUES ('admin', 'admin@gmail.com', '$adminPassword', 'admin')";
$conn->query($sql);

?>
