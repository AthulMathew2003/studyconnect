<?php
// Add this at the top of reviewpage.php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// Get student_id for the current user
$userid = (int)$_SESSION['userid'];
$query = "SELECT student_id FROM tbl_student WHERE userid = $userid";
$result = $conn->query($query);

// If user isn't a student, redirect them
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$student = $result->fetch_assoc();
$student_id = (int)$student['student_id'];

// Check if tutor_id is provided in POST or GET
if (isset($_POST['tutor_id'])) {
    $tutor_id = (int)$_POST['tutor_id'];
} else {
    $tutor_id = isset($_GET['tutor_id']) ? (int)$_GET['tutor_id'] : 0;
}

// If no tutor_id or invalid, redirect back to dashboard
if ($tutor_id <= 0) {
    header("Location: studentdashboard.php");
    exit;
}

// Check if student has an approved connection with this tutor
$can_review = false;
$connection_check_query = "SELECT 1 FROM tbl_response 
                          WHERE tutor_id = $tutor_id 
                          AND request_id IN (SELECT request_id FROM tbl_request WHERE student_id = $student_id)
                          AND status = 'approved'
                          UNION
                          SELECT 1 FROM tbl_tutorrequest
                          WHERE tutor_id = $tutor_id
                          AND student_id = $student_id
                          AND status = 'approved'";
$connection_result = $conn->query($connection_check_query);
$can_review = $connection_result->num_rows > 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // First check if the student can leave a review
    if (!$can_review) {
        $error = "You can only review tutors you have connected with.";
        header("Location: reviewpage.php?tutor_id=$tutor_id&error=" . urlencode($error));
        exit;
    }

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
    
    // Validate the rating
    if ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5";
    } else {
        // Check if user has already reviewed this tutor
        $check_query = "SELECT review_id FROM tbl_review 
                       WHERE student_id = $student_id AND tutor_id = $tutor_id";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            // Update existing review
            $review = $check_result->fetch_assoc();
            $review_id = $review['review_id'];
            
            $update_query = "UPDATE tbl_review 
                           SET rating = ?, comment = ?, subject_id = ?, updated_at = NOW() 
                           WHERE review_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("isis", $rating, $comment, $subject_id, $review_id);
            
            if ($stmt->execute()) {
                $success = "Your review has been updated successfully!";
            } else {
                $error = "Error updating review: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Insert new review
            $insert_query = "INSERT INTO tbl_review (student_id, tutor_id, subject_id, rating, comment) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiiis", $student_id, $tutor_id, $subject_id, $rating, $comment);
            
            if ($stmt->execute()) {
                $success = "Your review has been submitted successfully!";
            } else {
                $error = "Error submitting review: " . $conn->error;
            }
            $stmt->close();
        }
    }
    
    // Refresh the page to show updated content
    header("Location: reviewpage.php?tutor_id=$tutor_id" . (isset($error) ? "&error=" . urlencode($error) : "") . 
           (isset($success) ? "&success=" . urlencode($success) : ""));
    exit;
}

// Check if user has already reviewed this tutor
$has_reviewed = false;
$user_review = null;

$user_review_query = "SELECT * FROM tbl_review 
                     WHERE student_id = $student_id AND tutor_id = $tutor_id";
$user_review_result = $conn->query($user_review_query);

if ($user_review_result->num_rows > 0) {
    $has_reviewed = true;
    $user_review = $user_review_result->fetch_assoc();
}

// Fetch tutor information
$tutor_query = "SELECT t.*, u.username, s.subject
               FROM tbl_tutors t
               JOIN users u ON t.userid = u.userid
               LEFT JOIN tbl_tutorsubject ts ON t.tutor_id = ts.tutor_id
               LEFT JOIN tbl_subject s ON ts.subject_id = s.subject_id
               WHERE t.tutor_id = $tutor_id";
$tutor_result = $conn->query($tutor_query);

if ($tutor_result->num_rows == 0) {
    header("Location: studentdashboard.php");
    exit;
}

