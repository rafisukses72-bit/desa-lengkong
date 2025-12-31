<?php
// modules/register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../functions.php'; // Include functions.php

// Ambil pengaturan
$settings = [];
$settings_query = "SELECT * FROM pengaturan LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle registration
$errors = [];
$success = false;
$form_data = [
    'nama' => '',
    'username' => '',
    'email' => '',
    'telepon' => '',
    'alamat' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data - HAPUS clean_input() karena sudah ada di functions.php
    $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon'] ?? '');
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Store for re-populating
    $form_data = compact('nama', 'username', 'email', 'telepon', 'alamat');
    
    // Validation
    if (empty($nama)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($username)) $errors[] = 'Username harus diisi';
    elseif (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
    
    if (empty($email)) $errors[] = 'Email harus diisi';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
    
    if (empty($password)) $errors[] = 'Password harus diisi';
    elseif (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
    
    if ($password !== $confirm_password) $errors[] = 'Konfirmasi password tidak cocok';
    
    // Check if username exists
    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $errors[] = 'Username atau email sudah terdaftar';
        }
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // First, check the structure of users table
        $desc_query = "DESCRIBE users";
        $desc_result = mysqli_query($conn, $desc_query);
        $columns = [];
        while ($row = mysqli_fetch_assoc($desc_result)) {
            $columns[] = $row['Field'];
        }
        
        // Debug: Show available columns
        // echo "Available columns: " . implode(', ', $columns);
        
        // Check what column names exist for storing name
        $name_column = '';
        if (in_array('nama', $columns)) {
            $name_column = 'nama';
        } elseif (in_array('full_name', $columns)) {
            $name_column = 'full_name';
        } elseif (in_array('name', $columns)) {
            $name_column = 'name';
        } elseif (in_array('nama_lengkap', $columns)) {
            $name_column = 'nama_lengkap';
        }
        
        // If no name column exists, we need to create the query differently
        if (empty($name_column)) {
            // Try to insert without name column or create the column
            $errors[] = 'Sistem sedang dalam perbaikan. Silakan hubungi administrator.';
            
            // Alternatively, you can create the column automatically:
            // $alter_query = "ALTER TABLE users ADD COLUMN nama VARCHAR(100) NOT NULL";
            // mysqli_query($conn, $alter_query);
            // $name_column = 'nama';
        }
        
        if (empty($errors)) {
            // Build insert query with correct column names
            $query = "INSERT INTO users ($name_column, username, email, telepon, alamat, password, verification_token, role, status, created_at) 
                      VALUES ('$nama', '$username', '$email', '$telepon', '$alamat', '$hashed_password', '$verification_token', 'user', 'active', NOW())";
            
            if (mysqli_query($conn, $query)) {
                $user_id = mysqli_insert_id($conn);
                $success = true;
                
                // Auto login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $nama;
                $_SESSION['role'] = 'user';
                $_SESSION['avatar'] = '';
                
                // Send verification email (in production)
                // sendVerificationEmail($email, $verification_token);
                
                // Clear form data
                $form_data = [
                    'nama' => '',
                    'username' => '',
                    'email' => '',
                    'telepon' => '',
                    'alamat' => ''
                ];
            } else {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
                $errors[] = 'Error: ' . mysqli_error($conn); // For debugging
            }
        }
    }
}

