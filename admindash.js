document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const menuItems = document.querySelectorAll('.sidebar-nav-item');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });

    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });

    // Handle menu item clicks
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            menuItems.forEach(menuItem => menuItem.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');

            // Get the view to show
            const viewToShow = this.getAttribute('data-view');
            
            // Update page title
            const pageTitle = document.querySelector('.navbar-left h1');
            if (pageTitle) {
                pageTitle.textContent = viewToShow.charAt(0).toUpperCase() + viewToShow.slice(1);
            }

            // Hide all views
            const allViews = document.querySelectorAll('[id$="-view"]');
            allViews.forEach(view => {
                view.style.display = 'none';
            });

            // Show selected view with fade effect
            const selectedView = document.getElementById(viewToShow + '-view');
            if (selectedView) {
                selectedView.style.display = 'block';
                selectedView.style.opacity = '0';
                selectedView.style.transition = 'opacity 0.3s ease-in-out';
                
                setTimeout(() => {
                    selectedView.style.opacity = '1';
                }, 50);
            }

            // Close sidebar on mobile after selection
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    });

    // Initialize dashboard view as active
    const dashboardView = document.querySelector('[data-view="dashboard"]');
    if (dashboardView) {
        dashboardView.click();
    }

    // Profile dropdown functionality
    const profileTrigger = document.getElementById('profile-dropdown-trigger');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileTrigger && profileDropdown) {
        // Toggle dropdown on click
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileTrigger.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }
});