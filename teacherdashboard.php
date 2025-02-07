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
    </style>
  </head>
  <body>
    <!-- Remove this header section -->
    <!-- <header class="main-header">
      <h1>StudyConnect Teacher Dashboard</h1>
    </header> -->

    <div class="dashboard-container">
      <nav class="sidebar">
        <div class="sidebar-logo">StudyConnect</div>
        <ul class="nav-links">
          <li><a href="#" class="active">Dashboard</a></li>
          <li><a href="#">Student Request</a></li>
          <li><a href="#">Messages</a></li>
          <li><a href="#">Earnings</a></li>
          <li><a href="#">Settings</a></li>
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
                  Balance: <span class="coin-amount">1,500 coins</span>
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
                <a href="#">Profile</a>
                <a href="#">Account</a>
                <a href="#">Logout</a>
              </div>
            </div>
          </div>
        </header>
        <!-- Dashboard Grid Layout -->
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
              <h3>Course Completion</h3>
              <span class="stat-icon">📊</span>
            </div>
            <div class="stat-value">87%</div>
            <div class="stat-trend positive">↑ 5% vs last month</div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <h3>Monthly Earnings</h3>
              <span class="stat-icon">💰</span>
            </div>
            <div class="stat-value">$3,240</div>
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

          <!-- Recent Student Activity -->
          <div class="dashboard-card student-activity">
            <h3>Recent Student Activity</h3>
            <div class="activity-list">
              <div class="activity-item">
                <img
                  src="/api/placeholder/40/40"
                  alt="Student"
                  class="activity-avatar"
                />
                <div class="activity-info">
                  <h4>Sarah Johnson</h4>
                  <p>Completed Assignment: Quantum Physics Quiz</p>
                  <span class="activity-time">10 minutes ago</span>
                </div>
              </div>
              <div class="activity-item">
                <img
                  src="/api/placeholder/40/40"
                  alt="Student"
                  class="activity-avatar"
                />
                <div class="activity-info">
                  <h4>Michael Chen</h4>
                  <p>Submitted Project: Chemical Reactions</p>
                  <span class="activity-time">25 minutes ago</span>
                </div>
              </div>
              <div class="activity-item">
                <img
                  src="/api/placeholder/40/40"
                  alt="Student"
                  class="activity-avatar"
                />
                <div class="activity-info">
                  <h4>Emily Williams</h4>
                  <p>Asked Question in: Calculus Forum</p>
                  <span class="activity-time">1 hour ago</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dashboard content goes here -->
      </main>
    </div>

    <!-- Add footer before closing body tag -->
    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-section">
          <h4>Quick Links</h4>
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
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024 StudyConnect. All rights reserved.</p>
      </div>
    </footer>

    <script>
      // Toggle sidebar
      const menuToggle = document.querySelector(".menu-toggle");
      const sidebar = document.querySelector(".sidebar");
      const mainContent = document.querySelector(".main-content");

      menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        mainContent.classList.toggle("active");
      });

      // Toggle dropdowns
      const dropdownToggleElements = document.querySelectorAll(
        ".profile-dropdown, .coin-wallet, .ratings-reviews"
      );

      dropdownToggleElements.forEach((element) => {
        element.addEventListener("click", (e) => {
          e.stopPropagation();
          const dropdown = element.querySelector(".dropdown-menu");

          // Close all other dropdowns
          document.querySelectorAll(".dropdown-menu.active").forEach((menu) => {
            if (menu !== dropdown) {
              menu.classList.remove("active");
            }
          });

          dropdown.classList.toggle("active");
        });
      });

      // Close dropdowns when clicking outside
      document.addEventListener("click", () => {
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
          menu.classList.remove("active");
        });
      });

      // Close sidebar when clicking outside on mobile
      document.addEventListener("click", (e) => {
        if (
          window.innerWidth <= 768 &&
          !sidebar.contains(e.target) &&
          !menuToggle.contains(e.target) &&
          sidebar.classList.contains("active")
        ) {
          sidebar.classList.remove("active");
          mainContent.classList.remove("active");
        }
      });
    </script>
  </body>
</html>
