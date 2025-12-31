<?php
// admin/process_login.php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password harus diisi!';
        header("Location: login.php");
        exit();
    }
    
    try {
        // Query untuk mencari admin - FIXED: tidak cek login_attempts
        $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND role IN ('admin', 'superadmin')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $admin['password'])) {
                // Cek status akun (gunakan status default jika kolom tidak ada)
                $status = $admin['status'] ?? 'active';
                if ($status !== 'active') {
                    $_SESSION['error'] = 'Akun Anda tidak aktif. Silakan hubungi administrator.';
                    header("Location: login.php");
                    exit();
                }
                
                // Set session admin
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'] ?? $admin['username'];
                $_SESSION['admin_email'] = $admin['email'] ?? '';
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_avatar'] = $admin['avatar'] ?? '';
                
                // Set cookie jika remember me dipilih (opsional)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
                    
                    // Cek jika kolom remember_token ada
                    $check_column = "SHOW COLUMNS FROM users LIKE 'remember_token'";
                    $column_result = mysqli_query($conn, $check_column);
                    
                    if (mysqli_num_rows($column_result) > 0) {
                        // Update remember token di database
                        $update_query = "UPDATE users SET remember_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?";
                        $update_stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($update_stmt, "sii", $token, $expiry, $admin['id']);
                        mysqli_stmt_execute($update_stmt);
                        
                        // Set cookie
                        setcookie('admin_token', $token, $expiry, '/admin/');
                        setcookie('admin_id', $admin['id'], $expiry, '/admin/');
                    }
                }
                
                // Update last login jika kolom ada
                $check_last_login = "SHOW COLUMNS FROM users LIKE 'last_login'";
                $last_login_result = mysqli_query($conn, $check_last_login);
                
                if (mysqli_num_rows($last_login_result) > 0) {
                    $update_login = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    $stmt_login = mysqli_prepare($conn, $update_login);
                    mysqli_stmt_bind_param($stmt_login, "i", $admin['id']);
                    mysqli_stmt_execute($stmt_login);
                }
                
                // Log aktivitas (jika tabel activity_logs ada)
                $check_logs_table = "SHOW TABLES LIKE 'activity_logs'";
                $logs_result = mysqli_query($conn, $check_logs_table);
                
                if (mysqli_num_rows($logs_result) > 0) {
                    $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                                 VALUES (?, 'admin_login', ?, ?, ?)";
                    $log_stmt = mysqli_prepare($conn, $log_query);
                    $description = "Admin login: " . $admin['username'];
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    mysqli_stmt_bind_param($log_stmt, "isss", $admin['id'], $description, $ip_address, $user_agent);
                    mysqli_stmt_execute($log_stmt);
                }
                
                // Redirect ke dashboard
                header("Location: index.php");
                exit();
                
            } else {
                // Password salah
                $_SESSION['error'] = 'Password salah!';
                
                // Coba update login attempts jika kolom ada
                $check_attempts = "SHOW COLUMNS FROM users LIKE 'login_attempts'";
                $attempts_result = mysqli_query($conn, $check_attempts);
                
                if (mysqli_num_rows($attempts_result) > 0) {
                    $attempts = ($admin['login_attempts'] ?? 0) + 1;
                    $update_attempts = "UPDATE users SET login_attempts = ?, last_attempt = NOW() WHERE id = ?";
                    $stmt_attempts = mysqli_prepare($conn, $update_attempts);
                    mysqli_stmt_bind_param($stmt_attempts, "ii", $attempts, $admin['id']);
                    mysqli_stmt_execute($stmt_attempts);
                    
                    // Jika gagal 5 kali, lock account
                    if ($attempts >= 5) {
                        $lock_query = "UPDATE users SET status = 'locked', locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?";
                        $lock_stmt = mysqli_prepare($conn, $lock_query);
                        mysqli_stmt_bind_param($lock_stmt, "i", $admin['id']);
                        mysqli_stmt_execute($lock_stmt);
                        
                        $_SESSION['error'] = 'Akun terkunci karena terlalu banyak percobaan gagal. Coba lagi setelah 30 menit.';
                    } else {
                        $_SESSION['error'] = 'Password salah! Sisa percobaan: ' . (5 - $attempts);
                    }
                }
                
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Username/email tidak ditemukan atau Anda bukan admin!';
            header("Location: login.php");
            exit();
        }
        
    } catch (Exception $e) {
        // Tangani error dengan baik
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>