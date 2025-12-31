<?php
require_once '../config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi sanitize_input yang hilang
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi upload_file yang hilang
function upload_file($file, $target_dir) {
    $result = array();
    
    // Validasi file
    if (!isset($file['name']) || empty($file['name'])) {
        $result['error'] = 'File tidak ditemukan';
        return $result;
    }
    
    // Cek error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi batas form)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        ];
        
        $result['error'] = $error_messages[$file['error']] ?? 'Error upload tidak diketahui';
        return $result;
    }
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $result['error'] = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP';
        return $result;
    }
    
    // Validasi ukuran file (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        $result['error'] = 'Ukuran file maksimal 5MB';
        return $result;
    }
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate nama file unik
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = 'galeri_' . time() . '_' . uniqid() . '.' . $file_ext;
    $target_file = $target_dir . $file_name;
    
    // Coba upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $result['filename'] = $file_name;
        $result['full_path'] = $target_file;
        $result['file_type'] = $file_type;
        $result['file_size'] = $file['size'];
    } else {
        $result['error'] = 'Gagal mengupload file';
    }
    
    return $result;
}

// Fungsi display_message yang hilang
function display_message() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        
        $alert_class = '';
        $icon = '';
        switch($type) {
            case 'success': 
                $alert_class = 'alert-success'; 
                $icon = '<i class="fas fa-check-circle me-2"></i>';
                break;
            case 'error': 
                $alert_class = 'alert-danger'; 
                $icon = '<i class="fas fa-exclamation-circle me-2"></i>';
                break;
            case 'warning': 
                $alert_class = 'alert-warning'; 
                $icon = '<i class="fas fa-exclamation-triangle me-2"></i>';
                break;
            default: 
                $alert_class = 'alert-info'; 
                $icon = '<i class="fas fa-info-circle me-2"></i>';
        }
        
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert" 
              style="margin: 20px; border-radius: 10px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
              ' . $icon . htmlspecialchars($message) . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Fungsi format_tanggal yang hilang
function format_tanggal($date_string) {
    $timestamp = strtotime($date_string);
    if ($timestamp === false) {
        return 'Tanggal tidak valid';
    }
    return date('d M Y, H:i', $timestamp);
}

// Fungsi darken_color untuk statistik
function darken_color($color, $percent) {
    $color = str_replace('#', '', $color);
    $num = hexdec($color);
    $amt = round(2.55 * $percent);
    $r = ($num >> 16) - $amt;
    $g = ($num >> 8 & 0x00FF) - $amt;
    $b = ($num & 0x0000FF) - $amt;
    $r = max(0, min(255, $r));
    $g = max(0, min(255, $g));
    $b = max(0, min(255, $b));
    return '#' . str_pad(dechex($r * 0x10000 + $g * 0x100 + $b), 6, '0', STR_PAD_LEFT);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Proses Tambah Galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'tambah') {
    $judul = sanitize_input($_POST['judul'] ?? '');
    $deskripsi = $_POST['deskripsi'] ?? '';
    $kategori = sanitize_input($_POST['kategori'] ?? '');
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/galeri/');
        if (isset($upload_result['filename'])) {
            $gambar = $upload_result['filename'];
        } else {
            $_SESSION['message'] = 'Gagal upload gambar: ' . ($upload_result['error'] ?? 'Error tidak diketahui');
            $_SESSION['message_type'] = 'error';
            header("Location: galeri.php?action=tambah");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Silakan pilih file gambar';
        $_SESSION['message_type'] = 'error';
        header("Location: galeri.php?action=tambah");
        exit();
    }
    
    $query = "INSERT INTO galeri (judul, deskripsi, gambar, kategori) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $judul, $deskripsi, $gambar, $kategori);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Foto berhasil ditambahkan ke galeri!';
        $_SESSION['message_type'] = 'success';
        header("Location: galeri.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan foto: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: galeri.php?action=tambah");
        exit();
    }
}

