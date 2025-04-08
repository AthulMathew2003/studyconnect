<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
include 'connectdb.php';
$_SESSION['back_view'] = 'admindashboard.php';
// Handle form submission for adding new subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dataType']) && $_POST['dataType'] === 'course') {
    $subject_name = mysqli_real_escape_string($conn, $_POST['name']);
    
    // Check if subject already exists
    $check_query = "SELECT * FROM tbl_subject WHERE subject = '$subject_name'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Subject already exists!";
        $_SESSION['message_type'] = "error";
    } else {
        // Insert new subject
        $insert_query = "INSERT INTO tbl_subject (subject) VALUES ('$subject_name')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['message'] = "Subject added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding subject: " . mysqli_error($conn);
            $_SESSION['message_type'] = "error";
        }
    }
    
    $_SESSION['active_view'] = 'reports'; // Store the active view in session
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Handle subject deletion
if (isset($_POST['delete_subject'])) {
    $subject_id = mysqli_real_escape_string($conn, $_POST['delete_subject']);
    
    $delete_query = "DELETE FROM tbl_subject WHERE subject_id = '$subject_id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Subject deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting subject: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle request approval
if (isset($_POST['approve_request']) && isset($_POST['request_type'])) {
    $request_id = mysqli_real_escape_string($conn, $_POST['approve_request']);
    $request_type = mysqli_real_escape_string($conn, $_POST['request_type']);
    
    if ($request_type === 'tutor') {
        $update_query = "UPDATE tbl_tutorrequest SET status = 'approved' WHERE tutorrequestid = '$request_id'";
    } else {
        $update_query = "UPDATE tbl_response SET status = 'approved' WHERE response_id = '$request_id'";
    }
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Request approved successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error approving request: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    $_SESSION['active_view'] = 'requests';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle request rejection
if (isset($_POST['reject_request']) && isset($_POST['request_type'])) {
    $request_id = mysqli_real_escape_string($conn, $_POST['reject_request']);
    $request_type = mysqli_real_escape_string($conn, $_POST['request_type']);
    
    if ($request_type === 'tutor') {
        $update_query = "UPDATE tbl_tutorrequest SET status = 'rejected' WHERE tutorrequestid = '$request_id'";
    } else {
        $update_query = "UPDATE tbl_response SET status = 'rejected' WHERE response_id = '$request_id'";
    }
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Request rejected successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error rejecting request: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    $_SESSION['active_view'] = 'requests';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get the active view from session or default to dashboard
$active_view = isset($_SESSION['active_view']) ? $_SESSION['active_view'] : 'dashboard';
unset($_SESSION['active_view']); // Clear it after use

// Check if view is set in URL (for direct links)
if (isset($_GET['view']) && in_array($_GET['view'], ['dashboard', 'users', 'requests', 'stats', 'reports', 'coins'])) {
    $active_view = $_GET['view'];
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['delete_user']);
    
    $delete_user_query = "DELETE FROM users WHERE userid = '$user_id'";
    if (mysqli_query($conn, $delete_user_query)) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    // Store the active view before redirecting
    $_SESSION['active_view'] = 'users';
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyConnect Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admindash.css" />
    <script type="text/javascript" src="admindash.js" defer></script>
    <script>
        // Set active view based on PHP variable
        document.addEventListener('DOMContentLoaded', function() {
            const activeView = '<?php echo $active_view; ?>';
            document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.view === activeView) {
                    item.classList.add('active');
                }
            });
            
            // Update main heading
            const mainHeading = document.querySelector('.top-navbar h1');
            if (mainHeading) {
                const activeItemText = document.querySelector(`.sidebar-nav-item[data-view="${activeView}"] span`);
                if (activeItemText) {
                    mainHeading.textContent = activeItemText.textContent;
                }
            }
        });
    </script>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                StudyConnect
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item active" data-view="dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </li>
                <li class="sidebar-nav-item" data-view="users">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </li>
                <li class="sidebar-nav-item" data-view="requests">
                    <i class="fas fa-tasks"></i>
                    <span>Requests</span>
                </li>
                <li class="sidebar-nav-item" data-view="stats">
                    <i class="fas fa-chart-pie"></i>
                    <span>Statistics</span>
                </li>
                <li class="sidebar-nav-item" data-view="reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Add Data</span>
                </li>
                <li class="sidebar-nav-item" data-view="coins">
                    <i class="fas fa-coins"></i>
                    <span>Coin Transactions</span>
                </li>
                <li class="sidebar-nav-item" id="dark-mode-toggle">
                    <i class="fas fa-moon"></i>
                    <span>Dark Mode</span>
                </li>
            </ul>
        </div>

        <div class="main-container">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="navbar-left">
                    <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                <div class="navbar-right">
                   
                    <div class="profile-section" id="profile-dropdown-trigger">
                        <svg class="profile-icon" viewBox="0 0 24 24" width="40" height="40">
                            <path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <!-- <div class="profile-dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </div> -->
                            <div class="profile-dropdown-item">
                                <a href="confirmpassword.php" style="text-decoration: none; color: inherit;"><i class="fas fa-key"></i> Change Password</a>
                            </div>
                            <div class="profile-dropdown-item">
                                <a href="logout.php" style="text-decoration: none; color: inherit;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Dashboard View -->
                <div class="dashboard-stats" id="dashboard-view" style="<?php if ($active_view !== 'dashboard') echo 'display: none;' ?>">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <p><?php 
                            $query = "SELECT COUNT(*) as total FROM users WHERE role != 'admin'";
                            $result = mysqli_query($conn, $query);
                            $data = mysqli_fetch_assoc($result);
                            echo htmlspecialchars($data['total']);
                        ?></p>
                    </div>
                   
                </div>

                <!-- Users View -->
                <div class="users-table-container" id="users-view" style="<?php if ($active_view !== 'users') echo 'display: none;' ?>">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; 
                            <?php echo $_SESSION['message_type'] === 'success' ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
                            <?php 
                                echo $_SESSION['message']; 
                                unset($_SESSION['message']);
                                unset($_SESSION['message_type']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <table class="users-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <thead>
                            <tr>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">ID</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Username</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Email</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Role</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM users WHERE role != 'admin' ORDER BY userid";
                            $result = mysqli_query($conn, $query);
                            
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td style='padding: 12px 15px; border: 1px solid #ddd;'>" . htmlspecialchars($row['userid']) . "</td>";
                                echo "<td style='padding: 12px 15px; border: 1px solid #ddd;'>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td style='padding: 12px 15px; border: 1px solid #ddd;'>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td style='padding: 12px 15px; border: 1px solid #ddd;'>" . htmlspecialchars($row['role']) . "</td>";
                                echo "<td style='padding: 12px 15px; border: 1px solid #ddd;'>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='delete_user' value='" . htmlspecialchars($row['userid']) . "'>
                                            <button type='submit' style='background: none; border: none; cursor: pointer;'>
                                                <i class='fas fa-trash' style='color: red;'></i>
                                            </button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div id="reports-view" style="<?php if ($active_view !== 'reports') echo 'display: none;' ?>">
                    <div style="max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2 style="margin-bottom: 2rem;">Add New Data</h2>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; 
                                <?php echo $_SESSION['message_type'] === 'success' ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
                                <?php 
                                    echo $_SESSION['message']; 
                                    unset($_SESSION['message']);
                                    unset($_SESSION['message_type']);
                                ?>
                            </div>
                        <?php endif; ?>
                        <form id="addDataForm" method="POST">
                            <div style="margin-bottom: 1.5rem;">
                                <label for="dataType" style="display: block; margin-bottom: 0.5rem;">Select Data Type</label>
                                <select id="dataType" name="dataType" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
                                    <option value="">Choose type...</option>
                                    <option value="course">Subject</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 1.5rem;">
                                <label for="name" style="display: block; margin-bottom: 0.5rem;">Subject Name</label>
                                <input type="text" id="name" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
                            </div>
                            <button type="submit" style="background-color: #007bff; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; width: 100%;">Add Subject</button>
                        </form>
                    </div>
                    
                    <!-- Delete Subjects Section -->
                    <div style="max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2 style="margin-bottom: 2rem;">Delete Subject</h2>
                        <div id="deleteSubjectResponse"></div>
                        <form id="deleteSubjectForm" method="POST" onsubmit="handleDelete(event)">
                            <div style="margin-bottom: 1.5rem;">
                                <label for="deleteDataType" style="display: block; margin-bottom: 0.5rem;">Select Data Type</label>
                                <select id="deleteDataType" name="deleteDataType" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
                                    <option value="">Choose type...</option>
                                    <option value="subject">Subject</option>
                                </select>
                            </div>
                            <div id="subjectSelectDiv" style="margin-bottom: 1.5rem; display: none;">
                                <label for="subject_to_delete" style="display: block; margin-bottom: 0.5rem;">Select Subject to Delete</label>
                                <select id="subject_to_delete" name="delete_subject" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
                                    <option value="">Choose subject...</option>
                                    <?php
                                    $query = "SELECT * FROM tbl_subject ORDER BY subject";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<option value="' . $row['subject_id'] . '">' . htmlspecialchars($row['subject']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button id="deleteButton" type="submit" style="background-color: #dc3545; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; width: 100%; display: none;">Delete Subject</button>
                        </form>
                    </div>
                </div>
                
                <script>
                document.getElementById('deleteDataType').addEventListener('change', function() {
                    const subjectDiv = document.getElementById('subjectSelectDiv');
                    const deleteButton = document.getElementById('deleteButton');
                    if (this.value === 'subject') {
                        subjectDiv.style.display = 'block';
                        deleteButton.style.display = 'block';
                    } else {
                        subjectDiv.style.display = 'none';
                        deleteButton.style.display = 'none';
                    }
                });

                function handleDelete(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    const subjectSelect = document.getElementById('subject_to_delete');
                    const selectedSubjectText = subjectSelect.options[subjectSelect.selectedIndex].text;
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        const responseDiv = document.getElementById('deleteSubjectResponse');
                        responseDiv.innerHTML = '<div class="alert alert-success" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; background-color: #d4edda; color: #155724;">Subject "' + selectedSubjectText + '" deleted successfully!</div>';
                        
                        // Remove the deleted option from the dropdown
                        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                        subjectSelect.removeChild(selectedOption);
                        
                        // Reset the subject select but keep the data type selected
                        subjectSelect.value = '';
                        
                        // If no more subjects, show message
                        if (subjectSelect.options.length <= 1) {
                            subjectSelect.innerHTML = '<option value="">No subjects available</option>';
                            document.getElementById('deleteButton').style.display = 'none';
                        }
                    })
                    .catch(error => {
                        const responseDiv = document.getElementById('deleteSubjectResponse');
                        responseDiv.innerHTML = '<div class="alert alert-danger" style="padding: 10px; margin-bottom: 15px; border-radius: 4px; background-color: #f8d7da; color: #721c24;">Error deleting subject. Please try again.</div>';
                    });
                    return false;
                }
                </script>
                
                <!-- Requests Management View -->
                <div id="requests-view" style="<?php if ($active_view !== 'requests') echo 'display: none;' ?>">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2>Request Management</h2>
                        <p class="section-subtitle">Monitor and manage connection requests between students and tutors</p>
                    </div>
                    
                    <div class="tabs" style="margin-bottom: 20px;">
                        <button class="tab-btn active" onclick="showTab('pending-requests')">Pending Requests</button>
                        <button class="tab-btn" onclick="showTab('approved-requests')">Approved Requests</button>
                        <button class="tab-btn" onclick="showTab('rejected-requests')">Rejected Requests</button>
                    </div>
                    
                    <!-- Pending Requests Tab -->
                    <div id="pending-requests" class="tab-content" style="display: block;">
                        <h3>Pending Tutor-Student Connection Requests</h3>
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Tutor</th>
                                    <th>Request Type</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch pending tutor requests
                                $query = "SELECT tr.tutorrequestid, tr.description, tr.created_at, tr.status,
                                            s.student_id, t.tutor_id, 
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_tutorrequest tr
                                          JOIN tbl_student s ON tr.student_id = s.student_id
                                          JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE tr.status = 'created'
                                          ORDER BY tr.created_at DESC";
                                          
                                $result = mysqli_query($conn, $query);
                                
                                // Fetch pending response requests
                                $query2 = "SELECT r.response_id, r.message, r.created_at, r.status,
                                            s.student_id, t.tutor_id, req.subject, req.description,
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_response r
                                          JOIN tbl_request req ON r.request_id = req.request_id
                                          JOIN tbl_student s ON req.student_id = s.student_id
                                          JOIN tbl_tutors t ON r.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE r.status = 'pending'
                                          ORDER BY r.created_at DESC";
                                
                                $result2 = mysqli_query($conn, $query2);
                                
                                if (mysqli_num_rows($result) == 0 && mysqli_num_rows($result2) == 0) {
                                    echo "<tr><td colspan='7' style='text-align: center;'>No pending requests found</td></tr>";
                                } else {
                                    // Display tutor requests
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['tutorrequestid']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Direct Request</td>";
                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "<td class='action-buttons'>
                                                <form method='POST' style='display:inline;'>
                                                    <input type='hidden' name='approve_request' value='" . $row['tutorrequestid'] . "'>
                                                    <input type='hidden' name='request_type' value='tutor'>
                                                    <button type='submit' class='approve-btn'>Approve</button>
                                                </form>
                                                <form method='POST' style='display:inline;'>
                                                    <input type='hidden' name='reject_request' value='" . $row['tutorrequestid'] . "'>
                                                    <input type='hidden' name='request_type' value='tutor'>
                                                    <button type='submit' class='reject-btn'>Reject</button>
                                                </form>
                                            </td>";
                                        echo "</tr>";
                                    }
                                    
                                    // Display response requests
                                    while ($row = mysqli_fetch_assoc($result2)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['response_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Response (" . htmlspecialchars($row['subject']) . ")</td>";
                                        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "<td class='action-buttons'>
                                                <form method='POST' style='display:inline;'>
                                                    <input type='hidden' name='approve_request' value='" . $row['response_id'] . "'>
                                                    <input type='hidden' name='request_type' value='response'>
                                                    <button type='submit' class='approve-btn'>Approve</button>
                                                </form>
                                                <form method='POST' style='display:inline;'>
                                                    <input type='hidden' name='reject_request' value='" . $row['response_id'] . "'>
                                                    <input type='hidden' name='request_type' value='response'>
                                                    <button type='submit' class='reject-btn'>Reject</button>
                                                </form>
                                            </td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Approved Requests Tab -->
                    <div id="approved-requests" class="tab-content" style="display: none;">
                        <h3>Approved Tutor-Student Connections</h3>
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Tutor</th>
                                    <th>Request Type</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch approved tutor requests
                                $query = "SELECT tr.tutorrequestid, tr.description, tr.created_at,
                                            s.student_id, t.tutor_id, 
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_tutorrequest tr
                                          JOIN tbl_student s ON tr.student_id = s.student_id
                                          JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE tr.status = 'approved'
                                          ORDER BY tr.created_at DESC";
                                          
                                $result = mysqli_query($conn, $query);
                                
                                // Fetch approved response requests
                                $query2 = "SELECT r.response_id, r.message, r.created_at,
                                            s.student_id, t.tutor_id, req.subject, 
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_response r
                                          JOIN tbl_request req ON r.request_id = req.request_id
                                          JOIN tbl_student s ON req.student_id = s.student_id
                                          JOIN tbl_tutors t ON r.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE r.status = 'approved'
                                          ORDER BY r.created_at DESC LIMIT 20";
                                
                                $result2 = mysqli_query($conn, $query2);
                                
                                if (mysqli_num_rows($result) == 0 && mysqli_num_rows($result2) == 0) {
                                    echo "<tr><td colspan='6' style='text-align: center;'>No approved connections found</td></tr>";
                                } else {
                                    // Display tutor requests
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['tutorrequestid']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Direct Request</td>";
                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "</tr>";
                                    }
                                    
                                    // Display response requests
                                    while ($row = mysqli_fetch_assoc($result2)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['response_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Response (" . htmlspecialchars($row['subject']) . ")</td>";
                                        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Rejected Requests Tab -->
                    <div id="rejected-requests" class="tab-content" style="display: none;">
                        <h3>Rejected Connection Requests</h3>
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Tutor</th>
                                    <th>Request Type</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch rejected tutor requests
                                $query = "SELECT tr.tutorrequestid, tr.description, tr.created_at,
                                            s.student_id, t.tutor_id, 
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_tutorrequest tr
                                          JOIN tbl_student s ON tr.student_id = s.student_id
                                          JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE tr.status = 'rejected'
                                          ORDER BY tr.created_at DESC LIMIT 20";
                                          
                                $result = mysqli_query($conn, $query);
                                
                                // Fetch rejected response requests
                                $query2 = "SELECT r.response_id, r.message, r.created_at,
                                            s.student_id, t.tutor_id, req.subject, 
                                            us.username as student_name, ut.username as tutor_name
                                          FROM tbl_response r
                                          JOIN tbl_request req ON r.request_id = req.request_id
                                          JOIN tbl_student s ON req.student_id = s.student_id
                                          JOIN tbl_tutors t ON r.tutor_id = t.tutor_id
                                          JOIN users us ON s.userid = us.userid
                                          JOIN users ut ON t.userid = ut.userid
                                          WHERE r.status = 'rejected'
                                          ORDER BY r.created_at DESC LIMIT 20";
                                
                                $result2 = mysqli_query($conn, $query2);
                                
                                if (mysqli_num_rows($result) == 0 && mysqli_num_rows($result2) == 0) {
                                    echo "<tr><td colspan='6' style='text-align: center;'>No rejected requests found</td></tr>";
                                } else {
                                    // Display tutor requests
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['tutorrequestid']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Direct Request</td>";
                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "</tr>";
                                    }
                                    
                                    // Display response requests
                                    while ($row = mysqli_fetch_assoc($result2)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['response_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tutor_name']) . "</td>";
                                        echo "<td>Response (" . htmlspecialchars($row['subject']) . ")</td>";
                                        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <script>
                    function showTab(tabId) {
                        // Hide all tab contents
                        document.querySelectorAll('.tab-content').forEach(tab => {
                            tab.style.display = 'none';
                        });
                        
                        // Show the selected tab content
                        document.getElementById(tabId).style.display = 'block';
                        
                        // Update active button
                        document.querySelectorAll('.tab-btn').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        
                        // Add active class to clicked button
                        event.target.classList.add('active');
                    }
                    </script>
                </div>
                
                <!-- Statistics View -->
                <div id="stats-view" style="<?php if ($active_view !== 'stats') echo 'display: none;' ?>">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2>Platform Statistics</h2>
                        <p class="section-subtitle">Key metrics and analytics for StudyConnect</p>
                    </div>
                    
                    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                        <?php
                        // Get user type counts
                        $users_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
                        $users_result = mysqli_query($conn, $users_query);
                        $user_stats = array();
                        while ($row = mysqli_fetch_assoc($users_result)) {
                            $user_stats[$row['role']] = $row['count'];
                        }
                        
                        // Get connection stats
                        $conn_query = "SELECT 
                                        (SELECT COUNT(*) FROM tbl_tutorrequest WHERE status = 'approved') + 
                                        (SELECT COUNT(*) FROM tbl_response WHERE status = 'approved') as total_connections";
                        $conn_result = mysqli_query($conn, $conn_query);
                        $conn_data = mysqli_fetch_assoc($conn_result);
                        $total_connections = $conn_data['total_connections'];
                        
                        // Get popular subjects
                        $subjects_query = "SELECT s.subject, COUNT(ts.tutorsubjectid) as tutor_count
                                          FROM tbl_subject s
                                          LEFT JOIN tbl_tutorsubject ts ON s.subject_id = ts.subject_id
                                          GROUP BY s.subject_id
                                          ORDER BY tutor_count DESC
                                          LIMIT 5";
                        $subjects_result = mysqli_query($conn, $subjects_query);
                        
                        // Get review stats
                        $review_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM tbl_review";
                        $review_result = mysqli_query($conn, $review_query);
                        $review_data = mysqli_fetch_assoc($review_result);
                        $avg_rating = number_format($review_data['avg_rating'], 1);
                        $total_reviews = $review_data['total_reviews'];
                        ?>
                        
                        <!-- User Distribution Card -->
                        <div class="stats-card" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>User Distribution</h3>
                            <div class="chart-container" style="position: relative; height: 200px; width: 100%;">
                                <canvas id="userChart"></canvas>
                            </div>
                            <div class="stats-details" style="margin-top: 10px; display: flex; justify-content: center; gap: 20px;">
                                <div class="stat-item">
                                    <strong>Students:</strong> <?php echo isset($user_stats['student']) ? $user_stats['student'] : 0; ?>
                                </div>
                                <div class="stat-item">
                                    <strong>Teachers:</strong> <?php echo isset($user_stats['teacher']) ? $user_stats['teacher'] : 0; ?>
                                </div>
                                <div class="stat-item">
                                    <strong>Admins:</strong> <?php echo isset($user_stats['admin']) ? $user_stats['admin'] : 0; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Connections Card -->
                        <div class="stats-card" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Connection Analytics</h3>
                            <div class="chart-container" style="position: relative; height: 200px; width: 100%;">
                                <canvas id="connectionChart"></canvas>
                            </div>
                            <div class="stats-details" style="margin-top: 10px; text-align: center;">
                                <div class="stat-item">
                                    <strong>Total Active Connections:</strong> <?php echo $total_connections; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Popular Subjects Card -->
                        <div class="stats-card" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Popular Subjects</h3>
                            <div class="chart-container" style="position: relative; height: 200px; width: 100%;">
                                <canvas id="subjectsChart"></canvas>
                            </div>
                            <div class="stats-details" style="margin-top: 10px;">
                                <ul style="list-style: none; padding: 0;">
                                    <?php
                                    $subject_labels = array();
                                    $subject_data = array();
                                    while ($row = mysqli_fetch_assoc($subjects_result)) {
                                        $subject_labels[] = $row['subject'];
                                        $subject_data[] = $row['tutor_count'];
                                        echo "<li><strong>" . htmlspecialchars($row['subject']) . ":</strong> " . $row['tutor_count'] . " tutors</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Reviews Card -->
                        <div class="stats-card" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Tutor Reviews</h3>
                            <div style="display: flex; justify-content: center; align-items: center; height: 200px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 48px; font-weight: bold; color: #8672ff;"><?php echo $avg_rating; ?></div>
                                    <div style="font-size: 24px;">
                                        <?php
                                        $stars = round($avg_rating);
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $stars) {
                                                echo '<span style="color: gold;">★</span>';
                                            } else {
                                                echo '<span style="color: #ddd;">★</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div>Based on <?php echo $total_reviews; ?> reviews</div>
                                </div>
                            </div>
                            <div class="stats-details" style="margin-top: 10px; text-align: center;">
                                <div class="stat-item">
                                    <strong>Total Reviews:</strong> <?php echo $total_reviews; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Stats Section -->
                    <div class="additional-stats" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
                        <h3>System Activity</h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
                            <?php
                            // Get recent registrations
                            $recent_users_query = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                            $recent_users_result = mysqli_query($conn, $recent_users_query);
                            $recent_users = mysqli_fetch_assoc($recent_users_result)['count'];
                            
                            // Get recent connections
                            $recent_connections_query = "SELECT COUNT(*) as count FROM 
                                                        (SELECT created_at FROM tbl_tutorrequest WHERE status = 'approved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                        UNION ALL
                                                        SELECT created_at FROM tbl_response WHERE status = 'approved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS combined";
                            $recent_connections_result = mysqli_query($conn, $recent_connections_query);
                            $recent_connections = mysqli_fetch_assoc($recent_connections_result)['count'];
                            
                            // Get recent reviews
                            $recent_reviews_query = "SELECT COUNT(*) as count FROM tbl_review WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                            $recent_reviews_result = mysqli_query($conn, $recent_reviews_query);
                            $recent_reviews = mysqli_fetch_assoc($recent_reviews_result)['count'];
                            ?>
                            
                            <div class="activity-stat">
                                <h4>New Users (Last 7 Days)</h4>
                                <div class="activity-value"><?php echo $recent_users; ?></div>
                            </div>
                            
                            <div class="activity-stat">
                                <h4>New Connections (Last 7 Days)</h4>
                                <div class="activity-value"><?php echo $recent_connections; ?></div>
                            </div>
                            
                            <div class="activity-stat">
                                <h4>New Reviews (Last 7 Days)</h4>
                                <div class="activity-value"><?php echo $recent_reviews; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Include Chart.js -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    // User Distribution Chart
                    var userCtx = document.getElementById('userChart').getContext('2d');
                    var userChart = new Chart(userCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Students', 'Teachers', 'Admins'],
                            datasets: [{
                                data: [
                                    <?php echo isset($user_stats['student']) ? $user_stats['student'] : 0; ?>,
                                    <?php echo isset($user_stats['teacher']) ? $user_stats['teacher'] : 0; ?>,
                                    <?php echo isset($user_stats['admin']) ? $user_stats['admin'] : 0; ?>
                                ],
                                backgroundColor: ['#8672FF', '#36A2EB', '#FFCE56'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                    
                    // Connection Chart
                    var connCtx = document.getElementById('connectionChart').getContext('2d');
                    var connChart = new Chart(connCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Direct Requests', 'Response Requests'],
                            datasets: [{
                                label: 'Approved Connections',
                                data: [
                                    <?php 
                                    $direct_query = "SELECT COUNT(*) as count FROM tbl_tutorrequest WHERE status = 'approved'";
                                    $direct_result = mysqli_query($conn, $direct_query);
                                    echo mysqli_fetch_assoc($direct_result)['count'];
                                    ?>,
                                    <?php 
                                    $response_query = "SELECT COUNT(*) as count FROM tbl_response WHERE status = 'approved'";
                                    $response_result = mysqli_query($conn, $response_query);
                                    echo mysqli_fetch_assoc($response_result)['count'];
                                    ?>
                                ],
                                backgroundColor: ['#8672FF', '#36A2EB'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                    
                    // Subjects Chart
                    var subjCtx = document.getElementById('subjectsChart').getContext('2d');
                    var subjChart = new Chart(subjCtx, {
                        type: 'doughnut',
                        data: {
                            labels: <?php echo json_encode($subject_labels); ?>,
                            datasets: [{
                                data: <?php echo json_encode($subject_data); ?>,
                                backgroundColor: ['#8672FF', '#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });
                    </script>
                </div>

                <!-- Coin Transactions View -->
                <div id="coins-view" style="<?php if ($active_view !== 'coins') echo 'display: none;' ?>">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2>Coin Transactions</h2>
                        <p class="section-subtitle">View and manage all coin transactions in the system</p>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
                        <form method="GET" action="" id="filterForm">
                            <input type="hidden" name="view" value="coins">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div>
                                    <label for="transaction_type" style="display: block; margin-bottom: 5px; font-weight: 500;">Transaction Type</label>
                                    <select name="transaction_type" id="transaction_type" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        <option value="">All Types</option>
                                        <option value="Purchase" <?php echo isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'Purchase' ? 'selected' : ''; ?>>Purchase</option>
                                        <option value="Usage" <?php echo isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'Usage' ? 'selected' : ''; ?>>Usage</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="user_filter" style="display: block; margin-bottom: 5px; font-weight: 500;">User</label>
                                    <select name="user_id" id="user_filter" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        <option value="">All Users</option>
                                        <?php
                                        $users_query = "SELECT userid, username FROM users ORDER BY username";
                                        $users_result = mysqli_query($conn, $users_query);
                                        while ($user = mysqli_fetch_assoc($users_result)) {
                                            $selected = isset($_GET['user_id']) && $_GET['user_id'] == $user['userid'] ? 'selected' : '';
                                            echo "<option value='" . $user['userid'] . "' $selected>" . htmlspecialchars($user['username']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="date_from" style="display: block; margin-bottom: 5px; font-weight: 500;">Date From</label>
                                    <input type="date" name="date_from" id="date_from" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                <div>
                                    <label for="date_to" style="display: block; margin-bottom: 5px; font-weight: 500;">Date To</label>
                                    <input type="date" name="date_to" id="date_to" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                <div style="display: flex; align-items: flex-end;">
                                    <button type="submit" style="width: 100%; padding: 8px; background-color: #8672ff; color: white; border: none; border-radius: 5px; cursor: pointer;">Apply Filters</button>
                                </div>
                                <div style="display: flex; align-items: flex-end;">
                                    <button type="button" id="resetFilters" style="width: 100%; padding: 8px; background-color: #f1f1f1; border: none; border-radius: 5px; cursor: pointer;">Reset Filters</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Transaction Statistics -->
                    <div class="stats-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                        <?php
                        // Get summary statistics for coins
                        $stats_condition = "";
                        $params = [];

                        if (isset($_GET['transaction_type']) && !empty($_GET['transaction_type'])) {
                            $transaction_type = mysqli_real_escape_string($conn, $_GET['transaction_type']);
                            $stats_condition .= " AND c.transaction_type = '$transaction_type'";
                        }

                        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                            $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
                            $stats_condition .= " AND c.userid = '$user_id'";
                        }

                        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                            $date_from = mysqli_real_escape_string($conn, $_GET['date_from']);
                            $stats_condition .= " AND DATE(c.transaction_date) >= '$date_from'";
                        }

                        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                            $date_to = mysqli_real_escape_string($conn, $_GET['date_to']);
                            $stats_condition .= " AND DATE(c.transaction_date) <= '$date_to'";
                        }

                        // Total transactions
                        $total_query = "SELECT COUNT(*) as count FROM tbl_coins c WHERE 1=1" . $stats_condition;
                        $total_result = mysqli_query($conn, $total_query);
                        $total_transactions = mysqli_fetch_assoc($total_result)['count'];

                        // Total purchased coins
                        $purchase_query = "SELECT SUM(coins_amount) as total_purchased FROM tbl_coins c WHERE transaction_type = 'Purchase'" . $stats_condition;
                        $purchase_result = mysqli_query($conn, $purchase_query);
                        $total_purchased = mysqli_fetch_assoc($purchase_result)['total_purchased'] ?? 0;

                        // Total used coins
                        $usage_query = "SELECT SUM(coins_amount) as total_used FROM tbl_coins c WHERE transaction_type = 'Usage'" . $stats_condition;
                        $usage_result = mysqli_query($conn, $usage_query);
                        $total_used = mysqli_fetch_assoc($usage_result)['total_used'] ?? 0;

                        // Net coin balance in system
                        $net_balance = $total_purchased - $total_used;
                        ?>

                        <div class="stat-box" style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Total Transactions</h3>
                            <div style="font-size: 32px; font-weight: bold; color: #8672ff; margin-top: 10px;"><?php echo $total_transactions; ?></div>
                        </div>

                        <div class="stat-box" style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Total Coins Purchased</h3>
                            <div style="font-size: 32px; font-weight: bold; color: #4caf50; margin-top: 10px;"><?php echo number_format($total_purchased); ?></div>
                        </div>

                        <div class="stat-box" style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Total Coins Used</h3>
                            <div style="font-size: 32px; font-weight: bold; color: #f44336; margin-top: 10px;"><?php echo number_format($total_used); ?></div>
                        </div>

                        <div class="stat-box" style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h3>Net Coin Balance</h3>
                            <div style="font-size: 32px; font-weight: bold; color: #2196f3; margin-top: 10px;"><?php echo number_format($net_balance); ?></div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="transactions-table-container" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3>Transaction History</h3>
                        
                        <?php
                        // Build the query condition based on filters
                        $condition = "";
                        $params = [];

                        if (isset($_GET['transaction_type']) && !empty($_GET['transaction_type'])) {
                            $transaction_type = mysqli_real_escape_string($conn, $_GET['transaction_type']);
                            $condition .= " AND c.transaction_type = '$transaction_type'";
                        }

                        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                            $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
                            $condition .= " AND c.userid = '$user_id'";
                        }

                        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                            $date_from = mysqli_real_escape_string($conn, $_GET['date_from']);
                            $condition .= " AND DATE(c.transaction_date) >= '$date_from'";
                        }

                        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                            $date_to = mysqli_real_escape_string($conn, $_GET['date_to']);
                            $condition .= " AND DATE(c.transaction_date) <= '$date_to'";
                        }

                        // Count total records for pagination
                        $count_query = "SELECT COUNT(*) as total FROM tbl_coins c WHERE 1=1" . $condition;
                        $count_result = mysqli_query($conn, $count_query);
                        $total_records = mysqli_fetch_assoc($count_result)['total'];

                        // Pagination settings
                        $records_per_page = 20;
                        $total_pages = ceil($total_records / $records_per_page);
                        $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                        $offset = ($current_page - 1) * $records_per_page;

                        // Get transactions with pagination
                        $transactions_query = "SELECT c.*, u.username 
                                             FROM tbl_coins c
                                             JOIN users u ON c.userid = u.userid
                                             WHERE 1=1" . $condition . "
                                             ORDER BY c.transaction_date DESC
                                             LIMIT $offset, $records_per_page";
                        $transactions_result = mysqli_query($conn, $transactions_query);
                        ?>

                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">ID</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">User</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Type</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Coins</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Description</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Payment ID</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($transactions_result) == 0) {
                                    echo "<tr><td colspan='7' style='padding: 20px; text-align: center;'>No transactions found</td></tr>";
                                } else {
                                    while ($transaction = mysqli_fetch_assoc($transactions_result)) {
                                        $type_color = $transaction['transaction_type'] == 'Purchase' ? '#4caf50' : '#f44336';
                                        
                                        echo "<tr>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . $transaction['coin_id'] . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($transaction['username']) . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd; color: $type_color; font-weight: 500;'>" . $transaction['transaction_type'] . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . number_format($transaction['coins_amount']) . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($transaction['description'] ?? 'N/A') . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($transaction['payment_id'] ?? 'N/A') . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . date('Y-m-d H:i', strtotime($transaction['transaction_date'])) . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 5px;">
                            <?php
                            // Build the query string for pagination links
                            $query_params = $_GET;
                            
                            // Previous page link
                            if ($current_page > 1) {
                                $query_params['page'] = $current_page - 1;
                                $prev_link = '?' . http_build_query($query_params);
                                echo '<a href="' . $prev_link . '" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">&laquo; Previous</a>';
                            }
                            
                            // Page number links
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $query_params['page'] = $i;
                                $page_link = '?' . http_build_query($query_params);
                                
                                if ($i == $current_page) {
                                    echo '<span style="padding: 8px 12px; background-color: #8672ff; color: white; border-radius: 4px;">' . $i . '</span>';
                                } else {
                                    echo '<a href="' . $page_link . '" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">' . $i . '</a>';
                                }
                            }
                            
                            // Next page link
                            if ($current_page < $total_pages) {
                                $query_params['page'] = $current_page + 1;
                                $next_link = '?' . http_build_query($query_params);
                                echo '<a href="' . $next_link . '" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">Next &raquo;</a>';
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Manage Coin Settings Button -->
                    <div style="margin-top: 20px; text-align: center;">
                        <button id="addCoinsManually" style="padding: 12px 20px; background-color: #8672ff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            <i class="fas fa-plus-circle"></i> Add Coins Manually
                        </button>
                    </div>

                    <!-- Add Coins Modal -->
                    <div id="addCoinsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
                        <div style="background: white; border-radius: 10px; padding: 20px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="margin: 0;">Add Coins Manually</h3>
                                <button id="closeAddCoinsModal" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                            </div>
                            
                            <form method="POST" action="" id="addCoinsForm">
                                <input type="hidden" name="action" value="add_coins_manually">
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="coin_user_id" style="display: block; margin-bottom: 5px; font-weight: 500;">Select User</label>
                                    <select name="coin_user_id" id="coin_user_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                        <option value="">-- Select User --</option>
                                        <?php
                                        $users_query = "SELECT userid, username FROM users ORDER BY username";
                                        $users_result = mysqli_query($conn, $users_query);
                                        while ($user = mysqli_fetch_assoc($users_result)) {
                                            echo "<option value='" . $user['userid'] . "'>" . htmlspecialchars($user['username']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="transaction_type" style="display: block; margin-bottom: 5px; font-weight: 500;">Transaction Type</label>
                                    <select name="transaction_type" id="transaction_type" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                        <option value="Purchase">Purchase (Add Coins)</option>
                                        <option value="Usage">Usage (Deduct Coins)</option>
                                    </select>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="coins_amount" style="display: block; margin-bottom: 5px; font-weight: 500;">Coins Amount</label>
                                    <input type="number" name="coins_amount" id="coins_amount" required min="1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="transaction_description" style="display: block; margin-bottom: 5px; font-weight: 500;">Description</label>
                                    <textarea name="transaction_description" id="transaction_description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                                </div>
                                
                                <button type="submit" style="width: 100%; padding: 12px; background-color: #8672ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 500;">Add Coins</button>
                            </form>
                        </div>
                    </div>

                    <?php
                    // Handle add coins manually
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coins_manually') {
                        $user_id = mysqli_real_escape_string($conn, $_POST['coin_user_id']);
                        $transaction_type = mysqli_real_escape_string($conn, $_POST['transaction_type']);
                        $coins_amount = intval($_POST['coins_amount']);
                        $description = mysqli_real_escape_string($conn, $_POST['transaction_description'] ?? 'Manual adjustment by admin');
                        
                        // Insert transaction record
                        $insert_query = "INSERT INTO tbl_coins (userid, transaction_type, coins_amount, description) 
                                        VALUES ('$user_id', '$transaction_type', $coins_amount, '$description')";
                        
                        if (mysqli_query($conn, $insert_query)) {
                            // Update user's coin wallet
                            // First check if user has a wallet
                            $wallet_check = "SELECT * FROM tbl_coinwallet WHERE userid = '$user_id'";
                            $wallet_result = mysqli_query($conn, $wallet_check);
                            
                            if (mysqli_num_rows($wallet_result) > 0) {
                                // Update existing wallet
                                if ($transaction_type == 'Purchase') {
                                    $update_wallet = "UPDATE tbl_coinwallet SET coin_balance = coin_balance + $coins_amount WHERE userid = '$user_id'";
                                } else {
                                    $update_wallet = "UPDATE tbl_coinwallet SET coin_balance = GREATEST(0, coin_balance - $coins_amount) WHERE userid = '$user_id'";
                                }
                                mysqli_query($conn, $update_wallet);
                            } else {
                                // Create new wallet
                                $balance = $transaction_type == 'Purchase' ? $coins_amount : 0;
                                $create_wallet = "INSERT INTO tbl_coinwallet (userid, coin_balance) VALUES ('$user_id', $balance)";
                                mysqli_query($conn, $create_wallet);
                            }
                            
                            $_SESSION['message'] = "Coins " . ($transaction_type == 'Purchase' ? "added to" : "deducted from") . " user successfully!";
                            $_SESSION['message_type'] = "success";
                        } else {
                            $_SESSION['message'] = "Error updating coins: " . mysqli_error($conn);
                            $_SESSION['message_type'] = "error";
                        }
                        
                        $_SESSION['active_view'] = 'coins';
                        header("Location: " . $_SERVER['PHP_SELF'] . "?view=coins");
                        exit();
                    }
                    ?>

                    <script>
                    // Modal for adding coins
                    document.getElementById('addCoinsManually').addEventListener('click', function() {
                        document.getElementById('addCoinsModal').style.display = 'flex';
                    });
                    
                    document.getElementById('closeAddCoinsModal').addEventListener('click', function() {
                        document.getElementById('addCoinsModal').style.display = 'none';
                    });
                    
                    // Close modal when clicking outside
                    document.getElementById('addCoinsModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            this.style.display = 'none';
                        }
                    });
                    
                    // Reset filters button
                    document.getElementById('resetFilters').addEventListener('click', function() {
                        window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?view=coins';
                    });
                    </script>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.users-table {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.users-table th {
    background-color: #007bff;
    color: white;
}

.users-table td {
    background-color: #f9f9f9;
}

.users-table tr:hover {
    background-color: #f1f1f1;
}

button {
    border: none;
    background: none;
    cursor: pointer;
}

/* Tabs styling */
.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background-color: #f1f1f1;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s;
}

.tab-btn.active {
    background-color: #8672ff;
    color: white;
}

/* Request tables styling */
.requests-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
}

.requests-table th {
    padding: 12px 15px;
    background-color: #8672ff;
    color: white;
    text-align: left;
}

.requests-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.approve-btn {
    padding: 6px 12px;
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.reject-btn {
    padding: 6px 12px;
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

/* Statistics styling */
.activity-stat {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.activity-value {
    font-size: 36px;
    font-weight: bold;
    color: #8672ff;
    margin-top: 10px;
}
</style>

</body>
</html>
