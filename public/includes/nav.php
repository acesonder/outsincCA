<?php
/**
 * OUTSINC - Navigation Include
 * Common navigation for all authenticated pages
 */

$userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$userRole = $_SESSION['role'];
?>
<!-- Navigation -->
<nav class="navbar navbar-3d" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1030;">
    <div class="container-fluid">
        <div class="navbar-container">
            <a href="/public/dashboard.php" class="navbar-brand">
                <span>OUTSINC</span>
            </a>
            
            <div class="hamburger">
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
            </div>
            
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="/public/dashboard.php" class="navbar-link">
                        🏠 Dashboard
                    </a>
                </li>
                
                <?php if ($userRole === ROLE_CLIENT): ?>
                <li class="navbar-item">
                    <a href="/public/client/profile.php" class="navbar-link">
                        👤 My Profile
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="/public/client/goals.php" class="navbar-link">
                        🎯 My Goals
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="/public/resources.php" class="navbar-link">
                        📚 Resources
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($userRole, [ROLE_WORKER, ROLE_ADMIN])): ?>
                <li class="navbar-item dropdown">
                    <a href="#" class="navbar-link dropdown-toggle">
                        📋 Clients
                    </a>
                    <div class="dropdown-menu">
                        <a href="/public/clients/list.php" class="dropdown-item">Client List</a>
                        <a href="/public/clients/add.php" class="dropdown-item">Add New Client</a>
                        <a href="/public/clients/assessments.php" class="dropdown-item">Assessments</a>
                    </div>
                </li>
                <li class="navbar-item">
                    <a href="/public/orders/new.php" class="navbar-link">
                        📦 Orders
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($userRole === ROLE_ADMIN): ?>
                <li class="navbar-item dropdown">
                    <a href="#" class="navbar-link dropdown-toggle">
                        ⚙️ Admin
                    </a>
                    <div class="dropdown-menu">
                        <a href="/public/admin/users.php" class="dropdown-item">User Management</a>
                        <a href="/public/admin/settings.php" class="dropdown-item">Settings</a>
                        <a href="/public/admin/reports.php" class="dropdown-item">Reports</a>
                    </div>
                </li>
                <?php endif; ?>
                
                <li class="navbar-item">
                    <a href="/public/resources.php" class="navbar-link">
                        🔍 Search
                    </a>
                </li>
                
                <li class="navbar-item">
                    <a href="#" class="navbar-link">
                        🔔 <span class="badge">3</span>
                    </a>
                </li>
                
                <li class="navbar-item dropdown">
                    <a href="#" class="navbar-link dropdown-toggle">
                        👤 <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                    </a>
                    <div class="dropdown-menu">
                        <a href="/public/profile.php" class="dropdown-item">View Profile</a>
                        <a href="/public/settings.php" class="dropdown-item">Settings</a>
                        <a href="/public/preferences.php" class="dropdown-item">Preferences</a>
                        <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid var(--border-color);">
                        <a href="#" onclick="logout(); return false;" class="dropdown-item">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Marquee -->
<div class="marquee-container" style="margin-top: 70px;">
    <div class="marquee">
        <span class="marquee-content">Welcome to OUTSINC - Meeting people where they're at, walking with them where they want to go</span>
        <span class="marquee-content">Welcome to OUTSINC - Meeting people where they're at, walking with them where they want to go</span>
    </div>
</div>

<script>
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('/api/auth/logout.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.location.href = '/index.php';
                }
            });
    }
}
</script>
