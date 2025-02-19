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
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </a>
                <a href="#" class="nav-item" data-view="forums">
                    <i class="fas fa-comments"></i>
                    <span>Forums</span>
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
                        <img src="assets/default-avatar.png" alt="Profile" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="profile-dropdown" id="profileDropdown">
                            <a href="studentprofile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
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
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="learningMode">Learning Mode:</label>
                                <select id="learningMode" name="learningMode" required>
                                    <option value="online">Online</option>
                                    <option value="inPerson">Offline</option>
                                    <option value="hybrid">Both</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="budget">Budget (per hour):</label>
                                <input type="number" id="budget" name="budget" min="1" required>
                            </div>

                            <div class="form-group">
                                <label for="details">Additional Details:</label>
                                <textarea id="details" name="details" rows="4" required></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" onclick="closeRequestModal()" class="cancel-btn">Cancel</button>
                                <button type="submit" class="submit-btn">Submit Request</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="requests-grid" id="requestsContainer">
                    <div class="request-card">
                        <div class="card-header">
                            <div class="header-left">
                                <span class="request-id">REQ-230491</span>
                                <div class="status-badge">
                                    <span class="status-dot"></span>
                                    Pending
                                </div>
                            </div>
                            <div class="header-actions">
                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user"></i> Student Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-book"></i> Subject</span>
                                <span class="info-value">Advanced Physics</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-video"></i> Learning Mode</span>
                                <span class="info-value">Online</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-dollar-sign"></i> Budget</span>
                                <span class="info-value">$45/hour</span>
                            </div>
                           
                        </div>

                        <div class="details-section">
                            <div class="details-title"><i class="fas fa-info-circle"></i> Additional Details</div>
                            <p class="details-content">
                                Looking for help with quantum mechanics and thermodynamics.
                                Preparing for advanced placement exams. Previous experience with
                                calculus-based physics.
                            </p>
                        </div>

                        <div class="timestamp">
                            <i class="fas fa-calendar-alt"></i>
                            Submitted on Feb 19, 2025 at 10:30 AM
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-view" id="assignments" style="display: none;">
                <h1>Assignments</h1>
                <div class="assignments-grid">
                    <!-- Add your assignments content here -->
                </div>
            </div>

            <div class="dashboard-view" id="forums" style="display: none;">
                <h1>Forums</h1>
                <div class="forums-grid">
                    <!-- Add your forums content here -->
                </div>
            </div>

            <div class="dashboard-view" id="calendar" style="display: none;">
                <h1>Calendar</h1>
                <div class="calendar-container">
                    <!-- Add your calendar content here -->
                </div>
            </div>

            <div class="dashboard-view" id="resources" style="display: none;">
                <h1>Resources</h1>
                <div class="resources-grid">
                    <!-- Add your resources content here -->
                </div>
            </div>
        </main>
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
            // Here you would typically send the form data to your server
            // For now, we'll just close the modal
            alert('Request submitted successfully!');
            closeRequestModal();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('requestModal');
            if (event.target == modal) {
                closeRequestModal();
            }
        }
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
        margin: 10% auto;
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
    </style>
</body>
</html>