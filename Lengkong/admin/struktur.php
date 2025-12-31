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

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Proses Tambah Struktur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'tambah') {
    $nama = sanitize_input($_POST['nama']);
    $jabatan = sanitize_input($_POST['jabatan']);
    $urutan = intval($_POST['urutan']);
    $deskripsi = $_POST['deskripsi'];
    $tugas = $_POST['tugas'];
    $kontak = sanitize_input($_POST['kontak']);
    $status = sanitize_input($_POST['status']);
    
    // Handle upload foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_result = upload_file($_FILES['foto'], '../uploads/struktur/');
        if (isset($upload_result['filename'])) {
            $foto = $upload_result['filename'];
        }
    }
    
    $query = "INSERT INTO struktur_desa (nama, jabatan, foto, urutan, deskripsi, tugas, kontak, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssissis", $nama, $jabatan, $foto, $urutan, $deskripsi, $tugas, $kontak, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Anggota struktur berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: struktur.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan anggota struktur: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Update Struktur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $nama = sanitize_input($_POST['nama']);
    $jabatan = sanitize_input($_POST['jabatan']);
    $urutan = intval($_POST['urutan']);
    $deskripsi = $_POST['deskripsi'];
    $tugas = $_POST['tugas'];
    $kontak = sanitize_input($_POST['kontak']);
    $status = sanitize_input($_POST['status']);
    
    // Handle upload foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_result = upload_file($_FILES['foto'], '../uploads/struktur/');
        if (isset($upload_result['filename'])) {
            $foto = $upload_result['filename'];
            
            // Hapus foto lama jika ada
            $query_old = "SELECT foto FROM struktur_desa WHERE id = ?";
            $stmt_old = mysqli_prepare($conn, $query_old);
            mysqli_stmt_bind_param($stmt_old, "i", $id);
            mysqli_stmt_execute($stmt_old);
            $result_old = mysqli_stmt_get_result($stmt_old);
            $old_foto = mysqli_fetch_assoc($result_old)['foto'];
            
            if ($old_foto) {
                $old_file = '../uploads/struktur/' . $old_foto;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
        }
    }
    
    if ($foto) {
        $query = "UPDATE struktur_desa SET nama=?, jabatan=?, foto=?, urutan=?, deskripsi=?, tugas=?, kontak=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssissisi", $nama, $jabatan, $foto, $urutan, $deskripsi, $tugas, $kontak, $status, $id);
    } else {
        $query = "UPDATE struktur_desa SET nama=?, jabatan=?, urutan=?, deskripsi=?, tugas=?, kontak=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssissisi", $nama, $jabatan, $urutan, $deskripsi, $tugas, $kontak, $status, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Anggota struktur berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: struktur.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui anggota struktur: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Hapus Struktur
if ($action == 'hapus' && $id > 0) {
    // Hapus foto terlebih dahulu jika ada
    $query = "SELECT foto FROM struktur_desa WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $struktur = mysqli_fetch_assoc($result);
    
    if ($struktur && $struktur['foto']) {
        $file_path = '../uploads/struktur/' . $struktur['foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus dari database
    $query = "DELETE FROM struktur_desa WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Anggota struktur berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
        header("Location: struktur.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menghapus anggota struktur: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Update Urutan
if (isset($_POST['update_urutan'])) {
    $urutan_data = $_POST['urutan'];
    foreach ($urutan_data as $struktur_id => $urutan) {
        $query = "UPDATE struktur_desa SET urutan = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $urutan, $struktur_id);
        mysqli_stmt_execute($stmt);
    }
    
    $_SESSION['message'] = 'Urutan struktur berhasil diperbarui!';
    $_SESSION['message_type'] = 'success';
    header("Location: struktur.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Struktur - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Sortable JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --kepala-desa: #e74c3c;
            --sekretaris: #3498db;
            --bendahara: #2ecc71;
            --kasi: #f39c12;
            --staff: #9b59b6;
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
            animation: structureSlide 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes structureSlide {
            from {
                opacity: 0;
                transform: translateY(40px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
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
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path d="M50,10 L90,50 L50,90 L10,50 Z" fill="white"/></svg>');
            background-size: 200% 200%, 100px 100px;
            animation: shine 3s infinite, pattern 20s infinite linear;
        }
        
        .structure-container {
            padding: 30px;
        }
        
        .org-chart {
            position: relative;
            padding: 40px 0;
            min-height: 500px;
        }
        
        .chart-level {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 60px;
            animation: levelAppear 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        @keyframes levelAppear {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .chart-level-1 { animation-delay: 0.2s; }
        .chart-level-2 { animation-delay: 0.4s; }
        .chart-level-3 { animation-delay: 0.6s; }
        .chart-level-4 { animation-delay: 0.8s; }
        
        .chart-node {
            background: white;
            border-radius: 15px;
            padding: 20px;
            width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            cursor: pointer;
        }
        
        .chart-node:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .node-connector {
            position: absolute;
            background: #3498db;
            transition: all 0.3s ease;
        }
        
        .node-connector.vertical {
            width: 2px;
            height: 40px;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .node-connector.horizontal {
            height: 2px;
            position: absolute;
            top: 50%;
        }
        
        .node-header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .node-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 4px solid;
            transition: all 0.3s ease;
        }
        
        .node-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .node-kepala-desa { border-color: var(--kepala-desa); }
        .node-sekretaris { border-color: var(--sekretaris); }
        .node-bendahara { border-color: var(--bendahara); }
        .node-kasi { border-color: var(--kasi); }
        .node-staff { border-color: var(--staff); }
        
        .node-name {
            font-weight: 700;
            font-size: 1.1em;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .node-position {
            font-size: 0.9em;
            color: var(--secondary-color);
            font-weight: 600;
            padding: 5px 15px;
            border-radius: 20px;
            background: rgba(52, 152, 219, 0.1);
            display: inline-block;
        }
        
        .node-status {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-left: 5px;
        }
        
        .status-aktif { background: #2ecc71; animation: pulse 2s infinite; }
        .status-pensiun { background: #95a5a6; }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        }
        
        .node-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .chart-node:hover .node-actions {
            opacity: 1;
            transform: translateY(0);
        }
        
        .node-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .node-btn-edit {
            background: var(--sekretaris);
            color: white;
        }
        
        .node-btn-delete {
            background: var(--accent-color);
            color: white;
        }
        
        .node-btn:hover {
            transform: rotate(360deg) scale(1.1);
        }
        
        .structure-list {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            animation: listSlide 0.6s ease-out 0.3s both;
        }
        
        @keyframes listSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .list-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: move;
        }
        
        .list-item:hover {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.1), transparent);
            transform: translateX(10px);
            border-radius: 10px;
        }
        
        .list-item.sortable-chosen {
            background: rgba(52, 152, 219, 0.2);
            border-radius: 10px;
        }
        
        .list-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .list-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .list-details {
            flex: 1;
        }
        
        .list-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 3px;
        }
        
        .list-position {
            font-size: 0.9em;
            color: var(--secondary-color);
        }
        
        .list-urutan {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
            animation: countPulse 2s infinite;
        }
        
        @keyframes countPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .empty-structure {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .jabatan-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .jabatan-badge {
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .jabatan-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .badge-kepala-desa { background: linear-gradient(135deg, var(--kepala-desa), #c0392b); color: white; }
        .badge-sekretaris { background: linear-gradient(135deg, var(--sekretaris), #2980b9); color: white; }
        .badge-bendahara { background: linear-gradient(135deg, var(--bendahara), #27ae60); color: white; }
        .badge-kasi { background: linear-gradient(135deg, var(--kasi), #e67e22); color: white; }
        .badge-staff { background: linear-gradient(135deg, var(--staff), #8e44ad); color: white; }
        
        .modal-structure {
            animation: modalZoom 0.3s ease-out;
        }
        
        @keyframes modalZoom {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @media (max-width: 768px) {
            .chart-level {
                flex-direction: column;
                align-items: center;
            }
            
            .jabatan-badges {
                flex-direction: column;
            }
            
            .node-connector.horizontal {
                display: none;
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
                        <i class="fas fa-sitemap"></i> Struktur Organisasi Desa
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo SITE_NAME; ?> - Pengelolaan Struktur Pemerintahan
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
        <nav class="admin-nav" style="background: #2c3e50; padding: 15px 40px;">
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="berita.php"><i class="fas fa-newspaper"></i> Berita</a></li>
                <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                <li><a href="potensi.php"><i class="fas fa-chart-line"></i> Potensi</a></li>
                <li><a href="galeri.php"><i class="fas fa-images"></i> Galeri</a></li>
                <li><a href="struktur.php" class="active"><i class="fas fa-sitemap"></i> Struktur</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <?php display_message(); ?>
        
        <div class="structure-container">
            <?php if ($action == 'tambah' || $action == 'edit'): ?>
                <!-- Form Tambah/Edit Struktur -->
                <?php
                $anggota = null;
                if ($action == 'edit' && $id > 0) {
                    $query = "SELECT * FROM struktur_desa WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $anggota = mysqli_fetch_assoc($result);
                    
                    if (!$anggota) {
                        $_SESSION['message'] = 'Anggota struktur tidak ditemukan!';
                        $_SESSION['message_type'] = 'error';
                        header("Location: struktur.php");
                        exit();
                    }
                }
                ?>
                
                <h2 class="page-title" style="color: #2c3e50; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #3498db;">
                    <i class="fas fa-<?php echo $action == 'tambah' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action == 'tambah' ? 'Tambah Anggota Struktur' : 'Edit Anggota Struktur'; ?>
                </h2>
                
                <div class="form-container" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <form method="POST" enctype="multipart/form-data" 
                          action="struktur.php?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i> Nama Lengkap
                                    </label>
                                    <input type="text" class="form-control" name="nama" 
                                           value="<?php echo $anggota['nama'] ?? ''; ?>" 
                                           required placeholder="Masukkan nama lengkap">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-briefcase"></i> Jabatan
                                    </label>
                                    <input type="text" class="form-control" name="jabatan" 
                                           value="<?php echo $anggota['jabatan'] ?? ''; ?>" 
                                           required placeholder="Contoh: Kepala Desa, Sekretaris Desa">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-sort-numeric-down"></i> Urutan
                                            </label>
                                            <input type="number" class="form-control" name="urutan" 
                                                   value="<?php echo $anggota['urutan'] ?? '0'; ?>" 
                                                   min="0" placeholder="Urutan tampil">
                                            <small class="text-muted">Angka lebih kecil = tampil lebih awal</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-toggle-on"></i> Status
                                            </label>
                                            <select class="form-select" name="status" required>
                                                <option value="aktif" <?php echo ($anggota['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="pensiun" <?php echo ($anggota['status'] ?? '') == 'pensiun' ? 'selected' : ''; ?>>Pensiun</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Deskripsi Singkat
                                    </label>
                                    <textarea class="form-control" name="deskripsi" rows="3" 
                                              placeholder="Deskripsi singkat tentang anggota..."><?php echo $anggota['deskripsi'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tasks"></i> Tugas & Wewenang
                                    </label>
                                    <textarea class="form-control" name="tugas" rows="4" 
                                              placeholder="Tugas dan wewenang utama..."><?php echo $anggota['tugas'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i> Foto Profil
                                    </label>
                                    <input type="file" class="form-control" name="foto" accept="image/*" <?php echo $action == 'tambah' ? 'required' : ''; ?>>
                                    <?php if (isset($anggota['foto']) && $anggota['foto']): ?>
                                        <div class="mt-3">
                                            <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" 
                                                 alt="Foto saat ini" class="img-fluid rounded" style="max-height: 200px;">
                                            <p class="text-muted small mt-2">Foto saat ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i> Kontak
                                    </label>
                                    <input type="text" class="form-control" name="kontak" 
                                           value="<?php echo $anggota['kontak'] ?? ''; ?>" 
                                           placeholder="Nomor telepon/WhatsApp">
                                </div>
                                
                                <div class="card bg-light p-3 mb-3">
                                    <h6><i class="fas fa-info-circle"></i> Informasi</h6>
                                    <p class="small mb-2">Pastikan foto profil memiliki:</p>
                                    <ul class="small mb-0">
                                        <li>Ukuran maksimal 2MB</li>
                                        <li>Format: JPG, PNG</li>
                                        <li>Rasio 1:1 (persegi)</li>
                                        <li>Background netral</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-save"></i> 
                                <?php echo $action == 'tambah' ? 'Simpan Anggota' : 'Update Anggota'; ?>
                            </button>
                            <a href="struktur.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Jabatan Badges -->
                <div class="jabatan-badges">
                    <div class="jabatan-badge badge-kepala-desa">
                        <i class="fas fa-crown"></i> Kepala Desa
                    </div>
                    <div class="jabatan-badge badge-sekretaris">
                        <i class="fas fa-file-alt"></i> Sekretaris
                    </div>
                    <div class="jabatan-badge badge-bendahara">
                        <i class="fas fa-money-bill-wave"></i> Bendahara
                    </div>
                    <div class="jabatan-badge badge-kasi">
                        <i class="fas fa-users"></i> Kepala Seksi
                    </div>
                    <div class="jabatan-badge badge-staff">
                        <i class="fas fa-user-friends"></i> Staff
                    </div>
                </div>
                
                <!-- Header dengan tombol tambah -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title" style="color: #2c3e50; margin: 0; padding-bottom: 15px; border-bottom: 3px solid #3498db;">
                        <i class="fas fa-sitemap"></i> Struktur Organisasi Desa
                    </h2>
                    <a href="struktur.php?action=tambah" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Tambah Anggota
                    </a>
                </div>
                
                <?php
                // Query untuk data struktur
                $query = "SELECT * FROM struktur_desa WHERE status = 'aktif' ORDER BY urutan ASC, jabatan ASC";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0):
                    // Kelompokkan berdasarkan jabatan
                    $kepala_desa = [];
                    $sekretaris = [];
                    $bendahara = [];
                    $kasi = [];
                    $staff = [];
                    
                    mysqli_data_seek($result, 0);
                    while($anggota = mysqli_fetch_assoc($result)) {
                        $jabatan_lower = strtolower($anggota['jabatan']);
                        
                        if (strpos($jabatan_lower, 'kepala desa') !== false) {
                            $kepala_desa[] = $anggota;
                        } elseif (strpos($jabatan_lower, 'sekretaris') !== false) {
                            $sekretaris[] = $anggota;
                        } elseif (strpos($jabatan_lower, 'bendahara') !== false) {
                            $bendahara[] = $anggota;
                        } elseif (strpos($jabatan_lower, 'kasi') !== false || strpos($jabatan_lower, 'kepala seksi') !== false) {
                            $kasi[] = $anggota;
                        } else {
                            $staff[] = $anggota;
                        }
                    }
                ?>
                
                <!-- Organizational Chart -->
                <div class="org-chart">
                    <!-- Level 1: Kepala Desa -->
                    <?php if (!empty($kepala_desa)): ?>
                    <div class="chart-level chart-level-1">
                        <?php foreach($kepala_desa as $anggota): ?>
                        <div class="chart-node">
                            <div class="node-connector vertical"></div>
                            <div class="node-header">
                                <div class="node-avatar node-kepala-desa">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="node-name"><?php echo $anggota['nama']; ?></div>
                                <div class="node-position"><?php echo $anggota['jabatan']; ?></div>
                                <span class="node-status status-<?php echo $anggota['status']; ?>"></span>
                            </div>
                            <div class="node-actions">
                                <a href="struktur.php?action=edit&id=<?php echo $anggota['id']; ?>" 
                                   class="node-btn node-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="node-btn node-btn-delete" 
                                        onclick="deleteAnggota(<?php echo $anggota['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Level 2: Sekretaris & Bendahara -->
                    <?php if (!empty($sekretaris) || !empty($bendahara)): ?>
                    <div class="chart-level chart-level-2">
                        <?php foreach($sekretaris as $anggota): ?>
                        <div class="chart-node">
                            <div class="node-connector vertical"></div>
                            <div class="node-header">
                                <div class="node-avatar node-sekretaris">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="node-name"><?php echo $anggota['nama']; ?></div>
                                <div class="node-position"><?php echo $anggota['jabatan']; ?></div>
                                <span class="node-status status-<?php echo $anggota['status']; ?>"></span>
                            </div>
                            <div class="node-actions">
                                <a href="struktur.php?action=edit&id=<?php echo $anggota['id']; ?>" 
                                   class="node-btn node-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="node-btn node-btn-delete" 
                                        onclick="deleteAnggota(<?php echo $anggota['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php foreach($bendahara as $anggota): ?>
                        <div class="chart-node">
                            <div class="node-connector vertical"></div>
                            <div class="node-header">
                                <div class="node-avatar node-bendahara">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="node-name"><?php echo $anggota['nama']; ?></div>
                                <div class="node-position"><?php echo $anggota['jabatan']; ?></div>
                                <span class="node-status status-<?php echo $anggota['status']; ?>"></span>
                            </div>
                            <div class="node-actions">
                                <a href="struktur.php?action=edit&id=<?php echo $anggota['id']; ?>" 
                                   class="node-btn node-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="node-btn node-btn-delete" 
                                        onclick="deleteAnggota(<?php echo $anggota['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Level 3: Kasi -->
                    <?php if (!empty($kasi)): ?>
                    <div class="chart-level chart-level-3">
                        <?php foreach($kasi as $anggota): ?>
                        <div class="chart-node">
                            <div class="node-connector vertical"></div>
                            <div class="node-header">
                                <div class="node-avatar node-kasi">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="node-name"><?php echo $anggota['nama']; ?></div>
                                <div class="node-position"><?php echo $anggota['jabatan']; ?></div>
                                <span class="node-status status-<?php echo $anggota['status']; ?>"></span>
                            </div>
                            <div class="node-actions">
                                <a href="struktur.php?action=edit&id=<?php echo $anggota['id']; ?>" 
                                   class="node-btn node-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="node-btn node-btn-delete" 
                                        onclick="deleteAnggota(<?php echo $anggota['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Level 4: Staff -->
                    <?php if (!empty($staff)): ?>
                    <div class="chart-level chart-level-4">
                        <?php foreach($staff as $anggota): ?>
                        <div class="chart-node">
                            <div class="node-header">
                                <div class="node-avatar node-staff">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="node-name"><?php echo $anggota['nama']; ?></div>
                                <div class="node-position"><?php echo $anggota['jabatan']; ?></div>
                                <span class="node-status status-<?php echo $anggota['status']; ?>"></span>
                            </div>
                            <div class="node-actions">
                                <a href="struktur.php?action=edit&id=<?php echo $anggota['id']; ?>" 
                                   class="node-btn node-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="node-btn node-btn-delete" 
                                        onclick="deleteAnggota(<?php echo $anggota['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- List untuk mengurutkan -->
                <div class="structure-list">
                    <h4 class="mb-4">
                        <i class="fas fa-sort-amount-down"></i> Urutkan Struktur
                        <small class="text-muted">Drag & drop untuk mengubah urutan tampil</small>
                    </h4>
                    
                    <form method="POST" id="sortingForm">
                        <input type="hidden" name="update_urutan" value="1">
                        <div id="sortableList">
                            <?php 
                            mysqli_data_seek($result, 0);
                            $counter = 1;
                            while($anggota = mysqli_fetch_assoc($result)): 
                            ?>
                            <div class="list-item" data-id="<?php echo $anggota['id']; ?>">
                                <div class="list-avatar">
                                    <?php if ($anggota['foto']): ?>
                                        <img src="../uploads/struktur/<?php echo $anggota['foto']; ?>" alt="<?php echo $anggota['nama']; ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="list-details">
                                    <div class="list-name"><?php echo $anggota['nama']; ?></div>
                                    <div class="list-position"><?php echo $anggota['jabatan']; ?></div>
                                </div>
                                <div class="list-urutan"><?php echo $counter++; ?></div>
                                <input type="hidden" name="urutan[<?php echo $anggota['id']; ?>]" 
                                       value="<?php echo $anggota['urutan']; ?>" class="urutan-input">
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Simpan Urutan
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php else: ?>
                <div class="empty-structure">
                    <div class="empty-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3 class="text-muted mb-3">Struktur organisasi masih kosong</h3>
                    <p class="text-muted mb-4">Mulai dengan menambahkan anggota struktur pertama</p>
                    <a href="struktur.php?action=tambah" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Tambah Anggota Pertama
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Delete confirmation
        function deleteAnggota(id) {
            if (confirm('Apakah Anda yakin ingin menghapus anggota struktur ini?')) {
                window.location.href = 'struktur.php?action=hapus&id=' + id;
            }
        }
        
        // Initialize sortable
        const sortableList = document.getElementById('sortableList');
        if (sortableList) {
            const sortable = new Sortable(sortableList, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    // Update urutan numbers
                    const items = sortableList.querySelectorAll('.list-item');
                    items.forEach((item, index) => {
                        const urutanSpan = item.querySelector('.list-urutan');
                        const urutanInput = item.querySelector('.urutan-input');
                        urutanSpan.textContent = index + 1;
                        urutanInput.value = index + 1;
                    });
                }
            });
        }
        
        // Add animations for chart nodes
        document.querySelectorAll('.chart-node').forEach((node, index) => {
            node.style.animationDelay = (index * 0.1) + 's';
            
            // Hover effect with 3D rotation
            node.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.05) rotateX(5deg)';
                this.style.boxShadow = '0 30px 60px rgba(0,0,0,0.25)';
                
                // Highlight connectors
                const connectors = this.querySelectorAll('.node-connector');
                connectors.forEach(connector => {
                    connector.style.background = '#e74c3c';
                    connector.style.width = '3px';
                });
            });
            
            node.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1) rotateX(0)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
                
                // Reset connectors
                const connectors = this.querySelectorAll('.node-connector');
                connectors.forEach(connector => {
                    connector.style.background = '#3498db';
                    connector.style.width = '2px';
                });
            });
        });
        
        // Animate jabatan badges
        document.querySelectorAll('.jabatan-badge').forEach((badge, index) => {
            badge.style.animationDelay = (index * 0.2) + 's';
            badge.style.animation = 'badgeSlide 0.6s ease-out';
        });
        
        // Add CSS animation for badges
        const style = document.createElement('style');
        style.textContent = `
            @keyframes badgeSlide {
                from {
                    opacity: 0;
                    transform: translateY(20px) scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            .jabatan-badge {
                animation: badgeSlide 0.6s ease-out;
                animation-fill-mode: both;
            }
        `;
        document.head.appendChild(style);
        
        // Node click for details
        document.querySelectorAll('.chart-node').forEach(node => {
            node.addEventListener('click', function(e) {
                if (!e.target.closest('.node-actions')) {
                    const name = this.querySelector('.node-name').textContent;
                    const position = this.querySelector('.node-position').textContent;
                    
                    // Create modal or show details
                    alert(`Detail:\nNama: ${name}\nJabatan: ${position}`);
                }
            });
        });
        
        // Update connector lines on resize
        function updateConnectors() {
            const levels = document.querySelectorAll('.chart-level');
            levels.forEach((level, levelIndex) => {
                const nodes = level.querySelectorAll('.chart-node');
                
                if (levelIndex < levels.length - 1) {
                    const nextLevel = levels[levelIndex + 1];
                    const nextNodes = nextLevel.querySelectorAll('.chart-node');
                    
                    // Create horizontal connectors
                    nodes.forEach((node, nodeIndex) => {
                        const nodeRect = node.getBoundingClientRect();
                        const nextNodeRect = nextNodes[nodeIndex]?.getBoundingClientRect();
                        
                        if (nextNodeRect) {
                            const connector = document.createElement('div');
                            connector.className = 'node-connector horizontal';
                            connector.style.width = Math.abs(nextNodeRect.left - nodeRect.left) + 'px';
                            connector.style.left = Math.min(nodeRect.left, nextNodeRect.left) + 'px';
                            connector.style.top = (nodeRect.bottom + nextNodeRect.top) / 2 + 'px';
                            
                            document.querySelector('.org-chart').appendChild(connector);
                        }
                    });
                }
            });
        }
        
        // Update on window resize
        window.addEventListener('resize', updateConnectors);
        window.addEventListener('load', updateConnectors);
    </script>
</body>
</html>