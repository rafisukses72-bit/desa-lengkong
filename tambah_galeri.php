<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data
    $judul = sanitize_input($_POST['judul']);
    $deskripsi = $_POST['deskripsi'];
    $kategori = sanitize_input($_POST['kategori']);
    
    // Validasi data
    $errors = [];
    
    if (empty($judul)) {
        $errors[] = "Judul foto harus diisi.";
    }
    
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih.";
    }
    
    // Handle upload gambar
    $gambar = null;
    $upload_error = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 10 * 1024 * 1024; // 10MB untuk galeri
        
        if (!in_array($_FILES['gambar']['type'], $allowed_types)) {
            $upload_error = "Hanya file gambar JPG, JPEG, PNG, GIF, dan WebP yang diizinkan.";
        } elseif ($_FILES['gambar']['size'] > $max_size) {
            $upload_error = "Ukuran file terlalu besar. Maksimal 10MB.";
        } else {
            $upload_dir = '../uploads/galeri/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . date('YmdHis') . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            // Resize image untuk optimalisasi
            list($width, $height) = getimagesize($_FILES['gambar']['tmp_name']);
            
            // Tentukan ukuran maksimum
            $max_width = 1200;
            $max_height = 800;
            
            if ($width > $max_width || $height > $max_height) {
                // Hitung rasio baru
                $ratio = min($max_width/$width, $max_height/$height);
                $new_width = round($width * $ratio);
                $new_height = round($height * $ratio);
                
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
            } else {
                // Jika ukuran sudah sesuai, pindahkan langsung
                move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
            }
            
            $gambar = $filename;
            
            // Buat thumbnail (200x200)
            $thumbnail_width = 200;
            $thumbnail_height = 200;
            $thumbnail_file = $upload_dir . 'thumbs/' . $filename;
            
            if (!file_exists($upload_dir . 'thumbs/')) {
                mkdir($upload_dir . 'thumbs/', 0777, true);
            }
            
            $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
            
            switch ($_FILES['gambar']['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($target_file);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($target_file);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($target_file);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($target_file);
                    break;
            }
            
            // Crop ke tengah untuk thumbnail
            $src_x = ($width - $height) / 2;
            $src_y = 0;
            $src_w = $src_h = min($width, $height);
            
            imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, 
                              $thumbnail_width, $thumbnail_height, $src_w, $src_h);
            
            switch ($_FILES['gambar']['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($thumb, $thumbnail_file, 85);
                    break;
                case 'image/png':
                    imagepng($thumb, $thumbnail_file, 8);
                    break;
                case 'image/gif':
                    imagegif($thumb, $thumbnail_file);
                    break;
                case 'image/webp':
                    imagewebp($thumb, $thumbnail_file, 85);
                    break;
            }
            
            imagedestroy($source);
            imagedestroy($thumb);
        }
    } elseif ($_FILES['gambar']['error'] == 4) {
        $upload_error = "Gambar harus diupload.";
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
        header("Location: ../admin/galeri.php?action=tambah");
        exit();
    }
    
    // Simpan ke database
    $query = "INSERT INTO galeri (judul, deskripsi, gambar, kategori, created_at) 
              VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $judul, $deskripsi, $gambar, $kategori);
    
    if (mysqli_stmt_execute($stmt)) {
        $galeri_id = mysqli_insert_id($conn);
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'tambah_galeri', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Menambah foto ke galeri: " . $judul . " (ID: " . $galeri_id . ")";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Update statistik galeri
        $stats_query = "UPDATE pengaturan SET total_galeri = total_galeri + 1 WHERE id = 1";
        mysqli_query($conn, $stats_query);
        
        $_SESSION['message'] = 'Foto berhasil ditambahkan ke galeri!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/galeri.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan foto: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/galeri.php?action=tambah");
        exit();
    }
} else {
    header("Location: ../admin/galeri.php");
    exit();
}
?>