// Proses Update Galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $judul = sanitize_input($_POST['judul'] ?? '');
    $deskripsi = $_POST['deskripsi'] ?? '';
    $kategori = sanitize_input($_POST['kategori'] ?? '');
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/galeri/');
        if (isset($upload_result['filename'])) {
            $gambar = $upload_result['filename'];
        } else {
            $_SESSION['message'] = 'Gagal upload gambar: ' . ($upload_result['error'] ?? 'Error tidak diketahui');
            $_SESSION['message_type'] = 'error';
            header("Location: galeri.php?action=edit&id=" . $id);
            exit();
        }
    }
    
    if ($gambar) {
        // Hapus gambar lama jika ada
        $query_old = "SELECT gambar FROM galeri WHERE id = ?";
        $stmt_old = mysqli_prepare($conn, $query_old);
        mysqli_stmt_bind_param($stmt_old, "i", $id);
        mysqli_stmt_execute($stmt_old);
        $result_old = mysqli_stmt_get_result($stmt_old);
        $row = mysqli_fetch_assoc($result_old);
        $old_gambar = $row['gambar'] ?? null;
        
        if ($old_gambar) {
            $old_file = '../uploads/galeri/' . $old_gambar;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        $query = "UPDATE galeri SET judul=?, deskripsi=?, gambar=?, kategori=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $judul, $deskripsi, $gambar, $kategori, $id);
    } else {
        $query = "UPDATE galeri SET judul=?, deskripsi=?, kategori=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $judul, $deskripsi, $kategori, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Foto berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: galeri.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui foto: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: galeri.php?action=edit&id=" . $id);
        exit();
    }
}

