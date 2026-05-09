document.addEventListener('DOMContentLoaded', () => {
    // Splash Screen Logic
    const splashScreen = document.getElementById('splash-screen');
    if (splashScreen) {
        setTimeout(() => {
            splashScreen.style.opacity = '0';
            setTimeout(() => {
                splashScreen.style.display = 'none';
                // If it's the index page, redirect to dashboard or login
                if (window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/')) {
                    window.location.href = 'login.php';
                }
            }, 500);
        }, 2000); // 2 seconds splash
    }

    // Sidebar Toggle Logic for Mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
});
