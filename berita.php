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

// TAMBAHKAN FUNGSI-FUNGSI YANG DIPERLUKAN DI SINI
// -------------------------------------------------

// Fungsi sanitize input
function sanitize_input($data) {
    global $conn;
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk generate slug
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

// Fungsi upload file
function upload_file($file, $upload_dir) {
    $result = ['success' => false, 'filename' => '', 'error' => ''];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $result['error'] = 'Jenis file tidak diizinkan. Hanya JPG, PNG, GIF, WebP yang diperbolehkan.';
        return $result;
    }
    
    if ($file['size'] > $max_size) {
        $result['error'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        return $result;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Gagal mengupload file.';
    }
    
    return $result;
}

// Fungsi format tanggal
function format_tanggal($date) {
    if (empty($date)) return '';
    return date('d M Y', strtotime($date));
}

// -------------------------------------------------

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Proses Tambah Berita
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'tambah') {
    $judul = sanitize_input($_POST['judul']);
    $konten = $_POST['konten'];
    $kategori = sanitize_input($_POST['kategori']);
    $status = sanitize_input($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $penulis = $_SESSION['nama'] ?? 'Admin';
    $slug = generateSlug($judul);
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/berita/');
        if ($upload_result['success']) {
            $gambar = $upload_result['filename'];
        }
    }
    
    // Generate excerpt dari konten
    $excerpt = substr(strip_tags($konten), 0, 200) . '...';
    
    $query = "INSERT INTO berita (judul, slug, konten, excerpt, kategori, gambar, penulis, is_featured, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssis", $judul, $slug, $konten, $excerpt, $kategori, $gambar, $penulis, $is_featured, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Berita berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: berita.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan berita: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Update Berita
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $judul = sanitize_input($_POST['judul']);
    $konten = $_POST['konten'];
    $kategori = sanitize_input($_POST['kategori']);
    $status = sanitize_input($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $slug = generateSlug($judul);
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/berita/');
        if ($upload_result['success']) {
            $gambar = $upload_result['filename'];
        }
    }
    
    // Generate excerpt dari konten
    $excerpt = substr(strip_tags($konten), 0, 200) . '...';
    
    if ($gambar) {
        $query = "UPDATE berita SET judul=?, slug=?, konten=?, excerpt=?, kategori=?, gambar=?, is_featured=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssisi", $judul, $slug, $konten, $excerpt, $kategori, $gambar, $is_featured, $status, $id);
    } else {
        $query = "UPDATE berita SET judul=?, slug=?, konten=?, excerpt=?, kategori=?, is_featured=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssisi", $judul, $slug, $konten, $excerpt, $kategori, $is_featured, $status, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Berita berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: berita.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui berita: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Hapus Berita
if ($action == 'hapus' && $id > 0) {
    // Hapus gambar terlebih dahulu jika ada
    $query = "SELECT gambar FROM berita WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $berita = mysqli_fetch_assoc($result);
    
    if ($berita && $berita['gambar']) {
        $file_path = '../uploads/berita/' . $berita['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus dari database
    $query = "DELETE FROM berita WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Berita berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
        header("Location: berita.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menghapus berita: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Summernote Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 40px;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path d="M0,50 Q250,0 500,50 T1000,50 V100 H0 Z" fill="white" opacity="0.1"/></svg>');
            background-size: 100% 100%;
        }
        
        .admin-nav {
            background: var(--dark-color);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-menu {
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        
        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .nav-menu a.active {
            background: var(--secondary-color);
        }
        
        .content-area {
            padding: 30px;
            min-height: 500px;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary-color);
            position: relative;
            animation: titleSlide 0.6s ease-out;
        }
        
        @keyframes titleSlide {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            animation: lineExpand 0.8s ease-out 0.3s both;
        }
        
        @keyframes lineExpand {
            from { width: 0; }
            to { width: 100px; }
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.8s ease-out;
        }
        
        .form-group {
            margin-bottom: 25px;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            transform: translateY(-2px);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .btn-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn-custom:focus::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.8s ease-out;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .table th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #eee;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.1), transparent);
            transform: translateX(10px);
        }
        
        .table-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .table-img:hover {
            transform: scale(1.5);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            z-index: 100;
        }
        
        .badge-custom {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-published {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }
        
        .badge-draft {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }
        
        .badge-featured {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }
        
        .btn-action:hover {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            animation: bounceIn 1s ease-out;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        
        .page-link {
            margin: 0 5px;
            border-radius: 50% !important;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .table-img {
                width: 60px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="animate__animated animate__fadeInDown">
                        <i class="fas fa-newspaper"></i> Kelola Berita
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo SITE_NAME; ?> - Admin Panel
                    </p>
                </div>
                <div class="animate__animated animate__zoomIn animate__delay-2s">
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="admin-nav">
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="berita.php" class="active"><i class="fas fa-newspaper"></i> Berita</a></li>
                <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                <li><a href="potensi.php"><i class="fas fa-chart-line"></i> Potensi</a></li>
                <li><a href="galeri.php"><i class="fas fa-images"></i> Galeri</a></li>
                <li><a href="struktur.php"><i class="fas fa-sitemap"></i> Struktur</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <?php 
        // Tampilkan pesan
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message_type'] ?? 'info';
            $alert_class = '';
            switch($message_type) {
                case 'success': $alert_class = 'alert-success'; break;
                case 'error': $alert_class = 'alert-danger'; break;
                case 'warning': $alert_class = 'alert-warning'; break;
                default: $alert_class = 'alert-info';
            }
            
            echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show m-3" role="alert">
                    ' . htmlspecialchars($_SESSION['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        
        <div class="content-area">
            <?php if ($action == 'tambah' || $action == 'edit'): ?>
                <!-- Form Tambah/Edit Berita -->
                <?php
                $berita = null;
                if ($action == 'edit' && $id > 0) {
                    $query = "SELECT * FROM berita WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $berita = mysqli_fetch_assoc($result);
                    
                    if (!$berita) {
                        $_SESSION['message'] = 'Berita tidak ditemukan!';
                        $_SESSION['message_type'] = 'error';
                        header("Location: berita.php");
                        exit();
                    }
                }
                ?>
                
                <h2 class="page-title">
                    <i class="fas fa-<?php echo $action == 'tambah' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action == 'tambah' ? 'Tambah Berita Baru' : 'Edit Berita'; ?>
                </h2>
                
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data" 
                          action="berita.php?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-heading"></i> Judul Berita
                            </label>
                            <input type="text" class="form-control" name="judul" 
                                   value="<?php echo htmlspecialchars($berita['judul'] ?? ''); ?>" 
                                   required placeholder="Masukkan judul berita">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Konten Berita
                            </label>
                            <textarea class="form-control summernote" name="konten" rows="10" 
                                      required placeholder="Tulis konten berita di sini..."><?php echo htmlspecialchars($berita['konten'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i> Kategori
                                    </label>
                                    <select class="form-select" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="umum" <?php echo ($berita['kategori'] ?? '') == 'umum' ? 'selected' : ''; ?>>Umum</option>
                                        <option value="kegiatan" <?php echo ($berita['kategori'] ?? '') == 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                        <option value="pengumuman" <?php echo ($berita['kategori'] ?? '') == 'pengumuman' ? 'selected' : ''; ?>>Pengumuman</option>
                                        <option value="pembangunan" <?php echo ($berita['kategori'] ?? '') == 'pembangunan' ? 'selected' : ''; ?>>Pembangunan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i> Gambar Utama
                                    </label>
                                    <input type="file" class="form-control" name="gambar" accept="image/*">
                                    <?php if (isset($berita['gambar']) && $berita['gambar']): ?>
                                        <div class="mt-2">
                                            <img src="../uploads/berita/<?php echo $berita['gambar']; ?>" 
                                                 alt="Gambar saat ini" class="img-thumbnail" style="max-width: 200px;">
                                            <p class="text-muted small mt-1">Gambar saat ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-star"></i> Status Fitur
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_featured" 
                                               id="is_featured" <?php echo ($berita['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Tampilkan sebagai berita utama
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-eye"></i> Status Publikasi
                                    </label>
                                    <select class="form-select" name="status" required>
                                        <option value="draft" <?php echo ($berita['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo ($berita['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-custom me-2">
                                <i class="fas fa-save"></i> 
                                <?php echo $action == 'tambah' ? 'Simpan Berita' : 'Update Berita'; ?>
                            </button>
                            <a href="berita.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Daftar Berita -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title">
                        <i class="fas fa-newspaper"></i> Daftar Berita
                    </h2>
                    <a href="berita.php?action=tambah" class="btn btn-custom">
                        <i class="fas fa-plus"></i> Tambah Berita Baru
                    </a>
                </div>
                
                <?php
                // Pagination
                $limit = 10;
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset = ($page - 1) * $limit;
                
                // Query untuk data berita
                $query = "SELECT * FROM berita ORDER BY created_at DESC LIMIT ? OFFSET ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Query untuk total data
                $total_query = "SELECT COUNT(*) as total FROM berita";
                $total_result = mysqli_query($conn, $total_query);
                $total_row = mysqli_fetch_assoc($total_result);
                $total_data = $total_row['total'];
                $total_pages = ceil($total_data / $limit);
                ?>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Gambar</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = $offset + 1; ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="animate__animated animate__fadeIn">
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <?php if ($row['gambar']): ?>
                                            <img src="../uploads/berita/<?php echo $row['gambar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($row['judul']); ?>" class="table-img">
                                        <?php else: ?>
                                            <div class="table-img bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['judul']); ?></strong>
                                        <?php if ($row['is_featured']): ?>
                                            <span class="badge badge-featured ms-2">Featured</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($row['penulis']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst($row['kategori']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-custom badge-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo format_tanggal($row['created_at']); ?></small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-eye"></i> <?php echo $row['views']; ?> views
                                        </small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../berita-detail.php?slug=<?php echo $row['slug']; ?>" 
                                               target="_blank" class="btn-action btn-view" 
                                               title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="berita.php?action=edit&id=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                                               class="btn-action btn-delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container animate__animated animate__fadeInUp">
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
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3 class="text-muted mb-3">Belum ada berita</h3>
                        <p class="text-muted mb-4">Mulai dengan menambahkan berita pertama Anda</p>
                        <a href="berita.php?action=tambah" class="btn btn-custom">
                            <i class="fas fa-plus"></i> Tambah Berita Pertama
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery (diperlukan Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <script>
        // Inisialisasi Summernote
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
        
        // Konfirmasi hapus
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
                window.location.href = 'berita.php?action=hapus&id=' + id;
            }
        }
        
        // Animasi untuk form inputs
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>