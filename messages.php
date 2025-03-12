<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
include 'connectdb.php';
$userid = $_SESSION['userid'];

// Get tutor_id
$tutor_query = $conn->prepare("SELECT tutor_id FROM tbl_tutors WHERE userid = ?");
$tutor_query->bind_param("i", $userid);
$tutor_query->execute();
$tutor_result = $tutor_query->get_result();
$tutor = $tutor_result->fetch_assoc();
$tutor_id = $tutor['tutor_id'];
$tutor_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - StudyConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Copy the relevant styles from teacherdashboard.php */
        :root {
            --accent-color: #8672ff;
            --base-color: white;
            --text-color: #2e2b41;
            --input-color: #f3f0ff;
            --error-color: #f06272;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: var(--text-color);
        }

        .messages-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--base-color);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .back-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .messages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .message-card {
            background: var(--base-color);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .message-card:hover {
            transform: translateY(-5px);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .message-preview {
            color: #666;
            margin: 1rem 0;
        }

        .message-time {
            color: #999;
            font-size: 0.9rem;
        }

        .view-chat-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            margin-top: 1rem;
        }

        .no-messages {
            text-align: center;
            padding: 3rem;
            background: var(--base-color);
            border-radius: 12px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="messages-container">
        <header class="header">
            <h1>Messages</h1>
            <a href="teacherdashboard.php" class="back-button">Back to Dashboard</a>
        </header>

        <div class="messages-grid">
            <?php
            // Fetch messages for this tutor
            $query = "SELECT DISTINCT m.*, s.student_id, s.profilephoto, u.username 
                     FROM tbl_messages m 
                     JOIN tbl_student s ON m.student_id = s.student_id 
                     JOIN users u ON s.userid = u.userid 
                     WHERE m.tutor_id = ? 
                     ORDER BY m.timestamp DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $tutor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $profileImage = $row['profilephoto'] ? $row['profilephoto'] : '1.webp';
                    ?>
                    <div class="message-card">
                        <div class="student-info">
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Student" class="student-avatar">
                            <div>
                                <h3><?php echo htmlspecialchars($row['username']); ?></h3>
                                <span class="message-time"><?php echo date('M d, Y H:i', strtotime($row['timestamp'])); ?></span>
                            </div>
                        </div>
                        <p class="message-preview"><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></p>
                        <button class="view-chat-btn" onclick="location.href='chat.php?student_id=<?php echo $row['student_id']; ?>'">
                            View Chat
                        </button>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-messages">
                        <h3>No Messages Yet</h3>
                        <p>When you connect with students, your messages will appear here.</p>
                      </div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</body>
</html> 