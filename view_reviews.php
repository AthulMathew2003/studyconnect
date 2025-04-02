<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Verify the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: teacherdashboard.php');
    exit();
}

// Get tutor_id from POST data
$tutor_id = isset($_POST['tutor_id']) ? (int)$_POST['tutor_id'] : 0;

// Verify tutor_id is valid
if ($tutor_id <= 0) {
    header('Location: teacherdashboard.php');
    exit();
}

// Fetch all reviews for this tutor
$review_query = $conn->prepare("
    SELECT r.*, u.username, s.subject
    FROM tbl_review r
    JOIN tbl_student st ON r.student_id = st.student_id
    JOIN users u ON st.userid = u.userid
    LEFT JOIN tbl_subject s ON r.subject_id = s.subject_id
    WHERE r.tutor_id = ?
    ORDER BY r.created_at DESC
");

$review_query->bind_param("i", $tutor_id);
$review_query->execute();
$reviews = $review_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyConnect - Reviews</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-color: #8672FF;
            --text-color: #2E2B41;
            --background-color: #F8F9FA;
            --card-background: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .header {
            background: var(--card-background);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .logo {
            color: var(--accent-color);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .back-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .back-button:hover {
            opacity: 0.9;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .reviews-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reviews-header h1 {
            color: var(--text-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .reviews-grid {
            display: grid;
            gap: 1.5rem;
        }

        .review-card {
            background: var(--card-background);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
        }

        .student-name {
            font-weight: 500;
        }

        .review-date {
            color: #666;
            font-size: 0.9rem;
        }

        .review-rating {
            color: gold;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .review-subject {
            display: inline-block;
            background: #F0EDFF;
            color: var(--accent-color);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .review-text {
            color: #444;
            line-height: 1.5;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .no-reviews h2 {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .no-reviews p {
            color: #666;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">StudyConnect</div>
        <form action="teacherdashboard.php" method="post">
            <button type="submit" class="back-button">Back to Dashboard</button>
        </form>
    </header>

    <div class="container">
        <div class="reviews-header">
            <h1>Your Reviews</h1>
            <?php
            $avg_query = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM tbl_review WHERE tutor_id = ?");
            $avg_query->bind_param("i", $tutor_id);
            $avg_query->execute();
            $avg_result = $avg_query->get_result()->fetch_assoc();
            $avg_rating = number_format($avg_result['avg_rating'], 1);
            $total_reviews = $avg_result['total_reviews'];
            ?>
            <p>Average Rating: <?php echo str_repeat('⭐', round($avg_rating)); ?> (<?php echo $avg_rating; ?>) from <?php echo $total_reviews; ?> reviews</p>
        </div>

        <div class="reviews-grid">
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="student-info">
                                <div class="student-avatar">
                                    <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="student-name"><?php echo htmlspecialchars($review['username']); ?></div>
                                    <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php echo str_repeat('⭐', $review['rating']); ?>
                        </div>
                        <?php if ($review['subject']): ?>
                            <div class="review-subject">
                                <?php echo htmlspecialchars($review['subject']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="review-text">
                            <?php echo htmlspecialchars($review['comment']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <h2>No Reviews Yet</h2>
                    <p>Once students review your tutoring sessions, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 