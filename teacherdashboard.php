<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}
include 'connectdb.php';
$userid = $_SESSION['userid']; // Fixed the syntax error (missing semicolon)

// Fetch coin balance from tbl_coinwallet
$coin_query = $conn->prepare("SELECT coin_balance FROM tbl_coinwallet WHERE userid = ?");
$coin_query->bind_param("i", $userid);
$coin_query->execute();
$coin_query->bind_result($coin_balance);
$coin_query->fetch();
$coin_query->close();

// Check if user exists in tbl_tutor
$check_tutor = $conn->prepare("SELECT userid FROM tbl_tutors WHERE userid = ?");
$check_tutor->bind_param("i", $userid);
$check_tutor->execute();
$result = $check_tutor->get_result();

if ($result->num_rows == 0) {
    // User not found in tbl_tutor, redirect to profile setup
    header('Location: teacherprofile.php');
    exit();
}
$check_tutor->close();
$_SESSION['back_view'] = 'teacherdashboard.php';

// Add this PHP code at the top of the file after session_start()
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StudyConnect - Teacher Dashboard</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <style>
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

      .dashboard-container {
        display: flex;
        min-height: 100vh;
      }

      /* Sidebar Styles */
      .sidebar {
        width: 250px;
        background: var(--base-color);
        padding: 1.5rem;
        position: fixed;
        height: 100%;
        left: -250px;
        transition: 0.3s;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
      }
      .profile-dropdown {
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
      }

      .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid var(--input-color);
      }

      .sidebar.active {
        left: 0;
      }

      .sidebar-logo {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--accent-color);
        margin-bottom: 2rem;
      }

      .nav-links {
        list-style: none;
      }

      .nav-links li {
        margin-bottom: 1rem;
      }

      .nav-links a {
        color: var(--text-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 0.8rem;
        border-radius: 8px;
        transition: 0.3s;
      }

      .nav-links a:hover {
        background: var(--input-color);
        color: var(--accent-color);
      }

      .nav-links a.active {
        background: var(--accent-color);
        color: var(--base-color);
      }

      /* Main Content Styles */
      .main-content {
        flex: 1;
        margin-left: 0;
        transition: 0.3s;
        padding: 1.5rem;
      }

      .main-content.active {
        margin-left: 250px;
      }

      /* Header Styles */
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

      .header-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
      }

      .header-logo {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--accent-color);
      }

      .menu-toggle {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-color);
        cursor: pointer;
      }

      .header-actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
      }

      .notification-icon,
      .coin-wallet,
      .ratings-reviews {
        position: relative;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: 0.3s;
      }

      .notification-icon:hover,
      .coin-wallet:hover,
      .ratings-reviews:hover {
        background: var(--input-color);
      }

      .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--error-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--base-color);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 0.5rem;
        min-width: 200px;
        display: none;
        z-index: 1000;
      }

      .dropdown-menu.active {
        display: block;
      }

      .dropdown-menu a {
        display: block;
        padding: 0.8rem;
        color: var(--text-color);
        text-decoration: none;
        transition: 0.3s;
        border-radius: 4px;
      }

      .dropdown-menu a:hover {
        background: var(--input-color);
      }

      .coin-balance {
        padding: 0.8rem;
        border-bottom: 1px solid var(--input-color);
        margin-bottom: 0.5rem;
      }

      .coin-amount {
        font-weight: 600;
        color: var(--accent-color);
      }

      .ratings-stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem;
        border-bottom: 1px solid var(--input-color);
      }

      .star-rating {
        color: gold;
      }

      .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
      }

      .stat-card {
        background: var(--base-color);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }

      .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
      }

      .stat-header h3 {
        font-size: 1rem;
        color: var(--text-color);
      }

      .stat-value {
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--accent-color);
        margin-bottom: 0.5rem;
      }

      .stat-trend {
        font-size: 0.9rem;
      }

      .stat-trend.positive {
        color: #34c759;
      }

      .stat-trend.negative {
        color: var(--error-color);
      }

      .stat-trend.neutral {
        color: #8e8e93;
      }

      .dashboard-card {
        background: var(--base-color);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        grid-column: span 2;
      }

      .dashboard-card h3 {
        margin-bottom: 1rem;
        color: var(--text-color);
      }

      .session-list,
      .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
      }

      .session-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: var(--input-color);
        border-radius: 8px;
        gap: 1rem;
      }

      .session-time {
        font-weight: 600;
        color: var(--accent-color);
        min-width: 80px;
      }

      .session-info {
        flex: 1;
      }

      .session-info h4 {
        font-weight: 500;
        margin-bottom: 0.2rem;
      }

      .session-info p {
        font-size: 0.9rem;
        color: #666;
      }

      .session-action {
        padding: 0.5rem 1rem;
        background: var(--accent-color);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s;
      }

      .session-action:hover {
        opacity: 0.9;
      }

      .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--input-color);
      }

      .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
      }

      .activity-info h4 {
        font-weight: 500;
        margin-bottom: 0.2rem;
      }

      .activity-info p {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.2rem;
      }

      .activity-time {
        font-size: 0.8rem;
        color: #8e8e93;
      }

      @media (max-width: 768px) {
        .dashboard-card {
          grid-column: span 1;
        }
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .main-content.active {
          margin-left: 0;
        }

        .sidebar.active {
          width: 100%;
        }

        .header-actions {
          gap: 1rem;
        }
      }

      .request-details {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
      }

      .mode-tag,
      .schedule-tag,
      .duration-tag {
        font-size: 0.8rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: var(--input-color);
        color: var(--text-color);
      }

      .request-actions {
        display: flex;
        gap: 0.5rem;
      }

      .session-action.accept {
        background: var(--accent-color);
      }

      .session-action.decline {
        background: var(--error-color);
      }

      .activity-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--input-color);
      }

      .session-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: var(--input-color);
        border-radius: 8px;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .session-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }

      @media (max-width: 768px) {
        .request-actions {
          flex-direction: column;
        }

        .request-details {
          flex-direction: column;
          gap: 0.5rem;
        }

        .session-item {
          flex-direction: column;
          text-align: center;
        }

        .session-info {
          width: 100%;
        }
      }

      /* Add these new styles */
      .main-header {
        /* Remove or comment out this entire block */
      }

      .main-footer {
        background: var(--text-color);
        color: white;
        padding: 2rem;
        text-align: center;
        margin-top: 2rem;
      }

      .footer-content {
        display: flex;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        flex-wrap: wrap;
        gap: 2rem;
      }

      .footer-section {
        flex: 1;
        min-width: 250px;
      }

      .footer-section h4 {
        margin-bottom: 1rem;
        color: var(--accent-color);
      }

      .footer-section ul {
        list-style: none;
      }

      .footer-section ul li {
        margin-bottom: 0.5rem;
      }

      .footer-section a {
        color: white;
        text-decoration: none;
        transition: 0.3s;
      }

      .footer-section a:hover {
        color: var(--accent-color);
      }

      .footer-bottom {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
      }

      /* Content Section Styles */
      .content-section {
        display: none;
      }

      .content-section.active {
        display: block;
      }

      /* Student Request Section Styles */
      .requests-container {
        max-width: 1200px;
        margin: 0 auto;
        background-image: 
            radial-gradient(circle at 20% 20%, #f0edff 0%, transparent 25%),
            radial-gradient(circle at 80% 80%, #e8e4ff 0%, transparent 25%);
        min-height: 100vh;
        padding: 2rem;
      }

      .section-header {
        text-align: center;
        margin-bottom: 4rem;
        position: relative;
      }

      .section-header h2 {
        font-size: 3rem;
        color: var(--accent-color);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
      }

      .section-header h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
      }

      .filters-container {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
      }

      .filter-group {
        position: relative;
        min-width: 200px;
      }

      .filter-label {
        position: absolute;
        top: -10px;
        left: 20px;
        background: white;
        padding: 0 10px;
        color: var(--accent-color);
        font-size: 0.9rem;
        z-index: 1;
      }

      .filter-select {
        width: 100%;
        padding: 1rem 1.5rem;
        border: 2px solid var(--input-color);
        border-radius: 20px;
        background: white;
        color: var(--text-color);
        cursor: pointer;
        appearance: none;
        transition: all 0.3s ease;
        font-size: 1rem;
      }

      .filter-select:hover {
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(179, 165, 255, 0.2);
      }

      .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        padding: 1rem;
      }

      .request-card {
        background: white;
        border-radius: 30px;
        padding: 2rem;
        transition: all 0.3s ease;
        position: relative;
        border: 2px solid var(--input-color);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 600px;
      }

      .request-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(179, 165, 255, 0.2);
        border-color: var(--accent-color);
      }

      .student-name {
        font-size: 1.4rem;
        margin-bottom: 1rem;
        color: var(--text-color);
        border-bottom: 2px dashed var(--input-color);
        padding-bottom: 0.5rem;
      }

      .requirements {
        height: 50px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        margin-bottom: 1rem;
        color: var(--text-color);
      }

      .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 1rem 0;
      }

      .tag {
        background: #f0edff;
        color: var(--accent-color);
        padding: 0.5rem 1rem;
        border-radius: 15px;
        font-size: 0.9rem;
      }

      .request-info {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.5rem 1rem;
        margin: 1.5rem 0;
        color: var(--text-color);
      }

      .connect-btn {
        margin-top: auto;
        background: var(--accent-color);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 15px;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
      }

      .connect-btn:hover {
        background: #9f8dff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(179, 165, 255, 0.3);
      }

      @media (max-width: 768px) {
        .requests-container {
          padding: 1rem;
        }
        
        .section-header h2 {
          font-size: 2rem;
        }
        
        .filter-group {
          width: 100%;
        }
      }

      .confirmation-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      }

      .popup-content {
        background: var(--base-color);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
        text-align: center;
      }

      .popup-content p {
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
      }

      .popup-content button {
        margin: 0 0.5rem;
        padding: 0.8rem 2rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
      }

      .popup-content .confirm-connect {
        background: var(--accent-color);
        color: white;
      }

      .popup-content .confirm-connect:hover {
        background: #7561ff;
      }

      .popup-content .cancel-connect {
        background: #f3f0ff;
        color: var(--text-color);
      }

      .popup-content .cancel-connect:hover {
        background: #e8e4ff;
      }

      .request-card {
        position: relative;
        overflow: hidden;
      }

      .request-card.applied::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
        pointer-events: none;
      }

      .applied-badge {
        position: absolute;
        top: 20px;
        right: -35px;
        background: linear-gradient(135deg, #ff3e3e 0%, #ff0000 100%);
        color: white;
        padding: 8px 40px;
        transform: rotate(45deg);
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 2px 10px rgba(255, 0, 0, 0.2);
        animation: glow 1.5s ease-in-out infinite alternate;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
      }

      @keyframes glow {
        from {
          box-shadow: 0 2px 10px rgba(255, 0, 0, 0.2);
        }
        to {
          box-shadow: 0 0 20px rgba(255, 0, 0, 0.4),
                      0 0 30px rgba(255, 0, 0, 0.2);
        }
      }

      .request-card.applied {
        border-color: rgba(255, 0, 0, 0.2);
      }

      .request-card.applied:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(255, 0, 0, 0.1);
      }

      /* My Students Section Styles */
      .my-students-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
      }

      .section-header {
        text-align: center;
        margin-bottom: 3rem;
      }

      .section-header h2 {
        color: var(--accent-color);
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
      }

      .section-subtitle {
        color: #666;
        font-size: 1.1rem;
      }

      .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        padding: 1rem;
      }

      .student-card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 20px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(134, 114, 255, 0.1);
        box-shadow: 0 8px 24px rgba(134, 114, 255, 0.1);
      }

      .card-glass-effect {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
          135deg,
          rgba(255, 255, 255, 0.1),
          rgba(255, 255, 255, 0.05)
        );
        backdrop-filter: blur(10px);
        z-index: 0;
      }

      .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(134, 114, 255, 0.2);
      }

      .student-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
      }

      .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-color);
        box-shadow: 0 0 0 4px rgba(134, 114, 255, 0.1);
      }

      .student-info h3 {
        font-size: 1.2rem;
        color: var(--text-color);
        margin-bottom: 0.3rem;
      }

      .student-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.9rem;
      }

      .student-details {
        background: rgba(134, 114, 255, 0.05);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
      }

      .detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
      }

      .detail-item:last-child {
        margin-bottom: 0;
      }

      .detail-label {
        color: #666;
        font-size: 0.9rem;
      }

      .detail-value {
        color: var(--text-color);
        font-weight: 500;
      }

      .student-actions {
        display: flex;
        gap: 1rem;
        position: relative;
        z-index: 1;
      }

      .action-btn {
        flex: 1;
        padding: 0.8rem;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
      }

      .message-btn {
        background: var(--accent-color);
        color: white;
      }

      .profile-btn {
        background: rgba(134, 114, 255, 0.1);
        color: var(--accent-color);
      }

      .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(134, 114, 255, 0.2);
      }

      .btn-icon {
        font-size: 1.2rem;
      }

      .no-students {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem;
      }

      .empty-state {
        background: rgba(134, 114, 255, 0.05);
        border-radius: 20px;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
      }

      .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
      }

      .empty-state h3 {
        color: var(--text-color);
        font-size: 1.5rem;
      }

      .empty-state p {
        color: #666;
      }

      @media (max-width: 768px) {
        .my-students-container {
          padding: 1rem;
        }

        .students-grid {
          grid-template-columns: 1fr;
        }

        .student-card {
          padding: 1rem;
        }

        .student-actions {
          flex-direction: column;
        }
      }
    </style>
  </head>
  <body>
    <div class="dashboard-container">
      <nav class="sidebar">
        <div class="sidebar-logo">StudyConnect</div>
        <ul class="nav-links">
          <li><a href="#" data-section="dashboard-content" class="active">Dashboard</a></li>
          <li><a href="#" data-section="student-request-content">Student Request</a></li>
          <li><a href="messages.php">Messages</a></li>
          <li><a href="#" data-section="my-students-content">My Students</a></li>
          <li><a href="#" data-section="settings-content">Settings</a></li>
        </ul>
      </nav>

      <main class="main-content">
        <header class="header">
          <div class="header-brand">
            <button class="menu-toggle">☰</button>
            <div class="header-logo">StudyConnect</div>
          </div>
          <div class="header-actions">
            <div class="ratings-reviews">
              ⭐
              <div class="dropdown-menu">
                <div class="ratings-stat">
                  <span class="star-rating">⭐ 4.8</span>
                  <span>(120 reviews)</span>
                </div>
                <a href="#">View All Reviews</a>
                <a href="#">Rating Statistics</a>
              </div>
            </div>
            <div class="coin-wallet">
              🪙
              <div class="dropdown-menu">
                <div class="coin-balance">
                  Balance: <span class="coin-amount"><?php echo htmlspecialchars($coin_balance); ?> coins</span>
                </div>
                <a href="#">Buy Coins</a>
                <a href="#">Previous Transactions</a>
                <a href="#">Coin History</a>
              </div>
            </div>
            <div class="notification-icon">
              🔔
              <span class="notification-badge">3</span>
            </div>
            <div class="profile-dropdown">
              <img src="1.webp" alt="Profile" class="profile-img" />
              <div class="dropdown-menu">
                <a href="teacherprofile.php">Profile</a>
                <a href="confirmpassword.php">Forgot Password</a>
                <a href="logout.php">Logout</a>
              </div>
            </div>
          </div>
        </header>
        <!-- Content sections -->
        <div id="dashboard-content" class="content-section active">
          <div class="dashboard-grid">
            <!-- Quick Stats Section -->
            <div class="stat-card">
              <div class="stat-header">
                <h3>Active Students</h3>
                <span class="stat-icon">👥</span>
              </div>
              <div class="stat-value">256</div>
              <div class="stat-trend positive">↑ 12% this month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3> Total Courses </h3>
                <span class="stat-icon">📊</span>
              </div>
              <div class="stat-value">25</div>
              <div class="stat-trend positive"></div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Total Tutors</h3>
                <span class="stat-icon"></span>
              </div>
              <div class="stat-value">3,240</div>
              <div class="stat-trend positive">↑ 8% this month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Average Rating</h3>
                <span class="stat-icon">⭐</span>
              </div>
              <div class="stat-value">4.8</div>
              <div class="stat-trend neutral">Same as last month</div>
            </div>

            <!-- Recent Requests -->
            <div class="dashboard-card recent-requests">
              <h3>Recent Requests</h3>
              <div class="session-list">
                <div class="session-item">
                  <img
                    src="/api/placeholder/40/40"
                    alt="Student"
                    class="activity-avatar"
                  />
                  <div class="session-info">
                    <h4>Emma Watson</h4>
                    <p>Mathematics • One-on-One • Online</p>
                    <div class="request-details">
                      <span class="mode-tag online">💻 Online</span>
                      <span class="schedule-tag">📅 Flexible Schedule</span>
                      <span class="duration-tag">⏱️ 1 hour/session</span>
                    </div>
                  </div>
                  <div class="request-actions">
                    <button class="session-action accept">Accept</button>
                    <button class="session-action decline">Decline</button>
                  </div>
                </div>

                <div class="session-item">
                  <img
                    src="/api/placeholder/40/40"
                    alt="Student"
                    class="activity-avatar"
                  />
                  <div class="session-info">
                    <h4>James Smith</h4>
                    <p>Physics • Group Study • In-Person</p>
                    <div class="request-details">
                      <span class="mode-tag in-person">🏫 In-Person</span>
                      <span class="schedule-tag">📅 Mon, Wed, Fri</span>
                      <span class="duration-tag">⏱️ 2 hours/session</span>
                    </div>
                  </div>
                  <div class="request-actions">
                    <button class="session-action accept">Accept</button>
                    <button class="session-action decline">Decline</button>
                  </div>
                </div>

                <div class="session-item">
                  <img
                    src="/api/placeholder/40/40"
                    alt="Student"
                    class="activity-avatar"
                  />
                  <div class="session-info">
                    <h4>Sophie Chen</h4>
                    <p>Chemistry • One-on-One • Hybrid</p>
                    <div class="request-details">
                      <span class="mode-tag hybrid">🔄 Hybrid</span>
                      <span class="schedule-tag">📅 Weekends</span>
                      <span class="duration-tag">⏱️ 1.5 hours/session</span>
                    </div>
                  </div>
                  <div class="request-actions">
                    <button class="session-action accept">Accept</button>
                    <button class="session-action decline">Decline</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="student-request-content" class="content-section">
          <div class="requests-container">
            <div class="section-header">
              <h2>Student Requests</h2>
            </div>
            
            <div class="filters-container">
              <div class="filter-group">
                <span class="filter-label">Location</span>
                <select class="filter-select">
                  <option>All Locations</option>
                  <?php
                    // Prepare and execute query to get unique locations with city, state, and country
                    $stmt = $conn->prepare("SELECT DISTINCT city, state, country FROM tbl_studentlocation ORDER BY city, state, country");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    // Loop through results and create option elements with combined location info
                    while ($row = $result->fetch_assoc()) {
                      $location = htmlspecialchars($row['city'] . ', ' . $row['state'] . ', ' . $row['country']);
                      echo "<option>" . $location . "</option>";
                    }
                    $stmt->close();
                  ?>
                </select>
              </div>
              
              <div class="filter-group">
                <span class="filter-label">Subject</span>
                <select class="filter-select">
                  <option>All Subjects</option>
                  <?php
                    // Prepare and execute query to get subjects
                    $stmt = $conn->prepare("SELECT subject FROM tbl_subject");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    // Loop through results and create option elements
                    while ($row = $result->fetch_assoc()) {
                      echo "<option>" . htmlspecialchars($row['subject']) . "</option>";
                    }
                    $stmt->close();
                  ?>
                </select>
              </div>
              
              <div class="filter-group">
                <span class="filter-label">Mode</span>
                <select class="filter-select">
                  <option>All Modes</option>
                  <option>Online</option>
                  <option>Offline</option>
                  <option>Both</option>
                </select>
              </div>
              <div class="filter-group">
                <button id="search-button" class="search-button" style="background: var(--accent-color); color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 20px; cursor: pointer; transition: background 0.3s;">
                  Search
                </button>
              </div>
            </div>

            <div class="requests-grid">
  <?php

  

  $requestresult = $conn->query("SELECT * FROM tbl_request");

  // Check if there are any requests
  if ($requestresult->num_rows > 0) {
      while ($request = $requestresult->fetch_assoc()) {
          $studentid = $request['student_id'];
          $studentresult = $conn->query("SELECT * FROM tbl_student WHERE student_id=$studentid");
          $student = $studentresult->fetch_assoc();
          $userid = $student['userid'];
          $userresult = $conn->query("SELECT * FROM users WHERE userid=$userid");
          $user = $userresult->fetch_assoc(); 
          $locationresult = $conn->query("SELECT * FROM tbl_studentlocation WHERE student_id=$studentid");
          $location = $locationresult->fetch_assoc();

          // Get the count of responses for this request
          $request_id = $request['request_id'];
          $response_count_query = $conn->prepare("SELECT COUNT(*) as response_count FROM tbl_response WHERE request_id = ?");
          $response_count_query->bind_param("i", $request_id);
          $response_count_query->execute();
          $response_count_result = $response_count_query->get_result();
          $response_count = $response_count_result->fetch_assoc()['response_count'];
          $response_count_query->close();

          // Check if tutor has already applied for this request
          $check_response = $conn->prepare("SELECT response_id FROM tbl_response WHERE request_id = ? AND tutor_id = ?");
          $check_response->bind_param("ii", $request['request_id'], $tutor_id);
          $check_response->execute();
          $response_result = $check_response->get_result();
          $has_applied = $response_result->num_rows > 0;
          $check_response->close();

          echo '<div class="request-card' . ($has_applied ? ' applied' : '') . '">';
          echo '<h3 class="student-name">' . htmlspecialchars($user['username']) . '</h3>';
          echo '<p class="requirements">' . htmlspecialchars($request['description']) . '</p>';
          echo '<div class="tags">';
          echo '<span class="tag">📚 ' . htmlspecialchars($request['subject']) . '</span>';
          echo '<span class="tag">💰 $' . htmlspecialchars($request['fee_rate']) . '/hour</span>';
          echo '<span class="tag">💻 ' . htmlspecialchars($request['mode_of_learning']) . '</span>';
          echo '</div>';
          echo '<div class="request-info">';
          echo '<strong>Location:</strong> <span>' . htmlspecialchars($location['city'] . ', ' . $location['state'] . ', ' . $location['country']) . '</span>';
          echo '<strong>Submitted:</strong> <span>' . htmlspecialchars($request['created_at']) . '</span>';
          echo '<strong>Start Date:</strong> <span>' . htmlspecialchars($request['start_date']) . '</span>';
          echo '<strong>End Date:</strong> <span>' . htmlspecialchars($request['end_date']) . '</span>';
          echo '</div>';

          if ($has_applied) {
              echo '<div class="applied-badge">Applied</div>';
          } else {
              echo '<button class="connect-btn" data-request-id="' . $request['request_id'] . '" data-responses="' . $response_count . '">Connect with Student</button>';
          }

          echo '</div>';
      }
  } else {
      // No requests found
      echo "<p>No student requests found.</p>";
  }

