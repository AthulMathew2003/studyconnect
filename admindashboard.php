<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
include 'connectdb.php';
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
                <div class="dashboard-stats" id="dashboard-view">
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
                <div class="users-table-container" id="users-view" style="display: none;">
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

                <!-- Reports View (Add Data) -->
                <div id="reports-view" style="display: none;">
                    <div class="content-card">
                        <h2 style="color: var(--text-primary); margin-bottom: 1.5rem;">Add New Data</h2>
                        <form id="addDataForm" style="max-width: 600px; margin: 0 auto;">
                            <div class="form-group">
                                <label for="dataType">Data Type</label>
                                <select id="dataType" name="dataType" required>
                                    <option value="">Select Data Type</option>
                                    <option value="course">Course</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="resource">Resource</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4"></textarea>
                            </div>
                            <div id="dynamicFields">
                                <!-- Fields will be dynamically added here -->
                            </div>
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                </div>

                <!-- Settings View -->
                <div id="settings-view" style="display: none;">
                    <div class="content-card">
                        <h2 style="color: var(--text-primary); margin-bottom: 1.5rem;">Settings</h2>
                        <form id="settingsForm" style="max-width: 600px; margin: 0 auto;">
                            <div class="form-group">
                                <label for="siteName">Site Name</label>
                                <input type="text" id="siteName" name="siteName" value="StudyConnect">
                            </div>
                            <div class="form-group">
                                <label for="maintenanceMode">Maintenance Mode</label>
                                <select id="maintenanceMode" name="maintenanceMode">
                                    <option value="off">Off</option>
                                    <option value="on">On</option>
                                </select>
                            </div>
                            <button type="submit">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>