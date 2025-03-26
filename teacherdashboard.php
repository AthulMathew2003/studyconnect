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

      .nav-links {
        display: flex;
        flex-direction: column;
        gap: 1rem;
      }

      .nav-link {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        transition: 0.3s;
      }

      .nav-link:hover {
        background: var(--input-color);
        color: var(--accent-color);
      }

      .nav-link.active {
        background: var(--accent-color);
        color: var(--base-color);
      }

      .content-section {
        display: none;
      }

      .content-section.active {
        display: block;
      }

      /* Chat Layout Styles */
      .chat-layout {
        display: flex;
        height: 80vh;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      }

      /* Chat List Styles */
      .chat-list {
        width: 30%;
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
        background-color: #fff;
      }

      .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: #f5f5f5;
      }

      /* Chat Messages Section */
      .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background-color: #f5f5f5;
        display: flex;
        flex-direction: column;
      }

      /* Message Styles */
      .message {
        max-width: 70%;
        margin-bottom: 12px;
        display: flex;
        clear: both;
      }

      .message.sent {
        align-self: flex-end;
        justify-content: flex-end;
      }

      .message.received {
        align-self: flex-start;
      }

      .message-content {
        padding: 10px 15px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        max-width: 100%;
      }

      .message.sent .message-content {
        background-color: #0084ff;
        color: white;
        border-top-right-radius: 5px;
        margin-left: 10px;
      }

      .message.received .message-content {
        background-color: white;
        color: #333;
        border-top-left-radius: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        margin-right: 10px;
      }

      .message-time {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.7);
        text-align: right;
        display: block;
        margin-top: 5px;
      }

      .message.received .message-time {
        color: #999;
      }

      /* Date Separator */
      .message-date {
        text-align: center;
        margin: 15px 0;
        font-size: 0.8rem;
        color: #888;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .message-date:before, .message-date:after {
        content: "";
        height: 1px;
        background-color: #ddd;
        flex: 1;
        margin: 0 10px;
      }

      /* Chat Input Area */
      .chat-input {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        background-color: white;
        border-top: 1px solid #eee;
      }

      .message-input {
        flex: 1;
        margin: 0 10px;
      }

      .message-input textarea {
        width: 100%;
        padding: 10px 15px;
        border: none;
        border-radius: 20px;
        background-color: #f0f0f0;
        font-size: 14px;
        resize: none;
        overflow-y: auto;
        max-height: 150px;
        min-height: 40px;
        font-family: inherit;
      }

      .message-input textarea:focus {
        outline: none;
        background-color: #e8e8e8;
      }

      /* Send Button */
      .send-btn-main {
        background-color: #0084ff;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 15px;
        margin-left: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-weight: 500;
        transition: background-color 0.2s;
      }

      .send-btn-main i {
        margin-right: 5px;
      }

      .send-btn-main:hover {
        background-color: #0078e7;
      }

      .send-btn-main:active {
        background-color: #0069cc;
      }

      /* Chat Contacts */
      .chat-contacts {
        overflow-y: auto;
        flex: 1;
      }

      .chat-contact {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
      }

      .chat-contact:hover {
        background-color: #f5f5f5;
      }

      .chat-contact.active {
        background-color: #e9f3ff;
      }

      .contact-info {
        display: flex;
        flex-direction: column;
      }

      .contact-name {
        font-weight: 500;
        color: #333;
      }

      .contact-status {
        font-size: 0.8rem;
        color: #777;
      }

      /* Empty States */
      .no-conversation, .no-messages, .error-message, .no-contacts {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        color: #888;
        font-style: italic;
        text-align: center;
        padding: 20px;
      }

      /* Chat List Header */
      .chat-list-header {
        padding: 15px;
        border-bottom: 1px solid #eaeaea;
        background-color: #f9f9f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .user-profile {
        display: flex;
        align-items: center;
      }

      .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
      }

      .user-info h3 {
        font-size: 16px;
        margin: 0;
        color: #333;
      }

      .status {
        font-size: 12px;
        color: #4CAF50;
      }

      /* Chat Search */
      .chat-search {
        padding: 10px 15px;
        background-color: #fff;
        border-bottom: 1px solid #eaeaea;
      }

      .search-input {
        width: 100%;
        padding: 8px 15px;
        padding-left: 35px;
        border: 1px solid #e2e2e2;
        border-radius: 20px;
        font-size: 13px;
        background-color: #f5f5f5;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>');
        background-repeat: no-repeat;
        background-position: 10px center;
        transition: all 0.2s;
      }

      .search-input:focus {
        outline: none;
        border-color: #bfbfbf;
        background-color: #fff;
      }

      /* Chat Contacts */
      .chat-contacts {
        overflow-y: auto;
        flex: 1;
        background-color: #fff;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
      }

      .chat-contact {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
      }

      .chat-contact:hover {
        background-color: #f8f9fa;
      }

      .chat-contact.active {
        background-color: #e7f2ff;
        border-left: 3px solid #0084ff;
      }
      
      .chat-contact.active .contact-name {
        color: #0084ff;
      }

      .contact-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #0084ff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        margin-right: 12px;
        flex-shrink: 0;
      }

      /* Random colors for avatars to differentiate contacts */
      .chat-contact:nth-child(5n+1) .contact-avatar { background-color: #0084ff; }
      .chat-contact:nth-child(5n+2) .contact-avatar { background-color: #FF5722; }
      .chat-contact:nth-child(5n+3) .contact-avatar { background-color: #4CAF50; }
      .chat-contact:nth-child(5n+4) .contact-avatar { background-color: #9C27B0; }
      .chat-contact:nth-child(5n+5) .contact-avatar { background-color: #FF9800; }

      .contact-info {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-width: 0; /* Enables text truncation */
      }

      .contact-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
      }

      .contact-status {
        font-size: 12px;
        color: #72777a;
      }

      /* New message indicator */
      .chat-contact::after {
        content: "";
        display: none;
        width: 8px;
        height: 8px;
        background-color: #0084ff;
        border-radius: 50%;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
      }

      .chat-contact.has-new-message::after {
        display: block;
      }

      /* Empty state */
      .no-contacts {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 30px 20px;
        text-align: center;
        color: #72777a;
      }

      .no-contacts i {
        font-size: 40px;
        color: #d1d1d1;
        margin-bottom: 15px;
      }

      .no-contacts p {
        font-size: 14px;
        line-height: 1.5;
        max-width: 200px;
      }
    </style>
  </head>
  <body>
    <div class="dashboard-container">
      <nav class="sidebar">
        <div class="sidebar-logo">StudyConnect</div>
        <div class="nav-links">
          <a href="#" class="nav-link active" onclick="showContent('dashboard')">
            <i class="fas fa-home"></i> Dashboard
          </a>
          <a href="#" class="nav-link" onclick="showContent('student-requests')">
            <i class="fas fa-user-graduate"></i> Find a Student
          </a>
          <a href="#" class="nav-link" onclick="showContent('messages')">
            <i class="fas fa-comments"></i> Messages
          </a>
          <a href="#" class="nav-link" onclick="showContent('my-students')">
            <i class="fas fa-users"></i> My Students
          </a>
          <a href="#" class="nav-link" onclick="showContent('tutoring-requests')">
            <i class="fas fa-bell"></i> Tutoring Requests
          </a>
          <a href="#" class="nav-link" onclick="showContent('settings')">
            <i class="fas fa-cog"></i> Settings
          </a>
        </div>
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
      
        <div id="dashboard-content" class="content-section active">
          <div class="dashboard-grid">
            <div class="stat-card">
              <div class="stat-header">
                <h3>Active Students</h3>
                <span>📚</span>
              </div>
              <div class="stat-value">24</div>
              <div class="stat-trend positive">↑ 12% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Total Earnings</h3>
                <span>💰</span>
              </div>
              <div class="stat-value"><?php echo $coin_balance; ?> coins</div>
              <div class="stat-trend positive">↑ 8% from last month</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Rating</h3>
                <span>⭐</span>
              </div>
              <div class="stat-value">4.8</div>
              <div class="stat-trend neutral">120 reviews</div>
            </div>
          </div>
        </div>
        
        <div id="student-requests-content" class="content-section">
        <div class="requests-container">
            <div class="section-header">
              <h2>Find a Student</h2>
            </div>
            
            <!-- Add filters container here -->
            <div class="filters-container">
                <div class="filter-group">
                    <label class="filter-label">Subject</label>
                    <select class="filter-select" id="subject-filter">
                        <option value="all">All Subjects</option>
                        <?php
                        // Fetch subjects from tbl_subject
                        $subject_query = "SELECT * FROM tbl_subject ORDER BY subject";
                        $subject_result = $conn->query($subject_query);
                        while ($subject = $subject_result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($subject['subject']) . '">' 
                                 . htmlspecialchars($subject['subject']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Mode of Learning</label>
                    <select class="filter-select" id="mode-filter">
                        <option value="all">All Modes</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="both">Both</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Location</label>
                    <select class="filter-select" id="location-filter">
                        <option value="all">All Locations</option>
                        <?php
                        // Fetch unique locations from tbl_studentlocation
                        $location_query = "SELECT DISTINCT city, state FROM tbl_studentlocation ORDER BY city, state";
                        $location_result = $conn->query($location_query);
                        while ($location = $location_result->fetch_assoc()) {
                            $location_value = $location['city'] . ', ' . $location['state'];
                            echo '<option value="' . htmlspecialchars($location_value) . '">' 
                                 . htmlspecialchars($location_value) . '</option>';
                        }
                        ?>
                    </select>
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
        <p style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
            Your coin balance: <span class="coin-amount"><?php echo htmlspecialchars($coin_balance); ?></span> coins
        </p>
        <p style="color: #ff6b6b; font-size: 0.9rem;">
            Connecting with a student costs 50 coins
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

<!-- Update the insufficient coins modal to handle both cases -->
<div class="insufficient-coins-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="popup-content" style="background: white; padding: 2rem; border-radius: 12px; max-width: 400px; text-align: center;">
        <h3 style="color: #ff6b6b; margin-bottom: 1rem;">Connection Failed</h3>
        <div id="insufficient-coins-message">
            <p>You need at least 50 coins to connect with a student.</p>
            <p style="margin: 1rem 0;">Your current balance: <span class="coin-amount"><?php echo htmlspecialchars($coin_balance); ?></span> coins</p>
            <button onclick="window.location.href='buy_coins.php'" style="background: var(--accent-color); color: white; border: none; padding: 0.8rem 2rem; border-radius: 6px; cursor: pointer; margin-bottom: 1rem;">
                Buy Coins
            </button>
        </div>
        <button onclick="closeInsufficientCoinsModal()" style="background: #f3f0ff; color: var(--text-color); border: none; padding: 0.8rem 2rem; border-radius: 6px; cursor: pointer;">
            Close
        </button>
    </div>
</div>

        </div>
        </div>
        
        <div id="messages-content" class="content-section">
          <div class="messages-container">
            <div class="chat-layout">
              <!-- Chat List Sidebar -->
              <div class="chat-list">
                <div class="chat-list-header">
                  <div class="user-profile">
                    <img src="1.webp" alt="Profile" class="profile-img">
                    <div class="user-info">
                      <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                      <span class="status">Online</span>
                    </div>
                  </div>
                  <div class="chat-actions">
                    <button class="icon-btn">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                  </div>
                </div>
                
                <!-- Chat contacts list -->
                <div class="chat-contacts">
                  <?php
                  // Get approved students from responses
                  $stmt = $conn->prepare("
                    SELECT DISTINCT r.tutor_id, r.request_id, s.student_id, u.username
                    FROM tbl_response r
                    JOIN tbl_request req ON r.request_id = req.request_id
                    JOIN tbl_student s ON req.student_id = s.student_id
                    JOIN users u ON s.userid = u.userid
                    WHERE r.tutor_id = ? AND r.status = 'approved'
                  ");
                  
                  $stmt->bind_param("i", $tutor_id);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  
                  // Also get students from approved tutor requests
                  $tutor_requests = $conn->prepare("
                    SELECT tr.*, s.*, u.username, u.email, sl.*, t.teaching_mode,
                           tr.description
                    FROM tbl_tutorrequest tr
                    JOIN tbl_student s ON tr.student_id = s.student_id
                    JOIN users u ON s.userid = u.userid
                    JOIN tbl_studentlocation sl ON s.student_id = sl.student_id
                    JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                    WHERE tr.tutor_id = ? AND tr.status = 'approved'
                    GROUP BY tr.tutorrequestid
                  ");
                  
                  $tutor_requests->bind_param("i", $tutor_id);
                  $tutor_requests->execute();
                  $requests_result = $tutor_requests->get_result();
                  
                  // Combine both results to display all students
                  $displayed_students = [];
                  
                  // Display students from responses
                  while ($row = $result->fetch_assoc()) {
                    $student_id = $row['student_id'];
                    if (!in_array($student_id, $displayed_students)) {
                      $displayed_students[] = $student_id;
                      ?>
                      <div class="chat-contact" data-student-id="<?php echo $student_id; ?>">
                        <div class="contact-info">
                          <span class="contact-name"><?php echo htmlspecialchars($row['username']); ?></span>
                          <span class="contact-status">Student</span>
                        </div>
                      </div>
                      <?php
                    }
                  }
                  
                  // Display students from tutor requests
                  while ($row = $requests_result->fetch_assoc()) {
                    $student_id = $row['student_id'];
                    if (!in_array($student_id, $displayed_students)) {
                      $displayed_students[] = $student_id;
                      ?>
                      <div class="chat-contact" data-student-id="<?php echo $student_id; ?>">
                        <div class="contact-info">
                          <span class="contact-name"><?php echo htmlspecialchars($row['username']); ?></span>
                          <span class="contact-status">Student</span>
                        </div>
                      </div>
                      <?php
                    }
                  }
                  
                  if (empty($displayed_students)) {
                    ?>
                    <div class="no-contacts">
                      <p>No students to chat with yet</p>
                    </div>
                    <?php
                  }
                  ?>
                </div>
              </div>

              <!-- Chat Main Area -->
              <div class="chat-main">
                <div class="chat-header" id="chat-header">
                  <div class="chat-contact">
                    <img src="1.webp" alt="Contact" class="contact-img">
                    <div class="contact-info">
                      <h3 id="selected-contact-name">Select a student</h3>
                      <span class="status" id="selected-contact-status">-</span>
                    </div>
                  </div>
                  <div class="chat-actions">
                    <button class="icon-btn">
                      <i class="fas fa-search"></i>
                    </button>
                    <button class="icon-btn">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                  </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                  <div class="no-conversation">
                    <p>Select a student to start chatting</p>
                  </div>
                </div>

                <div class="chat-input" id="chat-input" style="display: none;">
                  <button class="icon-btn">
                    <i class="fas fa-smile"></i>
                  </button>
                  <button class="icon-btn">
                    <i class="fas fa-paperclip"></i>
                  </button>
                  <div class="message-input">
                    <textarea id="message-text" placeholder="Type a message" rows="1"></textarea>
                    <input type="hidden" id="selected-student-userid" value="">
                  </div>
                  <button class="send-btn-main" id="send-message-btn">
                    <i class="fas fa-paper-plane"></i> Send
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div id="my-students-content" class="content-section">
          <div class="my-students-container">
            <div class="section-header">
              <h2>My Students</h2>
              <p class="section-subtitle">Manage your current students and their progress</p>
            </div>
            <div class="students-grid">
              <?php
              // First query remains the same for responses
              $stmt = $conn->prepare("
                  SELECT r.*, req.*, s.*, u.username, u.email, sl.*
                  FROM tbl_response r
                  JOIN tbl_request req ON r.request_id = req.request_id
                  JOIN tbl_student s ON req.student_id = s.student_id
                  JOIN users u ON s.userid = u.userid
                  JOIN tbl_studentlocation sl ON s.student_id = sl.student_id
                  WHERE r.tutor_id = ? AND r.status = 'approved'
              ");
              
              $stmt->bind_param("i", $tutor_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
              // Second query for tutor requests
              $tutor_requests = $conn->prepare("
                  SELECT tr.*, s.*, u.username, u.email, sl.*, t.teaching_mode,
                         GROUP_CONCAT(subj.subject) as subjects
                  FROM tbl_tutorrequest tr
                  JOIN tbl_student s ON tr.student_id = s.student_id
                  JOIN users u ON s.userid = u.userid
                  JOIN tbl_studentlocation sl ON s.student_id = sl.student_id
                  JOIN tbl_tutors t ON tr.tutor_id = t.tutor_id
                  LEFT JOIN tbl_tutorsubject ts ON t.tutor_id = ts.tutor_id
                  LEFT JOIN tbl_subject subj ON ts.subject_id = subj.subject_id
                  WHERE tr.tutor_id = ? AND tr.status = 'approved'
                  GROUP BY tr.tutorrequestid
              ");
              
              $tutor_requests->bind_param("i", $tutor_id);
              $tutor_requests->execute();
              $requests_result = $tutor_requests->get_result();
              
              if ($result->num_rows > 0 || $requests_result->num_rows > 0) {
                  // Display students from responses
                  while ($row = $result->fetch_assoc()) {
                      displayStudentCard($row, 'response');
                  }
                  
                  // Display students from tutor requests
                  while ($row = $requests_result->fetch_assoc()) {
                      displayStudentCard($row, 'request');
                  }
              } else {
                  ?>
                  <div class="no-students">
                    <div class="empty-state">
                      <div class="empty-icon">👥</div>
                      <h3>No Students Yet</h3>
                      <p>You don't have any approved student connections yet. Check the Student Requests section to connect with students.</p>
                    </div>
                  </div>
                  <?php
              }
              
              $stmt->close();
              $tutor_requests->close();
              
              // Helper function to display student card
              function displayStudentCard($row, $type) {
                  ?>
                  <div class="student-card">
                    <div class="student-header">
                      <img src="<?php echo !empty($row['profilephoto']) ? htmlspecialchars($row['profilephoto']) : '1.webp'; ?>" 
                           alt="Student" class="student-avatar">
                      <div class="student-info">
                        <h3><?php echo htmlspecialchars($row['username']); ?></h3>
                        <div class="student-location">📍 <?php echo htmlspecialchars($row['city'] . ', ' . $row['country']); ?></div>
                      </div>
                    </div>
                    <div class="student-details">
                      <div class="detail-item">
                        <span class="detail-label">Type</span>
                        <span class="detail-value"><?php echo $type === 'response' ? 'Student Request' : 'Direct Request'; ?></span>
                      </div>
                      <?php if ($type === 'response'): ?>
                      <div class="detail-item">
                        <span class="detail-label">Subject</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['subject']); ?></span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Mode</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['mode_of_learning']); ?></span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Fee Rate</span>
                        <span class="detail-value">$<?php echo htmlspecialchars($row['fee_rate']); ?>/hour</span>
                      </div>
                      <?php else: ?>
                      <div class="detail-item">
                        <span class="detail-label">Subjects</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['description'] ?? 'Not specified'); ?></span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Mode</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['teaching_mode']); ?></span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Fee Rate</span>
                        <span class="detail-value">$<?php echo htmlspecialchars($row['feerate']); ?>/hour</span>
                      </div>
                      <?php endif; ?>
                    </div>
                    <div class="student-actions">
                      <button class="action-btn message-btn" onclick="startChat('<?php echo htmlspecialchars($row['username']); ?>')">Message</button>
                      <button class="action-btn profile-btn" onclick="window.location.href='display_studentprofile.php?student_id=<?php echo $row['student_id']; ?>'">View Profile</button>
                    </div>
                  </div>
                  <?php
              }
              ?>
            </div>
          </div>
        </div>
        
        <div id="settings-content" class="content-section">
          <div style="max-width: 800px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 2rem; color: var(--accent-color);">Settings</h2>
            
            <div style="margin-bottom: 2rem;">
              <h3 style="margin-bottom: 1rem;">Profile Settings</h3>
              <div style="display: grid; gap: 1rem;">
                <div>
                  <label style="display: block; margin-bottom: 0.5rem;">Display Name</label>
                  <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" style="width: 100%; padding: 0.8rem; border: 1px solid var(--input-color); border-radius: 8px;">
                </div>
                <div>
                  <label style="display: block; margin-bottom: 0.5rem;">Email</label>
                  <input type="email" value="user@example.com" style="width: 100%; padding: 0.8rem; border: 1px solid var(--input-color); border-radius: 8px;">
                </div>
              </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
              <h3 style="margin-bottom: 1rem;">Notification Settings</h3>
              <div style="display: grid; gap: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                  <input type="checkbox" checked> Email notifications for new messages
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                  <input type="checkbox" checked> Email notifications for new student requests
                </label>
              </div>
            </div>
            
            <button style="background: var(--accent-color); color: white; border: none; padding: 0.8rem 2rem; border-radius: 8px; cursor: pointer;">Save Changes</button>
          </div>
        </div>

        <div id="tutoring-requests-content" class="content-section">
          <div style="max-width: 800px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 2rem; color: var(--accent-color);">Tutoring Requests</h2>
            
            <div style="margin-bottom: 2rem;">
              <h3 style="margin-bottom: 1rem;">Pending Requests</h3>
              <div class="requests-list" style="display: grid; gap: 1rem;">
                <?php
                // Fetch pending requests (status = 'created')
                $pending_query = "SELECT tr.*, s.student_id, u.username, u.email 
                                 FROM tbl_tutorrequest tr
                                 JOIN tbl_student s ON tr.student_id = s.student_id
                                 JOIN users u ON s.userid = u.userid
                                 WHERE tr.tutor_id = $tutor_id AND tr.status = 'created'
                                 ORDER BY tr.created_at DESC";
                
                $pending_result = $conn->query($pending_query);
                
                if ($pending_result->num_rows > 0) {
                  while ($row = $pending_result->fetch_assoc()) {
                    ?>
                    <div style="padding: 1rem; border: 1px solid var(--input-color); border-radius: 8px; background: #f8f9fa;">
                      <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                          <h4 style="color: var(--text-color);"><?php echo htmlspecialchars($row['username']); ?></h4>
                          <a href="display_studentprofile.php?student_id=<?php echo $row['student_id']; ?>" 
                             style="font-size: 0.9rem; color: var(--accent-color); text-decoration: none; padding: 0.2rem 0.5rem; border: 1px solid var(--accent-color); border-radius: 4px;">
                            View Profile
                          </a>
                        </div>
                        <span style="color: #666; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                      </div>
                      <p style="color: #666; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($row['description']); ?></p>
                      <p style="color: #666; margin-bottom: 0.5rem;">Fee Rate: $<?php echo htmlspecialchars($row['feerate']); ?>/hour</p>
                      <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button onclick="updateRequestStatus(<?php echo $row['tutorrequestid']; ?>, 'approved')" 
                                style="background: var(--accent-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                          Accept
                        </button>
                        <button onclick="updateRequestStatus(<?php echo $row['tutorrequestid']; ?>, 'rejected')"
                                style="background: var(--error-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                          Decline
                        </button>
                      </div>
                    </div>
                    <?php
                  }
                } else {
                  echo "<p style='text-align: center; color: #666;'>No pending requests</p>";
                }
                ?>
              </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
              <h3 style="margin-bottom: 1rem;">Request History</h3>
              <div class="history-list" style="display: grid; gap: 1rem;">
                <?php
                // Fetch request history (status = 'approved' or 'rejected')
                $history_query = "SELECT tr.*, s.student_id, u.username, u.email 
                                 FROM tbl_tutorrequest tr
                                 JOIN tbl_student s ON tr.student_id = s.student_id
                                 JOIN users u ON s.userid = u.userid
                                 WHERE tr.tutor_id = $tutor_id AND tr.status != 'created'
                                 ORDER BY tr.created_at DESC";
                
                $history_result = $conn->query($history_query);
                
                if ($history_result->num_rows > 0) {
                  while ($row = $history_result->fetch_assoc()) {
                    $status_color = $row['status'] == 'approved' ? '#34c759' : '#ff3b30';
                    ?>
                    <div style="padding: 1rem; border: 1px solid var(--input-color); border-radius: 8px; background: white;">
                      <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <h4 style="color: var(--text-color);"><?php echo htmlspecialchars($row['username']); ?></h4>
                        <span style="color: <?php echo $status_color; ?>; font-weight: 500;">
                          <?php echo ucfirst($row['status']); ?>
                        </span>
                      </div>
                      <p style="color: #666; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($row['description']); ?></p>
                      <span style="color: #666; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                    </div>
                    <?php
                  }
                } else {
                  echo "<p style='text-align: center; color: #666;'>No request history</p>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
    // Add these functions at the beginning of your script tag
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
        });

        // Close sidebar when clicking outside (optional)
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('active');
            }
        });
    });

    //  // Handle dropdown menu visibility
    //  const profileDropdown = document.querySelector('.profile-dropdown');
    //   const dropdownMenu = profileDropdown.querySelector('.dropdown-menu');

    //   profileDropdown.addEventListener('click', () => {
    //     dropdownMenu.classList.toggle('active');
    //   });

    function showContent(contentId) {
        // Remove active class from all nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to the matching nav link
        document.querySelector(`.nav-link[onclick*="showContent('${contentId}')"]`).classList.add('active');
        
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Show selected content section
        document.getElementById(contentId + '-content').classList.add('active');
    }

    // Add this to your existing CSS
    const additionalStyles = `
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .nav-link:hover {
            background: var(--input-color);
            color: var(--accent-color);
        }

        .nav-link.active {
            background: var(--accent-color);
            color: var(--base-color);
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }
    `;
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
        const currentBalance = <?php echo $coin_balance ?? 0; ?>; // Get current balance
        
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
            body: `request_id=${requestId}&tutor_id=<?php echo $tutor_id; ?>&message=${encodeURIComponent(message)}&deduct_coins=50`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Your response has been sent to the student!');
                // Update displayed coin balance
                const coinAmountElements = document.querySelectorAll('.coin-amount');
                coinAmountElements.forEach(element => {
                    element.textContent = (currentBalance - 50);
                });
                // Update the UI to show the request has been responded to
                const connectBtn = document.querySelector(`[data-request-id="${requestId}"]`);
                if (connectBtn) {
                    connectBtn.disabled = true;
                    connectBtn.textContent = 'Response Sent';
                }
            } else {
                // Show error in modal
                const modal = document.querySelector('.insufficient-coins-modal');
                const messageDiv = document.getElementById('insufficient-coins-message');
                
                if (data.message === 'Insufficient coins') {
                    messageDiv.innerHTML = `
                        <p>You need at least 50 coins to connect with a student.</p>
                        <p style="margin: 1rem 0;">Your current balance: <span class="coin-amount">${currentBalance}</span> coins</p>
                        <button onclick="window.location.href='buy_coins.php'" style="background: var(--accent-color); color: white; border: none; padding: 0.8rem 2rem; border-radius: 6px; cursor: pointer; margin-bottom: 1rem;">
                            Buy Coins
                        </button>
                    `;
                } else {
                    messageDiv.innerHTML = `
                        <p>Failed to connect: ${data.message}</p>
                        <p style="margin: 1rem 0;">Please try again later or contact support if the problem persists.</p>
                    `;
                }
                
                modal.style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error connecting with student:', error);
            // Show error in modal
            const modal = document.querySelector('.insufficient-coins-modal');
            const messageDiv = document.getElementById('insufficient-coins-message');
            messageDiv.innerHTML = `
                <p>An error occurred while trying to connect.</p>
                <p style="margin: 1rem 0;">Please try again later or contact support if the problem persists.</p>
            `;
            modal.style.display = 'flex';
        });
        
        // Reset the message field to default value and close the confirmation popup
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
    // Add the styles to the existing style tag
    document.querySelector('style').textContent += additionalStyles;
    
    // Add this JavaScript after your existing script tag content
    document.addEventListener('DOMContentLoaded', function() {
        // Get all dropdown containers
        const dropdowns = document.querySelectorAll('.ratings-reviews, .coin-wallet, .profile-dropdown');
        
        // Add click handlers to each dropdown
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                // Close all other dropdowns first
                dropdowns.forEach(other => {
                    if (other !== dropdown) {
                        other.querySelector('.dropdown-menu').classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                const menu = this.querySelector('.dropdown-menu');
                menu.classList.toggle('active');
                
                // Stop event from bubbling up to document
                e.stopPropagation();
            });
        });
        
        // Close all dropdowns when clicking outside
        document.addEventListener('click', function() {
            dropdowns.forEach(dropdown => {
                dropdown.querySelector('.dropdown-menu').classList.remove('active');
            });
        });
    });
    
    // Add this function to your existing JavaScript
    function updateRequestStatus(requestId, status) {
      if (confirm('Are you sure you want to ' + status + ' this request?')) {
        fetch('update_request_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'request_id=' + requestId + '&status=' + status
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reload the page to show updated status
            location.reload();
          } else {
            alert('Error updating request status');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error updating request status');
        });
      }
    }

    // JavaScript to handle chat functionality
    document.addEventListener('DOMContentLoaded', function() {
      const chatContacts = document.querySelectorAll('.chat-contact');
      const chatMessages = document.getElementById('chat-messages');
      const chatHeader = document.getElementById('chat-header');
      const chatInput = document.getElementById('chat-input');
      const messageInput = document.getElementById('message-text');
      const sendButton = document.getElementById('send-message-btn');
      const selectedStudentInput = document.getElementById('selected-student-userid');
      
      // Function to fetch student details and their messages
      async function loadStudentChat(studentId) {
        try {
          const response = await fetch('get_student_chat.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `student_id=${studentId}`
          });
          
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          
          const data = await response.json();
          
          // Update chat header with student info
          document.getElementById('selected-contact-name').textContent = data.student.username;
          document.getElementById('selected-contact-status').textContent = 'Student';
          
          // Store student's userid for sending messages
          selectedStudentInput.value = data.student.userid;
          
          // Display chat input
          chatInput.style.display = 'flex';
          
          // Clear and populate chat messages
          chatMessages.innerHTML = '';
          
          if (data.messages.length === 0) {
            chatMessages.innerHTML = '<div class="no-messages"><p>No messages yet. Start the conversation!</p></div>';
            return;
          }
          
          // Group messages by date
          let currentDate = '';
          data.messages.forEach(message => {
            // Format the date
            const messageDate = new Date(message.sent_time);
            const formattedDate = messageDate.toLocaleDateString();
            
            // Add date separator if it's a new date
            if (formattedDate !== currentDate) {
              currentDate = formattedDate;
              const dateSeparator = document.createElement('div');
              dateSeparator.className = 'message-date';
              dateSeparator.textContent = currentDate;
              chatMessages.appendChild(dateSeparator);
            }
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = message.sender_id == <?php echo $_SESSION['userid']; ?> ? 'message sent' : 'message received';
            
            const messageTime = new Date(message.sent_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageDiv.innerHTML = `
              <div class="message-content">
                <p>${message.message_text}</p>
                <span class="message-time">${messageTime}</span>
              </div>
            `;
            
            chatMessages.appendChild(messageDiv);
          });
          
          // Scroll to bottom
          chatMessages.scrollTop = chatMessages.scrollHeight;
          
        } catch (error) {
          console.error('Error:', error);
          chatMessages.innerHTML = '<div class="error-message"><p>Failed to load chat messages</p></div>';
        }
      }
      
      // Add click event to chat contacts
      chatContacts.forEach(contact => {
        contact.addEventListener('click', function() {
          // Remove active class from all contacts
          chatContacts.forEach(c => c.classList.remove('active'));
          
          // Add active class to clicked contact
          this.classList.add('active');
          
          // Get student ID from data attribute
          const studentId = this.getAttribute('data-student-id');
          
          // Load chat for this student
          loadStudentChat(studentId);
        });
      });
      
      // Send message functionality - ONLY on button click, not Enter key
      sendButton.addEventListener('click', sendMessage);
      
      async function sendMessage() {
        const messageText = messageInput.value.trim();
        const receiverUserId = selectedStudentInput.value;
        
        if (!messageText || !receiverUserId) {
          return;
        }
        
        try {
          const response = await fetch('send_message.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${messageText}&receiver_id=${receiverUserId}`
          });
          
          if (!response.ok) {
            throw new Error('Failed to send message');
          }
          
          // Clear input field
          messageInput.value = '';
          
          // Refresh messages
          const studentId = document.querySelector('.chat-contact.active').getAttribute('data-student-id');
          loadStudentChat(studentId);
          
        } catch (error) {
          console.error('Error sending message:', error);
          alert('Failed to send message. Please try again.');
        }
      }
    });

    // Auto-resize textarea
    const messageTextarea = document.getElementById('message-text');
    
    messageTextarea.addEventListener('input', function() {
      // Reset height to default
      this.style.height = 'auto';
      
      // Calculate the new height (capped at 150px for max 5-6 lines)
      const newHeight = Math.min(this.scrollHeight, 150);
      
      // Set the new height
      this.style.height = newHeight + 'px';
    });

    // Add this to your existing DOMContentLoaded event handler
    document.addEventListener('DOMContentLoaded', function() {
      // Existing code...
      
      // Search contacts functionality
      const searchInput = document.getElementById('contact-search');
      const chatContacts = document.querySelectorAll('.chat-contact');
      
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const searchTerm = this.value.toLowerCase().trim();
          
          chatContacts.forEach(contact => {
            const username = contact.getAttribute('data-username').toLowerCase();
            if (username.includes(searchTerm)) {
              contact.style.display = 'flex';
            } else {
              contact.style.display = 'none';
            }
          });
        });
      }
      
      // Rest of your existing code...
    });

    // Add this function to your JavaScript section
    function startChat(username) {
        // First switch to the messages tab
        showContent('messages');
        
        // After a small delay to ensure the messages tab is loaded
        setTimeout(() => {
            // Find all chat contacts
            const chatContacts = document.querySelectorAll('.chat-contact');
            let contactFound = false;
            
            // Look for a contact with matching username
            chatContacts.forEach(contact => {
                const contactName = contact.querySelector('.contact-name');
                if (contactName && contactName.textContent === username) {
                    // Simulate a click on this contact
                    contact.click();
                    contactFound = true;
                }
            });
            
            // If no matching contact found, show a message
            if (!contactFound) {
                document.getElementById('chat-messages').innerHTML = `
                    <div class="no-conversation">
                        <p>Could not find a chat with ${username}.</p>
                        <p>This might occur if your connection was just approved.</p>
                        <p>Try refreshing the page to see all your current student connections.</p>
                        <button onclick="location.reload()" style="margin-top: 15px; padding: 8px 16px; background-color: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Refresh Page
                        </button>
                    </div>
                `;
            }
        }, 100); // Small delay to ensure tab switch is complete
    }

    // Update the filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const subjectFilter = document.getElementById('subject-filter');
        const modeFilter = document.getElementById('mode-filter');
        const locationFilter = document.getElementById('location-filter');
        
        // Function to apply filters
        function applyFilters() {
            const subject = subjectFilter.value;
            const mode = modeFilter.value;
            const location = locationFilter.value;
            
            // Create FormData object
            const formData = new FormData();
            formData.append('tutor_id', <?php echo $tutor_id; ?>);
            formData.append('subject', subject);
            formData.append('mode', mode);
            formData.append('location', location);
            
            // Fetch filtered requests
            fetch('fetch_requests.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const requestsGrid = document.querySelector('.requests-grid');
                requestsGrid.innerHTML = ''; // Clear existing content
                
                if (data.length > 0) {
                    data.forEach(request => {
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
                    requestsGrid.innerHTML = "<p>No student requests found matching the selected filters.</p>";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const requestsGrid = document.querySelector('.requests-grid');
                requestsGrid.innerHTML = "<p>Error loading requests. Please try again later.</p>";
            });
        }
        
        // Add event listeners to all filters
        subjectFilter.addEventListener('change', applyFilters);
        modeFilter.addEventListener('change', applyFilters);
        locationFilter.addEventListener('change', applyFilters);
        
        // Initial load with default filters
        applyFilters();
    });

    // Add function to close insufficient coins modal
    function closeInsufficientCoinsModal() {
        document.querySelector('.insufficient-coins-modal').style.display = 'none';
    }
    </script>
  </body>
</html>