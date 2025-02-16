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

// Create tutors table
$sql = "CREATE TABLE IF NOT EXISTS tbl_tutors (
    tutor_id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT,
    mobile VARCHAR(20) UNIQUE NOT NULL,
    about TEXT,
    qualification ENUM('10th', '12th', 'UG', 'PG', 'PhD') NOT NULL,
    teaching_mode ENUM('Online', 'Offline', 'Both') NOT NULL,
    experience INT DEFAULT 0,
    hourly_rate DECIMAL(10,2) NOT NULL,
    profile_photo VARCHAR(255) NULL,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
)";
$conn->query($sql);

// Create locations table
$sql = "CREATE TABLE IF NOT EXISTS tbl_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT,
    pincode VARCHAR(10) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
)";
$conn->query($sql);
$sql="CREATE TABLE IF NOT EXISTS tbl_subject (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(255) NOT NULL
)
";
$conn->query($sql);
$sql="CREATE TABLE IF NOT EXISTS tbl_tutorsubject (
    tutorsubjectid INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    tutor_id INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES tbl_subject(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tbl_tutors(tutor_id) ON DELETE CASCADE
)
";
$conn->query($sql);
$sql="CREATE TABLE IF NOT EXISTS tbl_student (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    mode_of_learning ENUM('Online', 'Offline', 'Both') NOT NULL,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
)";
$conn->query($sql);
?>
