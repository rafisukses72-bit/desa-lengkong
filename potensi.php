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

// Fungsi untuk display message
function display_message() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $class = '';
        switch($type) {
            case 'success': $class = 'alert-success'; break;
            case 'error': $class = 'alert-danger'; break;
            case 'warning': $class = 'alert-warning'; break;
            default: $class = 'alert-info';
        }
        
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// -------------------------------------------------

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Proses Tambah Potensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'tambah') {
    $nama = sanitize_input($_POST['nama']);
    $jenis = sanitize_input($_POST['jenis']);
    $deskripsi = $_POST['deskripsi'];
    $konten = $_POST['konten'];
    $alamat = sanitize_input($_POST['alamat']);
    $kontak = sanitize_input($_POST['kontak']);
    $pemilik = sanitize_input($_POST['pemilik']);
    $harga = sanitize_input($_POST['harga']);
    $status = sanitize_input($_POST['status']);
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/potensi/');
        if ($upload_result['success']) {
            $gambar = $upload_result['filename'];
        }
    }
    
    $query = "INSERT INTO potensi (nama, jenis, deskripsi, konten, gambar, alamat, kontak, pemilik, harga, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssss", $nama, $jenis, $deskripsi, $konten, $gambar, $alamat, $kontak, $pemilik, $harga, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Potensi berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: potensi.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan potensi: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Update Potensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $nama = sanitize_input($_POST['nama']);
    $jenis = sanitize_input($_POST['jenis']);
    $deskripsi = $_POST['deskripsi'];
    $konten = $_POST['konten'];
    $alamat = sanitize_input($_POST['alamat']);
    $kontak = sanitize_input($_POST['kontak']);
    $pemilik = sanitize_input($_POST['pemilik']);
    $harga = sanitize_input($_POST['harga']);
    $status = sanitize_input($_POST['status']);
    
    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = upload_file($_FILES['gambar'], '../uploads/potensi/');
        if ($upload_result['success']) {
            $gambar = $upload_result['filename'];
        }
    }
    
    if ($gambar) {
        $query = "UPDATE potensi SET nama=?, jenis=?, deskripsi=?, konten=?, gambar=?, alamat=?, kontak=?, pemilik=?, harga=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssi", $nama, $jenis, $deskripsi, $konten, $gambar, $alamat, $kontak, $pemilik, $harga, $status, $id);
    } else {
        $query = "UPDATE potensi SET nama=?, jenis=?, deskripsi=?, konten=?, alamat=?, kontak=?, pemilik=?, harga=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssi", $nama, $jenis, $deskripsi, $konten, $alamat, $kontak, $pemilik, $harga, $status, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Potensi berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: potensi.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui potensi: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Hapus Potensi
if ($action == 'hapus' && $id > 0) {
    // Hapus gambar terlebih dahulu jika ada
    $query = "SELECT gambar FROM potensi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $potensi = mysqli_fetch_assoc($result);
    
    if ($potensi && $potensi['gambar']) {
        $file_path = '../uploads/potensi/' . $potensi['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus dari database
    $query = "DELETE FROM potensi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Potensi berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
        header("Location: potensi.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menghapus potensi: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Potensi - <?php echo SITE_NAME; ?></title>
    
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
            --umkm-color: #3498db;
            --wisata-color: #2ecc71;
            --pertanian-color: #f39c12;
            --kerajinan-color: #9b59b6;
            --kuliner-color: #e74c3c;
        }
        
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e1f5fe 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: containerSlide 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes containerSlide {
            from {
                opacity: 0;
                transform: translateY(30px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
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
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
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
            background: #3498db;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px;
        }
        
        .stat-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .stat-item:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            animation: iconFloat 3s ease-in-out infinite;
        }
        
        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .stat-umkm { background: linear-gradient(135deg, var(--umkm-color), #2980b9); }
        .stat-wisata { background: linear-gradient(135deg, var(--wisata-color), #27ae60); }
        .stat-pertanian { background: linear-gradient(135deg, var(--pertanian-color), #e67e22); }
        .stat-kerajinan { background: linear-gradient(135deg, var(--kerajinan-color), #8e44ad); }
        .stat-kuliner { background: linear-gradient(135deg, var(--kuliner-color), #c0392b); }
        
        .potensi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 30px;
        }
        
        .potensi-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: cardPop 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        @keyframes cardPop {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .potensi-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        
        .card-header {
            height: 200px;
            position: relative;
            overflow: hidden;
        }
        
        .card-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .potensi-card:hover .card-header img {
            transform: scale(1.1);
        }
        
        .card-type {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: badgeSlide 0.5s ease-out;
        }
        
        @keyframes badgeSlide {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .type-umkm { background: linear-gradient(135deg, var(--umkm-color), #2980b9); }
        .type-wisata { background: linear-gradient(135deg, var(--wisata-color), #27ae60); }
        .type-pertanian { background: linear-gradient(135deg, var(--pertanian-color), #e67e22); }
        .type-kerajinan { background: linear-gradient(135deg, var(--kerajinan-color), #8e44ad); }
        .type-kuliner { background: linear-gradient(135deg, var(--kuliner-color), #c0392b); }
        
        .card-body {
            padding: 25px;
        }
        
        .card-title {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.3em;
            font-weight: 700;
            line-height: 1.4;
        }
        
        .card-desc {
            color: #7f8c8d;
            font-size: 0.95em;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
        }
        
        .detail-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-potensi {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .btn-potensi:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
            animation: bounceIn 1s ease-out;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
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
            border-color: #3498db;
            color: #3498db;
            transform: translateY(-3px);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-color: #3498db;
            color: white;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            padding: 30px;
        }
        
        .page-link {
            margin: 0 5px;
            border-radius: 50% !important;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
        }
        
        .page-link:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, #3498db, #2980b9);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }
        
        @media (max-width: 768px) {
            .potensi-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-options {
                flex-direction: column;
            }
            
            .card-actions {
                flex-direction: column;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 15px;
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
                        <i class="fas fa-chart-line"></i> Kelola Potensi Desa
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo SITE_NAME; ?> - Kelola UMKM, Wisata & Potensi Lainnya
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
                <li><a href="potensi.php" class="active"><i class="fas fa-chart-line"></i> Potensi</a></li>
                <li><a href="galeri.php"><i class="fas fa-images"></i> Galeri</a></li>
                <li><a href="struktur.php"><i class="fas fa-sitemap"></i> Struktur</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <?php display_message(); ?>
        
        <div class="content-area">
            <?php if ($action == 'tambah' || $action == 'edit'): ?>
                <!-- Form Tambah/Edit Potensi -->
                <?php
                $potensi = null;
                if ($action == 'edit' && $id > 0) {
                    $query = "SELECT * FROM potensi WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $potensi = mysqli_fetch_assoc($result);
                    
                    if (!$potensi) {
                        $_SESSION['message'] = 'Potensi tidak ditemukan!';
                        $_SESSION['message_type'] = 'error';
                        header("Location: potensi.php");
                        exit();
                    }
                }
                ?>
                
                <h2 class="page-title" style="color: #2c3e50; margin: 30px 30px 20px; padding-bottom: 15px; border-bottom: 3px solid #3498db;">
                    <i class="fas fa-<?php echo $action == 'tambah' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action == 'tambah' ? 'Tambah Potensi Baru' : 'Edit Potensi'; ?>
                </h2>
                
                <div class="form-container" style="margin: 0 30px 30px; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <form method="POST" enctype="multipart/form-data" 
                          action="potensi.php?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-signature"></i> Nama Potensi
                                    </label>
                                    <input type="text" class="form-control" name="nama" 
                                           value="<?php echo htmlspecialchars($potensi['nama'] ?? ''); ?>" 
                                           required placeholder="Contoh: UMKM Keripik Singkong">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Deskripsi Singkat
                                    </label>
                                    <textarea class="form-control" name="deskripsi" rows="3" 
                                              placeholder="Deskripsi singkat tentang potensi ini..."><?php echo htmlspecialchars($potensi['deskripsi'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-alt"></i> Konten Lengkap
                                    </label>
                                    <textarea class="form-control summernote" name="konten" rows="10"><?php echo htmlspecialchars($potensi['konten'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i> Gambar Utama
                                    </label>
                                    <input type="file" class="form-control" name="gambar" accept="image/*">
                                    <?php if (isset($potensi['gambar']) && $potensi['gambar']): ?>
                                        <div class="mt-3">
                                            <img src="../uploads/potensi/<?php echo $potensi['gambar']; ?>" 
                                                 alt="Gambar saat ini" class="img-fluid rounded" style="max-height: 200px;">
                                            <p class="text-muted small mt-2">Gambar saat ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i> Jenis Potensi
                                    </label>
                                    <select class="form-select" name="jenis" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="umkm" <?php echo ($potensi['jenis'] ?? '') == 'umkm' ? 'selected' : ''; ?>>UMKM</option>
                                        <option value="wisata" <?php echo ($potensi['jenis'] ?? '') == 'wisata' ? 'selected' : ''; ?>>Wisata</option>
                                        <option value="pertanian" <?php echo ($potensi['jenis'] ?? '') == 'pertanian' ? 'selected' : ''; ?>>Pertanian</option>
                                        <option value="kerajinan" <?php echo ($potensi['jenis'] ?? '') == 'kerajinan' ? 'selected' : ''; ?>>Kerajinan</option>
                                        <option value="kuliner" <?php echo ($potensi['jenis'] ?? '') == 'kuliner' ? 'selected' : ''; ?>>Kuliner</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i> Alamat
                                    </label>
                                    <textarea class="form-control" name="alamat" rows="2" 
                                              placeholder="Alamat lengkap..."><?php echo htmlspecialchars($potensi['alamat'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i> Kontak
                                    </label>
                                    <input type="text" class="form-control" name="kontak" 
                                           value="<?php echo htmlspecialchars($potensi['kontak'] ?? ''); ?>" 
                                           placeholder="Nomor telepon/WhatsApp">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i> Nama Pemilik
                                    </label>
                                    <input type="text" class="form-control" name="pemilik" 
                                           value="<?php echo htmlspecialchars($potensi['pemilik'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tags"></i> Harga/Range Harga
                                    </label>
                                    <input type="text" class="form-control" name="harga" 
                                           value="<?php echo htmlspecialchars($potensi['harga'] ?? ''); ?>" 
                                           placeholder="Contoh: Rp 10.000 - 50.000">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-toggle-on"></i> Status
                                    </label>
                                    <select class="form-select" name="status" required>
                                        <option value="aktif" <?php echo ($potensi['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo ($potensi['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-save"></i> 
                                <?php echo $action == 'tambah' ? 'Simpan Potensi' : 'Update Potensi'; ?>
                            </button>
                            <a href="potensi.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Statistik Overview -->
                <div class="stats-overview">
                    <?php
                    $types = ['umkm', 'wisata', 'pertanian', 'kerajinan', 'kuliner'];
                    $typeNames = [
                        'umkm' => 'UMKM',
                        'wisata' => 'Wisata',
                        'pertanian' => 'Pertanian',
                        'kerajinan' => 'Kerajinan',
                        'kuliner' => 'Kuliner'
                    ];
                    
                    foreach ($types as $type) {
                        $query = "SELECT COUNT(*) as total FROM potensi WHERE jenis = ? AND status = 'aktif'";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "s", $type);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $count = mysqli_fetch_assoc($result)['total'];
                        
                        echo '<div class="stat-item animate__animated animate__fadeInUp">';
                        echo '<div class="stat-icon stat-' . $type . '">';
                        switch($type) {
                            case 'umkm': echo '<i class="fas fa-store"></i>'; break;
                            case 'wisata': echo '<i class="fas fa-mountain"></i>'; break;
                            case 'pertanian': echo '<i class="fas fa-tractor"></i>'; break;
                            case 'kerajinan': echo '<i class="fas fa-hands"></i>'; break;
                            case 'kuliner': echo '<i class="fas fa-utensils"></i>'; break;
                        }
                        echo '</div>';
                        echo '<div class="stat-number" style="font-size: 2em; font-weight: 700; color: #2c3e50;">' . $count . '</div>';
                        echo '<div class="stat-label" style="color: #7f8c8d;">' . $typeNames[$type] . '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Filter Potensi</h4>
                        <a href="potensi.php?action=tambah" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Potensi Baru
                        </a>
                    </div>
                    
                    <div class="filter-options">
                        <button class="filter-btn active" data-filter="all">
                            <i class="fas fa-th-large"></i> Semua
                        </button>
                        <button class="filter-btn" data-filter="umkm">
                            <i class="fas fa-store"></i> UMKM
                        </button>
                        <button class="filter-btn" data-filter="wisata">
                            <i class="fas fa-mountain"></i> Wisata
                        </button>
                        <button class="filter-btn" data-filter="pertanian">
                            <i class="fas fa-tractor"></i> Pertanian
                        </button>
                        <button class="filter-btn" data-filter="kerajinan">
                            <i class="fas fa-hands"></i> Kerajinan
                        </button>
                        <button class="filter-btn" data-filter="kuliner">
                            <i class="fas fa-utensils"></i> Kuliner
                        </button>
                    </div>
                </div>
                
                <!-- Daftar Potensi -->
                <?php
                // Pagination
                $limit = 9;
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset = ($page - 1) * $limit;
                
                // Query untuk data potensi
                $query = "SELECT * FROM potensi ORDER BY created_at DESC LIMIT ? OFFSET ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Query untuk total data
                $total_query = "SELECT COUNT(*) as total FROM potensi";
                $total_result = mysqli_query($conn, $total_query);
                $total_row = mysqli_fetch_assoc($total_result);
                $total_data = $total_row['total'];
                $total_pages = ceil($total_data / $limit);
                ?>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="potensi-grid">
                        <?php while($potensi = mysqli_fetch_assoc($result)): ?>
                        <div class="potensi-card" data-type="<?php echo $potensi['jenis']; ?>">
                            <div class="card-header">
                                <?php if ($potensi['gambar']): ?>
                                    <img src="../uploads/potensi/<?php echo $potensi['gambar']; ?>" 
                                         alt="<?php echo htmlspecialchars($potensi['nama']); ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="card-type type-<?php echo $potensi['jenis']; ?>">
                                    <?php echo ucfirst($potensi['jenis']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($potensi['nama']); ?></h3>
                                <p class="card-desc"><?php echo htmlspecialchars($potensi['deskripsi']); ?></p>
                                
                                <div class="card-details">
                                    <?php if ($potensi['pemilik']): ?>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($potensi['pemilik']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($potensi['kontak']): ?>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($potensi['kontak']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($potensi['harga']): ?>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($potensi['harga']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-actions">
                                    <button class="btn-potensi btn-view" onclick="viewPotensi(<?php echo $potensi['id']; ?>)">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    <a href="potensi.php?action=edit&id=<?php echo $potensi['id']; ?>" 
                                       class="btn-potensi btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn-potensi btn-delete" 
                                            onclick="deletePotensi(<?php echo $potensi['id']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
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
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="text-muted mb-3">Belum ada potensi terdaftar</h3>
                        <p class="text-muted mb-4">Mulai dengan menambahkan potensi pertama Anda</p>
                        <a href="potensi.php?action=tambah" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Tambah Potensi Pertama
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
        
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const cards = document.querySelectorAll('.potensi-card');
                
                // Animate cards
                cards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-type') === filter) {
                        card.style.display = 'block';
                        card.style.animation = 'cardPop 0.6s ease-out';
                    } else {
                        card.style.animation = 'cardPop 0.6s ease-out reverse';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 600);
                    }
                });
            });
        });
        
        // View potensi detail
        function viewPotensi(id) {
            // Untuk implementasi nyata, ini akan membuka modal atau halaman detail
            window.open('../potensi-detail.php?id=' + id, '_blank');
        }
        
        // Delete confirmation
        function deletePotensi(id) {
            if (confirm('Apakah Anda yakin ingin menghapus potensi ini?')) {
                window.location.href = 'potensi.php?action=hapus&id=' + id;
            }
        }
    </script>
</body>
</html>