$tutor = $tutor_result->fetch_assoc();
$profile_photo = $tutor['profile_photo'] ? 'uploads/profile_photos/' . $tutor['profile_photo'] : '1.webp';

// Get tutor's subjects
$subjects_query = "SELECT s.subject_id, s.subject 
                  FROM tbl_tutorsubject ts 
                  JOIN tbl_subject s ON ts.subject_id = s.subject_id 
                  WHERE ts.tutor_id = $tutor_id";
$subjects_result = $conn->query($subjects_query);

// Get average rating and review count
$avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                     FROM tbl_review 
                     WHERE tutor_id = $tutor_id";
$avg_rating_result = $conn->query($avg_rating_query);
$rating_data = $avg_rating_result->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'] * 2) / 2 : 0; // Round to nearest 0.5
$review_count = $rating_data['review_count'];

// Get reviews
$reviews_query = "SELECT r.*, u.username as student_name, s.subject 
                 FROM tbl_review r 
                 JOIN tbl_student st ON r.student_id = st.student_id 
                 JOIN users u ON st.userid = u.userid 
                 LEFT JOIN tbl_subject s ON r.subject_id = s.subject_id 
                 WHERE r.tutor_id = $tutor_id 
                 ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_query);

// Display error/success messages if they exist
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Tutor - StudyConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --accent-color: #8672ff;
            --base-color: white;
            --text-color: #2e2b41;
            --input-color: #f3f0ff;
            --error-color: #f06272;
            --success-color: #4CAF50;
            --border-radius: 12px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
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
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--base-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            background: var(--accent-color);
            color: white;
            padding: 20px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .review-content {
            padding: 30px;
        }

        .tutor-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background: var(--input-color);
            padding: 15px;
            border-radius: var(--border-radius);
        }

        .tutor-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid var(--accent-color);
        }

        .tutor-details h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .tutor-details p {
            font-size: 14px;
            color: #666;
        }

        .tutor-subject {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 5px;
        }

        .rating-section {
            margin-bottom: 25px;
        }

        .rating-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .stars {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .star {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            margin: 0 5px;
            transition: var(--transition);
        }

        .star:hover, .star.active {
            color: #FFD700;
        }

        .star:hover ~ .star {
            color: #ddd;
        }

        .rating-text {
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            color: var(--accent-color);
            height: 24px;
            margin-bottom: 10px;
        }

        .comment-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .comment-area {
            width: 100%;
            min-height: 120px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            font-size: 14px;
            resize: vertical;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .comment-area:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(134, 114, 255, 0.2);
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-cancel {
            background: #f1f1f1;
            color: #666;
        }

        .btn-submit {
            background: var(--accent-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-cancel:hover {
            background: #e5e5e5;
        }

        .btn-submit:hover {
            background: #7561ff;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .previous-review {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .previous-review h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #666;
        }

        .previous-stars {
            display: flex;
            margin-bottom: 10px;
        }

        .previous-stars .fa-star {
            color: #FFD700;
            font-size: 16px;
            margin-right: 3px;
        }

        .previous-comment {
            background: var(--input-color);
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
        }

        .review-date {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
            text-align: right;
        }
        
        /* New styles for the other reviews section */
        .other-reviews-section {
            margin-top: 40px;
            border-top: 2px dashed #eee;
            padding-top: 30px;
        }
        
        .other-reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .other-reviews-header h3 {
            font-size: 18px;
            color: var(--text-color);
        }
        
        .reviews-summary {
            display: flex;
            align-items: center;
            background: var(--input-color);
            padding: 10px 20px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        
        .average-rating {
            font-size: 28px;
            font-weight: 600;
            color: var(--accent-color);
            margin-right: 15px;
        }
        
        .rating-stars {
            display: flex;
            margin-right: 15px;
        }
        
        .rating-count {
            font-size: 14px;
            color: #666;
        }
        
        .review-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .review-item {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: #fafafa;
            border-bottom: 1px solid #eee;
        }
        
        .reviewer-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .reviewer-name {
            font-weight: 500;
            font-size: 14px;
            color: var(--text-color);
        }
        
        .reviewer-meta {
            margin-left: auto;
            font-size: 12px;
            color: #888;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }
        
        .review-rating .fa-star {
            color: #FFD700;
            font-size: 12px;
            margin-right: 2px;
        }
        
        .review-body {
            padding: 15px;
            font-size: 14px;
            color: #555;
            background: white;
        }
        
        .subject-tag {
            display: inline-block;
            font-size: 11px;
            color: var(--accent-color);
            background: var(--input-color);
            padding: 2px 8px;
            border-radius: 12px;
            margin-top: 8px;
        }
        
        .review-footer {
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #888;
        }
        
        .review-actions {
            display: flex;
            gap: 15px;
        }
        
        .review-action {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .review-action:hover {
            color: var(--accent-color);
        }
        
        .review-date-time {
            font-style: italic;
        }
        
        .load-more-btn {
            margin-top: 20px;
            padding: 10px;
            background: var(--input-color);
            border: none;
            border-radius: 8px;
            color: var(--accent-color);
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
        }
        
        .load-more-btn:hover {
            background: #e9e3ff;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            .header {
                padding: 15px;
            }
            
            .review-content {
                padding: 20px;
            }
            
            .tutor-avatar {
                width: 50px;
                height: 50px;
            }
            
            .star {
                font-size: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
            
            .other-reviews-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .reviews-summary {
                width: 100%;
                justify-content: center;
            }
        }

        /* Add these styles to your existing CSS */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .alert i {
            margin-right: 10px;
            font-size: 16px;
        }

        .alert-error {
            background-color: #ffe5e8;
            color: #e74c3c;
            border: 1px solid #f9c5c5;
        }

        .alert-success {
            background-color: #e7f9e7;
            color: #27ae60;
            border: 1px solid #c5e5c5;
        }

        .subject-section {
            margin-bottom: 25px;
        }

        .subject-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .subject-select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 14px;
            color: var(--text-color);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        .subject-select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(134, 114, 255, 0.2);
        }

        /* Add these styles to your existing CSS */
        .cannot-review-message {
            margin-bottom: 30px;
        }

        .alert-info {
            background-color: #e8f4fd;
            color: #3498db;
            border: 1px solid #c9e2f5;
            display: flex;
            align-items: flex-start;
            padding: 20px;
        }

        .alert-info i {
            font-size: 24px;
            margin-right: 15px;
            margin-top: 2px;
        }

        .alert-info h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #2980b9;
        }

        .alert-info p {
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .btn-connect-first {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-connect-first:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rate Your Tutor</h1>
            <p>Your feedback helps improve our tutoring services</p>
        </div>
        
        <div class="review-content">
            <div class="tutor-info">
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Tutor Avatar" class="tutor-avatar">
                <div class="tutor-details">
                    <h2><?php echo htmlspecialchars($tutor['username']); ?></h2>
                    <p><?php echo htmlspecialchars($tutor['qualification']); ?> • <?php echo htmlspecialchars($tutor['experience']); ?> years experience</p>
                    <span class="tutor-subject"><?php echo htmlspecialchars($tutor['subject'] ?? 'Multiple Subjects'); ?></span>
                </div>
            </div>
            
            <!-- Replace the form section with this conditional display -->
            <?php if ($can_review): ?>
                <!-- Add this where you want to display success/error messages -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Your existing form code -->
                <form method="POST" action="reviewpage.php?tutor_id=<?php echo $tutor_id; ?>" id="review-form">
                    <div class="rating-section">
                        <h3>How would you rate your experience?</h3>
                        <div class="stars" id="star-rating">
                            <i class="star fas fa-star <?php echo ($has_reviewed && $user_review['rating'] >= 1) ? 'active' : ''; ?>" data-rating="1"></i>
                            <i class="star fas fa-star <?php echo ($has_reviewed && $user_review['rating'] >= 2) ? 'active' : ''; ?>" data-rating="2"></i>
                            <i class="star fas fa-star <?php echo ($has_reviewed && $user_review['rating'] >= 3) ? 'active' : ''; ?>" data-rating="3"></i>
                            <i class="star fas fa-star <?php echo ($has_reviewed && $user_review['rating'] >= 4) ? 'active' : ''; ?>" data-rating="4"></i>
                            <i class="star fas fa-star <?php echo ($has_reviewed && $user_review['rating'] >= 5) ? 'active' : ''; ?>" data-rating="5"></i>
                        </div>
                        <div class="rating-text" id="rating-text">
                            <?php
                            if ($has_reviewed) {
                                $ratings = [
                                    '', 
                                    'Poor - Did not meet expectations',
                                    'Fair - Below average experience',
                                    'Good - Average tutoring experience',
                                    'Very Good - Above average experience',
                                    'Excellent - Outstanding tutoring experience'
                                ];
                                echo $ratings[$user_review['rating']];
                            }
                            ?>
                        </div>
                        <!-- Hidden input to store the rating value -->
                        <input type="hidden" name="rating" id="rating-input" value="<?php echo $has_reviewed ? $user_review['rating'] : ''; ?>">
                    </div>
                    
                    <!-- Rest of your existing form elements -->
                    <?php if ($subjects_result->num_rows > 0): ?>
                    <div class="subject-section">
                        <h3>Which subject was taught?</h3>
                        <select name="subject_id" class="subject-select">
                            <option value="">General Review (All Subjects)</option>
                            <?php 
                            // Reset the result pointer to the beginning
                            $subjects_result->data_seek(0);
                            while ($subject = $subjects_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $subject['subject_id']; ?>" 
                                    <?php echo ($has_reviewed && $user_review['subject_id'] == $subject['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['subject']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="comment-section">
                        <h3>Share your thoughts about this tutor</h3>
                        <textarea class="comment-area" name="comment" placeholder="Write your review here... What did you like? What could be improved?"><?php echo $has_reviewed ? htmlspecialchars($user_review['comment']) : ''; ?></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="studentdashboard.php" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-submit" id="submit-review" <?php echo $has_reviewed ? '' : 'disabled'; ?>>
                            <?php echo $has_reviewed ? 'Update Review' : 'Submit Review'; ?>
                        </button>
                    </div>
                </form>
                
                <!-- Display previous review if exists -->
                <?php if ($has_reviewed): ?>
                <div class="previous-review">
                    <h3>Your Previous Review</h3>
                    <div class="previous-stars">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $user_review['rating']) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <div class="previous-comment">
                        <?php echo nl2br(htmlspecialchars($user_review['comment'])); ?>
                    </div>
                    <div class="review-date">Submitted on <?php echo date('F j, Y', strtotime($user_review['created_at'])); ?></div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Message for students who can't review -->
                <div class="cannot-review-message">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <h3>Want to leave a review?</h3>
                            <p>You can only review tutors you have connected with. If you'd like to work with this tutor and then leave a review, please connect with them first.</p>
                            <a href="studentdashboard.php#resources" class="btn btn-connect-first">Connect with Tutor</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Other students' reviews section -->
            <div class="other-reviews-section">
                <div class="other-reviews-header">
                    <h3>Reviews from Other Students</h3>
                    <div class="reviews-summary">
                        <div class="average-rating"><?php echo number_format($avg_rating, 1); ?></div>
                        <div class="rating-stars">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avg_rating) {
                                    echo '<i class="fas fa-star" style="color:#FFD700"></i>';
                                } elseif ($i - 0.5 == $avg_rating) {
                                    echo '<i class="fas fa-star-half-alt" style="color:#FFD700"></i>';
                                } else {
                                    echo '<i class="far fa-star" style="color:#FFD700"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="rating-count">Based on <?php echo $review_count; ?> reviews</div>
                    </div>
                </div>
                
                <div class="review-list">
                    <?php
                    if ($reviews_result->num_rows > 0) {
                        while ($review = $reviews_result->fetch_assoc()) {
                            $review_date = date('F j, Y', strtotime($review['created_at']));
                            $student_name = $review['student_name'] ? $review['student_name'] : 'Anonymous Student';
                            $subject = $review['subject'] ? $review['subject'] : 'General';
                            ?>
                            <div class="review-item">
                                <div class="review-header">
                                   
                                    <div class="reviewer-name"><?php echo htmlspecialchars($student_name); ?></div>
                                    <div class="review-rating">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="rating-value"><?php echo $review['rating']; ?>.0</span>
                                    </div>
                                </div>
                                <div class="review-body">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    <div class="subject-tag"><?php echo htmlspecialchars($subject); ?></div>
                                </div>
                                <div class="review-footer">
                                    <div class="review-actions">
                                        <div class="review-action">
                                            <i class="far fa-thumbs-up"></i>
                                            <span><?php echo rand(0, 20); ?></span>
                                        </div>
                                        <div class="review-action">
                                            <i class="far fa-comment"></i>
                                            <span>Reply</span>
                                        </div>
                                    </div>
                                    <div class="review-date-time"><?php echo $review_date; ?></div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-reviews">No reviews yet for this tutor.</div>';
                    }
                    ?>
                </div>
                
                <button class="load-more-btn">Load More Reviews</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reviewForm = document.getElementById('review-form');
            
            // Only run the rating script if the form exists (user can review)
            if (reviewForm) {
                const stars = document.querySelectorAll('.star');
                const ratingText = document.getElementById('rating-text');
                const submitButton = document.getElementById('submit-review');
                const ratingInput = document.getElementById('rating-input');
                let selectedRating = <?php echo $has_reviewed ? $user_review['rating'] : 0; ?>;
                
                const ratingDescriptions = [
                    '', // No rating
                    'Poor - Did not meet expectations',
                    'Fair - Below average experience',
                    'Good - Average tutoring experience',
                    'Very Good - Above average experience',
                    'Excellent - Outstanding tutoring experience'
                ];
                
                // Handle star rating selection
                stars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const rating = this.getAttribute('data-rating');
                        highlightStars(rating);
                        ratingText.textContent = ratingDescriptions[rating];
                    });
                    
                    star.addEventListener('mouseout', function() {
                        highlightStars(selectedRating);
                        ratingText.textContent = selectedRating ? ratingDescriptions[selectedRating] : '';
                    });
                    
                    star.addEventListener('click', function() {
                        selectedRating = parseInt(this.getAttribute('data-rating'));
                        highlightStars(selectedRating);
                        ratingText.textContent = ratingDescriptions[selectedRating];
                        submitButton.disabled = false;
                        
                        // Set the hidden input value
                        ratingInput.value = selectedRating;
                    });
                });
                
                function highlightStars(rating) {
                    stars.forEach(star => {
                        const starRating = parseInt(star.getAttribute('data-rating'));
                        if (starRating <= rating) {
                            star.classList.add('active');
                        } else {
                            star.classList.remove('active');
                        }
                    });
                }
                
                // Ensure form can't be submitted without a rating
                reviewForm.addEventListener('submit', function(e) {
                    if (!ratingInput.value || parseInt(ratingInput.value) < 1) {
                        e.preventDefault();
                        alert('Please select a star rating before submitting your review.');
                    }
                });
            }
            
            // Load more button functionality
            const loadMoreBtn = document.querySelector('.load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    // This would load additional reviews in a real implementation
                    this.textContent = 'Loading...';
                    setTimeout(() => {
                        this.textContent = 'No More Reviews to Load';
                        this.disabled = true;
                        this.style.opacity = 0.6;
                    }, 1000);
                });
            }
            
            // Like functionality for reviews
            const likeButtons = document.querySelectorAll('.review-action');
            likeButtons.forEach(button => {
                if (button.querySelector('.fa-thumbs-up')) {
                    button.addEventListener('click', function() {
                        const likeCount = this.querySelector('span');
                        const thumbsIcon = this.querySelector('i');
                        if (thumbsIcon.classList.contains('far')) {
                            thumbsIcon.classList.replace('far', 'fas');
                            likeCount.textContent = parseInt(likeCount.textContent) + 1;
                        } else {
                            thumbsIcon.classList.replace('fas', 'far');
                            likeCount.textContent = parseInt(likeCount.textContent) - 1;
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
