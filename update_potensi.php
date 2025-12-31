<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Ambil data lama
    $query_old = "SELECT * FROM potensi WHERE id = ?";
    $stmt_old = mysqli_prepare($conn, $query_old);
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $result_old = mysqli_stmt_get_result($stmt_old);
    
    if (mysqli_num_rows($result_old) == 0) {
        $_SESSION['message'] = 'Potensi tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php");
        exit();
    }
    
    $old_potensi = mysqli_fetch_assoc($result_old);
    
    // Ambil dan sanitasi data baru
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
    
    // Handle upload gambar baru
    $gambar = $old_potensi['gambar'];
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
            
            // Hapus gambar lama jika ada
            if ($old_potensi['gambar'] && file_exists($upload_dir . $old_potensi['gambar'])) {
                unlink($upload_dir . $old_potensi['gambar']);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . date('YmdHis') . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            // Resize image
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
    }
    
    // Gabungkan semua error
    if ($upload_error) {
        $errors[] = $upload_error;
    }
    
    // Jika ada error, tampilkan pesan error
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php?action=edit&id=" . $id);
        exit();
    }
    
    // Update database
    if ($gambar != $old_potensi['gambar']) {
        $query = "UPDATE potensi SET nama = ?, jenis = ?, deskripsi = ?, konten = ?, gambar = ?, 
                  alamat = ?, kontak = ?, pemilik = ?, harga = ?, status = ?, updated_at = NOW() 
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssi", 
            $nama, $jenis, $deskripsi, $konten, $gambar, $alamat, $kontak, $pemilik, $harga, $status, $id);
    } else {
        $query = "UPDATE potensi SET nama = ?, jenis = ?, deskripsi = ?, konten = ?, 
                  alamat = ?, kontak = ?, pemilik = ?, harga = ?, status = ?, updated_at = NOW() 
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssi", 
            $nama, $jenis, $deskripsi, $konten, $alamat, $kontak, $pemilik, $harga, $status, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity dengan detail perubahan
        $changes = [];
        
        if ($old_potensi['nama'] != $nama) {
            $changes[] = "Nama: '" . $old_potensi['nama'] . "' → '" . $nama . "'";
        }
        if ($old_potensi['jenis'] != $jenis) {
            $changes[] = "Jenis: " . $old_potensi['jenis'] . " → " . $jenis;
        }
        if ($old_potensi['status'] != $status) {
            $changes[] = "Status: " . $old_potensi['status'] . " → " . $status;
        }
        
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'edit_potensi', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Mengedit potensi: " . $nama . " (ID: " . $id . ")";
        if (!empty($changes)) {
            $description .= " - Perubahan: " . implode(", ", $changes);
        }
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Update statistik jika status berubah
        if ($old_potensi['status'] != $status) {
            if ($status == 'aktif') {
                $stats_query = "UPDATE pengaturan SET potensi_aktif = potensi_aktif + 1 WHERE id = 1";
            } else {
                $stats_query = "UPDATE pengaturan SET potensi_aktif = GREATEST(potensi_aktif - 1, 0) WHERE id = 1";
            }
            mysqli_query($conn, $stats_query);
        }
        
        $_SESSION['message'] = 'Potensi berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/potensi.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui potensi: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php?action=edit&id=" . $id);
        exit();
    }
} else {
    header("Location: ../admin/potensi.php");
    exit();
}
?>