// Proses Hapus Galeri
if ($action == 'hapus' && $id > 0) {
    // Hapus gambar terlebih dahulu jika ada
    $query = "SELECT gambar FROM galeri WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $galeri = mysqli_fetch_assoc($result);
    
    if ($galeri && isset($galeri['gambar'])) {
        $file_path = '../uploads/galeri/' . $galeri['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus dari database
    $query = "DELETE FROM galeri WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Foto berhasil dihapus dari galeri!';
        $_SESSION['message_type'] = 'success';
        header("Location: galeri.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menghapus foto: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: galeri.php");
        exit();
    }
}

// Proses Upload Multiple
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_multiple'])) {
    $kategori = sanitize_input($_POST['kategori_bulk'] ?? '');
    $success_count = 0;
    $error_count = 0;
    $error_messages = [];
    
    if (empty($kategori)) {
        $_SESSION['message'] = 'Silakan pilih kategori untuk semua foto';
        $_SESSION['message_type'] = 'error';
        header("Location: galeri.php");
        exit();
    }
    
    if (isset($_FILES['gambar_multiple'])) {
        foreach ($_FILES['gambar_multiple']['name'] as $key => $name) {
            if ($_FILES['gambar_multiple']['error'][$key] == 0) {
                $file = [
                    'name' => $_FILES['gambar_multiple']['name'][$key],
                    'type' => $_FILES['gambar_multiple']['type'][$key],
                    'tmp_name' => $_FILES['gambar_multiple']['tmp_name'][$key],
                    'error' => $_FILES['gambar_multiple']['error'][$key],
                    'size' => $_FILES['gambar_multiple']['size'][$key]
                ];
                
                $upload_result = upload_file($file, '../uploads/galeri/');
                if (isset($upload_result['filename'])) {
                    $gambar = $upload_result['filename'];
                    $judul = pathinfo($name, PATHINFO_FILENAME);
                    
                    $query = "INSERT INTO galeri (judul, gambar, kategori) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sss", $judul, $gambar, $kategori);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $error_messages[] = "File {$name}: " . mysqli_error($conn);
                    }
                } else {
                    $error_count++;
                    $error_messages[] = "File {$name}: " . ($upload_result['error'] ?? 'Error upload');
                }
            }
        }
    }
    
    $message = "Upload selesai: {$success_count} foto berhasil, {$error_count} gagal";
    if ($error_count > 0 && !empty($error_messages)) {
        $message .= ". Error: " . implode("; ", array_slice($error_messages, 0, 3));
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $error_count == 0 ? 'success' : ($success_count > 0 ? 'warning' : 'error');
    header("Location: galeri.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Query untuk total data
$total_query = "SELECT COUNT(*) as total FROM galeri";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'] ?? 0;
$total_pages = ceil($total_data / $limit);

// Query untuk data galeri
$query = "SELECT * FROM galeri ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
mysqli_stmt_execute($stmt);
$galeri_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Lightbox -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: gallerySlide 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes gallerySlide {
            from {
                opacity: 0;
                transform: translateY(40px) rotateY(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateY(0);
            }
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path d="M50,50 L80,50 L80,80 L50,80 Z M20,20 L50,20 L50,50 L20,50 Z" fill="white"/></svg>');
            background-size: 200% 200%, 100px 100px;
            animation: shine 3s infinite, pattern 20s infinite linear;
        }
        
        @keyframes shine {
            0% { background-position: -100% -100%, 0 0; }
            100% { background-position: 200% 200%, 0 0; }
        }
        
        @keyframes pattern {
            0% { background-position: -100% -100%, 0 0; }
            100% { background-position: 200% 200%, 100px 100px; }
        }
        
        .gallery-container {
            padding: 30px;
        }
        
        .gallery-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            animation: filterSlide 0.6s ease-out;
        }
        
        @keyframes filterSlide {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .filter-btn {
            padding: 10px 25px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            background: white;
            color: #7f8c8d;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .filter-btn:hover {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
            transform: translateY(-3px);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-color: var(--secondary-color);
            color: white;
        }
        
        .filter-btn.active::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: filterShine 2s infinite;
        }
        
        @keyframes filterShine {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: photoPop 0.8s ease-out;
            animation-fill-mode: both;
        }
        
        @keyframes photoPop {
            0% {
                opacity: 0;
                transform: scale(0.8) rotate(-5deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0);
            }
        }
        
        .gallery-item:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .gallery-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .gallery-item:hover .gallery-image {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.4s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }
        
        .gallery-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .gallery-category {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        .gallery-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover .gallery-actions {
            opacity: 1;
            transform: translateY(0);
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn-view {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .upload-zone {
            border: 3px dashed #3498db;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: rgba(52, 152, 219, 0.1);
            margin-bottom: 30px;
            transition: all 0.3s ease;
            animation: pulseBorder 2s infinite;
        }
        
        @keyframes pulseBorder {
            0%, 100% { border-color: rgba(52, 152, 219, 0.5); }
            50% { border-color: rgba(52, 152, 219, 1); }
        }
        
        .upload-zone:hover {
            background: rgba(52, 152, 219, 0.2);
            transform: translateY(-5px);
        }
        
        .upload-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            animation: previewSlide 0.5s ease-out;
        }
        
        @keyframes previewSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .preview-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }
        
        .preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .preview-remove:hover {
            transform: scale(1.2);
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .empty-gallery {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        /* Navbar styling */
        .admin-nav {
            background: #2c3e50;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255,255,255,0.1);
        }
        
        .nav-menu a.active {
            background: var(--secondary-color);
        }
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 70px;
            height: 70px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .filter-btn {
                flex: 1;
                min-width: 120px;
            }
            
            .upload-zone {
                padding: 20px;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-menu {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>
    
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="animate__animated animate__fadeInDown">
                        <i class="fas fa-images"></i> Galeri Foto Desa
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo SITE_NAME; ?> - Dokumentasi Kegiatan & Potensi
                    </p>
                </div>
                <div class="animate__animated animate__zoomIn animate__delay-2s">
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="admin-nav">
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="berita.php"><i class="fas fa-newspaper"></i> Berita</a></li>
                <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                <li><a href="potensi.php"><i class="fas fa-chart-line"></i> Potensi</a></li>
                <li><a href="galeri.php" class="active"><i class="fas fa-images"></i> Galeri</a></li>
                <li><a href="struktur.php"><i class="fas fa-sitemap"></i> Struktur</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <?php display_message(); ?>
        
        <div class="gallery-container">
            <?php if ($action == 'tambah' || $action == 'edit'): ?>
                <!-- Form Tambah/Edit Foto -->
                <?php
                $foto = null;
                if ($action == 'edit' && $id > 0) {
                    $query = "SELECT * FROM galeri WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $foto = mysqli_fetch_assoc($result);
                    
                    if (!$foto) {
                        $_SESSION['message'] = 'Foto tidak ditemukan!';
                        $_SESSION['message_type'] = 'error';
                        header("Location: galeri.php");
                        exit();
                    }
                }
                ?>
                
                <h2 class="page-title" style="color: #2c3e50; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #3498db;">
                    <i class="fas fa-<?php echo $action == 'tambah' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action == 'tambah' ? 'Tambah Foto ke Galeri' : 'Edit Foto Galeri'; ?>
                </h2>
                
                <div class="form-container" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <form method="POST" enctype="multipart/form-data" id="photoForm" 
                          action="galeri.php?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-heading"></i> Judul Foto
                            </label>
                            <input type="text" class="form-control" name="judul" 
                                   value="<?php echo htmlspecialchars($foto['judul'] ?? ''); ?>" 
                                   required placeholder="Masukkan judul foto">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Deskripsi
                            </label>
                            <textarea class="form-control" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi foto..."><?php echo htmlspecialchars($foto['deskripsi'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i> Upload Foto
                                    </label>
                                    <input type="file" class="form-control" name="gambar" accept="image/*" id="fileInput" <?php echo $action == 'tambah' ? 'required' : ''; ?>>
                                    <div class="form-text">Format: JPG, PNG, GIF, WebP (Max: 5MB)</div>
                                    
                                    <!-- Image preview -->
                                    <div id="imagePreview" class="mt-3" style="display: <?php echo isset($foto['gambar']) && $foto['gambar'] ? 'block' : 'none'; ?>;">
                                        <?php if (isset($foto['gambar']) && $foto['gambar']): ?>
                                            <img src="../uploads/galeri/<?php echo htmlspecialchars($foto['gambar']); ?>" 
                                                 alt="Gambar saat ini" class="img-fluid rounded" style="max-height: 200px; width: auto;">
                                            <p class="text-muted small mt-2">Foto saat ini</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Live preview -->
                                    <div id="livePreview" class="mt-3" style="display: none;">
                                        <img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px; width: auto;">
                                        <p class="text-muted small mt-2">Preview foto baru</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i> Kategori
                                    </label>
                                    <select class="form-select" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="kegiatan" <?php echo ($foto['kategori'] ?? '') == 'kegiatan' ? 'selected' : ''; ?>>Kegiatan Desa</option>
                                        <option value="potensi" <?php echo ($foto['kategori'] ?? '') == 'potensi' ? 'selected' : ''; ?>>Potensi Desa</option>
                                        <option value="infrastruktur" <?php echo ($foto['kategori'] ?? '') == 'infrastruktur' ? 'selected' : ''; ?>>Infrastruktur</option>
                                        <option value="alam" <?php echo ($foto['kategori'] ?? '') == 'alam' ? 'selected' : ''; ?>>Pemandangan Alam</option>
                                        <option value="lainnya" <?php echo ($foto['kategori'] ?? '') == 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                                
                                <!-- Upload progress -->
                                <div class="progress mb-3" id="uploadProgress" style="display: none; height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg me-2" id="submitBtn">
                                <i class="fas fa-save"></i> 
                                <?php echo $action == 'tambah' ? 'Simpan Foto' : 'Update Foto'; ?>
                            </button>
                            <a href="galeri.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Statistik -->
                <div class="stats-bar">
                    <?php
                    // Total foto
                    $query_total = "SELECT COUNT(*) as total FROM galeri";
                    $result_total = mysqli_query($conn, $query_total);
                    $total_foto = mysqli_fetch_assoc($result_total)['total'] ?? 0;
                    
                    // Foto per kategori
                    $kategori_stats = ['kegiatan', 'potensi', 'infrastruktur', 'alam', 'lainnya'];
                    $kategori_names = [
                        'kegiatan' => 'Kegiatan',
                        'potensi' => 'Potensi',
                        'infrastruktur' => 'Infrastruktur',
                        'alam' => 'Alam',
                        'lainnya' => 'Lainnya'
                    ];
                    
                    $colors = ['#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#95a5a6'];
                    
                    echo '<div class="stat-card" style="background: linear-gradient(135deg, #2c3e50, #34495e);">';
                    echo '<div class="stat-number">' . $total_foto . '</div>';
                    echo '<div>Total Foto</div>';
                    echo '</div>';
                    
                    $color_index = 0;
                    foreach ($kategori_stats as $kategori) {
                        $query = "SELECT COUNT(*) as total FROM galeri WHERE kategori = ?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "s", $kategori);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $count = $row['total'] ?? 0;
                        
                        $color = $colors[$color_index];
                        $dark_color = darken_color($color, 20);
                        
                        echo '<div class="stat-card" style="background: linear-gradient(135deg, ' . $color . ', ' . $dark_color . ');">';
                        echo '<div class="stat-number">' . $count . '</div>';
                        echo '<div>' . $kategori_names[$kategori] . '</div>';
                        echo '</div>';
                        
                        $color_index++;
                    }
                    ?>
                </div>
                
                <!-- Multiple Upload Zone -->
                <div class="upload-zone mb-5">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h4>Upload Multiple Foto</h4>
                    <p class="text-muted mb-4">Drag & drop atau klik untuk memilih beberapa foto sekaligus</p>
                    
                    <form method="POST" enctype="multipart/form-data" id="multipleUploadForm">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <select class="form-select" name="kategori_bulk" required id="kategoriBulk">
                                        <option value="">Pilih Kategori untuk semua foto</option>
                                        <option value="kegiatan">Kegiatan Desa</option>
                                        <option value="potensi">Potensi Desa</option>
                                        <option value="infrastruktur">Infrastruktur</option>
                                        <option value="alam">Pemandangan Alam</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="gambar_multiple[]" 
                                           multiple accept="image/*" id="multipleFiles">
                                </div>
                                <div class="preview-container" id="previewContainer"></div>
                                <button type="submit" name="upload_multiple" class="btn btn-primary btn-lg w-100 mt-3" id="multipleSubmitBtn">
                                    <i class="fas fa-upload"></i> Upload Semua Foto
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Filter dan Tombol Tambah -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title" style="color: #2c3e50; margin: 0; padding-bottom: 15px; border-bottom: 3px solid #3498db;">
                        <i class="fas fa-images"></i> Galeri Foto Desa
                    </h2>
                    <a href="galeri.php?action=tambah" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Tambah Foto
                    </a>
                </div>
                
                <!-- Filter -->
                <div class="gallery-filter">
                    <button class="filter-btn active" data-filter="all">
                        <i class="fas fa-th-large"></i> Semua
                    </button>
                    <button class="filter-btn" data-filter="kegiatan">
                        <i class="fas fa-calendar-alt"></i> Kegiatan
                    </button>
                    <button class="filter-btn" data-filter="potensi">
                        <i class="fas fa-chart-line"></i> Potensi
                    </button>
                    <button class="filter-btn" data-filter="infrastruktur">
                        <i class="fas fa-road"></i> Infrastruktur
                    </button>
                    <button class="filter-btn" data-filter="alam">
                        <i class="fas fa-mountain"></i> Alam
                    </button>
                    <button class="filter-btn" data-filter="lainnya">
                        <i class="fas fa-ellipsis-h"></i> Lainnya
                    </button>
                </div>
                
                <!-- Galeri Grid -->
                <?php if (mysqli_num_rows($galeri_result) > 0): ?>
                    <div class="gallery-grid" id="galleryGrid">
                        <?php 
                        $index = 0;
                        while($foto = mysqli_fetch_assoc($galeri_result)): 
                            $animate_delay = ($index * 0.1);
                        ?>
                        <div class="gallery-item" data-category="<?php echo htmlspecialchars($foto['kategori']); ?>" 
                             style="animation-delay: <?php echo $animate_delay; ?>s;">
                            <img src="../uploads/galeri/<?php echo htmlspecialchars($foto['gambar']); ?>" 
                                 alt="<?php echo htmlspecialchars($foto['judul']); ?>" class="gallery-image"
                                 data-lightbox="gallery" data-title="<?php echo htmlspecialchars($foto['judul']); ?>">
                            
                            <div class="gallery-overlay">
                                <div class="gallery-title"><?php echo htmlspecialchars($foto['judul']); ?></div>
                                <div class="gallery-category"><?php echo ucfirst(htmlspecialchars($foto['kategori'])); ?></div>
                                <small class="d-block mt-2">
                                    <i class="fas fa-calendar"></i> <?php echo format_tanggal($foto['created_at']); ?>
                                </small>
                            </div>
                            
                            <div class="gallery-actions">
                                <a href="../uploads/galeri/<?php echo htmlspecialchars($foto['gambar']); ?>" 
                                   class="action-btn btn-view" data-lightbox="gallery-<?php echo $foto['id']; ?>"
                                   data-title="<?php echo htmlspecialchars($foto['judul']); ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="galeri.php?action=edit&id=<?php echo $foto['id']; ?>" 
                                   class="action-btn btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="action-btn btn-delete" 
                                        onclick="deleteFoto(<?php echo $foto['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php 
                        $index++;
                        endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container" style="display: flex; justify-content: center; padding: 30px;">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-gallery">
                        <div class="empty-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3 class="text-muted mb-3">Galeri masih kosong</h3>
                        <p class="text-muted mb-4">Mulai dengan mengupload foto pertama Anda</p>
                        <a href="galeri.php?action=tambah" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Tambah Foto Pertama
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <script>
        // Lightbox configuration
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': "Foto %1 dari %2",
            'fadeDuration': 300,
            'imageFadeDuration': 300
        });
        
        // Loading overlay functions
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Form submission with loading
        document.addEventListener('DOMContentLoaded', function() {
            const photoForm = document.getElementById('photoForm');
            const multipleUploadForm = document.getElementById('multipleUploadForm');
            
            if (photoForm) {
                photoForm.addEventListener('submit', function() {
                    showLoading();
                });
            }
            
            if (multipleUploadForm) {
                multipleUploadForm.addEventListener('submit', function() {
                    const kategori = document.getElementById('kategoriBulk').value;
                    const files = document.getElementById('multipleFiles').files;
                    
                    if (!kategori) {
                        alert('Silakan pilih kategori untuk semua foto');
                        return false;
                    }
                    
                    if (files.length === 0) {
                        alert('Silakan pilih foto yang akan diupload');
                        return false;
                    }
                    
                    showLoading();
                    return true;
                });
            }
        });
        
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const items = document.querySelectorAll('.gallery-item');
                
                // Animate items
                items.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.animation = 'photoPop 0.6s ease-out';
                        }, 10);
                    } else {
                        item.style.animation = 'photoPop 0.3s ease-out reverse';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
        
        // Multiple file upload preview
        const multipleFiles = document.getElementById('multipleFiles');
        const previewContainer = document.getElementById('previewContainer');
        
        if (multipleFiles) {
            multipleFiles.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                const files = this.files;
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    
                    // Validasi ukuran file (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} terlalu besar (max 5MB)`);
                        continue;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="preview-image">
                            <button type="button" class="preview-remove" data-index="${i}">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(previewItem);
                        
                        // Add remove functionality
                        previewItem.querySelector('.preview-remove').addEventListener('click', function() {
                            const dataTransfer = new DataTransfer();
                            const newFiles = Array.from(multipleFiles.files);
                            const removeIndex = parseInt(this.getAttribute('data-index'));
                            newFiles.splice(removeIndex, 1);
                            
                            newFiles.forEach(file => {
                                dataTransfer.items.add(file);
                            });
                            
                            multipleFiles.files = dataTransfer.files;
                            previewItem.remove();
                            
                            // Update indices for remaining previews
                            const remainingPreviews = previewContainer.querySelectorAll('.preview-remove');
                            remainingPreviews.forEach((previewBtn, newIndex) => {
                                previewBtn.setAttribute('data-index', newIndex);
                            });
                        });
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Live image preview for single upload
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                const preview = document.getElementById('previewImage');
                const livePreview = document.getElementById('livePreview');
                const imagePreview = document.getElementById('imagePreview');
                
                if (file) {
                    // Validasi ukuran file
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Ukuran file maksimal 5MB');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        livePreview.style.display = 'block';
                        if (imagePreview) {
                            imagePreview.style.display = 'none';
                        }
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Delete confirmation
        function deleteFoto(id) {
            if (confirm('Apakah Anda yakin ingin menghapus foto ini dari galeri?')) {
                showLoading();
                window.location.href = 'galeri.php?action=hapus&id=' + id;
            }
        }
        
        // Add hover animations
        document.querySelectorAll('.gallery-item').forEach((item, index) => {
            item.style.animationDelay = (index * 0.1) + 's';
            
            // 3D effect on mousemove
            item.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-15px) scale(1.05)`;
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1)';
            });
        });
        
        // Drag and drop for upload zone
        const uploadZone = document.querySelector('.upload-zone');
        const multipleFileInput = document.getElementById('multipleFiles');
        
        if (uploadZone && multipleFileInput) {
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#3498db';
                this.style.background = 'rgba(52, 152, 219, 0.3)';
            });
            
            uploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.borderColor = '';
                this.style.background = '';
            });
            
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = '';
                this.style.background = '';
                
                const files = e.dataTransfer.files;
                multipleFileInput.files = files;
                
                // Trigger change event
                const event = new Event('change');
                multipleFileInput.dispatchEvent(event);
            });
        }
        
        // Auto hide loading after page load
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 500);
        });
    </script>
</body>
</html>