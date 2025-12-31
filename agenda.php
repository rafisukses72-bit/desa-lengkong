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

// Fungsi sanitize input
function sanitize_input($data) {
    global $conn;
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Proses Tambah Agenda
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'tambah') {
    $judul = sanitize_input($_POST['judul']);
    $deskripsi = $_POST['deskripsi']; // Tidak disanitize karena mungkin mengandung HTML
    $deskripsi = mysqli_real_escape_string($conn, $deskripsi); // Hanya escape untuk SQL
    $tanggal_mulai = sanitize_input($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize_input($_POST['tanggal_selesai']);
    $jam_mulai = sanitize_input($_POST['jam_mulai']);
    $jam_selesai = sanitize_input($_POST['jam_selesai']);
    $lokasi = sanitize_input($_POST['lokasi']);
    $jenis = sanitize_input($_POST['jenis']);
    $peserta = sanitize_input($_POST['peserta']);
    
    $query = "INSERT INTO agenda (judul, deskripsi, tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, lokasi, jenis, peserta) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssss", $judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, $jam_mulai, $jam_selesai, $lokasi, $jenis, $peserta);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Agenda berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: agenda.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan agenda: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Update Agenda
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $judul = sanitize_input($_POST['judul']);
    $deskripsi = $_POST['deskripsi'];
    $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
    $tanggal_mulai = sanitize_input($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize_input($_POST['tanggal_selesai']);
    $jam_mulai = sanitize_input($_POST['jam_mulai']);
    $jam_selesai = sanitize_input($_POST['jam_selesai']);
    $lokasi = sanitize_input($_POST['lokasi']);
    $jenis = sanitize_input($_POST['jenis']);
    $peserta = sanitize_input($_POST['peserta']);
    
    $query = "UPDATE agenda SET judul=?, deskripsi=?, tanggal_mulai=?, tanggal_selesai=?, jam_mulai=?, jam_selesai=?, lokasi=?, jenis=?, peserta=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssssi", $judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, $jam_mulai, $jam_selesai, $lokasi, $jenis, $peserta, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Agenda berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: agenda.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui agenda: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

// Proses Hapus Agenda
if ($action == 'hapus' && $id > 0) {
    $query = "DELETE FROM agenda WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Agenda berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
        header("Location: agenda.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menghapus agenda: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Agenda - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Flatpickr (Date Picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        /* Semua style CSS Anda tetap sama */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }
        
        body {
            background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideInFromRight 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path d="M0,50 C200,0 400,100 600,50 C800,0 1000,100 1000,100 V0 H0 Z" fill="white" opacity="0.1"/></svg>');
            background-size: cover;
        }
        
        .admin-nav {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }
        
        .nav-menu li a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-menu li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-menu li a.active {
            background: var(--secondary-color);
            font-weight: 500;
        }
        
        .agenda-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 5px solid var(--secondary-color);
            animation: cardAppear 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .agenda-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .agenda-date {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            min-width: 100px;
            margin-right: 20px;
            animation: pulseDate 2s infinite;
        }
        
        @keyframes pulseDate {
            0%, 100% {
                box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            }
            50% {
                box-shadow: 0 10px 25px rgba(52, 152, 219, 0.6);
            }
        }
        
        .agenda-date .day {
            font-size: 2em;
            font-weight: 700;
            line-height: 1;
        }
        
        .agenda-date .month {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .agenda-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            margin-bottom: 10px;
            animation: badgePop 0.3s ease-out;
        }
        
        .type-rapat { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .type-kegiatan { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; }
        .type-posyandu { background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white; }
        .type-penyuluhan { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .type-lainnya { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
        
        .timeline-container {
            position: relative;
            padding-left: 30px;
            margin-top: 30px;
        }
        
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--secondary-color), var(--accent-color));
            border-radius: 3px;
            animation: timelineGrow 1s ease-out;
        }
        
        @keyframes timelineGrow {
            from { height: 0; }
            to { height: 100%; }
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 20px;
            animation: timelineItemAppear 0.6s ease-out;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--secondary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--secondary-color);
            animation: dotPulse 2s infinite;
        }
        
        @keyframes dotPulse {
            0%, 100% {
                box-shadow: 0 0 0 3px var(--secondary-color);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(52, 152, 219, 0.5);
            }
        }
        
        .calendar-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 30px;
        }
        
        .calendar-day {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .calendar-day:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .calendar-day.has-event::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            background: var(--accent-color);
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .calendar-day .date {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .calendar-day.today {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
        }
        
        .calendar-day.today .date {
            color: white;
        }
        
        .event-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            animation: badgeBounce 0.5s ease-out;
        }
        
        @keyframes badgeBounce {
            0% { transform: scale(0); }
            70% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .btn-agenda {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-agenda:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-agenda::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-agenda:hover::after {
            width: 300px;
            height: 300px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            animation: statusPulse 2s infinite;
        }
        
        .status-upcoming { background: #2ecc71; }
        .status-ongoing { background: #f39c12; }
        .status-completed { background: #95a5a6; }
        
        @keyframes statusPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        }
        
        .month-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
        
        .agenda-detail-modal {
            animation: modalAppear 0.3s ease-out;
        }
        
        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: scale(0.7);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @media (max-width: 768px) {
            .calendar-view {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .agenda-date {
                margin-right: 0;
                margin-bottom: 15px;
                min-width: auto;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 10px;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
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
                        <i class="fas fa-calendar-alt"></i> Kelola Agenda
                    </h1>
                    <p class="mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo SITE_NAME; ?> - Jadwal Kegiatan Desa
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
                <li><a href="agenda.php" class="active"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
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
        // Panggil fungsi display_message jika ada
        if (function_exists('display_message')) {
            display_message();
        } else if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-' . ($_SESSION['message_type'] ?? 'info') . ' alert-dismissible fade show m-3" role="alert">
                    ' . $_SESSION['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        
        <div class="content-area" style="padding: 30px;">
            <?php if ($action == 'tambah' || $action == 'edit'): ?>
                <!-- Form Tambah/Edit Agenda -->
                <?php
                $agenda = null;
                if ($action == 'edit' && $id > 0) {
                    $query = "SELECT * FROM agenda WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $agenda = mysqli_fetch_assoc($result);
                    
                    if (!$agenda) {
                        $_SESSION['message'] = 'Agenda tidak ditemukan!';
                        $_SESSION['message_type'] = 'error';
                        header("Location: agenda.php");
                        exit();
                    }
                }
                ?>
                
                <h2 class="page-title" style="color: var(--primary-color); margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid var(--secondary-color); position: relative;">
                    <i class="fas fa-<?php echo $action == 'tambah' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action == 'tambah' ? 'Tambah Agenda Baru' : 'Edit Agenda'; ?>
                </h2>
                
                <div class="form-container" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <form method="POST" action="agenda.php?action=<?php echo $action; ?><?php echo $action == 'edit' ? '&id=' . $id : ''; ?>">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-heading"></i> Judul Agenda
                            </label>
                            <input type="text" class="form-control" name="judul" 
                                   value="<?php echo htmlspecialchars($agenda['judul'] ?? ''); ?>" 
                                   required placeholder="Masukkan judul agenda">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Deskripsi
                            </label>
                            <textarea class="form-control" name="deskripsi" rows="4" 
                                      placeholder="Deskripsi lengkap agenda..."><?php echo htmlspecialchars($agenda['deskripsi'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-day"></i> Tanggal Mulai
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_mulai" 
                                           value="<?php echo $agenda['tanggal_mulai'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-day"></i> Tanggal Selesai
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_selesai" 
                                           value="<?php echo $agenda['tanggal_selesai'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i> Jam Mulai
                                    </label>
                                    <input type="time" class="form-control" name="jam_mulai" 
                                           value="<?php echo $agenda['jam_mulai'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i> Jam Selesai
                                    </label>
                                    <input type="time" class="form-control" name="jam_selesai" 
                                           value="<?php echo $agenda['jam_selesai'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i> Lokasi
                                    </label>
                                    <input type="text" class="form-control" name="lokasi" 
                                           value="<?php echo htmlspecialchars($agenda['lokasi'] ?? ''); ?>" 
                                           placeholder="Lokasi kegiatan">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i> Jenis Kegiatan
                                    </label>
                                    <select class="form-select" name="jenis" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="rapat" <?php echo ($agenda['jenis'] ?? '') == 'rapat' ? 'selected' : ''; ?>>Rapat</option>
                                        <option value="kegiatan" <?php echo ($agenda['jenis'] ?? '') == 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                        <option value="posyandu" <?php echo ($agenda['jenis'] ?? '') == 'posyandu' ? 'selected' : ''; ?>>Posyandu</option>
                                        <option value="penyuluhan" <?php echo ($agenda['jenis'] ?? '') == 'penyuluhan' ? 'selected' : ''; ?>>Penyuluhan</option>
                                        <option value="lainnya" <?php echo ($agenda['jenis'] ?? '') == 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-users"></i> Peserta/Target
                            </label>
                            <input type="text" class="form-control" name="peserta" 
                                   value="<?php echo htmlspecialchars($agenda['peserta'] ?? ''); ?>" 
                                   placeholder="Contoh: Seluruh warga, Perangkat desa, dll">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-agenda me-2">
                                <i class="fas fa-save"></i> 
                                <?php echo $action == 'tambah' ? 'Simpan Agenda' : 'Update Agenda'; ?>
                            </button>
                            <a href="agenda.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Daftar Agenda -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title" style="color: var(--primary-color); margin: 0; padding-bottom: 15px; border-bottom: 3px solid var(--secondary-color);">
                        <i class="fas fa-calendar-alt"></i> Daftar Agenda Kegiatan
                    </h2>
                    <a href="agenda.php?action=tambah" class="btn btn-agenda">
                        <i class="fas fa-plus"></i> Tambah Agenda Baru
                    </a>
                </div>
                
                <!-- Month Navigation -->
                <div class="month-navigation">
                    <button class="btn btn-outline-primary prev-month">
                        <i class="fas fa-chevron-left"></i> Bulan Sebelumnya
                    </button>
                    <h4 class="mb-0" id="current-month"><?php echo date('F Y'); ?></h4>
                    <button class="btn btn-outline-primary next-month">
                        Bulan Berikutnya <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <!-- Calendar View -->
                <div class="calendar-view mb-5">
                    <?php
                    $today = date('Y-m-d');
                    $currentMonth = date('n');
                    $currentYear = date('Y');
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
                    $firstDay = date('w', strtotime("$currentYear-$currentMonth-01"));
                    
                    // Generate calendar days
                    for ($i = 0; $i < 42; $i++) {
                        $day = $i - $firstDay + 1;
                        $date = "$currentYear-$currentMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        
                        if ($day > 0 && $day <= $daysInMonth) {
                            // Check if there are events on this day
                            $query = "SELECT COUNT(*) as count FROM agenda WHERE DATE(tanggal_mulai) = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "s", $date);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $eventCount = mysqli_fetch_assoc($result)['count'];
                            
                            $isToday = ($date == $today) ? 'today' : '';
                            $hasEvent = ($eventCount > 0) ? 'has-event' : '';
                            
                            echo '<div class="calendar-day ' . $isToday . ' ' . $hasEvent . '" data-date="' . $date . '">';
                            echo '<div class="date">' . $day . '</div>';
                            echo '<small>' . date('D', strtotime($date)) . '</small>';
                            if ($eventCount > 0) {
                                echo '<span class="event-badge">' . $eventCount . '</span>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="calendar-day empty"></div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Agenda List -->
                <div class="timeline-container">
                    <h4 class="mb-4">
                        <i class="fas fa-list-ul"></i> Agenda Mendatang
                        <span class="badge bg-primary ms-2" id="event-count">0</span>
                    </h4>
                    
                    <?php
                    $query = "SELECT * FROM agenda WHERE tanggal_mulai >= CURDATE() ORDER BY tanggal_mulai ASC, jam_mulai ASC";
                    $result = mysqli_query($conn, $query);
                    
                    if (mysqli_num_rows($result) > 0):
                        $eventCounter = 0;
                        while($agenda = mysqli_fetch_assoc($result)):
                            $eventCounter++;
                            $status = '';
                            $now = date('Y-m-d H:i:s');
                            $eventStart = $agenda['tanggal_mulai'] . ' ' . $agenda['jam_mulai'];
                            $eventEnd = $agenda['tanggal_selesai'] . ' ' . $agenda['jam_selesai'];
                            
                            if ($now < $eventStart) {
                                $status = 'upcoming';
                                $statusText = 'Akan Datang';
                            } elseif ($now >= $eventStart && $now <= $eventEnd) {
                                $status = 'ongoing';
                                $statusText = 'Sedang Berlangsung';
                            } else {
                                $status = 'completed';
                                $statusText = 'Selesai';
                            }
                    ?>
                    <div class="timeline-item">
                        <div class="agenda-card">
                            <div class="d-flex align-items-start">
                                <div class="agenda-date me-3">
                                    <div class="day"><?php echo date('d', strtotime($agenda['tanggal_mulai'])); ?></div>
                                    <div class="month"><?php echo date('M', strtotime($agenda['tanggal_mulai'])); ?></div>
                                    <small><?php echo date('Y', strtotime($agenda['tanggal_mulai'])); ?></small>
                                </div>
                                <div style="flex: 1;">
                                    <span class="agenda-type type-<?php echo $agenda['jenis']; ?>">
                                        <?php echo ucfirst($agenda['jenis']); ?>
                                    </span>
                                    <h5 class="mb-2"><?php echo htmlspecialchars($agenda['judul']); ?></h5>
                                    <p class="text-muted mb-2"><?php echo substr(htmlspecialchars($agenda['deskripsi']), 0, 150) . '...'; ?></p>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <div>
                                            <i class="fas fa-clock text-primary"></i>
                                            <small><?php echo date('H:i', strtotime($agenda['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($agenda['jam_selesai'])); ?></small>
                                        </div>
                                        <div>
                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                            <small><?php echo htmlspecialchars($agenda['lokasi']); ?></small>
                                        </div>
                                        <div>
                                            <i class="fas fa-users text-success"></i>
                                            <small><?php echo htmlspecialchars($agenda['peserta']); ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="status-indicator status-<?php echo $status; ?>"></span>
                                            <small class="text-muted"><?php echo $statusText; ?></small>
                                        </div>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary view-detail" 
                                                    data-id="<?php echo $agenda['id']; ?>">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <a href="agenda.php?action=edit&id=<?php echo $agenda['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger delete-agenda" 
                                                    data-id="<?php echo $agenda['id']; ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                        echo '<script>document.getElementById("event-count").textContent = "' . $eventCounter . '";</script>';
                    else: 
                    ?>
                    <div class="text-center py-5">
                        <div class="empty-icon" style="font-size: 60px; color: #ddd; margin-bottom: 20px;">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h4 class="text-muted">Tidak ada agenda mendatang</h4>
                        <p class="text-muted">Mulai dengan menambahkan agenda kegiatan desa</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Modal for Agenda Detail -->
                <div class="modal fade agenda-detail-modal" id="agendaDetailModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                                <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Detail Agenda</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="modalDetailContent">
                                <!-- Content loaded via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        // Inisialisasi date picker
        flatpickr("input[type=date]", {
            dateFormat: "Y-m-d",
            locale: "id"
        });
        
        // Animasi untuk agenda cards
        document.querySelectorAll('.agenda-card').forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
        });
        
        // Calendar day click event
        document.querySelectorAll('.calendar-day.has-event').forEach(day => {
            day.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                alert('Agenda pada tanggal: ' + date);
                // Bisa ditambahkan fungsi untuk menampilkan agenda pada tanggal tersebut
            });
        });
        
        // Agenda detail modal
        document.querySelectorAll('.view-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const agendaId = this.getAttribute('data-id');
                fetchAgendaDetail(agendaId);
            });
        });
        
        function fetchAgendaDetail(id) {
            // Simulasi data - dalam implementasi nyata, ini akan fetch dari server
            const modalContent = document.getElementById('modalDetailContent');
            modalContent.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Untuk demo, gunakan setTimeout
            setTimeout(() => {
                modalContent.innerHTML = `
                    <div class="agenda-detail">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-heading"></i> Judul:</strong>
                                <p>Rapat Koordinasi Perangkat Desa</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag"></i> Jenis:</strong>
                                <span class="badge bg-primary">Rapat</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-day"></i> Tanggal:</strong>
                                <p>15 Desember 2025</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Waktu:</strong>
                                <p>09:00 - 12:00 WIB</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-map-marker-alt"></i> Lokasi:</strong>
                                <p>Balai Desa Lengkong</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-users"></i> Peserta:</strong>
                                <p>Seluruh perangkat desa</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-align-left"></i> Deskripsi:</strong>
                            <p>Rapat koordinasi bulanan untuk membahas program kerja dan evaluasi kegiatan desa. Agenda meliputi laporan keuangan, pembangunan infrastruktur, dan persiapan kegiatan posyandu.</p>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-sticky-note"></i> Catatan:</strong>
                            <p>Mohon membawa data dan laporan masing-masing bidang. Acara akan dimulai tepat waktu.</p>
                        </div>
                    </div>
                `;
            }, 500);
            
            const modal = new bootstrap.Modal(document.getElementById('agendaDetailModal'));
            modal.show();
        }
        
        // Delete confirmation
        document.querySelectorAll('.delete-agenda').forEach(btn => {
            btn.addEventListener('click', function() {
                const agendaId = this.getAttribute('data-id');
                if (confirm('Apakah Anda yakin ingin menghapus agenda ini?')) {
                    window.location.href = 'agenda.php?action=hapus&id=' + agendaId;
                }
            });
        });
        
        // Month navigation
        let currentMonth = <?php echo $currentMonth; ?>;
        let currentYear = <?php echo $currentYear; ?>;
        
        document.querySelector('.prev-month').addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            updateCalendar();
        });
        
        document.querySelector('.next-month').addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            updateCalendar();
        });
        
        function updateCalendar() {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            document.getElementById('current-month').textContent = monthNames[currentMonth - 1] + ' ' + currentYear;
            
            // Di sini Anda bisa menambahkan AJAX untuk update calendar
            // Untuk demo, kita hanya update teks
        }
        
        // Add hover effects
        document.querySelectorAll('.agenda-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>