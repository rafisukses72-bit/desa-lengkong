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

// Inisialisasi variabel session jika belum ada
if (!isset($_SESSION['nama_lengkap'])) {
    $_SESSION['nama_lengkap'] = 'Administrator';
}
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'admin';
}

// Fungsi untuk mengubah status pengajuan
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'approve' || $_GET['action'] == 'reject' || $_GET['action'] == 'process') {
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $status = '';
        
        switch ($_GET['action']) {
            case 'approve':
                $status = 'approved';
                break;
            case 'reject':
                $status = 'rejected';
                break;
            case 'process':
                $status = 'process';
                break;
        }
        
        $update_query = "UPDATE pengajuan_surat SET status = '$status' WHERE id = '$id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = "Status pengajuan berhasil diubah!";
            $_SESSION['message_type'] = "success";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['message'] = "Gagal mengubah status pengajuan!";
            $_SESSION['message_type'] = "error";
        }
    }
}

// Fungsi untuk menghapus pengajuan
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM pengajuan_surat WHERE id = '$id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Pengajuan berhasil dihapus!";
        $_SESSION['message_type'] = "success";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = "Gagal menghapus pengajuan!";
        $_SESSION['message_type'] = "error";
    }
}

// Fungsi untuk menandai pesan sebagai sudah dibaca
if (isset($_GET['mark_as_read'])) {
    $id = mysqli_real_escape_string($conn, $_GET['mark_as_read']);
    $update_query = "UPDATE pesan SET status = 'sudah_dibaca' WHERE id = '$id'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Pesan telah ditandai sebagai sudah dibaca!";
        $_SESSION['message_type'] = "success";
        header("Location: index.php");
        exit();
    }
}

// Ambil statistik
$stat_berita = mysqli_query($conn, "SELECT COUNT(*) as total FROM berita");
$stat_agenda = mysqli_query($conn, "SELECT COUNT(*) as total FROM agenda");
$stat_potensi = mysqli_query($conn, "SELECT COUNT(*) as total FROM potensi");
$stat_pesan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='belum_dibaca'");
$stat_galeri = mysqli_query($conn, "SELECT COUNT(*) as total FROM galeri");
$stat_pengajuan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengajuan_surat WHERE status='pending'");

// Cek jika query statistik gagal (tabel mungkin belum ada)
if ($stat_berita) $total_berita = mysqli_fetch_assoc($stat_berita)['total']; else $total_berita = 0;
if ($stat_agenda) $total_agenda = mysqli_fetch_assoc($stat_agenda)['total']; else $total_agenda = 0;
if ($stat_potensi) $total_potensi = mysqli_fetch_assoc($stat_potensi)['total']; else $total_potensi = 0;
if ($stat_pesan) $total_pesan = mysqli_fetch_assoc($stat_pesan)['total']; else $total_pesan = 0;
if ($stat_galeri) $total_galeri = mysqli_fetch_assoc($stat_galeri)['total']; else $total_galeri = 0;
if ($stat_pengajuan) $total_pengajuan = mysqli_fetch_assoc($stat_pengajuan)['total']; else $total_pengajuan = 0;

// Ambil berita terbaru
$query_berita = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at DESC LIMIT 5") or die(mysqli_error($conn));
// Ambil agenda mendatang
$query_agenda = mysqli_query($conn, "SELECT * FROM agenda WHERE tanggal_mulai >= CURDATE() ORDER BY tanggal_mulai ASC LIMIT 5") or die(mysqli_error($conn));
// Ambil pengajuan terbaru
$query_pengajuan = mysqli_query($conn, "SELECT * FROM pengajuan_surat ORDER BY tanggal_pengajuan DESC LIMIT 5") or die(mysqli_error($conn));
// Ambil pesan terbaru
$query_pesan = mysqli_query($conn, "SELECT * FROM pesan ORDER BY tanggal_kirim DESC LIMIT 5") or die(mysqli_error($conn));

