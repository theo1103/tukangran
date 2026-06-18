<?php
session_start();
require_once 'api/auth_handler.php';

$auth = new AuthHandler();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number';
    
    if (empty($errors)) {
        $result = $auth->registerUser($name, $username, $password, $email);
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

$nonce = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NetTech Suite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="auth-page">
    <canvas id="particleCanvas"></canvas>
    
    <div class="auth-container">
        <div class="auth-card glass-morphism" style="max-width: 480px;">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="ri-user-add-line"></i>
                </div>
                <h1>Create Account</h1>
                <p class="subtitle">Join NetTech Suite Platform</p>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> <?php echo $messageType === 'error' ? 'shake-animation' : ''; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" autocomplete="off">
                <div class="input-group">
                    <label><i class="ri-user-line"></i> Full Name</label>
                    <input type="text" name="name" placeholder="Your full name" required>
                    <span class="input-focus-border"></span>
                </div>
                
                <div class="input-group">
                    <label><i class="ri-at-line"></i> Username</label>
                    <input type="text" name="username" placeholder="Choose username" required autocomplete="off">
                    <span class="input-focus-border"></span>
                </div>
                
                <div class="input-group">
                    <label><i class="ri-mail-line"></i> Email</label>
                    <input type="email" name="email" placeholder="Your email address" required>
                    <span class="input-focus-border"></span>
                </div>
                
                <div class="input-group">
                    <label><i class="ri-lock-line"></i> Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="regPassword" 
                               placeholder="Min. 8 characters" required>
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="ri-eye-off-line"></i>
                        </button>
                    </div>
                    <span class="input-focus-border"></span>
                </div>
                
                <div class="input-group">
                    <label><i class="ri-lock-password-line"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" 
                           placeholder="Re-enter password" required>
                    <span class="input-focus-border"></span>
                </div>
                
                <button type="submit" class="btn-primary btn-login">
                    <span class="btn-text">Register</span>
                </button>
                
                <div class="auth-footer">
                    <p>Already have an account? 
                        <a href="index.php" class="link-glow">Sign In</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/theme.js"></script>
    <script>
        // Particle animation (same as login)
        const canvas = document.getElementById('particleCanvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let particles = [];
            function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
            resize();
            window.addEventListener('resize', resize);
            class Particle {
                constructor() { this.reset(); }
                reset() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.size = Math.random() * 2 + 0.5;
                    this.speedX = (Math.random() - 0.5) * 0.5;
                    this.speedY = (Math.random() - 0.5) * 0.5;
                    this.opacity = Math.random() * 0.5 + 0.1;
                }
                update() {
                    this.x += this.speedX; this.y += this.speedY;
                    if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) this.reset();
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
            for (let i = 0; i < 80; i++) particles.push(new Particle());
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(p => { p.update(); p.draw(); });
                requestAnimationFrame(animate);
            }
            animate();
        }
    </script>
</body>
</html>