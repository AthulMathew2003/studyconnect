(function () {
  document.addEventListener("DOMContentLoaded", function () {
    const profileDropdownTrigger = document.getElementById(
      "profile-dropdown-trigger"
    );
    const profileDropdown = document.getElementById("profile-dropdown");
    const sidebarToggle = document.getElementById("sidebar-toggle");
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebar-overlay");
    const darkModeToggle = document.getElementById("dark-mode-toggle");
    const body = document.body;
    const navItems = document.querySelectorAll(".sidebar-nav-item");

    // Get view containers
    const dashboardView = document.getElementById("dashboard-view");
    const usersView = document.getElementById("users-view");
    const reportsView = document.getElementById("reports-view");
    const settingsView = document.getElementById("settings-view");

    // Profile Dropdown
    profileDropdownTrigger.addEventListener("click", function (event) {
      event.stopPropagation();
      profileDropdown.classList.toggle("show");
    });

    // Sidebar Toggle for Mobile
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("show");
      sidebarOverlay.classList.toggle("show");
    });

    // Sidebar Overlay Close
    sidebarOverlay.addEventListener("click", function () {
      sidebar.classList.remove("show");
      sidebarOverlay.classList.remove("show");
    });

    // Dark Mode Toggle
    darkModeToggle.addEventListener("click", function () {
      body.classList.toggle("dark-mode");
    });

    // Navigation Item Selection
    navItems.forEach((item) => {
      item.addEventListener("click", function () {
        // Remove active class from all items
        navItems.forEach((nav) => nav.classList.remove("active"));
        // Add active class to clicked item
        this.classList.add("active");

        // Handle view switching
        const view = this.getAttribute("data-view");
        
        // Hide all views first
        if (dashboardView) dashboardView.style.display = "none";
        if (usersView) usersView.style.display = "none";
        if (reportsView) reportsView.style.display = "none";
        if (settingsView) settingsView.style.display = "none";

        // Show the selected view
        switch (view) {
          case "dashboard":
            if (dashboardView) dashboardView.style.display = "grid";
            break;
          case "users":
            if (usersView) usersView.style.display = "block";
            break;
          case "reports":
            if (reportsView) reportsView.style.display = "block";
            break;
          case "settings":
            if (settingsView) settingsView.style.display = "block";
            break;
        }

        // Update the header text
        const header = document.querySelector(".navbar-left h1");
        if (header) {
          header.textContent = view.charAt(0).toUpperCase() + view.slice(1);
        }

        // Close sidebar on mobile after selection
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
      });
    });

    // Close profile dropdown when clicking outside
    document.addEventListener("click", function (event) {
      if (!profileDropdownTrigger.contains(event.target)) {
        profileDropdown.classList.remove("show");
      }
    });
  });
})();