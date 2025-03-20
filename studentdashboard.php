<?php
session_start();
require_once 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Check if user exists in tbl_student
$userid = (int)$_SESSION['userid'];
$stmt = $conn->prepare("SELECT student_id FROM tbl_student WHERE userid = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found in tbl_student, redirect to profile page
    header('Location: studentprofile.php');
    exit();
}
$stmt->close();
$_SESSION['back_view'] = 'studentdashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyConnect - Student Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="studentdash.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>StudyConnect</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active" data-view="overview">
                    <i class="fas fa-home"></i>
                    <span>Overview</span>
                </a>
                <a href="#" class="nav-item" data-view="courses">
                    <i class="fas fa-book"></i>
                    <span>Post a Requirement</span>
                </a>
                <a href="#" class="nav-item" data-view="assignments">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>My Tutors</span>
                </a>
                <a href="#" class="nav-item" data-view="forums">
                    <i class="fas fa-comments"></i>
                    <span>Teaching Requests</span>
                </a>
                <a href="#" class="nav-item" data-view="calendar">
                    <i class="fas fa-calendar"></i>
                    <span>Calendar</span>
                </a>
                <a href="#" class="nav-item" data-view="resources">
                    <i class="fas fa-folder"></i>
                    <span>Resources</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search courses, assignments...">
                </div>
                <div class="nav-right">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-profile">
                        <svg class="profile-icon" viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        
                        <div class="profile-dropdown" id="profileDropdown">
                            <a href="studentprofile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="confirmpassword.php"><i class="fas fa-cog"></i> Forgot Password</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Views -->
            <div class="dashboard-view" id="overview">
                <div class="welcome-section">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p>Here's what's happening in your courses today</p>
                </div>

                <div class="dashboard-grid">
                    <!-- Progress Overview -->
                    <div class="dashboard-card progress-card">
                        <h3>Learning Progress</h3>
                        <canvas id="progressChart"></canvas>
                    </div>

                    <!-- Upcoming Assignments -->
                    <div class="dashboard-card assignments-card">
                        <h3>Upcoming Assignments</h3>
                        <div class="assignment-list">
                            <div class="assignment-item">
                                <div class="assignment-info">
                                    <h4>Web Development Basics</h4>
                                    <p>Due: Feb 20, 2025</p>
                                </div>
                                <span class="assignment-status pending">Pending</span>
                            </div>
                            <div class="assignment-item">
                                <div class="assignment-info">
                                    <h4>Database Design Project</h4>
                                    <p>Due: Feb 22, 2025</p>
                                </div>
                                <span class="assignment-status pending">Pending</span>
                            </div>
                            <div class="assignment-item">
                                <div class="assignment-info">
                                    <h4>UI/UX Research Paper</h4>
                                    <p>Due: Feb 25, 2025</p>
                                </div>
                                <span class="assignment-status pending">Pending</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-card activity-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <i class="fas fa-book-reader activity-icon"></i>
                                <div class="activity-details">
                                    <p>Completed Chapter 5 in Web Development</p>
                                    <span>2 hours ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-comment activity-icon"></i>
                                <div class="activity-details">
                                    <p>Posted in Discussion Forum</p>
                                    <span>Yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Recommendations -->
                    <div class="dashboard-card recommendations-card">
                        <h3>Recommended for You</h3>
                        <div class="course-recommendations">
                            <div class="recommended-course">
                                <img src="assets/course1.jpg" alt="Course">
                                <div class="course-info">
                                    <h4>Advanced Web Development</h4>
                                    <p>Master modern web technologies and frameworks</p>
                                    <button class="enroll-btn">Enroll Now</button>
                                </div>
                            </div>
                            <div class="recommended-course">
                                <img src="assets/course2.jpg" alt="Course">
                                <div class="course-info">
                                    <h4>Mobile App Development</h4>
                                    <p>Learn to build cross-platform mobile applications</p>
                                    <button class="enroll-btn">Enroll Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-view" id="courses" style="display: none;">
                <header class="header">
                    <div class="header-title">
                        <h1>Teacher Requests</h1>
                        <span class="header-subtitle">Manage and track your learning journey</span>
                    </div>
                    <button class="new-request-btn" onclick="openRequestModal()">
                        <span class="btn-icon">+</span>
                        <span class="btn-text">New Request</span>
                        <span class="btn-hover-effect"></span>
                    </button>
                </header>

                <!-- Add this modal form HTML -->
                <div id="requestModal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal" onclick="closeRequestModal()">&times;</span>
                        <h2>New Teacher Request</h2>
                        <form id="newRequestForm" onsubmit="submitRequest(event)">
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <select id="subject" name="subject" required>
                                    <option value="" disabled selected>Select a subject</option>
                                    <?php
                                    $query = "SELECT subject_id, subject FROM tbl_subject";
                                    $result = $conn->query($query);
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['subject']) . "'>" . 
                                             htmlspecialchars($row['subject']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="learningMode">Learning Mode:</label>
                                <select id="learningMode" name="learningMode" required>
                                <option value="" disabled selected>Select a Learning Mode</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="budget">Budget (per hour):</label>
                                <input type="number" id="budget" name="budget" min="1" required>
                                <span id="budgetError" style="color: red; display: none;">Please enter a positive budget.</span>
                                <span id="budgetLimitError" style="color: red; display: none;">Budget cannot exceed $1000.</span>
                            </div>

                            <div class="form-group">
                                <label for="startDate">Start Date:</label>
                                <input type="date" id="startDate" name="startDate" required>
                                <span id="startDateError" style="color: red; display: none;">Start date cannot be in the past.</span>
                            </div>

                            <div class="form-group">
                                <label for="endDate">End Date:</label>
                                <input type="date" id="endDate" name="endDate" required>
                                <span id="endDateError" style="color: red; display: none;">End date cannot be earlier than start date.</span>
                            </div>

                            <div class="form-group">
                                <label for="details">Additional Details:</label>
                                <textarea id="details" name="details" rows="4" required></textarea>
                            </div>

                            <div class="form-actions" style="display: flex; justify-content: space-between;">
                                <button type="button" onclick="closeRequestModal()" class="cancel-btn">Cancel</button>
                                <button type="submit" class="submit-btn">Submit Request</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="requests-grid" id="requestsContainer">
                    <?php
                    // Get student_id for the current user
                    $stmt = $conn->prepare("SELECT student_id FROM tbl_student WHERE userid = ?");
                    $stmt->bind_param("i", $_SESSION['userid']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $student = $result->fetch_assoc();
                    $student_id = $student['student_id'];
                    $stmt->close();

                    // Fetch requests for this student along with user information
                    $stmt = $conn->prepare("
                        SELECT r.*, u.username 
                        FROM tbl_request r
                        INNER JOIN tbl_student st ON r.student_id = st.student_id
                        INNER JOIN users u ON st.userid = u.userid
                        WHERE r.student_id = ?
                        ORDER BY r.created_at DESC
                    ");
                    $stmt->bind_param("i", $student_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="request-card">
                                <div class="card-header">
                                    <div class="header-left">
                                        <span class="request-id">REQ-<?php echo htmlspecialchars($row['request_id']); ?></span>
                                        <div class="status-badge">
                                            <span class="status-dot"></span>
                                            <span style="color: #88d3ce;"><?php echo htmlspecialchars($row['status']); ?></span>
                                        </div>
                                    </div>
                                    <div class="header-actions">
                                        
                                        <button class="action-btn delete" data-id="<?php echo $row['request_id']; ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="info-grid" id="infoGrid">
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-user"></i> Student Name</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['username']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-book"></i> Subject</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['subject']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-video"></i> Learning Mode</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['mode_of_learning']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-dollar-sign"></i> Budget</span>
                                        <span class="info-value">$<?php echo htmlspecialchars($row['fee_rate']); ?>/hour</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Start Date</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($row['start_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-calendar-alt"></i> End Date</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($row['end_date'])); ?></span>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <div class="details-title" style="color: #88d3ce;"><i class="fas fa-info-circle"></i> Additional Details</div>
                                    <p class="details-content" style="font-weight: 600;">
                                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                                    </p>
                                </div>

                                <div class="timestamp">
                                    <i class="fas fa-calendar-alt"></i>
                                    Submitted on <?php echo date('M d, Y \a\t h:i A', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-requests">No requests found. Create a new request to get started!</div>';
                    }
                    $stmt->close();
                    ?>
                </div>
            </div>

            <div class="dashboard-view" id="assignments" style="display: none;">
                <h1>My Tutors</h1>
                <div class="tutor-grid">
                    <?php
                    // Get student_id for the current user
                    $userid = (int)$_SESSION['userid'];
                    $query = "SELECT student_id FROM tbl_student WHERE userid = $userid";
                    $result = $conn->query($query);
                    $student = $result->fetch_assoc();
                    $student_id = (int)$student['student_id'];

                    // First query: Fetch approved responses (existing tutors)
                    $query1 = "
                        SELECT r.*, req.subject, req.description as request_description, req.fee_rate,
                               t.*, u.username as tutor_name, u.email as tutor_email, u.userid as tutor_userid,
                               l.city, l.state,
                               GROUP_CONCAT(s.subject) as subjects
                        FROM tbl_response r
                        INNER JOIN tbl_request req ON r.request_id = req.request_id
                        INNER JOIN tbl_tutors t ON r.tutor_id = t.tutor_id
                        INNER JOIN users u ON t.userid = u.userid
                        LEFT JOIN tbl_locations l ON u.userid = l.userid
                        LEFT JOIN tbl_tutorsubject ts ON t.tutor_id = ts.tutor_id
                        LEFT JOIN tbl_subject s ON ts.subject_id = s.subject_id
                        WHERE req.student_id = $student_id 
                        AND r.status = 'approved'
                        GROUP BY r.response_id
                        ORDER BY r.created_at DESC
                    ";
                    
                    // Second query: Fetch approved tutor requests
                    $query2 = "
                        SELECT tr.*, t.*, u.username as tutor_name, u.email as tutor_email, 
                               u.userid as tutor_userid, l.city, l.state,
                               GROUP_CONCAT(s.subject) as subjects
                        FROM tbl_tutorrequest tr
                        INNER JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                        INNER JOIN users u ON t.userid = u.userid
                        LEFT JOIN tbl_locations l ON u.userid = l.userid
                        LEFT JOIN tbl_tutorsubject ts ON t.tutor_id = ts.tutor_id
                        LEFT JOIN tbl_subject s ON ts.subject_id = s.subject_id
                        WHERE tr.student_id = $student_id 
                        AND tr.status = 'approved'
                        GROUP BY tr.tutorrequestid
                        ORDER BY tr.created_at DESC
                    ";

                    $result1 = $conn->query($query1);
                    $result2 = $conn->query($query2);

                    if (($result1 && $result1->num_rows > 0) || ($result2 && $result2->num_rows > 0)) {
                        // Display tutors from approved responses
                        if ($result1 && $result1->num_rows > 0) {
                            echo '<h2 class="section-title">Tutors from Your Requests</h2>';
                            while ($row = $result1->fetch_assoc()) {
                                $profile_photo = $row['profile_photo'] ? 'uploads/profile_photos/' . $row['profile_photo'] : 'assets/default-profile.png';
                                ?>
                                <div class="tutor-card response-card">
                                    <div class="card-type-badge">Request Response</div>
                                    <div class="tutor-header">
                                        <div class="tutor-photo">
                                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Tutor Photo">
                                        </div>
                                        <div class="tutor-info">
                                            <h3><?php echo htmlspecialchars($row['tutor_name']); ?></h3>
                                            <p class="location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="tutor-details-grid">
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Qualification</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['qualification']); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Subject</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['subject']); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Experience</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['experience']); ?> years</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Agreed Rate</span>
                                                <span class="detail-value">$<?php echo htmlspecialchars($row['fee_rate']); ?>/hour</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Standard Rate</span>
                                                <span class="detail-value">$<?php echo htmlspecialchars($row['hourly_rate']); ?>/hour</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tutor-about">
                                        <p><strong>Request Description:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($row['request_description'])); ?></p>
                                        <p><strong>Tutor Response:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                                    </div>

                                    <div class="tutor-actions">
                                        <button class="message-btn" onclick="startChat(<?php echo $row['tutor_id']; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <form action="display_teachprofile.php" method="POST" style="flex: 1;">
                                            <input type="hidden" name="tutor_userid" value="<?php echo $row['tutor_userid']; ?>">
                                            <button type="submit" class="profile-btn">
                                                <i class="fas fa-user"></i> View Profile
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                        }

                        // Display tutors from direct requests
                        if ($result2 && $result2->num_rows > 0) {
                            echo '<h2 class="section-title">Direct Tutor Connections</h2>';
                            while ($row = $result2->fetch_assoc()) {
                                $profile_photo = $row['profile_photo'] ? 'uploads/profile_photos/' . $row['profile_photo'] : 'assets/default-profile.png';
                                ?>
                                <div class="tutor-card direct-request-card">
                                    <div class="card-type-badge">Direct Request</div>
                                    <div class="tutor-header">
                                        <div class="tutor-photo">
                                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Tutor Photo">
                                        </div>
                                        <div class="tutor-info">
                                            <h3><?php echo htmlspecialchars($row['tutor_name']); ?></h3>
                                            <p class="location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="tutor-details-grid">
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Qualification</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['qualification']); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Subjects</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['subjects']); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Experience</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($row['experience']); ?> years</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Agreed Rate</span>
                                                <span class="detail-value">$<?php echo htmlspecialchars($row['feerate']); ?>/hour</span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Standard Rate</span>
                                                <span class="detail-value">$<?php echo htmlspecialchars($row['hourly_rate']); ?>/hour</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tutor-about">
                                        <p><strong>Request Description:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                    </div>

                                    <div class="tutor-actions">
                                        <button class="message-btn" onclick="startChat(<?php echo $row['tutor_id']; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <form action="display_teachprofile.php" method="POST" style="flex: 1;">
                                            <input type="hidden" name="tutor_userid" value="<?php echo $row['tutor_userid']; ?>">
                                            <button type="submit" class="profile-btn">
                                                <i class="fas fa-user"></i> View Profile
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    } else {
                        echo '<div class="no-tutors">No approved tutors found yet.</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="dashboard-view" id="forums" style="display: none;">
                <?php
                // Process response status update
                if (isset($_POST['response_id']) && isset($_POST['status'])) {
                    $response_id = (int)$_POST['response_id'];
                    $status = $_POST['status'];
                    
                    // Update the response status
                    $update_sql = "UPDATE tbl_response SET status = '$status' WHERE response_id = $response_id";
                    if ($conn->query($update_sql)) {
                        echo "<script>showSuccessMessage('Response " . ucfirst($status) . " successfully!');</script>";
                    } else {
                        echo "<script>alert('Error updating response status');</script>";
                    }
                }
                ?>
                
                <h1>Teaching Requests</h1>
                <br>
                <div class="forums-grid">
                    <?php
                    // Get student_id for the current user
                    $userid = (int)$_SESSION['userid']; // Cast to integer for safety
                    $query = "SELECT student_id FROM tbl_student WHERE userid = $userid";
                    $result = $conn->query($query);
                    $student = $result->fetch_assoc();
                    $student_id = (int)$student['student_id'];

                    // Fetch responses along with request and tutor details
                    $query = "
                        SELECT r.*, req.subject, req.description as request_description,
                               t.tutor_id, u.username as tutor_name,
                               req.request_id, req.fee_rate,
                               CASE r.status 
                                   WHEN 'pending' THEN 1
                                   WHEN 'approved' THEN 2
                                   WHEN 'rejected' THEN 3
                               END as status_order
                        FROM tbl_response r
                        INNER JOIN tbl_request req ON r.request_id = req.request_id
                        INNER JOIN tbl_tutors t ON r.tutor_id = t.tutor_id
                        INNER JOIN users u ON t.userid = u.userid
                        WHERE req.student_id = $student_id
                        ORDER BY status_order, r.created_at DESC
                    ";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = strtolower($row['status']);
                            ?>
                            <div class="message-card">
                                <div class="message-header">
                                    <div class="tutor-info">
                                        <i class="fas fa-user-circle"></i>
                                        <span class="tutor-name"><?php echo htmlspecialchars($row['tutor_name']); ?></span>
                                    </div>
                                    <div class="response-status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </div>
                                </div>

                                <div class="message-content">
                                    <div class="request-details">
                                        <h4>Request Details</h4>
                                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?></p>
                                        <p><strong>Budget:</strong> $<?php echo htmlspecialchars($row['fee_rate']); ?>/hour</p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['request_description']); ?></p>
                                    </div>

                                    <div class="response-details">
                                        <h4>Tutor's Response</h4>
                                        <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                                    </div>
                                </div>

                                <div class="message-footer">
                                    <span class="timestamp">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                                    </span>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <div class="action-buttons">
                                            <button class="approve-btn" onclick="updateResponseStatus(<?php echo $row['response_id']; ?>, 'approved')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="reject-btn" onclick="updateResponseStatus(<?php echo $row['response_id']; ?>, 'rejected')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-messages">No responses received yet.</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="dashboard-view" id="calendar" style="display: none;">
                <h1>Coins</h1>
                <div class="calendar-container">
                    <!-- Add your calendar content here -->
                </div>
            </div>

            <div class="dashboard-view" id="resources" style="display: none;">
                <h1>Available Teachers</h1>
                
                <!-- Add filter controls -->
                 <div class="filter-controls">
                    <div class="filter-wrapper">
                        <div class="filter-group">
                            <i class="fas fa-book-open filter-icon"></i>
                            <select id="subjectFilter">
                                <option value="">All Subjects</option>
                                <?php
                                $sql = "SELECT DISTINCT subject FROM tbl_subject ORDER BY subject";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['subject']) . "'>" . 
                                         htmlspecialchars($row['subject']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <i class="fas fa-chalkboard-teacher filter-icon"></i>
                            <select id="teachingModeFilter">
                                <option value="">All Modes</option>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="resources-grid">
                    <?php
                    // Get student_id for the current user
                    $userid = (int)$_SESSION['userid'];
                    $query = "SELECT student_id FROM tbl_student WHERE userid = $userid";
                    $result = $conn->query($query);
                    $student = $result->fetch_assoc();
                    $student_id = (int)$student['student_id'];

                    // Modified query to exclude tutors with approved requests
                    $sql = "SELECT u.username, u.email, l.pincode, l.city, l.state, l.country, 
                            t.tutor_id, t.qualification, t.about, t.teaching_mode, t.experience, t.profile_photo, t.hourly_rate,
                            GROUP_CONCAT(DISTINCT s.subject) as subjects,
                            (SELECT COUNT(*) FROM tbl_tutorrequest tr 
                             WHERE tr.tutor_id = t.tutor_id 
                             AND tr.student_id = $student_id
                             AND (tr.status = 'created' OR tr.status = 'approved')) as request_exists
                            FROM users u 
                            JOIN tbl_tutors t ON u.userid = t.userid 
                            JOIN tbl_locations l ON u.userid = l.userid 
                            LEFT JOIN tbl_tutorsubject ts ON t.tutor_id = ts.tutor_id
                            LEFT JOIN tbl_subject s ON ts.subject_id = s.subject_id
                            WHERE u.role = 'teacher'
                            AND t.tutor_id NOT IN (
                                SELECT tr.tutor_id 
                                FROM tbl_tutorrequest tr 
                                WHERE tr.student_id = $student_id 
                                AND tr.status = 'approved'
                            )
                            GROUP BY t.tutor_id";
                    
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $profile_photo = $row['profile_photo'] ? 'uploads/profile_photos/' . $row['profile_photo'] : 'assets/default-profile.png';
                            $subjects = explode(',', $row['subjects']);
                            ?>
                            <div class="teacher-resource-card" 
                                 data-subjects="<?php echo htmlspecialchars($row['subjects']); ?>"
                                 data-teaching-mode="<?php echo htmlspecialchars($row['teaching_mode']); ?>">
                                <div class="teacher-header">
                                    <div class="teacher-photo">
                                        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Teacher Photo">
                                    </div>
                                    <div class="teacher-basic-info">
                                        <h3><?php echo htmlspecialchars($row['username']); ?></h3>
                                        <p class="location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($row['city']) . ', ' . htmlspecialchars($row['state']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="teacher-details-grid">
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Qualification</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($row['qualification']); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Subjects</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($row['subjects']); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Teaching Mode</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($row['teaching_mode']); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Experience</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($row['experience']); ?> years</span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Hourly Rate</span>
                                            <span class="detail-value">$<?php echo htmlspecialchars($row['hourly_rate']); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="teacher-about">
                                    <h4>About</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['about'])); ?></p>
                                </div>

                                <div class="teacher-actions">
                                    <?php if ($row['request_exists'] > 0): ?>
                                        <button class="connect-btn already-requested" disabled>
                                            <i class="fas fa-check"></i> Already Requested
                                        </button>
                                    <?php else: ?>
                                        <button class="connect-btn" onclick="connectWithTeacher(<?php echo $row['tutor_id']; ?>)">
                                            <i class="fas fa-handshake"></i> Connect
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-teachers">No teachers available at the moment.</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add this HTML for the delete confirmation modal -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this request?</p>
            <div class="form-actions">
                <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <button class="submit-btn delete-confirm-btn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Add this HTML for the success message -->
    <div id="successMessage" class="success-message">
        <i class="fas fa-check-circle"></i>
        <span id="successText"></span>
    </div>

    <!-- Add this HTML for the response confirmation modal -->
    <div id="responseConfirmModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeResponseModal()">&times;</span>
            <h2>Confirm Action</h2>
            <p id="responseConfirmText">Are you sure you want to take this action?</p>
            <div class="form-actions">
                <button class="cancel-btn" onclick="closeResponseModal()">Cancel</button>
                <button id="confirmResponseBtn" class="submit-btn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        // Initialize active view
        let currentView = 'overview';

        // Navigation functionality
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                
                // Remove active class from all nav items
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Hide all views
                document.querySelectorAll('.dashboard-view').forEach(view => {
                    view.style.display = 'none';
                });
                
                // Show selected view
                const viewId = this.getAttribute('data-view');
                const viewElement = document.getElementById(viewId);
                if (viewElement) {
                    viewElement.style.display = 'block';
                    currentView = viewId;
                }
            });
        });

        // Toggle profile dropdown
        const userProfile = document.querySelector('.user-profile');
        const profileDropdown = document.getElementById('profileDropdown');

        userProfile.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event from bubbling up
            profileDropdown.classList.toggle('show');
        });

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfile.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });

        // Add these new functions
        function openRequestModal() {
            document.getElementById('requestModal').style.display = 'block';
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        function submitRequest(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('newRequestForm'));
            
            fetch('submit_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request submitted successfully!');
                    closeRequestModal();
                    location.reload(); // Refresh the page after successful submission
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the request');
            });
        }

        function showSuccessMessage(message) {
            const successMessage = document.getElementById('successMessage');
            const successText = document.getElementById('successText');
            successText.textContent = message;
            successMessage.style.display = 'block';
            
            // Hide the message after 3 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }

        document.querySelectorAll('.action-btn.delete').forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.getAttribute('data-id');
                const deleteModal = document.getElementById('deleteConfirmModal');
                const deleteBtn = deleteModal.querySelector('.delete-confirm-btn');
                
                deleteModal.style.display = 'block';
                
                deleteBtn.replaceWith(deleteBtn.cloneNode(true));
                
                deleteModal.querySelector('.delete-confirm-btn').addEventListener('click', function() {
                    fetch('delete_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'request_id=' + requestId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.closest('.request-card').remove();
                            showSuccessMessage('Request deleted successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                        closeDeleteModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the request');
                        closeDeleteModal();
                    });
                });
            });
        });

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        // Update the window click handler to include the delete modal
        window.onclick = function(event) {
            const requestModal = document.getElementById('requestModal');
            const deleteModal = document.getElementById('deleteConfirmModal');
            const responseModal = document.getElementById('responseConfirmModal');
            
            if (event.target == requestModal) {
                closeRequestModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
            if (event.target == responseModal) {
                closeResponseModal();
            }
        }

        function openEditModal(requestId) {
            // Fetch request details
            fetch('delete_request.php?id=' + requestId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const request = data.request;
                        document.getElementById('editRequestId').value = request.request_id;
                        document.getElementById('editSubject').value = request.subject;
                        document.getElementById('editLearningMode').value = request.mode_of_learning;
                        document.getElementById('editBudget').value = request.fee_rate;
                        document.getElementById('editDetails').value = request.description;
                        
                        document.getElementById('editRequestModal').style.display = 'block';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the request details');
                });
        }

        function updateRequest(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('editRequestForm'));
            formData.append('action', 'update'); // Add action parameter for update

            fetch('delete_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Request updated successfully!');
                    closeEditModal();
                    // Refresh the page to show updated data
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the request');
            });
        }

        // Add this event listener for live validation
        document.getElementById('budget').addEventListener('input', function() {
            const budgetInput = this;
            const budgetError = document.getElementById('budgetError');
            const budgetLimitError = document.getElementById('budgetLimitError');
            
            if (budgetInput.value <= 0) {
                budgetError.style.display = 'block'; // Show error message
                budgetLimitError.style.display = 'none'; // Hide limit error
            } else if (budgetInput.value > 1000) {
                budgetLimitError.style.display = 'block'; // Show limit error
            } else {
                budgetError.style.display = 'none'; // Hide error message
                budgetLimitError.style.display = 'none'; // Hide limit error
            }
        });

        // Add this event listener for live validation
        document.getElementById('startDate').addEventListener('input', function() {
            const startDateInput = this;
            const startDateError = document.getElementById('startDateError');
            const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

            if (startDateInput.value < today) {
                startDateError.style.display = 'block'; // Show error message
            } else {
                startDateError.style.display = 'none'; // Hide error message
            }
        });

        document.getElementById('endDate').addEventListener('input', function() {
            const endDateInput = this;
            const startDateInput = document.getElementById('startDate');
            const endDateError = document.getElementById('endDateError');

            if (endDateInput.value < startDateInput.value) {
                endDateError.style.display = 'block'; // Show error message
            } else {
                endDateError.style.display = 'none'; // Hide error message
            }
        });

        function updateResponseStatus(responseId, status) {
            const modal = document.getElementById('responseConfirmModal');
            const confirmText = document.getElementById('responseConfirmText');
            const confirmBtn = document.getElementById('confirmResponseBtn');
            
            confirmText.textContent = `Are you sure you want to ${status} this response?`;
            
            if (status === 'approved') {
                confirmBtn.style.background = '#4CAF50';
            } else {
                confirmBtn.style.background = '#f44336';
            }
            
            modal.style.display = 'block';
            
            confirmBtn.replaceWith(confirmBtn.cloneNode(true));
            
            document.getElementById('confirmResponseBtn').addEventListener('click', function() {
                // Create form data
                const formData = new FormData();
                formData.append('response_id', responseId);
                formData.append('status', status);
                
                // Submit form using POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const responseIdInput = document.createElement('input');
                responseIdInput.name = 'response_id';
                responseIdInput.value = responseId;
                
                const statusInput = document.createElement('input');
                statusInput.name = 'status';
                statusInput.value = status;
                
                form.appendChild(responseIdInput);
                form.appendChild(statusInput);
                document.body.appendChild(form);
                form.submit();
            });
        }

        function closeResponseModal() {
            document.getElementById('responseConfirmModal').style.display = 'none';
        }

        function startChat(tutorId) {
            // Implement chat functionality
            console.log('Starting chat with tutor:', tutorId);
            // You can redirect to a chat page or open a chat modal
        }

        function viewProfile(tutorId) {
            // Implement profile view functionality
            console.log('Viewing profile of tutor:', tutorId);
            // You can redirect to the tutor's profile page
            window.location.href = 'tutor_profile.php?id=' + tutorId;
        }

        function connectWithTeacher(tutorId) {
            fetch(`connect_teacher.php?tutor_id=${tutorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while connecting with the teacher');
                });
        }

        // Add filter functionality
        function filterTeachers() {
            const selectedSubject = document.getElementById('subjectFilter').value.toLowerCase();
            const selectedMode = document.getElementById('teachingModeFilter').value;
            const teachers = document.querySelectorAll('.teacher-resource-card');
            let visibleCount = 0;

            teachers.forEach(teacher => {
                const subjects = teacher.dataset.subjects.toLowerCase();
                const teachingMode = teacher.dataset.teachingMode;
                
                const subjectMatch = !selectedSubject || subjects.includes(selectedSubject);
                const modeMatch = !selectedMode || teachingMode === selectedMode;

                if (subjectMatch && modeMatch) {
                    teacher.style.display = 'block';
                    visibleCount++;
                } else {
                    teacher.style.display = 'none';
                }
            });

            // Show/hide no results message
            const noTeachersDiv = document.querySelector('.no-teachers');
            if (noTeachersDiv) {
                if (visibleCount === 0) {
                    noTeachersDiv.style.display = 'block';
                    noTeachersDiv.textContent = 'No teachers found matching your filters.';
                } else {
                    noTeachersDiv.style.display = 'none';
                }
            }
        }

        // Add event listeners for filters
        document.getElementById('subjectFilter').addEventListener('change', filterTeachers);
        document.getElementById('teachingModeFilter').addEventListener('change', filterTeachers);
    </script>

    <!-- Add this before closing body tag -->
    <style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        position: relative;
    }

    .close-modal {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 24px;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .submit-btn,
    .cancel-btn {
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .submit-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
    }

    .cancel-btn {
        background-color: #f44336;
        color: white;
        border: none;
    }

    /* Add these styles for the delete confirmation modal */
    .modal h2 {
        margin-top: 0;
        margin-bottom: 15px;
    }

    .modal p {
        margin-bottom: 20px;
    }

    .delete-confirm-btn {
        background-color: #f44336 !important;
    }

    .success-message {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #4CAF50;
        color: white;
        padding: 15px 25px;
        border-radius: 4px;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }

    .success-message i {
        margin-right: 10px;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .profile-icon {
        width: 32px;
        height: 32px;
        color: #666; /* Adjust color as needed */
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .teacher-card {
        background: #ffffff; /* White background for a clean look */
        border-radius: 10px; /* Slightly rounded corners */
        padding: 20px;
        margin: 10px 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        color: #333; /* Dark text for readability */
        transition: box-shadow 0.3s; /* Smooth shadow transition */
    }

    .teacher-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
    }

    .teacher-card h3 {
        color: #007BFF; /* Primary color for the title */
        text-align: center; /* Centered title */
        font-family: 'Arial', sans-serif; /* Modern font */
        margin-bottom: 15px; /* Space below the title */
    }

    .teacher-info p {
        margin: 8px 0; /* Margin between paragraphs */
        font-family: 'Arial', sans-serif; /* Modern font */
        line-height: 1.5; /* Improved readability */
    }

    .teacher-info p strong {
        color: #007BFF; /* Accent color for labels */
    }

    .message-card {
        background: linear-gradient(145deg, #ffffff, #f8f9ff);
        border: 1px solid #e0dbff;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 10px 20px rgba(179, 165, 255, 0.1),
                    0 2px 6px rgba(179, 165, 255, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .message-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #b3a5ff, #88d3ce);
        opacity: 0.8;
    }

    .message-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(179, 165, 255, 0.15);
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(224, 219, 255, 0.5);
    }

    .tutor-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tutor-info i {
        font-size: 1.8em;
        color: #b3a5ff;
        background: linear-gradient(135deg, #b3a5ff, #88d3ce);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .tutor-name {
        font-weight: 600;
        color: #2d2d2d;
        font-size: 1.1em;
        letter-spacing: 0.3px;
    }

    .response-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 500;
        letter-spacing: 0.5px;
        text-transform: capitalize;
    }

    .response-status.pending {
        background: linear-gradient(135deg, #fff3cd, #ffe5a0);
        color: #856404;
    }

    .response-status.approved {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .response-status.rejected {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    .message-content {
        padding: 20px 0;
        border-bottom: 1px solid rgba(224, 219, 255, 0.5);
    }

    .request-details h4, 
    .response-details h4 {
        color: #b3a5ff;
        margin-bottom: 15px;
        font-size: 1.1em;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .request-details p, 
    .response-details p {
        color: #666666;
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .request-details strong {
        color: #2d2d2d;
        font-weight: 600;
    }

    .message-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }

    .timestamp {
        color: #666666;
        font-size: 0.9em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .timestamp i {
        color: #b3a5ff;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
    }

    .approve-btn, 
    .reject-btn {
        padding: 8px 20px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .approve-btn {
        background: linear-gradient(135deg, #88d3ce, #6bc7c0);
        color: white;
    }

    .reject-btn {
        background: linear-gradient(135deg, #ff9a9e, #ff8087);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
        padding: 8px 20px;
        transition: all 0.3s ease;
        position: relative;
    }

    .reject-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 128, 135, 0.3);
        background: linear-gradient(135deg, #ff8087, #ff6b73);
    }

    .reject-btn i,
    .reject-btn span {
        position: relative;
        z-index: 1;
    }

    .approve-btn:hover,
    .reject-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    .no-messages {
        text-align: center;
        padding: 50px;
        color: #666666;
        font-size: 1.1em;
        background: linear-gradient(145deg, #ffffff, #f8f9ff);
        border-radius: 16px;
        border: 1px solid #e0dbff;
        margin: 20px 0;
    }

    /* Response Confirmation Modal Styles */
    #responseConfirmModal .modal-content {
        background: linear-gradient(145deg, #ffffff, #f8f9ff);
        border: 1px solid #e0dbff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 20px rgba(179, 165, 255, 0.1),
                    0 2px 6px rgba(179, 165, 255, 0.05);
        max-width: 400px;
        position: relative;
    }

    #responseConfirmModal .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #b3a5ff, #88d3ce);
        border-radius: 16px 16px 0 0;
        opacity: 0.8;
    }

    #responseConfirmModal h2 {
        color: #2d2d2d;
        font-size: 1.5em;
        font-weight: 600;
        margin-bottom: 20px;
        letter-spacing: 0.5px;
    }

    #responseConfirmText {
        color: #666666;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    #responseConfirmModal .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    #responseConfirmModal .cancel-btn,
    #responseConfirmModal .submit-btn {
        padding: 10px 25px;
        border: none;
        border-radius: 25px;
        font-size: 1em;
        font-weight: 500;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #responseConfirmModal .cancel-btn {
        background: linear-gradient(135deg, #ff9a9e, #ff8087);
        color: white;
    }

    #responseConfirmModal .submit-btn {
        background: linear-gradient(135deg, #88d3ce, #6bc7c0);
        color: white;
    }

    #responseConfirmModal .cancel-btn:hover,
    #responseConfirmModal .submit-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .tutor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        padding: 20px;
    }

    .tutor-card {
        background: linear-gradient(145deg, #ffffff, #f8f9ff);
        border: 1px solid #e0dbff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(179, 165, 255, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .tutor-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #b3a5ff, #88d3ce);
        opacity: 0.8;
    }

    .tutor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(179, 165, 255, 0.15);
    }

    .tutor-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .tutor-photo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e0dbff;
    }

    .tutor-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .tutor-resource-card:hover .tutor-photo img {
        transform: scale(1.1);
    }

    .tutor-basic-info h3 {
        color: #2d2d2d;
        font-size: 1.4em;
        font-weight: 600;
        margin-bottom: 8px;
        background: linear-gradient(45deg, #88d3ce, #b3a5ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .location {
        color: #666;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .teacher-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin: 20px 0;
        padding: 15px;
        background: rgba(248, 249, 255, 0.5);
        border-radius: 16px;
        backdrop-filter: blur(5px);
    }

    .detail-item {
        min-width: 0;
    }

    .detail-item .value {
        color: #2d2d2d;
        font-weight: 500;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .teacher-about {
        flex: 1;
        margin: 15px 0;
        padding: 15px;
        background: rgba(248, 249, 255, 0.5);
        border-radius: 16px;
        backdrop-filter: blur(5px);
        overflow-y: auto;
        max-height: 200px;
    }

    .teacher-about h4 {
        color: #88d3ce;
        font-size: 1.1em;
        margin-bottom: 12px;
        font-weight: 600;
    }

    .teacher-about p {
        color: #444;
        line-height: 1.6;
        font-size: 0.95em;
        margin: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .connect-btn {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 30px;
        font-size: 1em;
        font-weight: 500;
        cursor: pointer;
        background: linear-gradient(45deg, #88d3ce, #b3a5ff);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .connect-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .connect-btn:hover::before {
        left: 100%;
    }

    .connect-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(136, 211, 206, 0.4);
    }

    .connect-btn i {
        font-size: 1.2em;
    }

    .no-teachers {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px;
        color: #666;
        font-size: 1.1em;
        background: linear-gradient(165deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.9));
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid rgba(179, 165, 255, 0.2);
        box-shadow: 0 10px 30px rgba(179, 165, 255, 0.15);
    }

    /* Add custom scrollbar styling */
    .teacher-about::-webkit-scrollbar {
        width: 6px;
    }

    .teacher-about::-webkit-scrollbar-track {
        background: rgba(248, 249, 255, 0.5);
        border-radius: 3px;
    }

    .teacher-about::-webkit-scrollbar-thumb {
        background: #88d3ce;
        border-radius: 3px;
    }

    /* Add responsive adjustments */
    @media screen and (max-width: 480px) {
        .teacher-resource-card {
            padding: 20px;
        }

        .teacher-header {
            flex-direction: column;
            text-align: center;
        }

        .teacher-photo {
            margin: 0 auto;
        }

        .teacher-details-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Update the filter controls styles */
    .filter-controls {
        display: flex;
        justify-content: center;
        padding: 20px;
        margin: 20px auto;
        max-width: 800px;
    }

    .filter-wrapper {
        display: flex;
        gap: 20px;
        background: linear-gradient(145deg, #ffffff, #f8f9ff);
        border: 1px solid #e0dbff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(179, 165, 255, 0.1);
        width: 100%;
        position: relative;
    }

    .filter-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #88d3ce, #b3a5ff);
        border-radius: 16px 16px 0 0;
        opacity: 0.8;
    }

    .filter-group {
        flex: 1;
        position: relative;
    }

    .filter-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #b3a5ff;
        font-size: 1.2em;
        z-index: 1;
    }

    .filter-group select {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid rgba(179, 165, 255, 0.3);
        border-radius: 25px;
        background-color: white;
        color: #2d2d2d;
        font-size: 0.95em;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23b3a5ff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
    }

    .filter-group select:hover {
        border-color: #88d3ce;
        box-shadow: 0 0 0 3px rgba(136, 211, 206, 0.1);
    }

    .filter-group select:focus {
        outline: none;
        border-color: #b3a5ff;
        box-shadow: 0 0 0 3px rgba(179, 165, 255, 0.2);
    }

    /* Responsive adjustments */
    @media screen and (max-width: 768px) {
        .filter-wrapper {
            flex-direction: column;
            gap: 15px;
        }

        .filter-controls {
            padding: 15px;
        }
    }

    .connect-btn.already-requested {
        background: linear-gradient(45deg, #808080, #a0a0a0);
        cursor: not-allowed;
        opacity: 0.8;
    }

    .connect-btn.already-requested:hover {
        transform: none;
        box-shadow: none;
    }

    /* Add these new styles */
    .section-title {
        margin: 30px 0 20px;
        color: #2d2d2d;
        font-size: 1.5em;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0dbff;
        grid-column: 1 / -1;
    }

    .card-type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .response-card .card-type-badge {
        background: linear-gradient(135deg, #88d3ce, #6bc7c0);
        color: white;
    }

    .direct-request-card .card-type-badge {
        background: linear-gradient(135deg, #b3a5ff, #9f8fff);
        color: white;
    }

    .tutor-details .detail-item:last-child {
        color: #666;
        font-style: italic;
    }

    .tutor-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(224, 219, 255, 0.5);
    }

    .message-btn,
    .profile-btn {
        flex: 1;
        padding: 12px 24px;
        border: none;
        border-radius: 30px;
        font-size: 0.95em;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .message-btn {
        background: linear-gradient(135deg, #88d3ce, #6bc7c0);
        color: white;
        box-shadow: 0 4px 15px rgba(136, 211, 206, 0.3);
    }

    .profile-btn {
        background: linear-gradient(135deg, #b3a5ff, #9f8fff);
        color: white;
        box-shadow: 0 4px 15px rgba(179, 165, 255, 0.3);
    }

    .message-btn::before,
    .profile-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: 0.5s;
    }

    .message-btn:hover::before,
    .profile-btn:hover::before {
        left: 100%;
    }

    .message-btn:hover,
    .profile-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    .message-btn:hover {
        box-shadow: 0 8px 20px rgba(136, 211, 206, 0.4);
    }

    .profile-btn:hover {
        box-shadow: 0 8px 20px rgba(179, 165, 255, 0.4);
    }

    .message-btn:active,
    .profile-btn:active {
        transform: translateY(1px);
    }

    .message-btn i,
    .profile-btn i {
        font-size: 1.1em;
        transition: transform 0.3s ease;
    }

    .message-btn:hover i,
    .profile-btn:hover i {
        transform: scale(1.1);
    }

    /* Add glass morphism effect */
    .message-btn,
    .profile-btn {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    /* Responsive adjustments */
    @media screen and (max-width: 480px) {
        .tutor-actions {
            flex-direction: column;
        }

        .message-btn,
        .profile-btn {
            width: 100%;
        }
    }

    /* Updated and new styles for the tutor cards */
    .tutor-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 25px;
        margin: 20px 0;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .tutor-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4facfe, #00f2fe);
        opacity: 0.8;
    }

    .tutor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
    }

    .tutor-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 25px 0;
        padding: 20px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .detail-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 1px solid rgba(79, 172, 254, 0.1);
    }

    .detail-item:hover {
        background: rgba(255, 255, 255, 1);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.1);
        transform: translateY(-2px);
    }

    .detail-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        border-radius: 10px;
        color: white;
        font-size: 1.2em;
    }

    .detail-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 0.85em;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 1.1em;
        color: #333;
        font-weight: 600;
    }

    .card-type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 500;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.2);
    }

    /* Responsive adjustments */
    @media screen and (max-width: 768px) {
        .tutor-details-grid {
            grid-template-columns: 1fr;
        }
        
        .detail-item {
            padding: 10px;
        }
    }

    @media screen and (max-width: 480px) {
        .tutor-card {
            padding: 20px 15px;
        }
        
        .detail-icon {
            width: 35px;
            height: 35px;
            font-size: 1em;
        }
    }

    /* Modern Card Design */
    .teacher-resource-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        padding: 30px;
        margin: 20px 0;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .teacher-resource-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        opacity: 0.8;
    }

    .teacher-resource-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.15);
    }

    /* Header Section */
    .teacher-header {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 30px;
    }

    .teacher-photo {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        overflow: hidden;
        border: 3px solid rgba(99, 102, 241, 0.2);
        position: relative;
    }

    .teacher-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .teacher-photo::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, rgba(99, 102, 241, 0.2), transparent);
    }

    .teacher-basic-info h3 {
        font-size: 1.5em;
        font-weight: 600;
        margin-bottom: 8px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Details Grid */
    .teacher-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 25px 0;
        padding: 25px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .detail-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .detail-item:hover {
        background: rgba(255, 255, 255, 1);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }

    .detail-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 12px;
        color: white;
        font-size: 1.2em;
    }

    /* About Section */
    .teacher-about {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        padding: 25px;
        margin: 25px 0;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .teacher-about h4 {
        color: #6366f1;
        font-size: 1.2em;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .teacher-about p {
        color: #4b5563;
        line-height: 1.8;
        font-size: 1em;
    }

    /* Action Buttons */
    .connect-btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 16px;
        font-size: 1.1em;
        font-weight: 500;
        cursor: pointer;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .connect-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .connect-btn:hover::before {
        left: 100%;
    }

    .connect-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }

    .connect-btn.already-requested {
        background: linear-gradient(135deg, #9ca3af, #6b7280);
        cursor: not-allowed;
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .teacher-resource-card {
            padding: 20px;
        }

        .teacher-header {
            flex-direction: column;
            text-align: center;
        }

        .teacher-photo {
            margin: 0 auto;
        }

        .teacher-details-grid {
            grid-template-columns: 1fr;
            padding: 15px;
        }
    }

    /* Loading Animation */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    .loading {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-size: 1000px 100%;
    }
    </style>
</body>
</html>