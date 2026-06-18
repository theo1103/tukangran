<?php
session_start();
if (!isset($_SESSION['user_authenticated']) || $_SESSION['role'] !== 'administrator') {
    header('Location: dashboard.php');
    exit;
}

require_once 'api/auth_handler.php';
$auth = new AuthHandler();

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = $_POST['user_id'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $auth->updateUserStatus($userId, 'approved');
    } elseif ($action === 'reject') {
        $auth->updateUserStatus($userId, 'rejected');
    }
    
    header('Location: adm_user.php?msg=' . $action);
    exit;
}

$users = $auth->getAllUsers();
$pendingUsers = array_filter($users, fn($u) => $u['status'] === 'pending');
$approvedUsers = array_filter($users, fn($u) => $u['status'] === 'approved');
$rejectedUsers = array_filter($users, fn($u) => $u['status'] === 'rejected');

$nonce = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - NetTech Suite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="dashboard-body">
    <div id="loadingScreen" class="loading-screen">
        <div class="loader-wrapper">
            <div class="loader-ring"></div>
            <div class="loader-text">Loading...</div>
        </div>
    </div>
    
    <?php include 'sidebar_include.php'; ?>
    
    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h2 class="page-title">User Management</h2>
            </div>
            <div class="top-bar-right">
                <div class="top-clock">
                    <i class="ri-time-line"></i>
                    <span id="clockDisplay">--:--:--</span>
                </div>
                <button class="theme-toggle-btn"><i class="ri-sun-line"></i><i class="ri-moon-line"></i></button>
            </div>
        </header>
        
        <div class="content-area">
            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="pending">
                    Pending <span class="tab-count"><?php echo count($pendingUsers); ?></span>
                </button>
                <button class="tab-btn" data-tab="approved">
                    Approved <span class="tab-count"><?php echo count($approvedUsers); ?></span>
                </button>
                <button class="tab-btn" data-tab="rejected">
                    Rejected <span class="tab-count"><?php echo count($rejectedUsers); ?></span>
                </button>
            </div>
            
            <!-- Pending Users Table -->
            <div class="tab-content active" id="tab-pending">
                <div class="table-container glass-morphism">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($pendingUsers as $user): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['registered_at']; ?></td>
                                <td class="action-cell">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="approve" 
                                                class="btn-approve">
                                            <i class="ri-check-line"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" 
                                                class="btn-reject">
                                            <i class="ri-close-line"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pendingUsers)): ?>
                            <tr><td colspan="6" class="empty-state">No pending users</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Approved Users Table -->
            <div class="tab-content" id="tab-approved">
                <div class="table-container glass-morphism">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($approvedUsers as $user): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['registered_at']; ?></td>
                                <td><span class="status-badge approved">Approved</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($approvedUsers)): ?>
                            <tr><td colspan="6" class="empty-state">No approved users</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Rejected Users Table -->
            <div class="tab-content" id="tab-rejected">
                <div class="table-container glass-morphism">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($rejectedUsers as $user): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['registered_at']; ?></td>
                                <td><span class="status-badge rejected">Rejected</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($rejectedUsers)): ?>
                            <tr><td colspan="6" class="empty-state">No rejected users</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script src="assets/js/theme.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).classList.add('active');
            });
        });
        
        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clockDisplay').textContent = 
                now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Loading screen
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('loaded');
                setTimeout(() => document.getElementById('loadingScreen').remove(), 500);
            }, 500);
        });
    </script>
</body>
</html>