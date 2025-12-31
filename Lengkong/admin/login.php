<?php
// admin/login.php
session_start();

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../config.php';

// Auto login untuk demo
$auto_login = isset($_GET['demo']) ? $_GET['demo'] : false;

if ($auto_login && in_array($auto_login, ['admin', 'operator', 'warga'])) {
    // Get user data based on role
    $query = "SELECT * FROM users WHERE role = '$auto_login' AND status = 'active' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Set cookie jika remember me
        if (isset($_GET['remember'])) {
            setcookie('remember_token', base64_encode($user['id'] . '|' . $user['username']), time() + (86400 * 30), "/");
        }
        
        header("Location: index.php");
        exit();
    }
}

// Jika ada cookie remember token
if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
    $token_data = base64_decode($_COOKIE['remember_token']);
    $parts = explode('|', $token_data);
    
    if (count($parts) == 2) {
        $user_id = $parts[0];
        $username = $parts[1];
        
        // Verifikasi user
        $query = "SELECT * FROM users WHERE id = '$user_id' AND username = '$username' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Desa Lengkong</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.6s ease-out;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        .demo-login-buttons {
            margin-bottom: 20px;
        }
        
        .demo-btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: white;
        }
        
        .demo-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--primary), #1a2530);
        }
        
        .btn-operator {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
        }
        
        .btn-warga {
            background: linear-gradient(135deg, var(--success), #219653);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .login-btn {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: var(--secondary);
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .demo-info {
            background: rgba(243, 156, 18, 0.1);
            border: 1px solid rgba(243, 156, 18, 0.3);
            color: #d35400;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-village"></i>
                </div>
                <h1 class="fw-bold text-primary mb-2">Desa Lengkong</h1>
                <p class="text-muted">Sistem Administrasi Desa</p>
            </div>
            
            <!-- Demo Login Info -->
            <div class="demo-info animate__animated animate__fadeIn">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Login Demo:</strong> Klik salah satu tombol di bawah untuk login otomatis
            </div>
            
            <!-- Demo Login Buttons -->
            <div class="demo-login-buttons">
                <a href="?demo=admin&remember=1" class="demo-btn btn-admin animate__animated animate__fadeInLeft">
                    <i class="fas fa-user-shield me-2"></i>Login sebagai Admin
                </a>
                <a href="?demo=operator&remember=1" class="demo-btn btn-operator animate__animated animate__fadeInLeft animate__delay-1s">
                    <i class="fas fa-user-tie me-2"></i>Login sebagai Operator
                </a>
                <a href="?demo=warga&remember=1" class="demo-btn btn-warga animate__animated animate__fadeInLeft animate__delay-2s">
                    <i class="fas fa-user me-2"></i>Login sebagai Warga
                </a>
            </div>
            
            <!-- Divider -->
            <div class="divider">
                <span>ATAU</span>
            </div>
            
            <!-- Login Form -->
            <form method="POST" action="process_login.php" id="loginForm">
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-user text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" 
                               name="username" 
                               placeholder="Masukkan username atau email"
                               required
                               autocomplete="username"
                               autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-lock text-primary"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" 
                               name="password" 
                               id="password"
                               placeholder="Masukkan password"
                               required
                               autocomplete="current-password">
                        <button type="button" class="input-group-text bg-light border-start-0" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya selama 30 hari</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold login-btn">
                    <i class="fas fa-sign-in-alt me-2"></i>MASUK
                </button>
            </form>
            
            <div class="forgot-password mt-3">
                <a href="forgot_password.php" class="text-decoration-none">
                    <i class="fas fa-key me-1"></i>Lupa password?
                </a>
            </div>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = this.username.value.trim();
            const password = this.password.value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Username dan password harus diisi!');
                return false;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelector('input[name="username"]').focus();
            }, 500);
        });
    </script>
</body>
</html>