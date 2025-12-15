<?php
/**
 * OUTSINC - Dashboard
 * Main dashboard after login
 */

require_once __DIR__ . '/../config/config.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

$pageTitle = 'Dashboard';
$userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$userRole = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - OUTSINC</title>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/styles.css'); ?>">
    <style>
        body {
            padding-top: 70px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--bg-primary);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--primary-color);
            transition: all var(--transition-base);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-base);
            text-decoration: none;
            display: block;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            color: white;
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .recent-activity {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-3d">
        <div class="container">
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
                        <a href="/public/dashboard.php" class="navbar-link active">
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
                        <a href="/public/orders/list.php" class="navbar-link">
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
                            <hr>
                            <a href="#" onclick="logout(); return false;" class="dropdown-item">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Marquee -->
    <div class="marquee-container">
        <div class="marquee">
            <span class="marquee-content">Welcome to OUTSINC - Meeting people where they're at, walking with them where they want to go</span>
            <span class="marquee-content">Welcome to OUTSINC - Meeting people where they're at, walking with them where they want to go</span>
        </div>
    </div>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <p>Role: <?php echo ucfirst($userRole); ?> | Last login: <?php echo date('F j, Y'); ?></p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <?php if ($userRole === ROLE_CLIENT): ?>
            <div class="stat-card">
                <div class="stat-value">2</div>
                <div class="stat-label">Active Goals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">1</div>
                <div class="stat-label">Upcoming Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">5</div>
                <div class="stat-label">Connected Resources</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">3</div>
                <div class="stat-label">Unread Messages</div>
            </div>
            <?php elseif (in_array($userRole, [ROLE_WORKER, ROLE_ADMIN])): ?>
            <div class="stat-card">
                <div class="stat-value">12</div>
                <div class="stat-label">Active Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">5</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">3</div>
                <div class="stat-label">Due Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">8</div>
                <div class="stat-label">New Reports</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <h2 class="section-header">Quick Actions</h2>
        <div class="quick-actions">
            <?php if ($userRole === ROLE_CLIENT): ?>
            <a href="#" class="action-card">
                <div class="action-icon">📝</div>
                <div>Update My Info</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon">🎯</div>
                <div>Set New Goal</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon">📅</div>
                <div>Book Appointment</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon">💬</div>
                <div>Send Message</div>
            </a>
            <?php elseif (in_array($userRole, [ROLE_WORKER, ROLE_ADMIN])): ?>
            <a href="/public/clients/add.php" class="action-card">
                <div class="action-icon">➕</div>
                <div>Add Client</div>
            </a>
            <a href="/public/orders/new.php" class="action-card">
                <div class="action-icon">📦</div>
                <div>New Order</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon">📋</div>
                <div>Add Case Note</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon">📊</div>
                <div>View Reports</div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <h2 class="section-header">Recent Activity</h2>
        <div class="recent-activity">
            <p class="text-secondary">No recent activity to display.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> OUTSINC. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <div class="back-to-top">↑</div>

    <script src="/assets/js/main.js"></script>
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
</body>
</html>
