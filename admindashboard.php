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
                <li class="sidebar-nav-item" data-view="content">
                    <i class="fas fa-file-alt"></i>
                    <span>Content</span>
                </li>
                <li class="sidebar-nav-item" data-view="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
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
                    <div class="stat-card">
                        <h3>Active Studies</h3>
                        <p>42</p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Invites</h3>
                        <p>18</p>
                    </div>
                    <div class="stat-card">
                        <h3>Monthly Growth</h3>
                        <p>+12%</p>
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
                
                <!-- Content Management View -->
                <div id="content-view" style="<?php if ($active_view !== 'content') echo 'display: none;' ?>">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2>Content Management</h2>
                        <p class="section-subtitle">Manage system notifications and announcements</p>
                    </div>
                    
                    <!-- System Announcements Section -->
                    <div class="content-section" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
                        <h3>System Announcements</h3>
                        <p>Create new announcements to be sent to all users on the platform</p>
                        
                        <form method="POST" action="" style="margin-top: 20px;">
                            <input type="hidden" name="action" value="create_announcement">
                            
                            <div style="margin-bottom: 15px;">
                                <label for="announcement_title" style="display: block; margin-bottom: 5px; font-weight: 500;">Announcement Title</label>
                                <input type="text" name="announcement_title" id="announcement_title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label for="announcement_message" style="display: block; margin-bottom: 5px; font-weight: 500;">Announcement Message</label>
                                <textarea name="announcement_message" id="announcement_message" required rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label for="user_type" style="display: block; margin-bottom: 5px; font-weight: 500;">Target Users</label>
                                <select name="user_type" id="user_type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    <option value="all">All Users</option>
                                    <option value="student">Students Only</option>
                                    <option value="teacher">Teachers Only</option>
                                </select>
                            </div>
                            
                            <button type="submit" style="background-color: #8672ff; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: 500;">Send Announcement</button>
                        </form>
                    </div>
                    
                    <!-- Previous Announcements Section -->
                    <div class="content-section" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
                        <h3>Previous Announcements</h3>
                        
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Title</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Message</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Target</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get system notifications
                                $notifications_query = "SELECT * FROM tbl_notifications 
                                                      WHERE type = 'system' 
                                                      ORDER BY created_at DESC 
                                                      LIMIT 10";
                                $notifications_result = mysqli_query($conn, $notifications_query);
                                
                                if (mysqli_num_rows($notifications_result) == 0) {
                                    echo "<tr><td colspan='4' style='padding: 12px; text-align: center; border-bottom: 1px solid #ddd;'>No announcements found</td></tr>";
                                } else {
                                    while ($row = mysqli_fetch_assoc($notifications_result)) {
                                        echo "<tr>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($row['title']) . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($row['message']) . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . ($row['userid'] == 0 ? 'All Users' : 'User #' . $row['userid']) . "</td>";
                                        echo "<td style='padding: 12px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Notification Templates Section -->
                    <div class="content-section" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3>Email Notification Settings</h3>
                        <p>Configure system email notification settings for various actions</p>
                        
                        <form method="POST" action="" style="margin-top: 20px;">
                            <input type="hidden" name="action" value="update_notification_settings">
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="notify_new_connection" checked>
                                    <span>Send email for new connection request</span>
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="notify_new_message" checked>
                                    <span>Send email for new messages</span>
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="notify_review" checked>
                                    <span>Send email for new reviews</span>
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label for="notification_email" style="display: block; margin-bottom: 5px; font-weight: 500;">System Notification Email</label>
                                <input type="email" name="notification_email" id="notification_email" value="notifications@studyconnect.com" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            
                            <button type="submit" style="background-color: #8672ff; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: 500;">Save Settings</button>
                        </form>
                    </div>
                </div>
                
                <!-- Add PHP code to handle create announcement form submission -->
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_announcement') {
                    $title = mysqli_real_escape_string($conn, $_POST['announcement_title']);
                    $message = mysqli_real_escape_string($conn, $_POST['announcement_message']);
                    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
                    
                    // Create system notification for all users or specific user type
                    if ($user_type === 'all') {
                        // Get all users
                        $users_query = "SELECT userid FROM users";
                        $users_result = mysqli_query($conn, $users_query);
                        
                        while ($user = mysqli_fetch_assoc($users_result)) {
                            $user_id = $user['userid'];
                            $insert_query = "INSERT INTO tbl_notifications (userid, title, message, type) 
                                           VALUES ('$user_id', '$title', '$message', 'system')";
                            mysqli_query($conn, $insert_query);
                        }
                    } else {
                        // Get users of specific type
                        $users_query = "SELECT userid FROM users WHERE role = '$user_type'";
                        $users_result = mysqli_query($conn, $users_query);
                        
                        while ($user = mysqli_fetch_assoc($users_result)) {
                            $user_id = $user['userid'];
                            $insert_query = "INSERT INTO tbl_notifications (userid, title, message, type) 
                                           VALUES ('$user_id', '$title', '$message', 'system')";
                            mysqli_query($conn, $insert_query);
                        }
                    }
                    
                    $_SESSION['message'] = "Announcement sent successfully!";
                    $_SESSION['message_type'] = "success";
                    $_SESSION['active_view'] = 'content';
                    
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
                ?>

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
</html>