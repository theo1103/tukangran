<?php
// ============================================
// HIDDEN AUTHENTICATION LAYER
// Credentials verified server-side only
// No traces in HTML source
// ============================================
session_start();
require_once 'api/auth_handler.php';

// Auto-redirect if already logged in
if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$auth = new AuthHandler();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($auth->verifyLogin($username, $password)) {
        $_SESSION['user_authenticated'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $auth->getUserRole($username);
        $_SESSION['login_time'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['session_token'] = bin2hex(random_bytes(32));
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
        // Add delay to prevent brute force
        sleep(1);
    }
}

// Generate unique nonce for this request
$nonce = bin2hex(random_bytes(16));
$_SESSION['login_nonce'] = $nonce;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Network Technology Management Suite">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'nonce-<?php echo $nonce; ?>' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://api.openweathermap.org https://*.cnnindonesia.com;">
    <title>NetTech Suite - Authentication</title>
    
    <!-- CDN Resources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="auth-page">
    <!-- Animated Background Particles -->
    <canvas id="particleCanvas"></canvas>
    
    <!-- Login Container -->
    <div class="auth-container">
        <div class="auth-card glass-morphism">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="ri-radar-line"></i>
                </div>
                <h1>NetTech Suite</h1>
                <p class="subtitle">Network Technology Management Platform</p>
                <div class="theme-toggle-wrapper">
                    <button id="themeToggle" class="theme-btn" aria-label="Toggle theme">
                        <i class="ri-sun-line sun-icon"></i>
                        <i class="ri-moon-line moon-icon"></i>
                    </button>
                </div>
            </div>
            
            <form id="loginForm" method="POST" class="auth-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $nonce; ?>">
                
                <div class="input-group">
                    <label for="username">
                        <i class="ri-user-line"></i> Username
                    </label>
                    <input type="text" id="username" name="username" 
                           placeholder="Enter your username" required 
                           autocomplete="off" spellcheck="false">
                    <span class="input-focus-border"></span>
                </div>
                
                <div class="input-group">
                    <label for="password">
                        <i class="ri-lock-line"></i> Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" required 
                               autocomplete="off">
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="ri-eye-off-line"></i>
                        </button>
                    </div>
                    <span class="input-focus-border"></span>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error shake-animation">
                    <i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-primary btn-login" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loader" style="display:none;">
                        <i class="ri-loader-4-line spinning"></i>
                    </span>
                </button>
                
                <div class="auth-footer">
                    <p>Don't have an account? 
                        <a href="register.php" class="link-glow">Register here</a>
                    </p>
                </div>
            </form>
            
            <!-- Live Clock & Location -->
            <div class="auth-info-bar">
                <div class="live-clock" id="liveClock">--:--:--</div>
                <div class="location-info" id="locationInfo">Detecting...</div>
            </div>
        </div>
    </div>
    
    <!-- Hidden security layer - server-side verification only -->
    <?php /* Authentication processed via api/auth_handler.php - no client-side traces */ ?>
    
    <script nonce="<?php echo $nonce; ?>" src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/theme.js"></script>
    <script nonce="<?php echo $nonce; ?>" src="assets/js/auth.js"></script>
    <script nonce="<?php echo $nonce; ?>">
        // Particle canvas animation
        (function() {
            const canvas = document.getElementById('particleCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let particles = [];
            
            function resize() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            resize();
            window.addEventListener('resize', resize);
            
            class Particle {
                constructor() {
                    this.reset();
                }
                reset() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.size = Math.random() * 2 + 0.5;
                    this.speedX = (Math.random() - 0.5) * 0.5;
                    this.speedY = (Math.random() - 0.5) * 0.5;
                    this.opacity = Math.random() * 0.5 + 0.1;
                }
                update() {
                    this.x += this.speedX;
                    this.y += this.speedY;
                    if (this.x < 0 || this.x > canvas.width || 
                        this.y < 0 || this.y > canvas.height) {
                        this.reset();
                    }
                }
                draw() {
                    const theme = document.documentElement.getAttribute('data-theme');
                    const color = theme === 'dark' ? '100, 200, 255' : '0, 100, 200';
                    ctx.fillStyle = `rgba(${color}, ${this.opacity})`;
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fill();
                }
            }
            
            for (let i = 0; i < 80; i++) {
                particles.push(new Particle());
            }
            
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(p => { p.update(); p.draw(); });
                requestAnimationFrame(animate);
            }
            animate();
        })();
        
        // Live clock
        function updateClock() {
            const now = new Date();
            const options = { 
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                hour12: false 
            };
            document.getElementById('liveClock').textContent = 
                now.toLocaleTimeString('en-GB', options) + ' GMT' + 
                (now.getTimezoneOffset() > 0 ? '-' : '+') + 
                Math.abs(Math.floor(now.getTimezoneOffset() / 60)).toString().padStart(2, '0');
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Location detection
        fetch('https://ipapi.co/json/')
            .then(r => r.json())
            .then(data => {
                document.getElementById('locationInfo').textContent = 
                    data.city + ', ' + data.country_name;
            })
            .catch(() => {
                document.getElementById('locationInfo').textContent = 'Location detected';
            });
    </script>
</body>
</html>