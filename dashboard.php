<?php
session_start();
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'api/auth_handler.php';
$auth = new AuthHandler();
$isAdmin = ($_SESSION['role'] === 'administrator');
$username = $_SESSION['username'];

// Get pending user count for admin notification
$pendingCount = 0;
if ($isAdmin) {
    $users = $auth->getAllUsers();
    foreach ($users as $u) {
        if ($u['status'] === 'pending') $pendingCount++;
    }
}

$nonce = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'nonce-<?php echo $nonce; ?>' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://api.openweathermap.org https://*.cnnindonesia.com;">
    <title>NetTech Suite - Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="dashboard-body">
    <!-- Loading Screen -->
    <div id="loadingScreen" class="loading-screen">
        <div class="loader-wrapper">
            <div class="loader-ring"></div>
            <div class="loader-ring-inner"></div>
            <div class="loader-text">Loading NetTech Suite...</div>
            <div class="loader-progress">
                <div class="loader-progress-bar" id="loaderProgress"></div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="ri-radar-line"></i>
                <span>NetTech Suite</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="ri-menu-fold-line"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item active">
                    <a href="dashboard.php" class="nav-link" data-page="home">
                        <i class="ri-home-4-line"></i>
                        <span>Home</span>
                    </a>
                </li>
                
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a href="adm_user.php" class="nav-link" data-page="management">
                        <i class="ri-admin-line"></i>
                        <span>Management</span>
                        <?php if ($pendingCount > 0): ?>
                        <span class="badge badge-pending"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="mailing_news.php" class="nav-link" data-page="news">
                        <i class="ri-mail-line"></i>
                        <span>Mailing & News</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="nav-section-title">Workstaholic</span>
                </li>
                
                <li class="nav-item">
                    <a href="tools_ran.php" class="nav-link" data-page="tools-ran">
                        <i class="ri-search-eye-line"></i>
                        <span>Tools RAN</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="massive_incident.php" class="nav-link" data-page="massive-incident">
                        <i class="ri-alert-line"></i>
                        <span>Massive Incident Tools</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="tools_compiler.php" class="nav-link" data-page="tools-compiler">
                        <i class="ri-file-excel-2-line"></i>
                        <span>Tools Compiler</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="tools_mapping.php" class="nav-link" data-page="tools-mapping">
                        <i class="ri-map-pin-line"></i>
                        <span>Tools Mapping Long Lat</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="tools_validate.php" class="nav-link" data-page="tools-validate">
                        <i class="ri-check-double-line"></i>
                        <span>Tools Validate LTOA OWS</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="main_tools.php" class="nav-link" data-page="main-tools">
                        <i class="ri-database-2-line"></i>
                        <span>Main Tools</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="knowledge.php" class="nav-link" data-page="knowledge">
                        <i class="ri-book-open-line"></i>
                        <span>Knowledge</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info-mini">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($username, 0, 2)); ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="ri-logout-box-r-line"></i>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="ri-menu-line"></i>
                </button>
                <h2 class="page-title" id="pageTitle">Dashboard</h2>
            </div>
            <div class="top-bar-right">
                <!-- Weather Widget -->
                <div class="weather-widget" id="weatherWidget">
                    <div class="weather-loading">
                        <i class="ri-sun-line spinning"></i>
                    </div>
                    <div class="weather-data" style="display:none;">
                        <img class="weather-icon" id="weatherIcon" src="" alt="">
                        <span class="weather-temp" id="weatherTemp">--°C</span>
                        <span class="weather-city" id="weatherCity">--</span>
                    </div>
                </div>
                
                <!-- Live Clock -->
                <div class="top-clock" id="topClock">
                    <i class="ri-time-line"></i>
                    <span id="clockDisplay">--:--:--</span>
                    <span class="gmt-label" id="gmtLabel">GMT</span>
                </div>
                
                <!-- Theme Toggle -->
                <button class="theme-toggle-btn" id="themeToggleBtn">
                    <i class="ri-sun-line"></i>
                    <i class="ri-moon-line"></i>
                </button>
                
                <!-- Notifications -->
                <div class="notification-bell" id="notificationBell">
                    <i class="ri-notification-3-line"></i>
                    <?php if ($pendingCount > 0): ?>
                    <span class="notif-dot"></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content Area -->
        <div class="content-area" id="contentArea">
            <!-- Welcome Section -->
            <div class="welcome-section fade-in">
                <div class="welcome-card glass-morphism">
                    <div class="welcome-text">
                        <h1>Welcome back, <span class="highlight"><?php echo htmlspecialchars($username); ?></span></h1>
                        <p>Network Technology Management Suite - All systems operational</p>
                    </div>
                    <div class="welcome-stats">
                        <div class="stat-card">
                            <i class="ri-database-2-line"></i>
                            <div class="stat-info">
                                <span class="stat-value" id="statSites">--</span>
                                <span class="stat-label">Total Sites</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="ri-check-line"></i>
                            <div class="stat-info">
                                <span class="stat-value" id="statActive">--</span>
                                <span class="stat-label">Active</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="ri-alert-line"></i>
                            <div class="stat-info">
                                <span class="stat-value" id="statDown">--</span>
                                <span class="stat-label">Down</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access Grid -->
            <div class="quick-access-grid fade-in-delayed">
                <a href="tools_ran.php" class="quick-card">
                    <div class="quick-icon"><i class="ri-search-eye-line"></i></div>
                    <h3>Tools RAN</h3>
                    <p>Search & convert Excel to JSON</p>
                </a>
                <a href="massive_incident.php" class="quick-card">
                    <div class="quick-icon"><i class="ri-alert-line"></i></div>
                    <h3>Incident Tools</h3>
                    <p>Track & analyze site incidents</p>
                </a>
                <a href="tools_mapping.php" class="quick-card">
                    <div class="quick-icon"><i class="ri-map-pin-line"></i></div>
                    <h3>Mapping</h3>
                    <p>Geospatial site visualization</p>
                </a>
                <a href="main_tools.php" class="quick-card">
                    <div class="quick-icon"><i class="ri-database-2-line"></i></div>
                    <h3>Main Database</h3>
                    <p>Manage CMDB & site data</p>
                </a>
            </div>
            
            <!-- Recent Activity / News Preview -->
            <div class="dashboard-grid fade-in-delayed-2">
                <div class="dashboard-card glass-morphism">
                    <h3><i class="ri-history-line"></i> Recent Activity</h3>
                    <div class="activity-list" id="activityList">
                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-info">
                                <p>System initialized</p>
                                <span>Just now</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card glass-morphism">
                    <h3><i class="ri-newspaper-line"></i> Latest News</h3>
                    <div class="news-preview" id="newsPreview">
                        <div class="news-loading">
                            <i class="ri-loader-4-line spinning"></i> Loading news...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Hidden security layer -->
    <?php /* All authentication processed server-side via api/auth_handler.php */ ?>
    
    <script nonce="<?php echo $nonce; ?>" src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/theme.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/app.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/weather.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/news.js"></script>
    <script nonce="<?php echo $nonce; ?>">
        // Loading screen
        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingScreen');
            const progressBar = document.getElementById('loaderProgress');
            let progress = 0;
            
            const interval = setInterval(() => {
                progress += Math.random() * 30;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    setTimeout(() => {
                        loader.classList.add('loaded');
                        setTimeout(() => loader.remove(), 500);
                    }, 300);
                }
                progressBar.style.width = progress + '%';
            }, 200);
        });
        
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
        
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        });
        
        // Live clock update
        function updateTopClock() {
            const now = new Date();
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const offset = -now.getTimezoneOffset() / 60;
            document.getElementById('clockDisplay').textContent = 
                now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            document.getElementById('gmtLabel').textContent = 
                'GMT' + (offset >= 0 ? '+' : '') + offset;
        }
        setInterval(updateTopClock, 1000);
        updateTopClock();
        
        // Initialize weather
        if (typeof initWeather === 'function') initWeather('e922021fb8eb9da60417d263b5649b5c');
        
        // Initialize news
        if (typeof initNews === 'function') initNews();
        
        // Load stats
        function loadStats() {
            fetch('api/json_sync.php?action=stats')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('statSites').textContent = data.totalSites || 0;
                    document.getElementById('statActive').textContent = data.activeSites || 0;
                    document.getElementById('statDown').textContent = data.downSites || 0;
                })
                .catch(() => {
                    document.getElementById('statSites').textContent = 'N/A';
                    document.getElementById('statActive').textContent = 'N/A';
                    document.getElementById('statDown').textContent = 'N/A';
                });
        }
        loadStats();
    </script>
</body>
</html>