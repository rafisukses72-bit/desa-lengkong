<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $nik = sanitize_input($_POST['nik']);
    $telepon = sanitize_input($_POST['telepon']);
    $alamat = sanitize_input($_POST['alamat']);
    
    // Validasi data
    $errors = [];
    
    // Validasi username
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    } else {
        // Cek apakah username sudah digunakan
        $check_username = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_username);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username sudah digunakan.";
        }
    }
    
    // Validasi email
    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    } else {
        // Cek apakah email sudah digunakan
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email sudah digunakan.";
        }
    }
    
    // Validasi password
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    } elseif ($password != $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok.";
    }
    
    // Validasi NIK
    if (empty($nik)) {
        $errors[] = "NIK harus diisi.";
    } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
        $errors[] = "NIK harus 16 digit angka.";
    } else {
        // Cek apakah NIK sudah terdaftar
        $check_nik = "SELECT id FROM penduduk WHERE nik = ?";
        $stmt = mysqli_prepare($conn, $check_nik);
        mysqli_stmt_bind_param($stmt, "s", $nik);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "NIK sudah terdaftar.";
        }
    }
    
    // Validasi nama lengkap
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi.";
    }
    
    // Jika ada error, tampilkan pesan error
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../register.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Mulai transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Simpan ke tabel users
        $query_user = "INSERT INTO users (username, email, password, fullname, role, phone, address, is_active) 
                      VALUES (?, ?, ?, ?, 'user', ?, ?, 1)";
        $stmt_user = mysqli_prepare($conn, $query_user);
        mysqli_stmt_bind_param($stmt_user, "ssssss", $username, $email, $hashed_password, $nama_lengkap, $telepon, $alamat);
        
        if (!mysqli_stmt_execute($stmt_user)) {
            throw new Exception("Gagal menyimpan data user: " . mysqli_error($conn));
        }
        
        $user_id = mysqli_insert_id($conn);
        
        // 2. Simpan ke tabel penduduk
        $query_penduduk = "INSERT INTO penduduk (nik, nama, alamat, no_telepon, email, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_penduduk = mysqli_prepare($conn, $query_penduduk);
        mysqli_stmt_bind_param($stmt_penduduk, "sssssi", $nik, $nama_lengkap, $alamat, $telepon, $email, $user_id);
        
        if (!mysqli_stmt_execute($stmt_penduduk)) {
            throw new Exception("Gagal menyimpan data penduduk: " . mysqli_error($conn));
        }
        
        // 3. Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'register', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Pendaftaran akun baru: " . $username;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $user_id, $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Kirim email konfirmasi
        $subject = "Selamat Datang di " . SITE_NAME . " - Registrasi Berhasil";
        $message = "
        <html>
        <head>
            <title>Registrasi Berhasil</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .footer { background: #2c3e50; color: white; padding: 15px; text-align: center; }
                .button { display: inline-block; padding: 12px 25px; background: #2ecc71; 
                         color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Selamat Datang di " . SITE_NAME . "</h2>
                </div>
                <div class='content'>
                    <h3>Registrasi Berhasil!</h3>
                    <p>Halo <strong>" . $nama_lengkap . "</strong>,</p>
                    <p>Terima kasih telah mendaftar di sistem informasi desa kami. Akun Anda telah berhasil dibuat.</p>
                    
                    <div style='background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 20px 0;'>
                        <h4>Detail Akun Anda:</h4>
                        <p><strong>Username:</strong> " . $username . "</p>
                        <p><strong>Nama Lengkap:</strong> " . $nama_lengkap . "</p>
                        <p><strong>Email:</strong> " . $email . "</p>
                        <p><strong>NIK:</strong> " . $nik . "</p>
                        <p><strong>Tanggal Registrasi:</strong> " . date('d F Y H:i') . "</p>
                    </div>
                    
                    <p>Anda sekarang dapat login ke akun Anda untuk mengakses berbagai layanan desa.</p>
                    
                    <a href='" . BASE_URL . "login.php' class='button'>Login Sekarang</a>
                    
                    <p style='margin-top: 20px; color: #666; font-size: 0.9em;'>
                        <strong>Keamanan Akun:</strong><br>
                        • Jangan bagikan password Anda kepada siapapun<br>
                        • Gunakan password yang kuat dan unik<br>
                        • Segera hubungi admin jika ada aktivitas mencurigakan
                    </p>
                </div>
                <div class='footer'>
                    <p>" . SITE_NAME . " - Sistem Informasi Desa</p>
                    <small>&copy; " . date('Y') . " " . SITE_NAME . ". Semua hak dilindungi.</small>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
        
        @mail($email, $subject, $message, $headers);
        
        // Set session success message
        $_SESSION['message'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
        $_SESSION['message_type'] = 'success';
        
        // Redirect ke halaman login
        header("Location: ../login.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($conn);
        
        $_SESSION['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header("Location: ../register.php");
        exit();
    }
} else {
    header("Location: ../register.php");
    exit();
}
?>