?>
</div>

<!-- Updated confirmation popup -->
<div class="confirmation-popup" style="display: none;">
    <div class="popup-content">
        <p>Are you sure you want to connect with this student?</p>
        <p class="response-count" style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
            <span id="applicant-count">0</span> tutors have applied for this request
        </p>
        <!-- Add textarea for custom message -->
        <div style="margin-top: 1rem;">
            <label for="connect-message" style="display: block; margin-bottom: 0.5rem;">Message to student:</label>
            <textarea 
                id="connect-message" 
                rows="4" 
                style="width: 100%; padding: 0.5rem; border: 1px solid var(--input-color); border-radius: 4px; margin-bottom: 1rem;"
                placeholder="Enter your message to the student..."
            >I am interested in helping you with your studies.</textarea>
        </div>
        <div style="margin-top: 1rem;">
            <button class="confirm-connect">Yes</button>
            <button class="cancel-connect">No</button>
        </div>
    </div>
</div>

        </div>

        <div id="messages-content" class="content-section">
          <h2>Messages</h2>
          <!-- Messages content will go here -->
           <h1>hello hi</h1>
        </div>

       

        <div id="settings-content" class="content-section">
          <h2>Settings</h2>
          <h1>hhhdhdhhdhdhdhdhdhd</h1>
          <!-- Settings content will go here -->
        </div>
      </main>
    </div>

    <!-- Add footer before closing body tag -->
    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-section">
          <!-- <h4>Quick Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About Us</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Help Center</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Resources</h4>
          <ul>
            <li><a href="#">Teaching Guide</a></li>
            <li><a href="#">Best Practices</a></li>
            <li><a href="#">Community Forums</a></li>
            <li><a href="#">Blog</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Legal</h4>
          <ul>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Cookie Policy</a></li>
            <li><a href="#">Copyright Notice</a></li>
          </ul>
        </div>
      </div> -->
      <div class="footer-bottom">
        <p>&copy; 2024 StudyConnect. All rights reserved.</p>
      </div>
    </footer>

    <script>
      // Toggle sidebar visibility
      const menuToggle = document.querySelector('.menu-toggle');
      const sidebar = document.querySelector('.sidebar');
      const mainContent = document.querySelector('.main-content');

      menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
      });

      // Handle dropdown menu visibility
      const profileDropdown = document.querySelector('.profile-dropdown');
      const dropdownMenu = profileDropdown.querySelector('.dropdown-menu');

      profileDropdown.addEventListener('click', () => {
        dropdownMenu.classList.toggle('active');
      });

      // Close dropdown when clicking outside
      window.addEventListener('click', (event) => {
        if (!profileDropdown.contains(event.target)) {
          dropdownMenu.classList.remove('active');
        }
      });

      // Manage active state of navigation links and show respective content
      const navLinks = document.querySelectorAll('.nav-links a');
      const contentSections = document.querySelectorAll('.content-section');

      navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
          event.preventDefault(); // Prevent default anchor behavior
          
          // Get the target section ID from data-section attribute
          const targetSectionId = link.getAttribute('data-section');
          
          // Hide all content sections
          contentSections.forEach(section => {
            section.classList.remove('active');
          });

          // Remove active class from all links
          navLinks.forEach(nav => nav.classList.remove('active'));

          // Show the clicked section and add active class to the link
          const targetSection = document.getElementById(targetSectionId);
          if (targetSection) {
            targetSection.classList.add('active');
            link.classList.add('active');
          }
        });
      });

      // Handle dropdown menu visibility for ratings and coins
      const ratingsDropdown = document.querySelector('.ratings-reviews .dropdown-menu');
      const coinDropdown = document.querySelector('.coin-wallet .dropdown-menu');

      document.querySelector('.ratings-reviews').addEventListener('click', (event) => {
        event.stopPropagation(); // Prevent event from bubbling up
        ratingsDropdown.classList.toggle('active');
      });

      document.querySelector('.coin-wallet').addEventListener('click', (event) => {
        event.stopPropagation(); // Prevent event from bubbling up
        coinDropdown.classList.toggle('active');
      });

      // Close dropdowns when clicking outside
      window.addEventListener('click', () => {
        ratingsDropdown.classList.remove('active');
        coinDropdown.classList.remove('active');
      });

      // Fetch student requests using AJAX
      function fetchStudentRequests() {
          fetch('fetch_requests.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              }
          })
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              const requestsGrid = document.querySelector('.requests-grid');
              requestsGrid.innerHTML = ''; // Clear existing content

              if (data.length > 0) {
                  data.forEach(request => {
                      // Create card with applied class if tutor has already applied
                      const cardClass = request.has_applied ? 'request-card applied' : 'request-card';
                      const requestCard = `
                          <div class="${cardClass}">
                              <h3 class="student-name">${request.username}</h3>
                              <p class="requirements">${request.description}</p>
                              <div class="tags">
                                  <span class="tag">📚 ${request.subject}</span>
                                  <span class="tag">💰 $${request.fee_rate}/hour</span>
                                  <span class="tag">💻 ${request.mode_of_learning}</span>
                              </div>
                              <div class="request-info">
                                  <strong>Location:</strong> <span>${request.location}</span>
                                  <strong>Submitted:</strong> <span>${request.created_at}</span>
                                  <strong>Start Date:</strong> <span>${request.start_date}</span>
                                  <strong>End Date:</strong> <span>${request.end_date}</span>
                              </div>
                              ${request.has_applied ? 
                                  '<div class="applied-badge">Applied</div>' : 
                                  `<button class="connect-btn" data-request-id="${request.request_id}" data-responses="${request.response_count}">Connect with Student</button>`
                              }
                          </div>
                      `;
                      requestsGrid.innerHTML += requestCard;
                  });
              } else {
                  requestsGrid.innerHTML = "<p>No student requests found.</p>";
              }
          })
          .catch(error => {
              console.error('Error fetching requests:', error);
              const requestsGrid = document.querySelector('.requests-grid');
              requestsGrid.innerHTML = "<p>Error loading requests. Please try again later.</p>";
          });
      }

      // Call the function to fetch requests when the page loads
      document.addEventListener('DOMContentLoaded', fetchStudentRequests);

      // Add event listener to search button
      document.getElementById('search-button').addEventListener('click', function() {
        const location = document.querySelector('.filter-select:nth-of-type(1)').value;
        const subject = document.querySelector('.filter-select:nth-of-type(2)').value;
        const mode = document.querySelector('.filter-select:nth-of-type(3)').value;
        
        fetch('fetch_requests.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ location, subject, mode })
        })
        .then(response => response.json())
        .then(data => {
          const requestsGrid = document.querySelector('.requests-grid');
          requestsGrid.innerHTML = '';
          if (data.length > 0) {
            data.forEach(request => {
              const requestElement = document.createElement('div');
              requestElement.classList.add('request-item');
              requestElement.innerHTML = `<p>${request.title}</p><p>${request.description}</p>`;
              requestsGrid.appendChild(requestElement);
            });
          } else {
            requestsGrid.innerHTML = '<p>No student requests found.</p>';
          }
        })
        .catch(error => console.error('Error fetching requests:', error));
      });

      // Get the popup element
      const popup = document.querySelector('.confirmation-popup');

      // Handle connect button click
      document.addEventListener('click', (event) => {
        if (event.target.classList.contains('connect-btn')) {
          popup.style.display = 'flex';
          // Store the request ID and update the response count
          const requestId = event.target.dataset.requestId;
          const responseCount = event.target.dataset.responses;
          popup.dataset.requestId = requestId;
          document.getElementById('applicant-count').textContent = responseCount;
        }
      });

      // Handle confirmation actions
      document.querySelector('.confirm-connect').addEventListener('click', () => {
        const requestId = popup.dataset.requestId;
        const message = document.getElementById('connect-message').value.trim();
        
        // Validate message
        if (!message) {
            alert('Please enter a message for the student.');
            return;
        }
        
        // Send AJAX request to connect with the student
        fetch('connect_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `request_id=${requestId}&tutor_id=<?php echo $tutor_id; ?>&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Your response has been sent to the student!');
                // Optionally update the UI to show the request has been responded to
                const connectBtn = document.querySelector(`[data-request-id="${requestId}"]`);
                if (connectBtn) {
                    connectBtn.disabled = true;
                    connectBtn.textContent = 'Response Sent';
                }
            } else {
                alert('Failed to connect: ' + data.message);
            }
        })
        .catch(error => console.error('Error connecting with student:', error));
        
        // Reset the message field to default value
        document.getElementById('connect-message').value = 'I am interested in helping you with your studies.';
        popup.style.display = 'none';
      });

      document.querySelector('.cancel-connect').addEventListener('click', () => {
        popup.style.display = 'none';
      });

      // Close popup when clicking outside
      popup.addEventListener('click', (event) => {
        if (event.target === popup) {
          popup.style.display = 'none';
        }
      });
    </script>
  </body>
</html>