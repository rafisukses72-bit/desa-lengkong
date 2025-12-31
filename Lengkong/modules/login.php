<?php
// modules/login.php - FIXED VERSION WITH AUTO ADMIN
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once '../functions.php';

// Fungsi untuk membuat admin otomatis jika tidak ada
function setup_auto_admin($conn) {
    // Cek apakah tabel users ada
    $table_check = @mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        // Buat tabel users jika tidak ada
        $create_table = "
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                nama VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                telepon VARCHAR(20),
                role ENUM('admin','user') DEFAULT 'user',
                avatar VARCHAR(255),
                is_active TINYINT(1) DEFAULT 1,
                remember_token VARCHAR(100),
                token_expiry DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        if (!mysqli_query($conn, $create_table)) {
            return false;
        }
    }
    
    // Cek kolom yang ada
    $columns = [];
    $check_columns = @mysqli_query($conn, "SHOW COLUMNS FROM users");
    if ($check_columns && mysqli_num_rows($check_columns) > 0) {
        while ($col = mysqli_fetch_assoc($check_columns)) {
            $columns[] = $col['Field'];
        }
    }
    
    // Cek apakah kolom is_active ada, jika tidak tambahkan
    if (!in_array('is_active', $columns)) {
        @mysqli_query($conn, "ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1");
    }
    
    // Cek apakah kolom status ada, jika ada rename atau hapus
    if (in_array('status', $columns)) {
        // Ubah ke is_active
        @mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE status = 'active'");
        @mysqli_query($conn, "UPDATE users SET is_active = 0 WHERE status = 'inactive'");
    }
    
    // Cek apakah admin sudah ada
    $check_admin = mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'");
    if (!$check_admin || mysqli_num_rows($check_admin) == 0) {
        // Buat admin default
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "
            INSERT INTO users (username, password, nama, email, role, is_active) 
            VALUES ('admin', '$default_password', 'Administrator', 'admin@desa.lengkong.id', 'admin', 1)
        ";
        
        return mysqli_query($conn, $insert_admin);
    }
    
    return true;
}

// Fungsi khusus untuk login
function get_login_settings($conn) {
    $default_settings = [
        'nama_desa' => 'Desa Lengkong',
        'logo' => '',
        'motto_desa' => 'Maju, Mandiri, Sejahtera'
    ];
    
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'pengaturan'");
    if ($result && mysqli_num_rows($result) > 0) {
        $settings_result = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $db_settings = mysqli_fetch_assoc($settings_result);
            return array_merge($default_settings, $db_settings);
        }
    }
    
    return $default_settings;
}

// Setup auto admin
setup_auto_admin($conn);

// Ambil pengaturan
$settings = get_login_settings($conn);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'admin' ? '../admin/index.php' : '../index.php'));
    exit();
}

// Handle login
$errors = [];
$username = '';

// Auto-login untuk demo (opsional - bisa diaktifkan/dinonaktifkan)
$auto_login_enabled = true; // Set false untuk nonaktifkan auto-login

