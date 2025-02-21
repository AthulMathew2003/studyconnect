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
<div class="requests-grid">
  <?php
  // Ensure connection is established
  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  $requestresult=$conn->query("SELECT * FROM tbl_request");
  while($request=$requestresult->fetch_assoc()){
    $studentid=$request['student_id'];
    $studentresult=$conn->query("SELECT * FROM tbl_student WHERE student_id=$studentid");
    $student=$studentresult->fetch_assoc();
    $userid=$student['userid'];
    $userresult=$conn->query("SELECT * FROM users WHERE userid=$userid");
    $user=$userresult->fetch_assoc(); 
    $locationresult=$conn->query("select * from tbl_studentlocation where student_id=$studentid");
    $location=$locationresult->fetch_assoc();
  
  // Optimized SQL query with JOINs
   // Fetch all requests
  

  if ($requestresult->num_rows >0) {
      while ($request = $requestresult->fetch_assoc()) {
          echo '<div class="request-card">';
          echo '<h3 class="student-name">' . htmlspecialchars($user['username']) . '</h3>';
          echo '<p class="requirements">' . htmlspecialchars($request['description']) . '</p>';
          echo '<div class="tags">';
          echo '<span class="tag">📚 ' . htmlspecialchars($request['subject']) . '</span>';
          echo '<span class="tag">💰 $' . htmlspecialchars($request['fee_rate']) . '/hour</span>';
          echo '<span class="tag">💻 ' . htmlspecialchars($request['mode_of_learning']) . '</span>';
          echo '</div>';
          echo '<div class="request-info">';
          echo '<strong>Location:</strong> <span>' . htmlspecialchars($location['city'] . ', ' . $location['state'] . ', ' . $location['country']) . '</span>';
          echo '<strong>Submitted:</strong> <span>' . htmlspecialchars($request['created_at']) . '</span>';
          echo '</div>';
          echo '<button class="connect-btn">Connect with Student</button>';
          echo '</div>';
      }
  } else {
      echo "<p>No student requests found.</p>";
  }
}
  ?>
</div>

</body>
</html>