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
        item.addEventListener('click', function(e) {
            if (this.id === 'dark-mode-toggle') return; // Skip for dark mode toggle

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

            // Show selected view
            const selectedView = document.getElementById(viewToShow + '-view');
            if (selectedView) {
                selectedView.style.display = 'block';
            }
        });
    });

    // Handle form submission
    const addDataForm = document.getElementById('addDataForm');
    if (addDataForm) {
        addDataForm.addEventListener('submit', function(e) {
            const dataType = document.getElementById('dataType').value;
            const name = document.getElementById('name').value;
            
            if (dataType === '' || name.trim() === '') {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
        });
    }

    // Handle profile dropdown
    const profileDropdownTrigger = document.getElementById('profile-dropdown-trigger');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileDropdownTrigger && profileDropdown) {
        profileDropdownTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !profileDropdownTrigger.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
});