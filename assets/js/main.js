/**
 * Main Application Scripts - Fully Responsive Drawer & Modal System
 */

document.addEventListener('DOMContentLoaded', () => {
    // Mobile & Tablet Sidebar Drawer Handling
    const toggleBtn = document.querySelector('.menu-toggle-btn');
    const sidebar = document.getElementById('sidebar') || document.querySelector('.sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const closeBtn = document.getElementById('sidebar-close-btn');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.classList.add('sidebar-opened');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('sidebar-opened');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
            // Also close active modal if any
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) activeModal.classList.remove('active');
        }
    });

    // Close sidebar on small screens when clicking a nav link
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });

    // Auto-dismiss alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});

// Modal Helpers
function openModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.remove('active');
        // Only restore body overflow if no other modal is active
        if (!document.querySelector('.modal-overlay.active')) {
            document.body.style.overflow = '';
        }
    }
}
