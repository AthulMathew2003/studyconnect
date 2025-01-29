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
        switch (view) {
          case "dashboard":
            dashboardView.style.display = "grid";
            usersView.style.display = "none";
            break;
          case "users":
            dashboardView.style.display = "none";
            usersView.style.display = "block";
            break;
          // Add more cases for other views as needed
        }

        // Close sidebar on mobile after selection
        if (window.innerWidth <= 768) {
          sidebar.classList.remove("show");
          sidebarOverlay.classList.remove("show");
        }
      });
    });

    // Close dropdown if clicked outside
    document.addEventListener("click", function (event) {
      if (!profileDropdownTrigger.contains(event.target)) {
        profileDropdown.classList.remove("show");
      }
    });
  });
})();
