<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $judul = sanitize_input($_POST['judul']);
    $konten = $_POST['konten'];
    $kategori = sanitize_input($_POST['kategori']);
    $status = sanitize_input($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Generate slug
    function generateSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
    
    $slug = generateSlug($judul);
    
    // Generate excerpt dari konten
    $excerpt = substr(strip_tags($konten), 0, 200) . '...';
    
    // Ambil data berita lama untuk mendapatkan nama gambar
    $query_old = "SELECT gambar FROM berita WHERE id = ?";
    $stmt_old = mysqli_prepare($conn, $query_old);
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $result_old = mysqli_stmt_get_result($stmt_old);
    $old_berita = mysqli_fetch_assoc($result_old);
    $old_gambar = $old_berita['gambar'];
    
    // Handle upload gambar baru
    $gambar = $old_gambar;
    $upload_error = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['gambar']['type'], $allowed_types)) {
            $upload_error = "Hanya file gambar JPG, JPEG, PNG, GIF, dan WebP yang diizinkan.";
        } elseif ($_FILES['gambar']['size'] > $max_size) {
            $upload_error = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            $upload_dir = '../uploads/berita/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Hapus gambar lama jika ada
            if ($old_gambar && file_exists($upload_dir . $old_gambar)) {
                unlink($upload_dir . $old_gambar);
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
    }
    
    // Jika ada error upload, tampilkan pesan error
    if ($upload_error) {
        $_SESSION['message'] = $upload_error;
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/berita.php?action=edit&id=" . $id);
        exit();
    }
    
    // Update database
    if ($gambar != $old_gambar) {
        $query = "UPDATE berita SET judul = ?, slug = ?, konten = ?, excerpt = ?, kategori = ?, 
                  gambar = ?, is_featured = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssisi", $judul, $slug, $konten, $excerpt, $kategori, $gambar, $is_featured, $status, $id);
    } else {
        $query = "UPDATE berita SET judul = ?, slug = ?, konten = ?, excerpt = ?, kategori = ?, 
                  is_featured = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssisi", $judul, $slug, $konten, $excerpt, $kategori, $is_featured, $status, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Update tags
        // Hapus tags lama
        $delete_tags = "DELETE FROM berita_tags WHERE berita_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_tags);
        mysqli_stmt_bind_param($delete_stmt, "i", $id);
        mysqli_stmt_execute($delete_stmt);
        
        // Tambah tags baru
        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $tag_query = "INSERT INTO berita_tags (berita_id, tag) VALUES (?, ?)";
                    $tag_stmt = mysqli_prepare($conn, $tag_query);
                    mysqli_stmt_bind_param($tag_stmt, "is", $id, $tag);
                    mysqli_stmt_execute($tag_stmt);
                }
            }
        }
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'edit_berita', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Mengedit berita: " . $judul . " (ID: " . $id . ")";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        $_SESSION['message'] = 'Berita berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/berita.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui berita: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/berita.php?action=edit&id=" . $id);
        exit();
    }
} else {
    header("Location: ../admin/berita.php");
    exit();
}
?>