if ($auto_login_enabled && empty($_POST)) {
    // Coba login otomatis dengan admin
    $auto_username = 'admin';
    // Cek kolom yang ada untuk menentukan query
    $check_columns = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($check_columns && mysqli_num_rows($check_columns) > 0) {
        $query = "SELECT * FROM users WHERE username = ? AND is_active = 1";
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
    }
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $auto_username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Set session untuk admin
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'] ?? 'Administrator';
            $_SESSION['role'] = $user['role'] ?? 'admin';
            $_SESSION['avatar'] = $user['avatar'] ?? '';
            
            // Redirect ke admin dashboard
            header('Location: ../admin/index.php');
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($username)) $errors[] = 'Username harus diisi';
    if (empty($password)) $errors[] = 'Password harus diisi';
    
    if (empty($errors)) {
        // Cek kolom yang ada untuk menentukan query
        $check_columns = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'is_active'");
        if ($check_columns && mysqli_num_rows($check_columns) > 0) {
            $query = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        } else {
            $query = "SELECT * FROM users WHERE username = ?";
        }
        
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama'] = $user['nama'] ?? '';
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    $_SESSION['avatar'] = $user['avatar'] ?? '';
                    
                    // Remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (30 * 24 * 60 * 60);
                        
                        setcookie('remember_token', $token, $expiry, '/');
                        setcookie('remember_user', $user['id'], $expiry, '/');
                        
                        $check_columns = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'remember_token'");
                        if ($check_columns && mysqli_num_rows($check_columns) > 0) {
                            $update_query = "UPDATE users SET remember_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?";
                            $update_stmt = mysqli_prepare($conn, $update_query);
                            if ($update_stmt) {
                                mysqli_stmt_bind_param($update_stmt, 'sii', $token, $expiry, $user['id']);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);
                            }
                        }
                    }
                    
                    // Redirect based on role
                    header('Location: ' . (($_SESSION['role'] == 'admin') ? '../admin/index.php' : '../index.php'));
                    exit();
                } else {
                    $errors[] = 'Password salah';
                }
            } else {
                $errors[] = 'Username tidak ditemukan atau akun tidak aktif';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #7209b7;
            --success: #20bf55;
            --danger: #f72585;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-auto-admin {
            background: linear-gradient(135deg, var(--success), #01baef);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-auto-admin:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(32, 191, 85, 0.3);
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid var(--success);
        }
        
        .demo-credentials h6 {
            color: var(--success);
            margin-bottom: 10px;
        }
        
        .auto-login-notice {
            background: #e8f4fd;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .admin-welcome {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            border-radius: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <main class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                    <div class="login-card">
                        <!-- Welcome Message -->
                        <div class="admin-welcome">
                            <h4 class="mb-2 text-primary">
                                <i class="fas fa-user-shield me-2"></i>
                                Selamat Datang Admin
                            </h4>
                            <p class="text-muted mb-0">
                                Sistem Manajemen Desa <?php echo htmlspecialchars($settings['nama_desa']); ?>
                            </p>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-1">Login Gagal</h6>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($errors as $error): ?>
                                        <li class="small"><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Demo Credentials -->
                        <div class="demo-credentials">
                            <h6><i class="fas fa-key me-2"></i>Akun Admin Demo:</h6>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Username:</strong><br>
                                    <code>admin</code>
                                </div>
                                <div class="col-6">
                                    <strong>Password:</strong><br>
                                    <code>admin123</code>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Auto Login Notice -->
                        <div class="auto-login-notice">
                            <i class="fas fa-bolt me-1"></i>
                            <strong>Auto-Login Aktif</strong> - Anda akan langsung masuk sebagai admin
                        </div>
                        
                        <!-- Countdown untuk auto-login -->
                        <div id="autoLoginCountdown" class="text-center mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 mb-0 text-muted small">
                                Login otomatis dalam: <span id="countdown">3</span> detik
                            </p>
                        </div>
                        
                        <!-- Manual Login Form -->
                        <form method="POST" action="" id="loginForm" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($username); ?>" 
                                       placeholder="admin">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="••••••••">
                            </div>
                            
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Manual
                            </button>
                        </form>
                        
                        <!-- Quick Login Buttons -->
                        <div class="mt-4">
                            <button onclick="loginAsAdmin()" class="btn btn-auto-admin">
                                <i class="fas fa-bolt me-2"></i>Login sebagai Admin
                            </button>
                            
                            <button onclick="showManualForm()" class="btn btn-outline-primary w-100 mt-2">
                                <i class="fas fa-user-edit me-2"></i>Login Manual
                            </button>
                        </div>
                        
                        <!-- Admin Info -->
                        <div class="mt-4 pt-3 border-top text-center">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Sistem akan membuat akun admin otomatis jika belum ada
                            </p>
                            <a href="../index.php" class="small text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="py-3 text-center text-white">
        <div class="container">
            <p class="mb-0 small opacity-75">
                <i class="fas fa-copyright me-1"></i>
                <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['nama_desa']); ?> 
                | Admin System v1.0
            </p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown untuk auto-login
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        const countdownContainer = document.getElementById('autoLoginCountdown');
        
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                loginAsAdmin();
            }
        }, 1000);
        
        // Fungsi login sebagai admin
        function loginAsAdmin() {
            // Show loading
            countdownContainer.innerHTML = `
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0 text-success">
                    <i class="fas fa-user-shield me-1"></i>
                    Login sebagai Admin...
                </p>
            `;
            
            // Submit form dengan data admin
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const usernameInput = document.createElement('input');
            usernameInput.type = 'hidden';
            usernameInput.name = 'username';
            usernameInput.value = 'admin';
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = 'admin123';
            
            form.appendChild(usernameInput);
            form.appendChild(passwordInput);
            document.body.appendChild(form);
            
            // Submit setelah delay
            setTimeout(() => {
                form.submit();
            }, 1000);
        }
        
        // Fungsi untuk menampilkan form manual
        function showManualForm() {
            clearInterval(countdownInterval);
            countdownContainer.style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }
        
        // Cancel auto-login jika user berinteraksi
        document.addEventListener('click', () => {
            clearInterval(countdownInterval);
            countdownContainer.innerHTML = `
                <p class="text-muted small">
                    <i class="fas fa-hand-pointer me-1"></i>
                    Auto-login dibatalkan. Silakan klik tombol di bawah.
                </p>
            `;
        });
        
        // Enter key untuk submit form manual
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const form = document.getElementById('loginForm');
                if (form.style.display !== 'none') {
                    form.submit();
                }
            }
        });
    </script>
</body>
</html>