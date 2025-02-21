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
                                <select id="subject" name="subject" required>
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
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="budget">Budget (per hour):</label>
                                <input type="number" id="budget" name="budget" min="1" required>
                            </div>

                            <div class="form-group">
                                <label for="startDate">Start Date:</label>
                                <input type="date" id="startDate" name="startDate" required>
                            </div>

                            <div class="form-group">
                                <label for="endDate">End Date:</label>
                                <input type="date" id="endDate" name="endDate" required>
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
                                        <button class="action-btn edit" data-id="<?php echo $row['request_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
                    // Redirect to the Post a Requirement page
                    window.location.href = 'studentdashboard.php#courses'; // Adjust the URL as needed
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
            if (event.target == requestModal) {
                closeRequestModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
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
    </style>
</body>
</html>