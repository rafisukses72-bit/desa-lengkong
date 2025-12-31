<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    // Query untuk mencari user
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
            mysqli_stmt_execute($update_stmt);
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                         VALUES (?, 'login', ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $description = "User login: " . $user['username'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_stmt_bind_param($log_stmt, "isss", $user['id'], $description, $ip_address, $user_agent);
            mysqli_stmt_execute($log_stmt);
            
            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                $_SESSION['message'] = 'Selamat datang, ' . $user['nama_lengkap'] . '!';
                $_SESSION['message_type'] = 'success';
                header("Location: ../admin/index.php");
            } else {
                $_SESSION['message'] = 'Login berhasil!';
                $_SESSION['message_type'] = 'success';
                header("Location: ../index.php");
            }
            exit();
        } else {
            $_SESSION['message'] = 'Password salah!';
            $_SESSION['message_type'] = 'error';
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Username/email tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>