// HAPUS fungsi clean_input() karena sudah ada di functions.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Keep all your existing CSS styles */
        .register-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .register-container {
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.25);
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .form-control.with-icon {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .btn-register {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }
        .register-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 3;
        }
        .password-strength {
            height: 5px;
            border-radius: 2.5px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        .strength-0 { width: 0%; background: #dc3545; }
        .strength-1 { width: 25%; background: #dc3545; }
        .strength-2 { width: 50%; background: #ffc107; }
        .strength-3 { width: 75%; background: #ffc107; }
        .strength-4 { width: 100%; background: #28a745; }
        .form-check-input:checked {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .terms-link {
            color: #2ecc71;
            text-decoration: none;
        }
        .terms-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="register-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Daftar Akun</h1>
            <p class="lead">Buat akun untuk mengakses layanan desa</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="register-container">
                        <?php if ($success): ?>
                        <!-- Success Message -->
                        <div class="register-card">
                            <div class="text-center">
                                <div class="register-logo">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 class="text-success mb-3">Pendaftaran Berhasil!</h3>
                                <p class="mb-4">Akun Anda telah berhasil dibuat. Selamat datang di Desa <?php echo htmlspecialchars($settings['nama_desa']); ?>!</p>
                                <div class="d-grid gap-3">
                                    <a href="../index.php" class="btn btn-success">
                                        <i class="fas fa-home me-2"></i>Kembali ke Beranda
                                    </a>
                                    <a href="profile.php" class="btn btn-outline-success">
                                        <i class="fas fa-user me-2"></i>Profil Saya
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Registration Form -->
                        <div class="register-card">
                            <div class="register-header">
                                <div class="register-logo">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h3>Buat Akun Baru</h3>
                                <p class="text-muted">Isi data diri Anda dengan benar</p>
                            </div>
                            
                            <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="registerForm">
                                <!-- Nama Lengkap -->
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" name="nama" class="form-control with-icon" 
                                               value="<?php echo htmlspecialchars($form_data['nama']); ?>" 
                                               placeholder="Masukkan nama lengkap" required>
                                    </div>
                                </div>
                                
                                <!-- Username -->
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-at"></i>
                                        </span>
                                        <input type="text" name="username" class="form-control with-icon" 
                                               value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                                               placeholder="Masukkan username" required>
                                    </div>
                                    <small class="text-muted">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control with-icon" 
                                               value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                                               placeholder="Masukkan email" required>
                                    </div>
                                </div>
                                
                                <!-- Telepon -->
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" name="telepon" class="form-control with-icon" 
                                               value="<?php echo htmlspecialchars($form_data['telepon']); ?>" 
                                               placeholder="Masukkan nomor telepon">
                                    </div>
                                </div>
                                
                                <!-- Alamat -->
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="2" 
                                              placeholder="Masukkan alamat"><?php echo htmlspecialchars($form_data['alamat']); ?></textarea>
                                </div>
                                
                                <!-- Password -->
                                <div class="mb-3 position-relative">
                                    <label class="form-label">Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="password" id="password" 
                                               class="form-control with-icon" 
                                               placeholder="Masukkan password" required>
                                        <button type="button" class="password-toggle" 
                                                onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="passwordStrength"></div>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="mb-3 position-relative">
                                    <label class="form-label">Konfirmasi Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="confirm_password" id="confirm_password" 
                                               class="form-control with-icon" 
                                               placeholder="Ulangi password" required>
                                        <button type="button" class="password-toggle" 
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" id="passwordMatch"></small>
                                </div>
                                
                                <!-- Terms & Conditions -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="terms" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            Saya menyetujui 
                                            <a href="terms.php" class="terms-link">Syarat & Ketentuan</a>
                                            dan 
                                            <a href="privacy.php" class="terms-link">Kebijakan Privasi</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-register mb-4">
                                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                </button>
                            </form>
                            
                            <div class="register-footer">
                                <p class="mb-0">
                                    Sudah punya akun? 
                                    <a href="login.php" class="text-success fw-bold">Login disini</a>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleButton = document.querySelector(`#${fieldId} + .password-toggle i`);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.remove('fa-eye');
                toggleButton.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.remove('fa-eye-slash');
                toggleButton.classList.add('fa-eye');
            }
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return Math.min(strength, 4);
        }
        
        // Password match checker
        function checkPasswordMatch(password, confirmPassword) {
            if (!password || !confirmPassword) return '';
            
            if (password === confirmPassword) {
                return '<span class="text-success"><i class="fas fa-check-circle"></i> Password cocok</span>';
            } else {
                return '<span class="text-danger"><i class="fas fa-times-circle"></i> Password tidak cocok</span>';
            }
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            
            // Real-time password strength check
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const strength = checkPasswordStrength(this.value);
                    passwordStrength.className = 'password-strength strength-' + strength;
                    
                    if (confirmPasswordInput.value) {
                        passwordMatch.innerHTML = checkPasswordMatch(this.value, confirmPasswordInput.value);
                    }
                });
            }
            
            // Real-time password match check
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    passwordMatch.innerHTML = checkPasswordMatch(passwordInput.value, this.value);
                });
            }
            
            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    
                    // Get form elements
                    const nama = document.querySelector('input[name="nama"]');
                    const username = document.querySelector('input[name="username"]');
                    const email = document.querySelector('input[name="email"]');
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm_password');
                    const terms = document.getElementById('terms');
                    
                    // Reset styles
                    [nama, username, email, password, confirmPassword].forEach(input => {
                        if (input) input.classList.remove('is-invalid');
                    });
                    
                    // Validate
                    if (nama && !nama.value.trim()) {
                        nama.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (username && (!username.value.trim() || username.value.length < 3)) {
                        username.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (email && (!email.value.trim() || !validateEmail(email.value))) {
                        email.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (password && (!password.value.trim() || password.value.length < 6)) {
                        password.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (confirmPassword && password.value !== confirmPassword.value) {
                        confirmPassword.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (terms && !terms.checked) {
                        terms.classList.add('is-invalid');
                        valid = false;
                    }
                    
                    if (!valid) {
                        e.preventDefault();
                    }
                });
            }
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            // Auto-focus first field
            const firstInput = document.querySelector('input[name="nama"]');
            if (firstInput && !firstInput.value) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>