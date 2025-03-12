<?php
include 'connectdb.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <?php
    // Fetch student profile photos from database
    $query = "SELECT student_id, profilephoto FROM tbl_student";
    $result = mysqli_query($conn, $query);

    if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
        // Display each student's profile photo
        echo '<div class="student-photo">';
        if (!empty($row['profilephoto'])) {
          echo '<img src="' . htmlspecialchars($row['profilephoto']) . '" alt="Student ID: ' . $row['student_id'] . '" style="max-width: 200px;">';
        } else {
          echo '<p>No profile photo available</p>';
        }
        echo '</div>';
      }
    } else {
      echo "Error fetching profile photos: " . mysqli_error($conn);
    }
  ?>
</body>
</html>