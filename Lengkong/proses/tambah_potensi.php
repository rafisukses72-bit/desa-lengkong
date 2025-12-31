<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data
    $nama = sanitize_input($_POST['nama']);
    $jenis = sanitize_input($_POST['jenis']);
    $deskripsi = $_POST['deskripsi'];
    $konten = $_POST['konten'];
    $alamat = sanitize_input($_POST['alamat']);
    $kontak = sanitize_input($_POST['kontak']);
    $pemilik = sanitize_input($_POST['pemilik']);
    $harga = sanitize_input($_POST['harga']);
    $status = sanitize_input($_POST['status']);
    
    // Validasi data
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama potensi harus diisi.";
    }
    
    if (empty($jenis)) {
        $errors[] = "Jenis potensi harus dipilih.";
    }
    
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi harus diisi.";
    }
    
    // Handle upload gambar
    $gambar = null;
    $upload_error = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['gambar']['type'], $allowed_types)) {
            $upload_error = "Hanya file gambar JPG, JPEG, PNG, GIF, dan WebP yang diizinkan.";
        } elseif ($_FILES['gambar']['size'] > $max_size) {
            $upload_error = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            $upload_dir = '../uploads/potensi/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . date('YmdHis') . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            // Resize image jika terlalu besar
            list($width, $height) = getimagesize($_FILES['gambar']['tmp_name']);
            $new_width = 800;
            $new_height = ($height / $width) * $new_width;
            
            $image_p = imagecreatetruecolor($new_width, $new_height);
            
            switch ($_FILES['gambar']['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($_FILES['gambar']['tmp_name']);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($_FILES['gambar']['tmp_name']);
                    imagealphablending($image_p, false);
                    imagesavealpha($image_p, true);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($_FILES['gambar']['tmp_name']);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($_FILES['gambar']['tmp_name']);
                    break;
            }
            
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Simpan gambar
            switch ($_FILES['gambar']['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($image_p, $target_file, 85);
                    break;
                case 'image/png':
                    imagepng($image_p, $target_file, 8);
                    break;
                case 'image/gif':
                    imagegif($image_p, $target_file);
                    break;
                case 'image/webp':
                    imagewebp($image_p, $target_file, 85);
                    break;
            }
            
            imagedestroy($image);
            imagedestroy($image_p);
            
            $gambar = $filename;
        }
    } elseif ($_FILES['gambar']['error'] == 4) {
        // Tidak ada file yang diupload
        $gambar = null;
    } else {
        $upload_error = "Terjadi kesalahan saat mengupload gambar.";
    }
    
    // Gabungkan semua error
    if ($upload_error) {
        $errors[] = $upload_error;
    }
    
    // Jika ada error, tampilkan pesan error
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php?action=tambah");
        exit();
    }
    
    // Simpan ke database
    $query = "INSERT INTO potensi (nama, jenis, deskripsi, konten, gambar, alamat, 
              kontak, pemilik, harga, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssss", 
        $nama, $jenis, $deskripsi, $konten, $gambar, $alamat, $kontak, $pemilik, $harga, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $potensi_id = mysqli_insert_id($conn);
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'tambah_potensi', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Menambah potensi: " . $nama . " (ID: " . $potensi_id . ")";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Jika potensi aktif, buat notifikasi
        if ($status == 'aktif') {
            // Update statistik potensi aktif
            $stats_query = "UPDATE pengaturan SET potensi_aktif = potensi_aktif + 1 WHERE id = 1";
            mysqli_query($conn, $stats_query);
            
            // Kirim notifikasi ke email admin
            $admin_email = "admin@desa.lengkong"; // Ganti dengan email admin
            
            $subject = "Potensi Baru Ditambahkan: " . $nama . " - " . SITE_NAME;
            $message = "
            <html>
            <head>
                <title>Potensi Baru</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #27ae60; color: white; padding: 15px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .footer { background: #2c3e50; color: white; padding: 10px; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Potensi Desa Baru</h2>
                    </div>
                    <div class='content'>
                        <h3>" . $nama . "</h3>
                        <p><strong>Jenis:</strong> " . ucfirst($jenis) . "</p>
                        <p><strong>Deskripsi:</strong> " . nl2br(substr($deskripsi, 0, 200)) . "...</p>
                        <p><strong>Alamat:</strong> " . $alamat . "</p>
                        <p><strong>Pemilik:</strong> " . $pemilik . "</p>
                        <p><strong>Kontak:</strong> " . $kontak . "</p>
                        <p><strong>Harga:</strong> " . $harga . "</p>
                    </div>
                    <div class='footer'>
                        <p>" . SITE_NAME . " - Sistem Informasi Desa</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
            
            @mail($admin_email, $subject, $message, $headers);
        }
        
        $_SESSION['message'] = 'Potensi berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/potensi.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan potensi: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php?action=tambah");
        exit();
    }
} else {
    header("Location: ../admin/potensi.php");
    exit();
}
?>