<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
include 'connectdb.php';

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

// Get the active view from session or default to dashboard
$active_view = isset($_SESSION['active_view']) ? $_SESSION['active_view'] : 'dashboard';
unset($_SESSION['active_view']); // Clear it after use

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
                <li class="sidebar-nav-item" data-view="reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Add Data</span>
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
                        <img src="/api/placeholder/40/40" alt="Profile" class="profile-avatar">
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <div class="profile-dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </div>
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
                    <table class="users-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <thead>
                            <tr>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">ID</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Username</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Email</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">Role</th>
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
            </div>
        </div>
    </div>
</div>

</body>
</html>