// Fungsi untuk menampilkan pesan
function display_message() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] == 'error' ? 'danger' : 'success';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show m-4" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
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
            position: relative;
            overflow: hidden;
        }
        
        .nav-menu a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .nav-menu a:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .notification-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            padding: 30px;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .content-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .content-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .content-item:hover {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.1), transparent);
            transform: translateX(10px);
            border-radius: 10px;
        }
        
        .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            padding: 30px;
        }
        
        .action-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
        }
        
        .admin-footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }
        
        .welcome-text {
            animation: slideInLeft 1s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-process { background: #d1ecf1; color: #0c5460; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-belum_dibaca { background: #d1ecf1; color: #0c5460; font-weight: bold; }
        .status-sudah_dibaca { background: #e2e3e5; color: #383d41; }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background: var(--success-color);
            color: white;
            border: none;
        }
        
        .btn-reject {
            background: var(--accent-color);
            color: white;
            border: none;
        }
        
        .btn-process {
            background: var(--warning-color);
            color: white;
            border: none;
        }
        
        .btn-view {
            background: var(--secondary-color);
            color: white;
            border: none;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: rgba(52, 152, 219, 0.05);
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="welcome-text">
                    <h1 class="animate__animated animate__fadeInDown">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        Selamat datang, <?php echo $_SESSION['username']; ?>! 
                        <span class="badge bg-light text-dark animate__animated animate__pulse animate__infinite">
                            <?php echo date('d F Y H:i'); ?>
                        </span>
                    </p>
                </div>
                <div class="user-info animate__animated animate__zoomIn animate__delay-2s">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5em;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?php echo $_SESSION['nama_lengkap']; ?></h5>
                            <small class="text-light"><?php echo $_SESSION['role']; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="admin-nav">
            <ul class="nav-menu">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="berita.php"><i class="fas fa-newspaper"></i> Berita</a></li>
                <li><a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
                <li><a href="potensi.php"><i class="fas fa-chart-line"></i> Potensi</a></li>
                <li><a href="galeri.php"><i class="fas fa-images"></i> Galeri</a></li>
                <li><a href="#" data-bs-toggle="modal" data-bs-target="#manageSubmissionsModal"><i class="fas fa-file-alt"></i> Pengajuan 
                    <?php if($total_pengajuan > 0): ?>
                    <span class="badge bg-danger"><?php echo $total_pengajuan; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="#" data-bs-toggle="modal" data-bs-target="#manageMessagesModal"><i class="fas fa-envelope"></i> Pesan 
                    <?php if($total_pesan > 0): ?>
                    <span class="badge bg-danger"><?php echo $total_pesan; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <a href="logout.php" class="action-btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <?php display_message(); ?>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card animate__animated animate__fadeInUp">
                <div class="stat-icon">
                    <i class="fas fa-newspaper fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_berita; ?></div>
                <div class="stat-label">Total Berita</div>
                <div class="stat-desc">Artikel yang dipublikasi</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_agenda; ?></div>
                <div class="stat-label">Total Agenda</div>
                <div class="stat-desc">Kegiatan yang dijadwalkan</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s">
                <div class="stat-icon">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_potensi; ?></div>
                <div class="stat-label">Potensi Desa</div>
                <div class="stat-desc">UMKM dan wisata</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="stat-icon">
                    <i class="fas fa-envelope fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_pesan; ?></div>
                <div class="stat-label">Pesan Baru</div>
                <div class="stat-desc">Belum dibaca</div>
                <?php if($total_pesan > 0): ?>
                <div class="notification-badge"><?php echo $total_pesan; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-4s">
                <div class="stat-icon">
                    <i class="fas fa-images fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_galeri; ?></div>
                <div class="stat-label">Foto Galeri</div>
                <div class="stat-desc">Dokumentasi kegiatan</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-5s">
                <div class="stat-icon">
                    <i class="fas fa-file-alt fa-2x"></i>
                </div>
                <div class="stat-number"><?php echo $total_pengajuan; ?></div>
                <div class="stat-label">Pengajuan</div>
                <div class="stat-desc">Menunggu verifikasi</div>
                <?php if($total_pengajuan > 0): ?>
                <div class="notification-badge"><?php echo $total_pengajuan; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="berita.php?action=tambah" class="action-btn animate__animated animate__bounceIn">
                <i class="fas fa-plus"></i> Tambah Berita
            </a>
            <a href="agenda.php?action=tambah" class="action-btn animate__animated animate__bounceIn animate__delay-1s">
                <i class="fas fa-plus"></i> Tambah Agenda
            </a>
            <a href="potensi.php?action=tambah" class="action-btn animate__animated animate__bounceIn animate__delay-2s">
                <i class="fas fa-plus"></i> Tambah Potensi
            </a>
            <a href="galeri.php?action=tambah" class="action-btn animate__animated animate__bounceIn animate__delay-3s">
                <i class="fas fa-plus"></i> Tambah Foto
            </a>
            <button type="button" class="action-btn animate__animated animate__bounceIn animate__delay-4s" data-bs-toggle="modal" data-bs-target="#manageSubmissionsModal">
                <i class="fas fa-tasks"></i> Kelola Pengajuan
                <?php if($total_pengajuan > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo $total_pengajuan; ?> baru</span>
                <?php endif; ?>
            </button>
            <button type="button" class="action-btn animate__animated animate__bounceIn animate__delay-5s" data-bs-toggle="modal" data-bs-target="#manageMessagesModal">
                <i class="fas fa-inbox"></i> Kelola Pesan
                <?php if($total_pesan > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo $total_pesan; ?> baru</span>
                <?php endif; ?>
            </button>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Berita Terbaru -->
            <div class="content-card">
                <h4 class="mb-3"><i class="fas fa-newspaper text-primary"></i> Berita Terbaru</h4>
                <ul class="content-list">
                    <?php 
                    if ($query_berita && mysqli_num_rows($query_berita) > 0) {
                        while($berita = mysqli_fetch_assoc($query_berita)): ?>
                    <li class="content-item">
                        <div class="item-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($berita['judul']); ?></h6>
                            <small class="text-muted">
                                <?php echo date('d M Y', strtotime($berita['created_at'])); ?>
                            </small>
                        </div>
                        <div class="ms-auto">
                            <span class="badge <?php echo $berita['status'] == 'published' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo $berita['status']; ?>
                            </span>
                        </div>
                    </li>
                    <?php endwhile; 
                    } else {
                        echo '<li class="content-item"><div class="text-center text-muted">Belum ada berita</div></li>';
                    } ?>
                </ul>
                <a href="berita.php" class="btn btn-outline-primary w-100 mt-3">Lihat Semua Berita</a>
            </div>
            
            <!-- Agenda Mendatang -->
            <div class="content-card">
                <h4 class="mb-3"><i class="fas fa-calendar-alt text-success"></i> Agenda Mendatang</h4>
                <ul class="content-list">
                    <?php 
                    if ($query_agenda && mysqli_num_rows($query_agenda) > 0) {
                        while($agenda = mysqli_fetch_assoc($query_agenda)): ?>
                    <li class="content-item">
                        <div class="item-icon" style="background: <?php echo $agenda['jenis'] == 'rapat' ? '#3498db' : ($agenda['jenis'] == 'kegiatan' ? '#2ecc71' : '#e74c3c'); ?>">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($agenda['judul']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($agenda['jam_mulai'])); ?> - 
                                <?php echo htmlspecialchars($agenda['lokasi']); ?>
                            </small>
                        </div>
                        <div class="ms-auto">
                            <small class="text-primary">
                                <?php echo date('d M', strtotime($agenda['tanggal_mulai'])); ?>
                            </small>
                        </div>
                    </li>
                    <?php endwhile; 
                    } else {
                        echo '<li class="content-item"><div class="text-center text-muted">Belum ada agenda</div></li>';
                    } ?>
                </ul>
                <a href="agenda.php" class="btn btn-outline-success w-100 mt-3">Lihat Semua Agenda</a>
            </div>
            
            <!-- Pengajuan Terbaru -->
            <div class="content-card">
                <h4 class="mb-3"><i class="fas fa-file-contract text-warning"></i> Pengajuan Terbaru</h4>
                <ul class="content-list">
                    <?php 
                    if ($query_pengajuan && mysqli_num_rows($query_pengajuan) > 0) {
                        while($pengajuan = mysqli_fetch_assoc($query_pengajuan)): ?>
                    <li class="content-item">
                        <div class="item-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($pengajuan['nama_pemohon']); ?></h6>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($pengajuan['jenis_layanan']); ?>
                            </small>
                        </div>
                        <div class="ms-auto">
                            <span class="status-badge status-<?php echo $pengajuan['status']; ?>">
                                <?php echo $pengajuan['status']; ?>
                            </span>
                        </div>
                    </li>
                    <?php endwhile; 
                    } else {
                        echo '<li class="content-item"><div class="text-center text-muted">Belum ada pengajuan</div></li>';
                    } ?>
                </ul>
                <button type="button" class="btn btn-outline-warning w-100 mt-3" data-bs-toggle="modal" data-bs-target="#manageSubmissionsModal">
                    Kelola Pengajuan
                </button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="admin-footer">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - Sistem Informasi Desa 
                <span class="badge bg-light text-dark ms-2">v1.0</span>
            </p>
            <small class="text-light">
                Terakhir diakses: <?php echo date('d F Y H:i:s'); ?>
            </small>
        </div>
    </div>

    <!-- Modal: Kelola Pengajuan -->
    <div class="modal fade" id="manageSubmissionsModal" tabindex="-1" aria-labelledby="manageSubmissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageSubmissionsModalLabel">
                        <i class="fas fa-tasks me-2"></i>Kelola Pengajuan Surat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pemohon</th>
                                    <th>Jenis Layanan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_submissions = mysqli_query($conn, "SELECT * FROM pengajuan_surat ORDER BY tanggal_pengajuan DESC");
                                if ($all_submissions && mysqli_num_rows($all_submissions) > 0) {
                                    $no = 1;
                                    while($submission = mysqli_fetch_assoc($all_submissions)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($submission['nama_pemohon']); ?></td>
                                    <td><?php echo htmlspecialchars($submission['jenis_layanan']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($submission['tanggal_pengajuan'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $submission['status']; ?>">
                                            <?php echo $submission['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-view me-1" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal<?php echo $submission['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($submission['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-approve me-1" onclick="return confirm('Setujui pengajuan ini?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?action=process&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-process me-1" onclick="return confirm('Tandai sedang diproses?')">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                            <a href="?action=reject&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-reject me-1" onclick="return confirm('Tolak pengajuan ini?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete_id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengajuan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal: Detail Pengajuan -->
                                <div class="modal fade" id="viewSubmissionModal<?php echo $submission['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Pengajuan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Nama Pemohon:</strong>
                                                    <p><?php echo htmlspecialchars($submission['nama_pemohon']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>NIK:</strong>
                                                    <p><?php echo htmlspecialchars($submission['nik']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Jenis Layanan:</strong>
                                                    <p><?php echo htmlspecialchars($submission['jenis_layanan']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Alamat:</strong>
                                                    <p><?php echo htmlspecialchars($submission['alamat']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>No. Telepon:</strong>
                                                    <p><?php echo htmlspecialchars($submission['no_telepon']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Keperluan:</strong>
                                                    <p><?php echo htmlspecialchars($submission['keperluan']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Tanggal Pengajuan:</strong>
                                                    <p><?php echo date('d F Y H:i', strtotime($submission['tanggal_pengajuan'])); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Status:</strong>
                                                    <span class="status-badge status-<?php echo $submission['status']; ?>">
                                                        <?php echo $submission['status']; ?>
                                                    </span>
                                                </div>
                                                <?php if(!empty($submission['catatan'])): ?>
                                                <div class="mb-3">
                                                    <strong>Catatan:</strong>
                                                    <p><?php echo htmlspecialchars($submission['catatan']); ?></p>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; 
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">Belum ada pengajuan</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Kelola Pesan -->
    <div class="modal fade" id="manageMessagesModal" tabindex="-1" aria-labelledby="manageMessagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageMessagesModalLabel">
                        <i class="fas fa-envelope me-2"></i>Kelola Pesan Masuk
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pengirim</th>
                                    <th>Email</th>
                                    <th>Subjek</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_messages = mysqli_query($conn, "SELECT * FROM pesan ORDER BY tanggal_kirim DESC");
                                if ($all_messages && mysqli_num_rows($all_messages) > 0) {
                                    $no = 1;
                                    while($message = mysqli_fetch_assoc($all_messages)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($message['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subjek']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($message['tanggal_kirim'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo $message['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-view me-1" data-bs-toggle="modal" data-bs-target="#viewMessageModal<?php echo $message['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($message['status'] == 'belum_dibaca'): ?>
                                            <a href="?mark_as_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Tandai sudah dibaca?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete_message_id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal: Detail Pesan -->
                                <div class="modal fade" id="viewMessageModal<?php echo $message['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Pesan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Nama Pengirim:</strong>
                                                    <p><?php echo htmlspecialchars($message['nama']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Email:</strong>
                                                    <p><?php echo htmlspecialchars($message['email']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Telepon:</strong>
                                                    <p><?php echo htmlspecialchars($message['telepon']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Subjek:</strong>
                                                    <p><?php echo htmlspecialchars($message['subjek']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Isi Pesan:</strong>
                                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                                        <?php echo nl2br(htmlspecialchars($message['isi_pesan'])); ?>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Tanggal Kirim:</strong>
                                                    <p><?php echo date('d F Y H:i', strtotime($message['tanggal_kirim'])); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Status:</strong>
                                                    <span class="status-badge status-<?php echo $message['status']; ?>">
                                                        <?php echo $message['status']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <?php if($message['status'] == 'belum_dibaca'): ?>
                                                <a href="?mark_as_read=<?php echo $message['id']; ?>" class="btn btn-success">
                                                    <i class="fas fa-check me-1"></i>Tandai Sudah Dibaca
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; 
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">Belum ada pesan</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animasi untuk stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.05)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Animasi untuk content items
        document.querySelectorAll('.content-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(10px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.querySelector('.badge.bg-light').textContent = 
                now.toLocaleDateString('id-ID', options);
        }
        
        setInterval(updateTime, 1000);
        
        // Efek ripple untuk tombol
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple-animation 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    top: ${y}px;
                    left: ${x}px;
                `;
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Tambah style untuk ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .action-btn {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
        
        // Auto-refresh jika ada notifikasi baru
        function checkForUpdates() {
            fetch('check_updates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_submissions > 0 || data.new_messages > 0) {
                        // Tampilkan notifikasi
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
                        notification.style.zIndex = '9999';
                        notification.innerHTML = `
                            <strong>Update!</strong> Ada ${data.new_submissions} pengajuan baru dan ${data.new_messages} pesan baru.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(notification);
                        
                        // Refresh page setelah 5 detik
                        setTimeout(() => {
                            location.reload();
                        }, 5000);
                    }
                });
        }
        
        // Cek update setiap 30 detik
        setInterval(checkForUpdates, 30000);
    </script>
</body>
</html>