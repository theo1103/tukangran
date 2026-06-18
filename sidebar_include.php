<?php
// Sidebar include - used across all dashboard pages
if (!isset($_SESSION['user_authenticated'])) {
    header('Location: index.php');
    exit;
}

$isAdmin = ($_SESSION['role'] === 'administrator');
$username = $_SESSION['username'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Get pending count for admin
$pendingCount = 0;
if ($isAdmin && file_exists(__DIR__ . '/_db/db_user.json')) {
    $users = json_decode(file_get_contents(__DIR__ . '/_db/db_user.json'), true) ?? [];
    foreach ($users as $u) {
        if (($u['status'] ?? '') === 'pending') $pendingCount++;
    }
}
?>
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
            <li class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <i class="ri-home-4-line"></i>
                    <span>Home</span>
                </a>
            </li>
            
            <?php if ($isAdmin): ?>
            <li class="nav-item <?php echo $currentPage === 'adm_user.php' ? 'active' : ''; ?>">
                <a href="adm_user.php" class="nav-link">
                    <i class="ri-admin-line"></i>
                    <span>Management</span>
                    <?php if ($pendingCount > 0): ?>
                    <span class="badge badge-pending"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item <?php echo $currentPage === 'mailing_news.php' ? 'active' : ''; ?>">
                <a href="mailing_news.php" class="nav-link">
                    <i class="ri-mail-line"></i>
                    <span>Mailing & News</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Workstaholic</span>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'tools_ran.php' ? 'active' : ''; ?>">
                <a href="tools_ran.php" class="nav-link">
                    <i class="ri-search-eye-line"></i>
                    <span>Tools RAN</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'massive_incident.php' ? 'active' : ''; ?>">
                <a href="massive_incident.php" class="nav-link">
                    <i class="ri-alert-line"></i>
                    <span>Massive Incident Tools</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'tools_compiler.php' ? 'active' : ''; ?>">
                <a href="tools_compiler.php" class="nav-link">
                    <i class="ri-file-excel-2-line"></i>
                    <span>Tools Compiler</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'tools_mapping.php' ? 'active' : ''; ?>">
                <a href="tools_mapping.php" class="nav-link">
                    <i class="ri-map-pin-line"></i>
                    <span>Tools Mapping Long Lat</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'tools_validate.php' ? 'active' : ''; ?>">
                <a href="tools_validate.php" class="nav-link">
                    <i class="ri-check-double-line"></i>
                    <span>Tools Validate LTOA OWS</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'main_tools.php' ? 'active' : ''; ?>">
                <a href="main_tools.php" class="nav-link">
                    <i class="ri-database-2-line"></i>
                    <span>Main Tools</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $currentPage === 'knowledge.php' ? 'active' : ''; ?>">
                <a href="knowledge.php" class="nav-link">
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